<?php

declare(strict_types=1);

describe('forward', function () {
    it('navigates forward', function (): void {
        $this->visit(playgroundUrl())
            ->clickLink('Interacting with Elements')
            ->assertUrlIs(playgroundUrl('/test/interacting-with-elements'))
            ->back()
            ->assertUrlIs(playgroundUrl())
            ->forward()
            ->assertUrlIs(playgroundUrl('/test/interacting-with-elements'));
    });
});
