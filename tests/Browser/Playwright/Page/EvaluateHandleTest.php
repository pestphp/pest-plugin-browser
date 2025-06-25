<?php

declare(strict_types=1);

use Pest\Browser\Playwright\JSHandle;
use PHPUnit\Framework\ExpectationFailedException;

it('evaluates JavaScript expressions and returns JSHandle', function (string $expression, mixed $arg = null): void {
    $page = page('/test/frame-tests');

    $handle = is_null($arg)
        ? $page->evaluateHandle($expression)
        : $page->evaluateHandle($expression, $arg);

    expect($handle)->toBeInstanceOf(JSHandle::class);
})->with([
    ['document.querySelector("#test-content")'],
    ['({element: document.querySelector("#test-content"), data: "test"})'],
    ['(selector) => document.querySelector(selector)', '#html-content'],
    ['() => () => "test function"'],
    ['Array.from(document.querySelectorAll(".test-item"))'],
    ['window'],
    ['document'],
    ['new Map([["key1", "value1"], ["key2", "value2"]])'],
    ['Promise.resolve({success: true, data: "async result"})'],
    ['document.createElement("div")'],
]);

it('evaluates JavaScript expressions with complex arguments', function (): void {
    $page = page('/test/frame-tests');

    $config = ['selector' => '.test-item', 'attribute' => 'textContent'];
    $handle = $page->evaluateHandle('(config) => Array.from(document.querySelectorAll(config.selector)).map(el => el[config.attribute])', $config);
    expect($handle)->toBeInstanceOf(JSHandle::class);
});

it('evaluates JavaScript expressions that access form elements', function (string $selector): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle("document.querySelector(\"{$selector}\")");
    expect($handle)->toBeInstanceOf(JSHandle::class);
})->with([
    '#test-form',
    'input[type=checkbox]',
]);

it('handles complex expressions that may succeed or fail', function (string $expression): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle($expression);
    expect($handle)->toBeInstanceOf(JSHandle::class);
})->with([
    '() => ({ test: "value" })',
    '(arg) => ({ result: arg })',
    '() => window',
    '() => document',
    '() => []',
    '() => null',
    '() => undefined',
]);

it('handles expressions with arguments', function (string $expression): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle($expression, 'testArg');
    expect($handle)->toBeInstanceOf(JSHandle::class);
})->with([
    '(x) => x',
    '(arg) => ({ value: arg })',
]);

it('handles potentially failing expressions', function (string $expression): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle($expression);
    expect($handle)->toBeInstanceOf(JSHandle::class);
})->with([
    '() => { const obj = {}; Object.defineProperty(obj, "test", { get() { throw new Error("test"); } }); return obj; }',
    '() => { return Symbol("test"); }',
    '() => { const circular = {}; circular.self = circular; return circular; }',
    '() => { return Symbol.for("test"); }',
]);

it('throws exception for expressions that cause errors', function (): void {
    $page = page('/test/frame-tests');

    $page->evaluateHandle('() => { throw new Error("test error"); }');
})->throws(ExpectationFailedException::class);
