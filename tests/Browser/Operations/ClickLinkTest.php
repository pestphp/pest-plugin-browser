<?php

declare(strict_types=1);

it('clicks a link', function (): void {
    $this->visit('/')
        ->clickLink('Laravel Docs')
        ->assertUrlIs('https://laravel.com/docs/11.x');
});
