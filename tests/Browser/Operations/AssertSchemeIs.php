<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertSchemeIs', function () {
    it('passes when the scheme matches', function () {
        $this->visit(playgroundUrl())
            ->assertSchemeIs('http');
    });

    it('fails when the scheme does not match', function () {
        $this->visit(playgroundUrl())
            ->assertSchemeIs('https');
    })->throws(ExpectationFailedException::class);
});
