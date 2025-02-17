<?php

declare(strict_types=1);

test('refreshes', function (): void {
    $this->visit('/')
        ->assertUrlIs('http://localhost:9357')
        ->assertSee('Pest is a testing framework')
        ->refresh()
        ->assertUrlIs('http://localhost:9357')
        ->assertSee('Pest is a testing framework');
});
