<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertUrlIs', function () {
    it('passes when the current URL matches the expected URL', function (): void {
        $this->visit(playgroundUrl())
            ->assertUrlIs(playgroundUrl());
    });

    it('fails when the current URL does not match the expected URL', function (): void {
        $this->visit(playgroundUrl())
            ->assertUrlIs('https://example.com/');
    })->throws(ExpectationFailedException::class);
});
