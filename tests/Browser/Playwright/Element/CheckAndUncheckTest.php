<?php

declare(strict_types=1);

it('can check and uncheck checkboxes', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByRole('checkbox', ['name' => 'Remember Me']);
    $element = $locator->elementHandle();

    expect($element->isChecked())->toBeFalse();

    $element->check();
    expect($element->isChecked())->toBeTrue();

    $element->uncheck();
    expect($element->isChecked())->toBeFalse();
});
