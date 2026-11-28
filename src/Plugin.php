<?php

declare(strict_types=1);

namespace Pest\Browser;

use Error;
use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Enums\ColorScheme;
use Pest\Browser\Enums\TraceMode;
use Pest\Browser\Exceptions\BrowserNotSupportedException;
use Pest\Browser\Exceptions\OptionNotSupportedInParallelException;
use Pest\Browser\Exceptions\TraceModeNotSupportedException;
use Pest\Browser\Filters\UsesBrowserTestCaseMethodFilter;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Playwright\Tracing;
use Pest\Browser\Support\TraceReporter;
use Pest\Contracts\Plugins\Bootable;
use Pest\Contracts\Plugins\HandlesArguments;
use Pest\Contracts\Plugins\Terminable;
use Pest\Plugins\Concerns\HandleArguments;
use Pest\Plugins\Parallel;
use Pest\TestSuite;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Util\ExcludeList;

/**
 * @internal
 */
final class Plugin implements Bootable, HandlesArguments, Terminable // @pest-arch-ignore-line
{
    use HandleArguments;

    /**
     * The parallel global key used to propagate the CLI trace mode to worker processes.
     */
    private const string TRACE_MODE_GLOBAL = 'BROWSER_TRACE_MODE';

    /**
     * Indicates whether the plugin has been booted.
     */
    public static bool $booted = false;

    /**
     * Boots the plugin.
     */
    public function boot(): void
    {
        // Keep the plugin's own frames out of failure traces so a failing
        // browser check is attributed to the user's test file, not vendor code.
        ExcludeList::addDirectory(__DIR__);

        TestSuite::getInstance()
            ->tests
            ->addTestCaseMethodFilter(new UsesBrowserTestCaseMethodFilter());

        pest()->afterEach(function (): void {
            /** @var TestStatus $status */
            $status = $this->status(); // @phpstan-ignore-line

            $failed = $status->isFailure() || $status->isError();

            if (Playwright::shouldDebugAssertions() && $failed && Playwright::hasContexts()) {
                Execution::instance()->debug($status);
            }

            Tracing::stopAll(
                save: Playwright::shouldSaveTrace($failed),
                failed: $failed,
            );

            ServerManager::instance()->http()->flush();

            Playwright::reset();
        })->in($this->in());
    }

    /**
     * Handles the arguments passed to the plugin.
     *
     * @param  array<int, string>  $arguments
     */
    public function handleArguments(array $arguments): array
    {
        if ($this->hasArgument('--headed', $arguments)) {
            Playwright::headed();

            $arguments = $this->popArgument('--headed', $arguments);
        }

        if ($this->hasArgument('--diff', $arguments)) {
            Playwright::setShouldDiffOnScreenshotAssertions();

            $arguments = $this->popArgument('--diff', $arguments);
        }

        if ($this->hasArgument('--debug', $arguments)) {
            Playwright::setShouldDebugAssertions();

            $arguments = $this->popArgument('--debug', $arguments);
        }

        if ($this->hasArgument('--trace', $arguments)) {
            $arguments = $this->handleTraceArgument($arguments);
        } elseif (Parallel::isWorker()) {
            $persistedMode = Parallel::getGlobal(self::TRACE_MODE_GLOBAL);

            if (is_string($persistedMode) && ($mode = TraceMode::tryFrom($persistedMode)) instanceof TraceMode) {
                Playwright::setShouldTrace(mode: $mode);
            }
        }

        if ($this->hasArgument('--dark', $arguments)) {
            Playwright::setColorScheme(ColorScheme::DARK);

            $arguments = $this->popArgument('--dark', $arguments);
        }

        if ($this->hasArgument('--light', $arguments)) {
            Playwright::setColorScheme(ColorScheme::LIGHT);

            $arguments = $this->popArgument('--light', $arguments);
        }

        if ($this->hasArgument('--browser', $arguments)) {
            $index = array_search('--browser', $arguments, true);

            if ($index === false || ! isset($arguments[$index + 1])) {
                throw new BrowserNotSupportedException(
                    'The "--browser" argument requires a value. Usage: --browser <browser-type> (e.g., chrome, firefox, webkit).'
                );
            }

            $browser = $arguments[$index + 1];

            if (($browser = BrowserType::tryFrom($browser)) === null) {
                throw new BrowserNotSupportedException(
                    'The specified browser type is not supported. Supported types are: '.
                    implode(', ', array_map(fn (BrowserType $type): string => mb_strtolower($type->name), BrowserType::cases()))
                );
            }

            Playwright::setDefaultBrowserType($browser);

            unset($arguments[$index], $arguments[$index + 1]);

            $arguments = array_values($arguments);
        }

        if ($this->hasArgument('--slow-mo', $arguments)) {
            $index = array_search('--slow-mo', $arguments, true);

            $value = $index === false
                ? $this->popArgumentValue('--slow-mo', $arguments)
                : $arguments[$index + 1] ?? null;

            if ($index !== false && is_numeric($value)) {
                unset($arguments[$index + 1]);
            }

            Playwright::setSlowMo(is_numeric($value) ? (int) $value : Playwright::DEFAULT_SLOW_MO);

            $arguments = $this->popArgument('--slow-mo', $arguments);
        }

        $this->validateNonSupportedParallelFeatures();

        return $arguments;
    }

    /**
     * Terminates the plugin.
     */
    public function terminate(): void
    {
        try {
            if (Parallel::isWorker() || Parallel::isEnabled() === false) {
                ServerManager::instance()->http()->stop();

                Playwright::close();
            }

            if (Parallel::isWorker() === false) {
                ServerManager::instance()->playwright()->stop();

                // Only browser tests clean up the traces of previous runs, so they are reported only when they ran.
                if (self::$booted && Playwright::shouldTrace()) {
                    TraceReporter::report(Playwright::shouldOpenTraces(), Playwright::traceMode());
                }
            }
        } catch (Error $e) {
            if ($e->getMessage() === 'Must call resume() or throw() before calling suspend() again') {
                return;
            }

            throw $e;
        }
    }

    /**
     * Parses and removes all `--trace` arguments, applying the resolved trace mode.
     *
     * @param  array<int, string>  $arguments
     * @return array<int, string>
     */
    private function handleTraceArgument(array $arguments): array
    {
        $arguments = array_values($arguments);
        $remaining = [];
        $modes = [];
        $count = count($arguments);

        for ($i = 0; $i < $count; $i++) {
            $argument = $arguments[$i];

            if ($argument === '--trace') {
                $next = $arguments[$i + 1] ?? null;
                $parsed = $next !== null ? TraceMode::tryFrom($next) : null;

                if ($parsed instanceof TraceMode) {
                    $modes[] = $parsed;
                    $i++;

                    continue;
                }

                $modes[] = null;

                continue;
            }

            if (str_starts_with($argument, '--trace=')) {
                $value = mb_substr($argument, mb_strlen('--trace='));
                $modes[] = TraceMode::tryFrom($value) ?? throw new TraceModeNotSupportedException(
                    'The specified trace mode is not supported. Supported modes are: '.
                    implode(', ', array_map(fn (TraceMode $mode): string => $mode->value, TraceMode::cases())).'.'
                );

                continue;
            }

            $remaining[] = $argument;
        }

        foreach ($modes as $mode) {
            Playwright::setShouldTrace(mode: $mode);
        }

        if ($this->hasArgument('--parallel', $remaining) || $this->hasArgument('-p', $remaining)) {
            Parallel::setGlobal(self::TRACE_MODE_GLOBAL, Playwright::traceMode()->value);
        }

        return $remaining;
    }

    /**
     * Returns the path where the test files are located.
     */
    private function in(): string
    {
        return TestSuite::getInstance()->rootPath.DIRECTORY_SEPARATOR.TestSuite::getInstance()->testPath;
    }

    /**
     * Validates that non-supported features are not used when running tests in parallel.
     *
     * @throws OptionNotSupportedInParallelException
     */
    private function validateNonSupportedParallelFeatures(): void
    {
        if (Parallel::isEnabled() === false) {
            return;
        }

        if (Playwright::isHeadless() === false) {
            throw new OptionNotSupportedInParallelException(
                'Running tests in headed mode is not supported when running tests in parallel.',
            );
        }

        if (Playwright::shouldShowDiffOnScreenshotAssertions()) {
            throw new OptionNotSupportedInParallelException(
                'Showing the diff on screenshot assertions is not supported when running tests in parallel.',
            );
        }

        if (Playwright::shouldDebugAssertions()) {
            throw new OptionNotSupportedInParallelException(
                'Debugging assertions is not supported when running tests in parallel.',
            );
        }

        if (Playwright::slowMo() > 0) {
            throw new OptionNotSupportedInParallelException(
                'Running tests in slow motion is not supported when running tests in parallel.',
            );
        }
    }
}
