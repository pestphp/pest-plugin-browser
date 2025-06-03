<?php

declare(strict_types=1);

describe('check', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('checks unchecked checkboxes', function (): void {
        expect($this->page->isChecked('#unchecked-checkbox'))->toBeFalse();

        $this->page->check('#unchecked-checkbox');

        expect($this->page->isChecked('#unchecked-checkbox'))->toBeTrue();
    });

    it('changes state after checking', function (): void {
        // Initially unchecked
        expect($this->page->isChecked('#unchecked-checkbox'))->toBeFalse();

        // Check it
        $this->page->check('#unchecked-checkbox');
        expect($this->page->isChecked('#unchecked-checkbox'))->toBeTrue();
    });
});
