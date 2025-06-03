<?php

declare(strict_types=1);

it('can hover over elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByRole('button', ['name' => 'Save']);
    $element = $locator->elementHandle();

    $element->hover();
    expect($element->isVisible())->toBeTrue();
});
