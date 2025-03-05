<?php

declare(strict_types=1);

describe('back', function () {
    it('navigates back', function (): void {
        $this->visit(playgroundUrl())
            ->clickLink('Interacting with Elements')
            ->assertUrlIs(playgroundUrl('/test/interacting-with-elements'))
            ->back()
            ->assertUrlIs(playgroundUrl());
    });
});
