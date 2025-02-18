<?php

declare(strict_types=1);

it('pause', function (): void {
    $this->visit('https://laravel.com')
        ->clickLink('Get Started')
        ->pause(5000)
        ->assertUrlIs('https://laravel.com/docs/11.x');
});

test('throws an exception when pause is negative', function (): void {
    expect(function () {
        $this->visit('https://laravel.com')
            ->clickLink('Get Started')
            ->pause(-5000)
            ->assertUrlIs('https://laravel.com/docs/11.x');
    })
        ->toThrow(
            InvalidArgumentException::class,
            'The number of milliseconds must be greater than 0.'
        );
});
