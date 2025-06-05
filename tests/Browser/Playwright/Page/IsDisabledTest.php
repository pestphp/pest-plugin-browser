<?php

declare(strict_types=1);

describe('isDisabled', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('returns true for disabled elements', function (): void {
        expect($this->page->isDisabled('#disabled-button'))->toBeTrue();
        expect($this->page->isDisabled('#disabled-input'))->toBeTrue();
    });

    it('returns false for enabled elements', function (): void {
        expect($this->page->isDisabled('#enabled-button'))->toBeFalse();
        expect($this->page->isDisabled('input[type="text"]'))->toBeFalse();
    });

    it('returns false for elements that cannot be disabled', function (): void {
        expect($this->page->isDisabled('div'))->toBeFalse();
        expect($this->page->isDisabled('span'))->toBeFalse();
    });
});
