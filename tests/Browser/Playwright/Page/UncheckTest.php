<?php

declare(strict_types=1);

describe('uncheck', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('unchecks checked checkboxes', function (): void {
        expect($this->page->isChecked('#checked-checkbox'))->toBeTrue();

        $this->page->uncheck('#checked-checkbox');

        expect($this->page->isChecked('#checked-checkbox'))->toBeFalse();
    });

    it('changes state after unchecking', function (): void {
        // Check first to ensure it's checked
        $this->page->check('#unchecked-checkbox');
        expect($this->page->isChecked('#unchecked-checkbox'))->toBeTrue();

        // Uncheck it
        $this->page->uncheck('#unchecked-checkbox');
        expect($this->page->isChecked('#unchecked-checkbox'))->toBeFalse();
    });
});
