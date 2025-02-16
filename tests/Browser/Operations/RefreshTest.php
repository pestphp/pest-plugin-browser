<?php

declare(strict_types=1);

test('refreshes', function (): void {
    $this->visit('/')
        ->assertUrlIs('http://127.0.0.1:9357')
        ->assertSee('Pest is a testing framework')
        ->refresh()
        ->assertUrlIs('http://127.0.0.1:9357')
        ->assertSee('Pest is a testing framework');
});
