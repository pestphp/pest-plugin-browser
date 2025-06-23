<?php

declare(strict_types=1);

it('evaluates JavaScript expression and returns JSHandle for DOM elements', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('document.querySelector("#test-content")');
    expect($handle)->not()->toBeNull();
    // JSHandle objects are typically used for further operations,
    // so we verify it's not null and can be used
});

it('evaluates JavaScript expression and returns JSHandle for complex objects', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('({element: document.querySelector("#test-content"), data: "test"})');
    expect($handle)->not()->toBeNull();
});

it('evaluates JavaScript expression with arguments and returns JSHandle', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('(selector) => document.querySelector(selector)', '#html-content');
    expect($handle)->not()->toBeNull();
});

it('evaluates JavaScript expression that returns function as JSHandle', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('() => () => "test function"');
    expect($handle)->not()->toBeNull();
});

it('evaluates JavaScript expression that returns array as JSHandle', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('Array.from(document.querySelectorAll(".test-item"))');
    expect($handle)->not()->toBeNull();
});

it('evaluates JavaScript expression that returns window object as JSHandle', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('window');
    expect($handle)->not()->toBeNull();
});

it('evaluates JavaScript expression that returns document as JSHandle', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('document');
    expect($handle)->not()->toBeNull();
});

it('evaluates JavaScript expression with complex arguments and returns JSHandle', function (): void {
    $page = page('/test/frame-tests');

    $config = ['selector' => '.test-item', 'attribute' => 'textContent'];
    $handle = $page->evaluateHandle('(config) => Array.from(document.querySelectorAll(config.selector)).map(el => el[config.attribute])', $config);
    expect($handle)->not()->toBeNull();
});

it('evaluates JavaScript expression that creates and returns new object as JSHandle', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('new Map([["key1", "value1"], ["key2", "value2"]])');
    expect($handle)->not()->toBeNull();
});

it('evaluates JavaScript expression that returns Promise as JSHandle', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('Promise.resolve({success: true, data: "async result"})');
    expect($handle)->not()->toBeNull();
});

it('evaluates JavaScript expression without arguments and returns JSHandle', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('document.createElement("div")');
    expect($handle)->not()->toBeNull();
});

it('evaluates JavaScript expression that accesses form elements as JSHandle', function (): void {
    $page = page('/test/frame-tests');

    $handle = $page->evaluateHandle('document.querySelector("#test-form")');
    expect($handle)->not()->toBeNull();

    $handle = $page->evaluateHandle('document.querySelectorAll("input[type=checkbox]")');
    expect($handle)->not()->toBeNull();
});
