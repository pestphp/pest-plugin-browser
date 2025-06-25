<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Context;

it('returns the browser context', function (): void {
    $page = page('/test/frame-tests');
    $context = $page->context();

    expect($context)->toBeInstanceOf(Context::class);
});
