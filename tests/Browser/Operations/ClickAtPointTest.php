<?php

declare(strict_types=1);

it('clicks an element using x and y coordinates', function (): void {
    $this->visit('/test/interacting-with-elements')
        ->clickAtPoint(1050, 70)
        ->assertSee('Single clicked!');
});
