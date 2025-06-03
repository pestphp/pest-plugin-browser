<?php

declare(strict_types=1);

describe('waitForLoadState', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('waits for load state', function (): void {
        $this->page->waitForLoadState();

        expect(true)->toBeTrue();
    });

    it('waits for specific load state', function (): void {
        $this->page->waitForLoadState('domcontentloaded');

        expect(true)->toBeTrue();
    });

    it('waits for networkidle state', function (): void {
        $this->page->waitForLoadState('networkidle');

        expect(true)->toBeTrue();
    });
});
