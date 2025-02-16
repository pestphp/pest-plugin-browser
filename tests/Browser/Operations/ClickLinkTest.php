<?php

declare(strict_types=1);

it('clicks a link', function (): void {
    $this->visit('/')
        ->clickLink('Link')
        ->assertUrlIs('https://pestphp.com/docs/installation');
});
