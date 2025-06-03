<?php

declare(strict_types=1);

describe('textContent', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('gets the text content of an element', function (): void {
        $text = $this->page->textContent('#test-content span');

        expect($text)->toBe('Inner text content');
    });

    it('gets text content from mixed content', function (): void {
        $text = $this->page->textContent('#mixed-content');

        expect($text)->toContain('Paragraph with bold and italic text');
        expect($text)->toContain('List item 1');
        expect($text)->toContain('List item 2');
    });
});
