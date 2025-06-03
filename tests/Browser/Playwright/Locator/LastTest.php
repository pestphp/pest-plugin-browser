<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can get last element from multiple matches', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $lastButton = $buttons->last();

    expect($lastButton)->toBeInstanceOf(Locator::class);
});
