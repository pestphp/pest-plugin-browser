<?php

declare(strict_types=1);

it('can take screenshot of element', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $button = $page->getByTestId('click-button');

    $screenshot = $button->screenshot();

    expect($screenshot)->toBeString();
    expect(mb_strlen($screenshot))->toBeGreaterThan(0);
    // Base64 encoded images typically start with data:image or are pure base64
    expect($screenshot)->toMatch('/^[A-Za-z0-9+\/]+=*$/');
});

it('can take screenshot with options', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $button = $page->getByTestId('click-button');

    $screenshot = $button->screenshot([
        'type' => 'png',
    ]);

    expect($screenshot)->toBeString();
    expect(mb_strlen($screenshot))->toBeGreaterThan(0);
});

it('can scroll element into view if needed', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $button = $page->getByTestId('click-button');

    // This should not throw an error
    $button->scrollIntoViewIfNeeded();

    // Verify element is still interactable after scrolling
    expect($button->isVisible())->toBeTrue();
});

it('can highlight element', function (): void {
    $page = $this->page()->goto('/test/element-tests');
    $button = $page->getByTestId('click-button');

    // This should not throw an error
    $button->highlight();

    // Element should still be accessible after highlighting
    expect($button->isVisible())->toBeTrue();
});
