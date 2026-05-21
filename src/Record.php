<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Recorder\Codegen;
use Pest\Browser\Recorder\EventParser;
use Pest\Browser\Recorder\EventSanitizer;
use Pest\Browser\Recorder\TestGenerator;
use Pest\Browser\Recorder\TestWriter;
use Pest\Contracts\Plugins\HandlesArguments;
use Pest\Plugins\Concerns\HandleArguments;
use Pest\TestSuite;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class Record implements HandlesArguments
{
    use HandleArguments;

    private const string OPTION = '--record';

    private const string DEFAULT_TEST_ID_ATTRIBUTE = 'id';

    public function __construct(
        private readonly OutputInterface $output,
        private readonly TestSuite $testSuite,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function handleArguments(array $arguments): array
    {
        if (! $this->hasArgument(self::OPTION, $arguments)) {
            return $arguments;
        }

        $arguments = $this->popArgument(self::OPTION, $arguments);

        $url = $this->popArgumentValue('--url', $arguments) ?? $this->resolveAppUrl();
        $visitPath = $this->popArgumentValue('--visit', $arguments);
        $authName = $this->popArgumentValue('--acting-as', $arguments);
        $viewport = $this->popArgumentValue('--viewport', $arguments);
        $device = $this->popArgumentValue('--device', $arguments);
        $testIdAttribute = $this->popArgumentValue('--test-id-attribute', $arguments) ?? self::DEFAULT_TEST_ID_ATTRIBUTE;
        $env = $this->popArgumentValue('--env', $arguments) ?? 'local';

        $server = $this->hasArgument('--server', $arguments);
        if ($server) {
            $arguments = $this->popArgument('--server', $arguments);
        }

        $migrateFresh = $this->hasArgument('--migrate-fresh', $arguments);
        if ($migrateFresh) {
            $arguments = $this->popArgument('--migrate-fresh', $arguments);
        }

        $seed = $this->hasArgument('--seed', $arguments);
        if ($seed) {
            $arguments = $this->popArgument('--seed', $arguments);
        }

        $this->record($url, $visitPath, $authName, $viewport, $device, $testIdAttribute, $server, $env, $migrateFresh, $seed);

        exit(0);
    }

    private function record(
        string $url,
        ?string $visitPath,
        ?string $authName,
        ?string $viewport,
        ?string $device,
        string $testIdAttribute,
        bool $server = false,
        string $env = 'local',
        bool $migrateFresh = false,
        bool $seed = false,
    ): void
    {
        $codegen = new Codegen;

        try {
            $codegen->checkDependencies();
        } catch (RuntimeException $e) {
            $this->writeLine('<fg=red>✗</> ' . $e->getMessage());

            return;
        }

        if ($migrateFresh) {
            try {
                $this->migrateFresh($env, $seed);
            } catch (RuntimeException $e) {
                $this->writeLine('<fg=red>✗</> ' . $e->getMessage());

                return;
            }
        }

        $serverProcess = $server ? $this->startServer($url, $env) : null;

        $loadStorage = ! is_null($authName)
            ? $this->resolveAuthState($codegen, $url, $authName, $viewport, $testIdAttribute)
            : null;

        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pest-recording-' . getmypid() . '.jsonl';

        try {
            $this->writeLine('<fg=yellow>●</> Recorder running — close the browser to finish...');

            $jsonl = $codegen->record($url, $tmpFile, $testIdAttribute, $viewport, $visitPath, $loadStorage, $device);

            if ($jsonl === '') {
                $this->writeLine('<fg=red>✗</> No recording captured.');

                return;
            }

            $events = (new EventParser)->parse($jsonl);
            $events = (new EventSanitizer($testIdAttribute))->sanitize($events);

            if ($events === []) {
                $this->writeLine('<fg=red>✗</> No mappable actions recorded.');

                return;
            }

            $title = $this->prompt('Test description');
            $outputPath = $this->resolveOutputPath();
            $code = (new TestGenerator($testIdAttribute))->generate($events, $title, $url, ! is_null($authName));

            (new TestWriter)->write($outputPath, $code);

            $this->writeLine(sprintf('<fg=green>✔</> Test written: %s', $outputPath));
        } catch (RuntimeException $e) {
            $this->writeLine('<fg=red>✗</> ' . $e->getMessage());
        } finally {
            @unlink($tmpFile);
            $serverProcess?->stop(3);
        }
    }

    private function startServer(string $url, string $env): Process
    {
        $port = (int) (parse_url($url, PHP_URL_PORT) ?? 8000);

        $process = new Process(['php', 'artisan', 'serve', '--port=' . $port, '--env=' . $env]);
        $process->setTimeout(null);
        $process->start();

        $deadline = time() + 10;

        while (time() < $deadline) {
            usleep(200_000);
            $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);

            if ($connection !== false) {
                fclose($connection);
                $this->writeLine('<fg=green>✔</> Dev server started at ' . $url);

                return $process;
            }
        }

        $this->writeLine('<fg=yellow>●</> Server may not be ready yet, proceeding...');

        return $process;
    }

    private function migrateFresh(string $env, bool $seed): void
    {
        $this->writeLine('<fg=yellow>●</> Running migrate:fresh...');

        $command = ['php', 'artisan', 'migrate:fresh', '--env=' . $env, '--force'];

        if ($seed) {
            $command[] = '--seed';
        }

        $process = new Process($command);
        $process->setTimeout(null);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('migrate:fresh failed: ' . trim($process->getErrorOutput()));
        }

        $this->writeLine('<fg=green>✔</> Database ready.');
    }

    private function resolveAuthState(
        Codegen $codegen,
        string $url,
        string $name,
        ?string $viewport,
        string $testIdAttribute,
    ): ?string
    {
        $storageFile = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'pest-auth-' . preg_replace('/[^a-z0-9_-]/i', '-', $name) . '.json';

        if (! file_exists($storageFile)) {
            $this->writeLine(sprintf(
                "<fg=yellow>●</> No auth state found for '%s'. Record a login sequence to save it.",
                $name,
            ));

            try {
                $codegen->captureAuthState($url, $storageFile, '/login', $testIdAttribute, $viewport);
            } catch (RuntimeException $e) {
                $this->writeLine('<fg=red>✗</> ' . $e->getMessage());

                return null;
            }
        }

        return file_exists($storageFile) ? $storageFile : null;
    }

    private function resolveOutputPath(): string
    {
        $testsDir = $this->testSuite->rootPath . DIRECTORY_SEPARATOR . $this->testSuite->testPath . DIRECTORY_SEPARATOR . 'Browser';
        $writer = new TestWriter;
        $existing = $writer->findExistingTestFiles($testsDir);

        if ($existing !== []) {
            $choices = array_merge(
                ['New file...'],
                array_map(
                    fn(string $path): string => ltrim(str_replace($testsDir, '', $path), DIRECTORY_SEPARATOR),
                    $existing,
                ),
            );

            $choice = $this->choose('Output file', $choices);

            if ($choice !== 'New file...') {
                return $testsDir . DIRECTORY_SEPARATOR . $choice;
            }
        }

        $name = ucfirst(str_replace(['.php', 'Test.php'], '', $this->prompt('Test file name')));

        return $testsDir . DIRECTORY_SEPARATOR . $name . 'Test.php';
    }

    private function resolveAppUrl(): string
    {
        return $_ENV['APP_URL']
            ?? $_SERVER['APP_URL']
            ?? (getenv('APP_URL') ?: null)
            ?? $this->readDotEnvValue('APP_URL')
            ?? 'http://localhost:8000';
    }

    private function readDotEnvValue(string $key): ?string
    {
        $envFile = $this->testSuite->rootPath.DIRECTORY_SEPARATOR.'.env';

        if (! file_exists($envFile)) {
            return null;
        }

        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(ltrim($line), '#')) {
                continue;
            }

            if (str_starts_with($line, $key.'=')) {
                return trim(substr($line, strlen($key) + 1), '"\'');
            }
        }

        return null;
    }

    private function prompt(string $question): string
    {
        $this->output->write("  <fg=blue>?</> {$question}: ");

        $answer = fgets(STDIN);

        return ($answer !== false && trim($answer) !== '') ? trim($answer) : 'Untitled';
    }

    private function choose(string $question, array $choices): string
    {
        $this->output->writeln("  <fg=blue>?</> {$question}:");

        foreach ($choices as $index => $choice) {
            $this->output->writeln("    [{$index}] {$choice}");
        }

        $this->output->write('  Choice: ');

        $input = fgets(STDIN);
        $index = ($input !== false && is_numeric(trim($input))) ? (int) trim($input) : 0;

        return $choices[$index] ?? $choices[0];
    }

    private function writeLine(string $message): void
    {
        $this->output->writeln("  {$message}");
    }
}
