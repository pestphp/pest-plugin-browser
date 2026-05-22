<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Recorder\Codegen;
use Pest\Browser\Recorder\EventParser;
use Pest\Browser\Recorder\EventSanitizer;
use Pest\Browser\Recorder\TestGenerator;
use Pest\Browser\Recorder\TestWriter;
use Pest\Browser\Support\Port;
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

        $url = $this->popArgumentValue('--url', $arguments);
        $visitPath = $this->popArgumentValue('--visit', $arguments);
        $auth = $this->hasArgument('--auth', $arguments) || $this->hasArgument('--user', $arguments);
        $arguments = $this->hasArgument('--auth', $arguments) ? $this->popArgument('--auth', $arguments) : $arguments;
        $arguments = $this->hasArgument('--user', $arguments) ? $this->popArgument('--user', $arguments) : $arguments;
        $viewport = $this->popArgumentValue('--viewport', $arguments);
        $device = $this->popArgumentValue('--device', $arguments);
        $testIdAttribute = $this->popArgumentValue('--test-id-attribute', $arguments) ?? self::DEFAULT_TEST_ID_ATTRIBUTE;
        $env = $this->popArgumentValue('--env', $arguments) ?? 'testing';

        $authScript = $this->popArgumentValue('--auth-script', $arguments);

        $migrateFresh = $this->hasArgument('--migrate-fresh', $arguments);
        if ($migrateFresh) {
            $arguments = $this->popArgument('--migrate-fresh', $arguments);
        }

        $seed = $this->hasArgument('--seed', $arguments);
        if ($seed) {
            $arguments = $this->popArgument('--seed', $arguments);
        }

        $this->record($url, $visitPath, $auth, $authScript, $viewport, $device, $testIdAttribute, $env, $migrateFresh, $seed);

        exit(0);
    }

    private function record(
        ?string $url,
        ?string $visitPath,
        bool $auth,
        ?string $authScript,
        ?string $viewport,
        ?string $device,
        string $testIdAttribute,
        string $env = 'testing',
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

        if ($seed && ! $migrateFresh) {
            $this->writeLine('<fg=yellow>⚠</> --seed has no effect without --migrate-fresh.');
        }

        if ($migrateFresh) {
            try {
                $this->migrateFresh($env, $seed);
            } catch (RuntimeException $e) {
                $this->writeLine('<fg=red>✗</> ' . $e->getMessage());

                return;
            }
        }

        if (is_null($viewport) && is_null($device)) {
            $viewport = $this->detectScreenResolution();
        }

        $serverProcess = null;

        if (is_null($url)) {
            $port = Port::find();
            $url = sprintf('http://127.0.0.1:%d', $port);
            $serverProcess = $this->startServer($url, $env);
        }

        $loadStorage = null;
        $userModelClass = null;

        if ($auth) {
            $auth = $this->resolveAuthState($url, $env, $authScript);

            if (is_null($auth)) {
                $this->writeLine('<fg=red>✗</> Auth state generation failed.');

                return;
            }

            [$loadStorage, $userModelClass] = $auth;
        }

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

            $writer = new TestWriter;
            $title = $this->prompt('Test description');
            $outputPath = $this->resolveOutputPath($writer);
            $code = (new TestGenerator($testIdAttribute))->generate($events, $title, $url, $userModelClass);

            $writer->write($outputPath, $code, ! is_null($userModelClass));

            $this->writeLine(sprintf('<fg=green>✔</> Test written: %s', $outputPath));
        } catch (RuntimeException $e) {
            $this->writeLine('<fg=red>✗</> ' . $e->getMessage());
        } finally {
            @unlink($tmpFile);
            if (! is_null($loadStorage)) {
                @unlink($loadStorage);
            }
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

        $process->stop(3);

        throw new RuntimeException('Dev server failed to start within 10 seconds.');
    }

    private function detectScreenResolution(): string
    {
        $output = match (PHP_OS_FAMILY) {
            'Linux' => shell_exec('xrandr --current 2>/dev/null | grep -m1 " connected" | grep -oP "\d+x\d+"'),
            'Darwin' => shell_exec('system_profiler SPDisplaysDataType 2>/dev/null | grep -m1 "Resolution"'),
            'Windows' => shell_exec('wmic desktopmonitor get screenwidth,screenheight 2>nul'),
            default => null,
        };

        if ($output === null || $output === '') {
            return '1920,1000';
        }

        return match (PHP_OS_FAMILY) {
            'Linux' => preg_match('/(\d+)x(\d+)/', trim($output), $m) ? $m[1] . ',' . ((int) $m[2] - 80) : '1920,1000',
            'Darwin' => preg_match('/(\d+) x (\d+)/', $output, $m) ? $m[1] . ',' . ((int) $m[2] - 80) : '1920,1000',
            'Windows' => preg_match('/(\d+)\s+(\d+)/', trim($output), $m) ? $m[1] . ',' . ((int) $m[2] - 80) : '1920,1000',
            default => '1920,1000',
        };
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

    /**
     * @return array{string, ?string}|null
     */
    private function resolveAuthState(string $url, string $env, ?string $authScript): ?array
    {
        $storageFile = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'pest-auth-' . getmypid() . '.json';

        $this->writeLine('<fg=yellow>●</> Generating auth state...');

        try {
            $userModelClass = $this->generateAuthState($url, $storageFile, $env, $authScript);
        } catch (RuntimeException $e) {
            $this->writeLine('<fg=red>✗</> ' . $e->getMessage());

            return null;
        }

        $this->writeLine('<fg=green>✔</> Auth state ready.');

        return [$storageFile, $userModelClass];
    }

    private function generateAuthState(string $url, string $storageFile, string $env, ?string $authScript): ?string
    {
        $host = preg_replace('/[^a-zA-Z0-9._\-\[\]:]/', '', parse_url($url, PHP_URL_HOST) ?? '127.0.0.1');

        $scriptPath = $this->resolveAuthScriptPath($authScript);

        $process = new Process(['php', $scriptPath, $this->testSuite->rootPath, $host, $storageFile]);
        $process->setTimeout(30);
        $process->setEnv(['APP_ENV' => $env]);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Auth generation failed: ' . trim($process->getErrorOutput()));
        }

        $userModelClass = trim($process->getOutput());

        return $userModelClass !== '' ? $userModelClass : null;
    }

    private function resolveAuthScriptPath(?string $customScript): string
    {
        if (! is_null($customScript)) {
            return $customScript;
        }

        if (! file_exists($this->testSuite->rootPath . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php')) {
            throw new RuntimeException(
                '--auth requires a Laravel application. For other frameworks, provide a custom bootstrap script with --auth-script=path/to/auth.php',
            );
        }

        return __DIR__ . DIRECTORY_SEPARATOR . 'Recorder' . DIRECTORY_SEPARATOR . 'Laravel' . DIRECTORY_SEPARATOR . 'auth-gen.php';
    }

    private function resolveOutputPath(TestWriter $writer): string
    {
        $testsDir = $this->testSuite->rootPath . DIRECTORY_SEPARATOR . $this->testSuite->testPath . DIRECTORY_SEPARATOR . 'Browser';
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
