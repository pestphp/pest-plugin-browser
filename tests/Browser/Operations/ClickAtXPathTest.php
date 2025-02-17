<?php

declare(strict_types=1);

it('clicks an element using xpath', function (): void {
    $this->visit('/')
        ->clickAtXPath('//*[@id="app"]/header/div[2]/a[1]')
        ->assertUrlIs('https://pestphp.com/docs/installation');
});
