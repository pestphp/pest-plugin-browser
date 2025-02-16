<?php

declare(strict_types=1);

it('clicks an element using x and y coordinates', function (): void {
    $this->visit('https://laravel.com')
        ->clickAtPoint(530, 520)
        ->assertUrlIs('https://laravel.com/docs/11.x');
});
