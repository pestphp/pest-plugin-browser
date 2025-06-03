<?php

declare(strict_types=1);

describe('isEnabled', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('returns true for enabled elements', function (): void {
        expect($this->page->isEnabled('#test-input'))->toBeTrue();
        expect($this->page->isEnabled('#enabled-button'))->toBeTrue();
    });

    it('returns false for disabled elements', function (): void {
        expect($this->page->isEnabled('#disabled-input'))->toBeFalse();
        expect($this->page->isEnabled('#disabled-button'))->toBeFalse();
        expect($this->page->isEnabled('#disabled-checkbox'))->toBeFalse();
    });
});
