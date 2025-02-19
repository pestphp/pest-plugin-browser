<?php

declare(strict_types=1);

it('clicks an element using x and y coordinates', function (): void {
    $this->visit('/test/interacting-with-elements')
        ->assertSee('Home')
        ->clickAtPoint(320, 640)
        ->assertUrlIs(playgroundUrl());
});
