<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

it('may assert element has a specific attribute value', function (): void {
    Route::get('/', fn (): string => '<div id="content" data-role="main" aria-label="Main content">Hello World</div>');

    $page = visit('/');

    $page->assertAttribute('#content', 'id', 'content');
    $page->assertAttribute('#content', 'data-role', 'main');
    $page->assertAttribute('#content', 'aria-label', 'Main content');
});

it('may fail when asserting element has a specific attribute value but it does not', function (): void {
    Route::get('/', fn (): string => '<div id="content" data-role="main">Hello World</div>');

    $page = visit('/');

    $page->assertAttribute('#content', 'data-role', 'secondary');
})->throws(ExpectationFailedException::class);

it('may assert element is missing an attribute', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    $page->assertAttributeMissing('#content', 'data-role');
});

it('may fail when asserting element is missing an attribute but it has it', function (): void {
    Route::get('/', fn (): string => '<div id="content" data-role="main">Hello World</div>');

    $page = visit('/');

    $page->assertAttributeMissing('#content', 'data-role');
})->throws(ExpectationFailedException::class);

it('may assert element attribute contains a value', function (): void {
    Route::get('/', fn (): string => '<div id="content" class="container main-content">Hello World</div>');

    $page = visit('/');

    $page->assertAttributeContains('#content', 'class', 'main-content');
    $page->assertAttributeContains('#content', 'class', 'container');
});

it('may fail when asserting element attribute contains a value but it does not', function (): void {
    Route::get('/', fn (): string => '<div id="content" class="container main-content">Hello World</div>');

    $page = visit('/');

    $page->assertAttributeContains('#content', 'class', 'secondary-content');
})->throws(ExpectationFailedException::class);

it('may assert element attribute does not contain a value', function (): void {
    Route::get('/', fn (): string => '<div id="content" class="container main-content">Hello World</div>');

    $page = visit('/');

    $page->assertAttributeDoesntContain('#content', 'class', 'secondary-content');
});

it('may fail when asserting element attribute does not contain a value but it does', function (): void {
    Route::get('/', fn (): string => '<div id="content" class="container main-content">Hello World</div>');

    $page = visit('/');

    $page->assertAttributeDoesntContain('#content', 'class', 'main-content');
})->throws(ExpectationFailedException::class);

it('may assert element attribute does not contain a value when attribute is missing', function (): void {
    Route::get('/', fn (): string => '<div id="content">Hello World</div>');

    $page = visit('/');

    // This should pass because the attribute doesn't exist (null)
    $result = $page->assertAttributeDoesntContain('#content', 'data-role', 'any-value');

    // Verify that the method returns the page instance for method chaining
    expect($result)->toBe($page);

    // Also verify that the attribute is actually null
    $attributeValue = $page->attribute('#content', 'data-role');
    expect($attributeValue)->toBeNull();
});

it('may assert element has a specific aria attribute value', function (): void {
    Route::get('/', fn (): string => '<div id="content" aria-label="Main content">Hello World</div>');

    $page = visit('/');

    $page->assertAriaAttribute('#content', 'label', 'Main content');
});

it('may assert element has a specific data attribute value', function (): void {
    Route::get('/', fn (): string => '<div id="content" data-role="main">Hello World</div>');

    $page = visit('/');

    $page->assertDataAttribute('#content', 'role', 'main');
});
