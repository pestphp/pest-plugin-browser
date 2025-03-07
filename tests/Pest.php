<?php

declare(strict_types=1);

use Pest\Browser\Support\Screenshot;

pest()->afterEach(fn () => Screenshot::cleanup());

function playgroundUrl(string $uri = '/'): string
{
    return 'http://localhost:9357/'.ltrim($uri, '/');
}
