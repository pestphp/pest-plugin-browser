<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertSchemeIsNot', function () {
    it('passes when the scheme doesn\'t match', function () {
        $this->visit(playgroundUrl())
            ->assertSchemeIsNot('https');
    });

    it('fails when the scheme matches', function () {
        $this->visit(playgroundUrl())
            ->assertSchemeIsNot('http');
    })->throws(ExpectationFailedException::class);
});
