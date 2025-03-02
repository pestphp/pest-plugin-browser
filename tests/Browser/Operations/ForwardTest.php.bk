<?php

declare(strict_types=1);

test('navigates forward', function (): void {
    $this->visit('/')
        ->clickLink('Interacting with Elements')
        ->assertUrlIs(playgroundUrl('/test/interacting-with-elements'))
        ->back()
        ->assertUrlIs(playgroundUrl())
        ->forward()
        ->assertUrlIs(playgroundUrl('/test/interacting-with-elements'));
});
