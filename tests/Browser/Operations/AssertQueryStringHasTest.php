<?php

declare(strict_types=1);

test('assert query string has a giving query param with value', function () {
    $url = 'https://laravel.com?q=test';
    $this->visit($url)
        ->assertQueryStringHas('q', 'test');
});

test('assert query string has a giving only the query param', function () {
    $url = 'https://laravel.com?q=test';
    $this->visit($url)
        ->assertQueryStringHas('q');
});
