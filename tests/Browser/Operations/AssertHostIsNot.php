<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertHostIsNot', function () {
    it('passes when host doesn\'t match', function () {
        $this->visit(playgroundUrl())
            ->assertHostIsNot('example.com');
    });

    it('fails when host matches', function () {
        $this->visit(playgroundUrl())
            ->assertHostIsNot('localhost');
    })->throws(ExpectationFailedException::class);
});
