<?php

declare(strict_types=1);

describe('click', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('can click on buttons', function (): void {
        $this->page->click('#enabled-button');

        // Verify button is still visible after click
        expect($this->page->isVisible('#enabled-button'))->toBeTrue();
    });
});
