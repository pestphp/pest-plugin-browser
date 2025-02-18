<?php

declare(strict_types=1);

test('assert query string does not have the given query param with value', function () {
    $url = 'https://laravel.com?q=test';
    $this->visit($url)
        ->assertQueryStringMissing('q', 'test-1');
});

test('assert query string does not have the only given the query param', function () {
    $url = 'https://laravel.com?q=test';
    $this->visit($url)
        ->assertQueryStringMissing('s');
});
