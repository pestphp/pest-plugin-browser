<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use Pest\Browser\Enums\TraceMode;
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
     * while locally, the first failed trace is opened on the trace viewer, or saved passing traces are listed.
     */
    public static function report(bool $open, TraceMode $mode = TraceMode::RETAIN_ON_FAILURE): void
    {
        $traces = Trace::saved();

        if ($traces === []) {
            return;
        }

        if (Ci::isRunning()) {
            self::summarize($traces, $mode);

            return;
        }

        $failed = $mode === TraceMode::RETAIN_ON_FAILURE
            ? $traces
            : Trace::failed();

        if ($failed !== []) {
            if ($open) {
                self::open($failed, $traces);
            }

            return;
        }

        self::listSaved($traces);
    }

    /**
     * Lists the given traces on the GitHub Actions' job summary, if available.
     *
     * @param  array<int, string>  $traces
     */
    private static function summarize(array $traces, TraceMode $mode): void
    {
        $summary = getenv('GITHUB_STEP_SUMMARY');

        if (! is_string($summary) || $summary === '') {
            return;
        }

        $intro = $mode === TraceMode::RETAIN_ON_FAILURE
            ? 'The following failed browser tests recorded a trace. '
            : 'The following browser tests recorded a trace. ';

        $markdown = "### Browser test traces\n\n"
            .$intro
            .'Upload the `tests/Browser/Traces` directory as an artifact, '
            .'then drop a trace on [trace.playwright.dev](https://trace.playwright.dev), or run `npx playwright show-trace <trace>`.'
            ."\n\n"
            .implode("\n", array_map(fn (string $trace): string => '- `'.Trace::relative($trace).'`', $traces))
            ."\n";

        file_put_contents($summary, $markdown, FILE_APPEND);
    }

    /**
     * Opens the first of the given failed traces on the trace viewer, without waiting for it to be closed.
     *
     * @param  array<int, string>  $failed
     * @param  array<int, string>  $all
     */
    private static function open(array $failed, array $all): void
    {
        $directory = PackageJsonDirectory::find();
        $binary = $directory.DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR.'.bin'.DIRECTORY_SEPARATOR.'playwright';

        $command = escapeshellarg($binary).' show-trace '.escapeshellarg($failed[0]);

        if (PHP_OS_FAMILY === 'Windows') {
            $handle = popen('start "" /B '.$command.' > NUL 2>&1', 'r');

            if ($handle !== false) {
                pclose($handle);
            }
        } else {
            exec('nohup '.$command.' > /dev/null 2>&1 &');
        }

        $count = count($failed);

        $lines = [
            $count === 1
                ? '  <fg=white;options=bold;bg=blue> INFO </> Opening the trace of the failed test.'
                : "  <fg=white;options=bold;bg=blue> INFO </> Opening the first of the {$count} traces of the failed tests.",
            '',
        ];

        foreach ($failed as $index => $trace) {
            $lines[] = $index === 0
                ? '  <fg=green>➜</> '.Trace::relative($trace)
                : '  <fg=gray>-</> '.Trace::relative($trace);
        }

        $passedCount = count($all) - $count;

        if ($passedCount > 0) {
            $lines[] = '';
            $lines[] = $passedCount === 1
                ? '  <fg=gray>1 passing test trace was also saved to</> '.Trace::relative(Trace::dir()).'.'
                : "  <fg=gray>{$passedCount} passing test traces were also saved to</> ".Trace::relative(Trace::dir()).'.';
        }

        if ($count > 1 || $passedCount > 0) {
            $lines[] = '';
            $lines[] = '  <fg=gray>You may open the others with:</> npx playwright show-trace <trace>';
        }

        $lines[] = '';

        // @phpstan-ignore-next-line
        Container::getInstance()->get(OutputInterface::class)->writeln($lines);
    }

    /**
     * Lists the saved traces locally when no tests failed.
     *
     * @param  array<int, string>  $traces
     */
    private static function listSaved(array $traces): void
    {
        $count = count($traces);
        $dir = Trace::relative(Trace::dir());

        $lines = [
            $count === 1
                ? "  <fg=white;options=bold;bg=blue> INFO </> Saved 1 trace to {$dir}."
                : "  <fg=white;options=bold;bg=blue> INFO </> Saved {$count} traces to {$dir}.",
            '',
        ];

        foreach ($traces as $trace) {
            $lines[] = '  <fg=gray>-</> '.Trace::relative($trace);
        }

        $lines[] = '';
        $lines[] = '  <fg=gray>You may open a trace with:</> npx playwright show-trace <trace>';
        $lines[] = '';

        // @phpstan-ignore-next-line
        Container::getInstance()->get(OutputInterface::class)->writeln($lines);
    }
}
