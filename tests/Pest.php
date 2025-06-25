<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Page;
use Tests\Drivers\Laravel\TestCase;

pest()->uses(TestCase::class);

/**
 * Visits the given URL, and starts a new browser test.
 *
 * @param  string|null  $url  The URL to visit
 * @param  array<string, mixed>  $options  Options for the page or for the goto, e.g. ['hasTouch' => true]
 */
function page(?string $url = null, array $options = []): Page
{
    $webpage = visit($url, $options);

    return (fn (): Page => $this->page)->call($webpage);
}
