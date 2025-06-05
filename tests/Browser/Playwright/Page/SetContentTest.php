<?php

declare(strict_types=1);

describe('setContent', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('can set simple HTML content', function (): void {
        $html = '<h1>Test Title</h1><p>Test paragraph</p>';

        $this->page->setContent($html);

        expect($this->page->innerText('h1'))->toBe('Test Title');
        expect($this->page->innerText('p'))->toBe('Test paragraph');
    });

    it('can set content with forms', function (): void {
        $html = '
        <form>
            <input type="text" id="test-input" value="test-value">
            <button type="submit" id="test-button">Submit</button>
        </form>';

        $this->page->setContent($html);

        expect($this->page->inputValue('#test-input'))->toBe('test-value');
        expect($this->page->isVisible('#test-button'))->toBeTrue();
    });

    it('can set content with scripts', function (): void {
        $html = '
        <div id="target">Initial</div>
        <script>
            document.getElementById("target").textContent = "Updated by script";
        </script>';

        $this->page->setContent($html);

        expect($this->page->innerText('#target'))->toBe('Updated by script');
    });

    it('can set complete HTML document', function (): void {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Test Page</title>
        </head>
        <body>
            <h1>Complete Document</h1>
            <div id="content">This is a complete HTML document</div>
        </body>
        </html>';

        $this->page->setContent($html);

        expect($this->page->title())->toBe('Test Page');
        expect($this->page->innerText('h1'))->toBe('Complete Document');
        expect($this->page->innerText('#content'))->toBe('This is a complete HTML document');
    });

    it('can clear content by setting empty HTML', function (): void {
        $this->page->setContent('<div id="test">Original content</div>');
        expect($this->page->isVisible('#test'))->toBeTrue();

        $this->page->setContent('<html><body></body></html>');
        expect($this->page->isVisible('#test'))->toBeFalse();
    });
});
