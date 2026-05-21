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

/**
 * @internal
 */
final class Record implements HandlesArguments
{
    use HandleArguments;

    private const string OPTION = '--record';

    private const string DEFAULT_TEST_ID_ATTRIBUTE = 'data-test';

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

        $url = $this->popArgumentValue('--url', $arguments) ?? 'http://localhost:8000';
        $visitPath = $this->popArgumentValue('--visit', $arguments);
        $authName = $this->popArgumentValue('--acting-as', $arguments);
        $viewport = $this->popArgumentValue('--viewport', $arguments);
        $device = $this->popArgumentValue('--device', $arguments);
        $testIdAttribute = $this->popArgumentValue('--test-id-attribute', $arguments) ?? self::DEFAULT_TEST_ID_ATTRIBUTE;

        $this->record($url, $visitPath, $authName, $viewport, $device, $testIdAttribute);

        exit(0);
    }

    private function record(
        string $url,
        ?string $visitPath,
        ?string $authName,
        ?string $viewport,
        ?string $device,
        string $testIdAttribute,
    ): void
    {
        $codegen = new Codegen;

        try {
            $codegen->checkDependencies();
        } catch (RuntimeException $e) {
            $this->writeLine('<fg=red>✗</> ' . $e->getMessage());

            return;
        }

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
            $code = (new TestGenerator($testIdAttribute))->generate($events, $title, $url);

            (new TestWriter)->write($outputPath, $code);

            $this->writeLine(sprintf('<fg=green>✔</> Test written: %s', $outputPath));
        } catch (RuntimeException $e) {
            $this->writeLine('<fg=red>✗</> ' . $e->getMessage());
        } finally {
            @unlink($tmpFile);
        }
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
