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
        $authName = $this->popArgumentValue('--acting-as', $arguments);
        $viewport = $this->popArgumentValue('--viewport', $arguments);
        $device = $this->popArgumentValue('--device', $arguments);
        $testIdAttribute = $this->popArgumentValue('--test-id-attribute', $arguments) ?? self::DEFAULT_TEST_ID_ATTRIBUTE;
        $env = $this->popArgumentValue('--env', $arguments) ?? 'testing';

        $migrateFresh = $this->hasArgument('--migrate-fresh', $arguments);
        $seed = $this->hasArgument('--seed', $arguments);

        $this->record($url, $visitPath, $authName, $viewport, $device, $testIdAttribute, $env, $migrateFresh, $seed);

        exit(0);
    }

    private function record(
        ?string $url,
        ?string $visitPath,
        ?string $authName,
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

        if (! is_null($authName)) {
            $loadStorage = $this->resolveAuthState($url, $authName, $env);

            if (is_null($loadStorage)) {
                $this->writeLine('<fg=red>✗</> Auth state generation failed.');

                return;
            }
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
            'Windows' => preg_match('/(\d+)\s+(\d+)/', trim($output), $m) ? $m[2] . ',' . ((int) $m[1] - 80) : '1920,1000',
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

    private function resolveAuthState(string $url, string $name, string $env): ?string
    {
        $storageFile = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'pest-auth-' . preg_replace('/[^a-z0-9_-]/i', '-', $name) . '.json';

        $this->writeLine(sprintf("<fg=yellow>●</> Generating auth state for '%s'...", $name));

        try {
            $this->generateAuthState($url, $storageFile, $env);
        } catch (RuntimeException $e) {
            $this->writeLine('<fg=red>✗</> ' . $e->getMessage());

            return null;
        }

        $this->writeLine('<fg=green>✔</> Auth state ready.');

        return $storageFile;
    }

    private function generateAuthState(string $url, string $storageFile, string $env): void
    {
        $rootPath = addslashes($this->testSuite->rootPath);
        $host = parse_url($url, PHP_URL_HOST) ?? '127.0.0.1';
        $cookieName = addslashes(preg_replace('/[^a-z0-9_\-]/i', '_', basename($rootPath)) . '_session');

        $script = <<<PHP
        <?php
        define('LARAVEL_START', microtime(true));
        require '{$rootPath}/vendor/autoload.php';
        \$app = require_once '{$rootPath}/bootstrap/app.php';
        \$kernel = \$app->make(\Illuminate\Contracts\Console\Kernel::class);
        \$kernel->bootstrap();

        \$manager = app('session');
        \$store = \$manager->driver();
        \$store->setId(\Illuminate\Support\Str::random(40));
        \$store->start();

        \$user = \App\Models\User::factory()->create();
        app('auth')->guard()->setUser(\$user);
        \$store->put(app('auth')->guard()->getName(), \$user->getAuthIdentifier());
        \$store->put('password_hash_' . app('auth')->getDefaultDriver(), \$user->getAuthPassword());
        \$store->save();

        \$sessionId = \$store->getId();
        \$cookieName = config('session.cookie');
        \$encrypter = app('encrypter');
        \$prefix = \Illuminate\Cookie\CookieValuePrefix::create(\$cookieName, \$encrypter->getKey());
        \$encrypted = \$encrypter->encrypt(\$prefix . \$sessionId, false);

        echo json_encode([
            'cookies' => [[
                'name'     => \$cookieName,
                'value'    => \$encrypted,
                'domain'   => '{$host}',
                'path'     => '/',
                'expires'  => -1,
                'httpOnly' => true,
                'secure'   => false,
                'sameSite' => 'Lax',
            ]],
            'origins' => [],
        ]);
        PHP;

        $tmpScript = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pest-auth-gen-' . getmypid() . '.php';
        file_put_contents($tmpScript, $script);

        $process = new Process(['php', $tmpScript]);
        $process->setTimeout(30);
        $process->setEnv(['APP_ENV' => $env]);
        $process->run();

        @unlink($tmpScript);

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Auth generation failed: ' . trim($process->getErrorOutput()));
        }

        $output = trim($process->getOutput());

        if ($output === '' || json_decode($output) === null) {
            throw new RuntimeException('Auth generation returned invalid output.');
        }

        file_put_contents($storageFile, $output);
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
