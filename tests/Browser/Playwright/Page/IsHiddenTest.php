<?php

declare(strict_types=1);

describe('isHidden', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('returns false for visible elements when checking isHidden', function (): void {
        expect($this->page->isHidden('#visible-element'))->toBeFalse();
        expect($this->page->isHidden('#test-form'))->toBeFalse();
    });

    it('returns true for hidden elements when checking isHidden', function (): void {
        expect($this->page->isHidden('#hidden-element'))->toBeTrue();
    });
});
