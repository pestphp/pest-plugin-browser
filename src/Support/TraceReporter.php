<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use Pest\Support\Ci;
use Pest\Support\Container;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 *
 * @codeCoverageIgnore This class is used at plugin level, once the test suite has finished.
 */
final class TraceReporter
{
    /**
     * Reports the traces saved during the test run: on CI, they are listed on the job's summary,
     * while locally, the first one is opened on the trace viewer.
     */
    public static function report(bool $open): void
    {
        $traces = Trace::saved();

        if ($traces === []) {
            return;
        }

        if (Ci::isRunning()) {
            self::summarize($traces);

            return;
        }

        if ($open) {
            self::open($traces);
        }
    }

    /**
     * Lists the given traces on the GitHub Actions' job summary, if available.
     *
     * @param  array<int, string>  $traces
     */
    private static function summarize(array $traces): void
    {
        $summary = getenv('GITHUB_STEP_SUMMARY');

        if (! is_string($summary) || $summary === '') {
            return;
        }

        $markdown = "### Browser test traces\n\n"
            .'The following failed browser tests recorded a trace. Upload the `tests/Browser/Traces` directory as an artifact, '
            .'then drop a trace on [trace.playwright.dev](https://trace.playwright.dev), or run `npx playwright show-trace <trace>`.'
            ."\n\n"
            .implode("\n", array_map(fn (string $trace): string => '- `'.Trace::relative($trace).'`', $traces))
            ."\n";

        file_put_contents($summary, $markdown, FILE_APPEND);
    }

    /**
     * Opens the first of the given traces on the trace viewer, without waiting for it to be closed.
     *
     * @param  array<int, string>  $traces
     */
    private static function open(array $traces): void
    {
        $directory = PackageJsonDirectory::find();
        $binary = $directory.DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR.'.bin'.DIRECTORY_SEPARATOR.'playwright';

        $command = escapeshellarg($binary).' show-trace '.escapeshellarg($traces[0]);

        if (PHP_OS_FAMILY === 'Windows') {
            $handle = popen('start "" /B '.$command.' > NUL 2>&1', 'r');

            if ($handle !== false) {
                pclose($handle);
            }
        } else {
            exec('nohup '.$command.' > /dev/null 2>&1 &');
        }

        $count = count($traces);

        $lines = [
            $count === 1
                ? '  <fg=white;options=bold;bg=blue> INFO </> Opening the trace of the failed test.'
                : "  <fg=white;options=bold;bg=blue> INFO </> Opening the first of the {$count} traces of the failed tests.",
            '',
        ];

        foreach ($traces as $index => $trace) {
            $lines[] = $index === 0
                ? '  <fg=green>➜</> '.Trace::relative($trace)
                : '  <fg=gray>-</> '.Trace::relative($trace);
        }

        if ($count > 1) {
            $lines[] = '';
            $lines[] = '  <fg=gray>You may open the others with:</> npx playwright show-trace <trace>';
        }

        $lines[] = '';

        // @phpstan-ignore-next-line
        Container::getInstance()->get(OutputInterface::class)->writeln($lines);
    }
}
