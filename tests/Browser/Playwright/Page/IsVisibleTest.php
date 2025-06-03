<?php

declare(strict_types=1);

describe('isVisible', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('returns true for visible elements', function (): void {
        expect($this->page->isVisible('#visible-element'))->toBeTrue();
        expect($this->page->isVisible('#test-form'))->toBeTrue();
    });

    it('returns false for hidden elements', function (): void {
        expect($this->page->isVisible('#hidden-element'))->toBeFalse();
    });
});
