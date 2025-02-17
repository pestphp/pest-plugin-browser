<?php

declare(strict_types=1);

it('clicks an element using x and y coordinates', function (): void {
    $this->visit('/')
        ->clickAtPoint(530, 520)
        ->assertUrlIs('https://pestphp.com/docs/installation');
})->only();
