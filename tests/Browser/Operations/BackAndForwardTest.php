<?php

declare(strict_types=1);

test('goes back and forward', function (): void {
    $this->visit('/')
        ->clickLink('Link')
        ->assertUrlIs('https://pestphp.com/docs/installation')
        ->back()
        ->assertUrlIs('http://127.0.0.1:9357')
        ->forward()
        ->assertUrlIs('https://pestphp.com/docs/installation');
});
