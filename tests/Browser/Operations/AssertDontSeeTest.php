<?php

declare(strict_types=1);

test('assert does not see', function () {
    $this->visit('https://laravel.com')
        ->assertDontSee('The PHP Foobar');
})->only();

test('assert does not see ignoring case by default', function () {
    expect(function () {
        $this->visit('https://laravel.com')
            ->assertDontSee('the php framework');
    })->toThrow(Exception::class);
})->only();

test('assert does not see can be case sensitive', function () {
    $this->visit('https://laravel.com')
        ->assertDontSee('the php framework', false);
})->only();

test('assert does not see escaping regex special characters', function () {
    $this->visit('https://laravel.com')
        ->assertDontSee('I tried (some) different ecosystems');
})->only();
