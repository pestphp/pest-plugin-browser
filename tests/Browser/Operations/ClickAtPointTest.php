<?php

declare(strict_types=1);

it('clicks an element using x and y coordinates', function (): void {
    $this->visit('/test/interacting-with-elements')
        ->assertSee('Home')
        ->pause(500)
        ->clickAtPoint(320, 640)
        ->assertUrlIs(playgroundUrl());
});
