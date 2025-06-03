<?php

declare(strict_types=1);

describe('isChecked', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('returns true for checked checkboxes', function (): void {
        expect($this->page->isChecked('#checked-checkbox'))->toBeTrue();
        expect($this->page->isChecked('#radio-option2'))->toBeTrue();
    });

    it('returns false for unchecked checkboxes', function (): void {
        expect($this->page->isChecked('#unchecked-checkbox'))->toBeFalse();
        expect($this->page->isChecked('#radio-option1'))->toBeFalse();
        expect($this->page->isChecked('#radio-option3'))->toBeFalse();
    });
});
