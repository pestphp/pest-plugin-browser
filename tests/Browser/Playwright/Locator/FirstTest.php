<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Locator;

it('can get first element from multiple matches', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');
    $firstButton = $buttons->first();

    expect($firstButton)->toBeInstanceOf(Locator::class);
});
