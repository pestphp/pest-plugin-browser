<?php

declare(strict_types=1);

it('can fill input fields', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByLabel('Username');
    $element = $locator->elementHandle();

    $element->fill('newusername');
    expect($element->inputValue())->toBe('newusername');
});

it('can fill with options', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $locator = $page->getByLabel('Username');
    $element = $locator->elementHandle();

    $element->fill('testuser', ['force' => true]);
    expect($element->inputValue())->toBe('testuser');
});
