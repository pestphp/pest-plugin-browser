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

    /**
     * @return string[]
     */
    private function buildCommand(
        string $url,
        string $outputFile,
        string $testIdAttribute,
        ?string $viewport,
        ?string $visitPath,
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

        $flag = match (true) {
            ! is_null($device) => '--device=' . $device,
            ! is_null($viewport) => '--viewport-size=' . $viewport,
            default => null,
        };

        if (! is_null($flag)) {
            $command[] = $flag;
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
