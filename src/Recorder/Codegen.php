<?php

declare(strict_types=1);

namespace Pest\Browser\Recorder;

use RuntimeException;
use Symfony\Component\Process\Process;

final class Codegen
{
    public function checkDependencies(): void
    {
        $process = new Process(['npx', 'playwright', '--version']);
        $process->run();

        if (! $process->isSuccessful()) {
            $stderr = trim($process->getErrorOutput());
            $hint = $stderr !== '' ? "\n{$stderr}" : '';

            throw new RuntimeException(
                'Playwright not found. Run: npm install -D @playwright/test' . $hint,
            );
        }
    }

    public function record(
        string $url,
        string $outputFile,
        string $testIdAttribute,
        ?string $viewport = null,
        ?string $visitPath = null,
        ?string $loadStorage = null,
        ?string $device = null,
    ): string
    {
        $command = $this->buildCommand(
            url: $url,
            outputFile: $outputFile,
            testIdAttribute: $testIdAttribute,
            viewport: $viewport,
            visitPath: $visitPath,
            saveStorage: null,
            loadStorage: $loadStorage,
            device: $device,
        );

        $process = (new Process($command))->setTimeout(null);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->throwFromStderr($process->getErrorOutput());
        }

        if (! file_exists($outputFile)) {
            return '';
        }

        $content = file_get_contents($outputFile);

        return is_string($content) ? $content : '';
    }

    public function captureAuthState(
        string $url,
        string $storageFile,
        string $loginPath,
        string $testIdAttribute,
        ?string $viewport = null,
    ): void
    {
        $dir = dirname($storageFile);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $tmpOutput = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pest-auth-capture-' . getmypid() . '.jsonl';

        $command = $this->buildCommand(
            url: $url,
            outputFile: $tmpOutput,
            testIdAttribute: $testIdAttribute,
            viewport: $viewport,
            visitPath: $loginPath,
            saveStorage: $storageFile,
        );

        (new Process($command))->setTimeout(null)->run();

        @unlink($tmpOutput);
    }

    /**
     * @return string[]
     */
    private function buildCommand(
        string $url,
        string $outputFile,
        string $testIdAttribute,
        ?string $viewport,
        ?string $visitPath,
        ?string $saveStorage,
        ?string $loadStorage = null,
        ?string $device = null,
    ): array
    {
        $command = [
            'npx', 'playwright', 'codegen',
            '--target=jsonl',
            '--test-id-attribute=' . $testIdAttribute,
            '--output=' . $outputFile,
        ];

        if (! is_null($device)) {
            $command[] = '--device=' . $device;
        } else if (! is_null($viewport)) {
            $command[] = '--viewport-size=' . $viewport;
        }

        if (! is_null($saveStorage)) {
            $command[] = '--save-storage=' . $saveStorage;
        }

        if (! is_null($loadStorage)) {
            $command[] = '--load-storage=' . $loadStorage;
        }

        $command[] = ! is_null($visitPath)
            ? rtrim($url, '/') . '/' . ltrim($visitPath, '/')
            : $url;

        return $command;
    }

    private function throwFromStderr(string $stderr): never
    {
        $stderr = trim($stderr);

        if (str_contains($stderr, "Executable doesn't exist")) {
            throw new RuntimeException('Playwright browsers not installed. Run: npx playwright install');
        }

        throw new RuntimeException($stderr !== '' ? $stderr : 'Recording failed unexpectedly.');
    }
}
