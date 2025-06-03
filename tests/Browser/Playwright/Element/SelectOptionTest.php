<?php

declare(strict_types=1);

it('can select options from select elements', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByTestId('test-select');
    $element = $locator->elementHandle();

    $selected = $element->selectOption('option2');

    expect($selected)->toBeArray();
});
