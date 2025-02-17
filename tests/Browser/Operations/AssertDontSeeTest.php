<?php

declare(strict_types=1);

test('assert does not see', function () {
    $this->visit('https://laravel.com')
        ->assertDontSee('The PHP Foobar');
});

test('assert does not see ignoring case', function () {
    $this->visit('https://laravel.com')
        ->assertDontSee('the php foobar', true);
});

test('assert does not see escaping regex special characters', function () {
    $this->visit('https://laravel.com')
        ->assertDontSee('I tried (some) different ecosystems');
});
