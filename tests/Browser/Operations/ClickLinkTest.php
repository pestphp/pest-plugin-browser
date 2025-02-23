<?php

declare(strict_types=1);

it('clicks a link', function (): void {
    $this->visit(playground()->url('/test/interacting-with-elements'))
        ->clickLink('Home')
        ->assertUrlIs(playground()->url());
});

it('is case insensitive', function (): void {
    $this->visit(playground()->url('/test/interacting-with-elements'))
        ->clickLink('home')
        ->assertUrlIs(playground()->url());
});
