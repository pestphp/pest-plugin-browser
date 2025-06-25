<?php

declare(strict_types=1);

use Pest\Browser\Playwright\JSHandle;

it('can evaluate basic expressions on a JSHandle', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('document.querySelector("#test-content")');
    expect($handle)->toBeInstanceOf(JSHandle::class);

    $textContent = $handle->evaluate('node => node.textContent');
    expect($textContent)->toContain('This is the main content for testing');
});

it('can evaluate expressions that modify a JSHandle element', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('document.querySelector("#test-input")');
    expect($handle)->toBeInstanceOf(JSHandle::class);

    $handle->evaluate('input => { input.value = "test value"; return true; }');

    $value = $page->locator('#test-input')->inputValue();
    expect($value)->toBe('test value');
});

it('can pass arguments when evaluating expressions on a JSHandle', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('document.querySelector("#test-content")');
    expect($handle)->toBeInstanceOf(JSHandle::class);

    $result = $handle->evaluate('(node, text) => { node.textContent = text; return node.textContent; }', 'Updated content');
    expect($result)->toBe('Updated content');

    $content = $page->locator('#test-content')->textContent();
    expect($content)->toBe('Updated content');
});

it('can evaluate expressions on JSHandles that return primitives', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('document.querySelector("h1")');
    expect($handle)->toBeInstanceOf(JSHandle::class);

    $text = $handle->evaluate('h1 => h1.textContent');
    expect($text)->toBeString();
    expect($text)->toContain('PESTPHP');

    $hasChildren = $handle->evaluate('h1 => h1.hasChildNodes()');
    expect($hasChildren)->toBeTrue();

    $childCount = $handle->evaluate('h1 => h1.childNodes.length');
    expect($childCount)->toBeGreaterThan(0);
});

it('can get JSON value from JSHandle with objects', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('({name: "test", value: 42, active: true})');
    expect($handle)->toBeInstanceOf(JSHandle::class);

    $jsonValue = $handle->jsonValue();
    expect($jsonValue)->toBeArray();
    expect($jsonValue)->toHaveKey('name');
    expect($jsonValue)->toHaveKey('value');
    expect($jsonValue['name'])->toBe('test');
    expect($jsonValue['value'])->toBe(42);
    expect($jsonValue['active'])->toBeTrue();
});

it('can get JSON value from JSHandle with different data types', function (): void {
    $page = page('/test/frame-tests');

    $arrayHandle = $page->evaluateHandle('[1, 2, "test"]');
    expect($arrayHandle->jsonValue())->toBe([1, 2, 'test']);

    $numberHandle = $page->evaluateHandle('42');
    expect($numberHandle->jsonValue())->toBe(42);
});

it('can get JSON value from JSHandle for DOM elements', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('document.querySelector("#test-content")');
    expect($handle)->toBeInstanceOf(JSHandle::class);

    $jsonValue = $handle->jsonValue();
    expect($jsonValue)->toBeString();
    expect($jsonValue)->toContain('ref:');
});

it('can convert JSHandle to string representation', function (): void {
    $page = page('/test/frame-tests');

    $stringHandle = $page->evaluateHandle('"hello world"');
    expect($stringHandle->toString())->toBe('hello world');

    $numberHandle = $page->evaluateHandle('42');
    expect($numberHandle->toString())->toBe('42');
});

it('can dispose JSHandle', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('document.querySelector("#test-content")');
    expect($handle)->toBeInstanceOf(JSHandle::class);

    $handle->dispose();
    expect(true)->toBeTrue();
});

it('can evaluate complex expressions with various argument types', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('document');
    expect($handle)->toBeInstanceOf(JSHandle::class);

    expect($handle->evaluate('(doc, arg) => arg === null', null))->toBeTrue();
    expect($handle->evaluate('(doc, num) => num * 2', 21))->toBe(42);
    expect($handle->evaluate('(doc, arr) => arr.length', [1, 2, 3]))->toBe(3);
    expect($handle->evaluate('(doc, obj) => obj.name + ":" + obj.value', ['name' => 'test', 'value' => 123]))->toBe('test:123');
});
