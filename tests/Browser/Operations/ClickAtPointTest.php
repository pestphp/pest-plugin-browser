<?php

declare(strict_types=1);

it('clicks an element using x and y coordinates', function (): void {
    $this->visit('/')
        ->assertSee('Click me')
        ->assertDontSee('Single clicked!')
        ->clickAtPoint(640, 970)
        ->assertSee('Single clicked!')
        ->assertDontSee('Click me');
});
