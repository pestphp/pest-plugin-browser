<?php

declare(strict_types=1);

describe('fill', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('fills text inputs', function (): void {
        $this->page->fill('#test-input', 'filled value');

        expect($this->page->inputValue('#test-input'))->toBe('filled value');
    });

    it('fills password inputs', function (): void {
        $this->page->fill('#password-input', 'secret123');

        expect($this->page->inputValue('#password-input'))->toBe('secret123');
    });

    it('fills textarea elements', function (): void {
        $this->page->fill('#test-textarea', 'new textarea content');

        expect($this->page->inputValue('#test-textarea'))->toBe('new textarea content');
    });
});
