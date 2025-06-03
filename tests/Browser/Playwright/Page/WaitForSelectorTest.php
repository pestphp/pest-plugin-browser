<?php

declare(strict_types=1);

describe('waitForSelector', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('waits for element to be available', function (): void {
        $this->page->waitForSelector('#hover-target');

        expect($this->page->isVisible('#hover-target'))->toBeTrue();
    });

    it('waits for multiple elements', function (): void {
        $this->page->waitForSelector('#test-input');
        $this->page->waitForSelector('#test-form');

        expect($this->page->isVisible('#test-input'))->toBeTrue();
        expect($this->page->isVisible('#test-form'))->toBeTrue();
    });
});
