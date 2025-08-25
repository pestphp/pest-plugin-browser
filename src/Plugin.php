<?php

declare(strict_types=1);

namespace Pest\Browser;

use Error;
use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Enums\ColorScheme;
use Pest\Browser\Exceptions\BrowserNotSupportedException;
use Pest\Browser\Exceptions\HttpServerConfigurationException;
use Pest\Browser\Exceptions\OptionNotSupportedInParallelException;
use Pest\Browser\Filters\UsesBrowserTestCaseMethodFilter;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Support\PersistHttpServer;
use Pest\Browser\Support\PersistPlaywrightServer;
use Pest\Browser\Support\Port;
use Pest\Contracts\Plugins\Bootable;
use Pest\Contracts\Plugins\HandlesArguments;
use Pest\Contracts\Plugins\Terminable;
use Pest\Plugins\Concerns\HandleArguments;
use Pest\Plugins\Parallel;
use Pest\TestSuite;
use PHPUnit\Framework\TestStatus\TestStatus;

/**
 * @internal
 */
final class Plugin implements Bootable, HandlesArguments, Terminable // @pest-arch-ignore-line
{
    use HandleArguments;

    /**
     * Indicates whether the plugin has been booted.
     */
    public static bool $booted = false;

    /**
     * Boots the plugin.
     */
    public function boot(): void
    {
        TestSuite::getInstance()
            ->tests
            ->addTestCaseMethodFilter(new UsesBrowserTestCaseMethodFilter());

        pest()->afterEach(function (): void {
            if (Playwright::shouldDebugAssertions()) {
                /** @var TestStatus $status */
                $status = $this->status(); // @phpstan-ignore-line

                if ($status->isFailure() || $status->isError()) {
                    Execution::instance()->debug($status);
                }
            }

            ServerManager::instance()->http()->flush();

            Playwright::reset();
        })->in($this->in());
    }

    /**
     * Handles the arguments passed to the plugin.
     *
     * @param  array<int, string>  $arguments}
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

        if ($this->hasArgument('--dark', $arguments)) {
            Playwright::setColorScheme(ColorScheme::DARK);

            $arguments = $this->popArgument('--dark', $arguments);
        }

        if ($this->hasArgument('--light', $arguments)) {
            Playwright::setColorScheme(ColorScheme::LIGHT);

            $arguments = $this->popArgument('--light', $arguments);
        }

        $arguments = $this->handleHttpServerArguments($arguments);

        $arguments = $this->handlePlaywrightServerArguments($arguments);

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
            }
        } catch (Error $e) {
            if ($e->getMessage() === 'Must call resume() or throw() before calling suspend() again') {
                return;
            }

            throw $e;
        }
    }

    /**
     * Returns the path where the test files are located.
     */
    private function in(): string
    {
        return TestSuite::getInstance()->rootPath.DIRECTORY_SEPARATOR.TestSuite::getInstance()->testPath;
    }

    /**
     * Handles the HTTP server arguments.
     */
    private function handleHttpServerArguments(array $arguments): array
    {
        if (! Parallel::isWorker()) {
            PersistHttpServer::cleanup();
        }
        foreach ($arguments as $key => $argument) {
            if (str_starts_with($argument, '--http-host=')) {
                $host = mb_substr($argument, mb_strlen('--http-host='));
                PersistHttpServer::$host = $host;
                unset($arguments[$key]);
            }
            if (str_starts_with($argument, '--http-bind=')) {
                $bindAddress = mb_substr($argument, mb_strlen('--http-bind='));
                PersistHttpServer::$bindAddress = $bindAddress;
                unset($arguments[$key]);
            }
        }
        PersistHttpServer::persistIfNeeded();

        return $arguments;
    }

    /**
     * Handles the Playwright server arguments.
     */
    private function handlePlaywrightServerArguments(array $arguments): array
    {
        foreach ($arguments as $key => $argument) {
            if (str_starts_with($argument, '--playwright-host=')) {
                $host = mb_substr($argument, mb_strlen('--playwright-host='));
                PersistPlaywrightServer::$host = $host;
                unset($arguments[$key]);
            }
            if (str_starts_with($argument, '--playwright-port=')) {
                $port = mb_substr($argument, mb_strlen('--playwright-port='));
                if (! Port::isValid($port)) {
                    throw HttpServerConfigurationException::portIsNotValid($port);
                }
                PersistPlaywrightServer::$port = (int) $port;
                unset($arguments[$key]);
            }
        }

        return $arguments;
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
    }
}
