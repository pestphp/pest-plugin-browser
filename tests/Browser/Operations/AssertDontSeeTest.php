<?php

declare(strict_types=1);

test('assert does not see ignoring case by default', function () {
    $this->visit('https://laravel.com')
        ->assertDontSee('the php foobar');

    expect(function () {
        $this->visit('https://laravel.com')
            ->assertDontSee('the php framework');
    })->toThrow(Exception::class);
});

test('assert does not see without ignoring case', function () {
    $this->visit('https://laravel.com')
        ->assertDontSee('The PHP Foobar', false);

    expect(function () {
        $this->visit('https://laravel.com')
            ->assertDontSee('The PHP Framework', false);
    })->toThrow(Exception::class);
});
