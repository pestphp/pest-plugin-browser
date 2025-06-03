<?php

declare(strict_types=1);

describe('waitForURL', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('waits for URL pattern', function (): void {
        $currentUrl = '/test/selector-tests';
        $this->page->waitForURL($currentUrl);

        expect(true)->toBeTrue();
    });
});
