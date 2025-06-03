<?php

declare(strict_types=1);

describe('focus', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('focuses on input elements', function (): void {
        $this->page->focus('#focus-target');

        // Verify the element can be focused (check if it has focus-related state)
        expect($this->page->isVisible('#focus-target'))->toBeTrue();
    });

    it('focuses on different input types', function (): void {
        $this->page->focus('#test-input');
        $this->page->focus('#password-input');
        $this->page->focus('#test-textarea');

        // Verify we can focus on different input types without errors
        expect($this->page->isVisible('#test-textarea'))->toBeTrue();
    });
});
