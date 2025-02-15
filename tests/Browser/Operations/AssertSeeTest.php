<?php

declare(strict_types=1);

test('assert sees', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertSee('The PHP Framework');
});

test('assert sees ignoring case', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertSee('the php framework', true);
});
