<?php

declare(strict_types=1);

namespace Pest\Browser\Filters;

use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Plugin;
use Pest\Browser\ServerManager;
use Pest\Browser\Support\BrowserTestIdentifier;
use Pest\Browser\Support\Screenshot;
use Pest\Browser\Support\Source;
use Pest\Contracts\TestCaseMethodFilter;
use Pest\Factories\TestCaseMethodFactory;
use Pest\Plugins\Only;
use Pest\Plugins\Parallel;
use Pest\Support\Backtrace;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final readonly class UsesBrowserTestCaseMethodFilter implements TestCaseMethodFilter
{
    /**
     * Either the test case method uses the browser or not.
     */
    public function accept(TestCaseMethodFactory $factory): bool
    {
        $usesBrowser = BrowserTestIdentifier::isBrowserTest($factory);

        if ($usesBrowser === false) {
            return true;
        }

        $usesDebugMethod = BrowserTestIdentifier::isDebugTest($factory);

        if ($usesDebugMethod) {
            Playwright::headed();

            Only::enable($factory);
        }

        $usesDebug = Playwright::shouldDebugAssertions();

        if ($usesDebug) {
            Playwright::headed();
        }

        $factory->proxies->add(
            $factory->filename,
            Backtrace::line(),
            '__markAsBrowserTest',
            [],
        );

        if (Parallel::isWorker() === false && Plugin::$booted === false) {
            Plugin::$booted = true;

            ServerManager::instance()->playwright()->start();
            Screenshot::cleanup();
            Source::cleanup();
        }

        return true;
    }
}
