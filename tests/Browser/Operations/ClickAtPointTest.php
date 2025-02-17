<?php

declare(strict_types=1);

it('clicks an element using x and y coordinates', function (): void {
    $this->visit('/')
        ->screenshot('click-at-point.png')
        ->clickAtPoint(530, 550)
        ->assertUrlIs('https://pestphp.com/docs/installation');
})->only();
