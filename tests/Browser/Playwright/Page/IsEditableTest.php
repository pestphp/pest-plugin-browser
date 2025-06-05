<?php

declare(strict_types=1);

describe('isEditable', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('returns true for editable elements', function (): void {
        expect($this->page->isEditable('input[type="text"]'))->toBeTrue();
        expect($this->page->isEditable('textarea'))->toBeTrue();
    });

    it('returns false for non-editable elements', function (): void {
        expect($this->page->isEditable('button'))->toBeFalse();
        expect($this->page->isEditable('div'))->toBeFalse();
        expect($this->page->isEditable('span'))->toBeFalse();
    });

    it('returns false for disabled input elements', function (): void {
        expect($this->page->isEditable('#disabled-input'))->toBeFalse();
    });

    it('returns false for readonly input elements', function (): void {
        expect($this->page->isEditable('#readonly-input'))->toBeFalse();
    });
});
