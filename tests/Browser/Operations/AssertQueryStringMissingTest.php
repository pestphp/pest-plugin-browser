<?php

declare(strict_types=1);

test('assert query string does not have the givin query param with value', function () {
    $url = 'https://laravel.com?q=test';
    $this->visit($url)
        ->assertQueryStringMissing('q', 'test-1');
});

test('assert query string has a giving only the query param', function () {
    $url = 'https://laravel.com?q=test';
    $this->visit($url)
        ->assertQueryStringMissing('s');
});
