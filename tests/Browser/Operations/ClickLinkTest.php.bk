<?php

declare(strict_types=1);

it('clicks a link', function (): void {
    $this->visit('/test/interacting-with-elements')
        ->clickLink('Home')
        ->assertUrlIs(playgroundUrl());
});

it('is case insensitive', function (): void {
    $this->visit('/test/interacting-with-elements')
        ->clickLink('home')
        ->assertUrlIs(playgroundUrl());
});
