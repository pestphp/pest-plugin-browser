<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Filters\UsesBrowserTestCaseMethodFilter;
use Pest\Browser\Playwright\Playwright;
use Pest\Contracts\Plugins\Bootable;
use Pest\Contracts\Plugins\Terminable;
use Pest\Plugins\Parallel;
use Pest\TestSuite;

/**
 * @internal
 */
final class Plugin implements Bootable, Terminable // @pest-arch-ignore-line
{
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
            ServerManager::instance()->http()->flush();

            Playwright::reset();
        })->in($this->in());
    }

    /**
     * Terminates the plugin.
     */
    public function terminate(): void
    {
        if (Parallel::isWorker() || Parallel::isEnabled() === false) {
            ServerManager::instance()->http()->stop();

            Playwright::close();
        }

        if (Parallel::isWorker() === false) {
            ServerManager::instance()->playwright()->stop();
        }
    }

    /**
     * Returns the path where the test files are located.
     */
    private function in(): string
    {
        return TestSuite::getInstance()->rootPath.DIRECTORY_SEPARATOR.TestSuite::getInstance()->testPath;
    }
}
