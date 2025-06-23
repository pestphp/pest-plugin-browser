<?php

declare(strict_types=1);

namespace Pest\Browser\Filters;

use Pest\Browser\Plugin;
use Pest\Browser\ServerManager;
use Pest\Browser\Support\BrowserTestIdentifier;
use Pest\Browser\Support\Screenshot;
use Pest\Contracts\TestCaseMethodFilter;
use Pest\Factories\TestCaseMethodFactory;
use Pest\Plugins\Parallel;
use Pest\Support\Backtrace;

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
        }

        return true;
    }
}
