<?php

declare(strict_types=1);

describe('visit', function () {
    it('visits the given URL', function () {
        $this->visit(playgroundUrl())
            ->assertUrlIs(playgroundUrl());
    });
});
