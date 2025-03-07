<?php

declare(strict_types=1);

describe('clickLink', function () {
    it('clicks a link', function ($linkText): void {
        $this->visit(playgroundUrl('/test/interacting-with-elements'))
            ->clickLink($linkText)
            ->assertUrlIs(playgroundUrl());
    })->with([
        ['Home'],
        ['home'],
    ]);
});
