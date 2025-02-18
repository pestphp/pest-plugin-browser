<?php

declare(strict_types=1);

test('refreshes', function (): void {
    $this->visit('/')
        ->assertUrlIs(playgroundUrl())
        ->assertSee('Pest is a testing framework')
        ->refresh()
        ->assertUrlIs(playgroundUrl())
        ->assertSee('Pest is a testing framework');
});
