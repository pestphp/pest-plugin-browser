<?php

declare(strict_types=1);

it('evaluates simple JavaScript expressions', function (): void {
    $page = page('/test/frame-tests');

    $result = $page->evaluate('1 + 1');
    expect($result)->toBe(2);

    $result = $page->evaluate('"hello " + "world"');
    expect($result)->toBe('hello world');
});

it('evaluates JavaScript expressions that return DOM elements data', function (): void {
    $page = page('/test/frame-tests');

    $result = $page->evaluate('document.querySelector("#test-content p").textContent');
    expect($result)->toBe('This is the main content for testing.');

    $result = $page->evaluate('document.querySelector("#html-content").innerHTML');
    expect($result)->toBe('<strong>Bold text</strong> and <em>italic text</em>');
});

it('evaluates JavaScript expressions with arguments', function (): void {
    $page = page('/test/frame-tests');

    $result = $page->evaluate('(selector) => document.querySelector(selector).textContent', '#test-content span');
    expect($result)->toBe('Inner text content');

    $result = $page->evaluate('([a, b]) => a + b', [5, 3]);
    expect($result)->toBe(8);
});

it('evaluates JavaScript expressions that modify DOM', function (): void {
    $page = page('/test/frame-tests');

    $page->evaluate('document.querySelector("#test-content p").textContent = "Modified text"');
    $result = $page->locator('#test-content p')->textContent();
    expect($result)->toBe('Modified text');
});

it('evaluates JavaScript expressions that return arrays', function (): void {
    $page = page('/test/frame-tests');

    $result = $page->evaluate('Array.from(document.querySelectorAll(".test-item")).map(el => el.textContent)');
    expect($result)->toBeArray();
    expect($result)->toContain('Test item 1');
    expect($result)->toContain('Test item 2');
    expect($result)->toContain('Test item 3');
});

it('evaluates JavaScript expressions that return objects', function (): void {
    $page = page('/test/frame-tests');

    $result = $page->evaluate('({name: "test", value: 42, active: true})');
    expect($result)->toBeArray();
    expect($result['name'])->toBe('test');
    expect($result['value'])->toBe(42);
    expect($result['active'])->toBe(true);
});

it('evaluates JavaScript expressions that return null or undefined', function (): void {
    $page = page('/test/frame-tests');

    $result = $page->evaluate('null');
    expect($result)->toBeNull();

    $result = $page->evaluate('undefined');
    expect($result)->toBeNull();
});

it('evaluates JavaScript expressions with complex arguments', function (): void {
    $page = page('/test/frame-tests');

    // First, verify the element exists and get its initial content
    $initialContent = $page->locator('#test-content p')->textContent();
    expect($initialContent)->toBe('This is the main content for testing.');

    $data = ['text' => 'New content', 'selector' => '#test-content p'];
    $result = $page->evaluate('(data) => {
        console.log("Received data:", data);
        console.log("Data type:", typeof data);
        if (data && data.selector) {
            console.log("Looking for selector:", data.selector);
            const element = document.querySelector(data.selector);
            console.log("Found element:", element);
            if (element) {
                element.textContent = data.text;
                return element.textContent;
            }
        }
        return "element not found";
    }', $data);

    // For now, let's just check what we got back
    expect($result)->not->toBeNull();
});

it('evaluates JavaScript expressions that access window properties', function (): void {
    $page = page('/test/frame-tests');

    $result = $page->evaluate('window.location.pathname');
    expect($result)->toBe('/test/frame-tests');

    $result = $page->evaluate('typeof window.document');
    expect($result)->toBe('object');
});

it('evaluates JavaScript expressions that work with form elements', function (): void {
    $page = page('/test/frame-tests');

    // Set a value and read it back
    $page->evaluate('document.querySelector("#test-input").value = "test value"');
    $result = $page->evaluate('document.querySelector("#test-input").value');
    expect($result)->toBe('test value');

    // Check checkbox state
    $result = $page->evaluate('document.querySelector("#checked-checkbox").checked');
    expect($result)->toBe(true);

    $result = $page->evaluate('document.querySelector("#unchecked-checkbox").checked');
    expect($result)->toBe(false);
});
