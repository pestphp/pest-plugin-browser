<?php

declare(strict_types=1);

it('can double-click on elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByRole('button', ['name' => 'Save']);
    $element = $locator->elementHandle();

    $element->dblclick();
    expect($element->isVisible())->toBeTrue();
});
