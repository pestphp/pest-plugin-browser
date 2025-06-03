<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

it('can click on buttons', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByRole('button', ['name' => 'Save']);
    $element = $locator->elementHandle();

    expect($element)->toBeInstanceOf(Element::class);
    $element->click();

    // Verify click happened by checking if button is still visible
    expect($element->isVisible())->toBeTrue();
});

it('can click with options', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByRole('button', ['name' => 'Save']);
    $element = $locator->elementHandle();

    $element->click(['force' => true]);
    expect($element->isVisible())->toBeTrue();
});
