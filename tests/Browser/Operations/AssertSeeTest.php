<?php

declare(strict_types=1);

test('assert sees', function () {
    $this->visit('https://laravel.com')
        ->assertSee('The PHP Framework');
});

test('assert sees ignoring case', function () {
    $this->visit('https://laravel.com')
        ->assertSee('the php framework', true);
});

test('assert sees escaping regex special characters', function () {
    $this->visit('https://laravel.com')
        ->assertSee('I tried (many) different ecosystems');
});

test('lol', function () {
    $this->visit('https://laravel.com')
        ->assertSee('“Laravel’s best-in-class testing tools');
})->only();
