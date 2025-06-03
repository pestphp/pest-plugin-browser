<?php

declare(strict_types=1);

it('can count matching elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $buttons = $page->locator('button');

    expect($buttons->count())->toBeGreaterThan(0);
});

it('returns 0 for non-existent elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $nonExistent = $page->locator('.non-existent-class');

    expect($nonExistent->count())->toBe(0);
});
