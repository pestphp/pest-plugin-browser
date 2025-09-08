<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\Webpage;
use Pest\Browser\Playwright\Page;

/**
 * @mixin Webpage
 */
trait InteractsWithFrames
{
    /**
     * Runs the given callback within the context of the specified iframe.
     */
    public function withinFrame(string $selector, callable $callback): self
    {
        $this->page->waitForLoadState('networkidle');

        $locator = $this->guessLocator($selector)->frameLocator($selector);
        $locator->waitFor(['state' => 'attached']);

        $contentFrameObj = $locator->contentFrame();

        expect($contentFrameObj)->not->toBeNull("Expected to find iframe on the page initially with the url [{$this->initialUrl}] using the selector [{$selector}], but it was not found.");

        assert($contentFrameObj !== null);

        assert(
            property_exists($contentFrameObj, 'guid') && is_string($contentFrameObj->guid),
            'Expected contentFrame to have string guid property',
        );

        $iframePage = new Page(
            $this->page->context(),
            $contentFrameObj->guid,
            $contentFrameObj->guid,
        );

        $iframeWebpage = new AwaitableWebpage($iframePage, $this->url());

        $callback($iframeWebpage);

        return $this;
    }
}
