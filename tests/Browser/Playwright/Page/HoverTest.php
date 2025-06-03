<?php

declare(strict_types=1);

describe('hover', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('hovers over elements', function (): void {
        // Hover action should complete without error
        $this->page->hover('#hover-target');

        // Verify the element exists and is visible (basic verification that hover action worked)
        expect($this->page->isVisible('#hover-target'))->toBeTrue();
    });

    it('hovers over buttons', function (): void {
        $this->page->hover('#enabled-button');

        // Verify the button exists and is still enabled after hover
        expect($this->page->isEnabled('#enabled-button'))->toBeTrue();
    });

    it('hovers over elements and triggers hover state changes', function (): void {
        // Wait for page to load completely
        $this->page->waitForSelector('#hover-target');

        // Verify initial state before hover
        expect($this->page->textContent('#hover-display'))->toBe('No element hovered yet');

        // Perform hover action
        $this->page->hover('#hover-target');

        // Verify hover state changed
        expect($this->page->textContent('#hover-display'))->toBe('Last hovered: hover-target');

        // Verify the element is still visible after hover
        expect($this->page->isVisible('#hover-target'))->toBeTrue();
    });

    it('hovers over disabled elements with force parameter', function (): void {
        $this->page->waitForSelector('#disabled-button');

        // Hover with force on disabled element
        $this->page->hover('#disabled-button', force: true);

        // Verify element is still disabled after hover
        expect($this->page->isEnabled('#disabled-button'))->toBeFalse();
    });

    it('hovers with position parameter', function (): void {
        $this->page->waitForSelector('#hover-target');

        // Hover at specific position
        $this->page->hover('#hover-target', position: ['x' => 10, 'y' => 10]);

        // Verify hover worked
        expect($this->page->textContent('#hover-display'))->toBe('Last hovered: hover-target');
    });
});
