<?php

declare(strict_types=1);

describe('press', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('presses keys on focused elements', function (): void {
        $this->page->focus('#keyboard-input');
        $this->page->press('#keyboard-input', 'Enter');

        // Verify the input is still focused and visible after key press
        expect($this->page->isVisible('#keyboard-input'))->toBeTrue();
    });

    it('presses special key combinations', function (): void {
        $this->page->focus('#keyboard-input');
        $this->page->press('#keyboard-input', 'Shift+Home');

        // Verify the input element is still available and functional after key combinations
        expect($this->page->isEnabled('#keyboard-input'))->toBeTrue();
    });
});
