<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Pest\Browser\Api\Webpage;
use Pest\Browser\Playwright\Page;
use RuntimeException;

/**
 * @mixin Webpage
 */
trait InteractsWithFrames
{
    public function withinFrame(string $selector, callable $callback): Webpage
    {
        $this->page->waitForLoadState('networkidle');

        $iframeLocator = $this->guessLocator($selector)->frameLocator($selector);

        $iframeLocator->waitFor(['state' => 'attached']);

        $contentFrameObj = $iframeLocator->contentFrame();

        if ($contentFrameObj !== null) {
            assert(
                property_exists($contentFrameObj, 'guid') && is_string($contentFrameObj->guid),
                'Expected contentFrame to have string guid property',
            );

            $iframePage = new Page(
                $this->page->context(),
                $contentFrameObj->guid,
                $contentFrameObj->guid,
            );

            $iframeWebpage = new Webpage($iframePage, $this->url());

            $callback($iframeWebpage);

            return $this;
        }

        throw new RuntimeException("Unable to access iframe content with selector: {$selector}. The Playwright server may not support iframe operations for this setup.");
    }
}
