<?php

declare(strict_types=1);

it('clicks an element using xpath', function (): void {
    $this->visit('https://laravel.com')
        ->clickAtXPath('/html/body/div[2]/section/div/div[4]/div/a[1]')
        ->assertUrlIs('https://laravel.com/docs/11.x');
});
