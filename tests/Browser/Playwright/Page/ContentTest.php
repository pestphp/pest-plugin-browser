<?php

declare(strict_types=1);

describe('content', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('returns the full HTML content of the frame', function (): void {
        $content = $this->page->content();

        expect($content)->toBeString();
        expect($content)->toContain('<html');
        expect($content)->toContain('</html>');
        expect($content)->toContain('Frame Testing Page');
    });
});
