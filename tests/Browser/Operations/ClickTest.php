<?php

declare(strict_types=1);

it('clicks an element', function (): void {
    $this->visit('https://laravel.com')
        ->click('.uppercase.bg-red-500')
        ->assertUrlIs('https://laravel.com/docs/11.x');
});
