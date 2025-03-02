<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertHostIs', function () {
    it('passes when host matches', function () {
        $this->visit(playgroundUrl())
            ->assertHostIs('localhost');
    });

    it('fails when host doesn\'t match', function () {
        $this->visit(playgroundUrl())
            ->assertHostIs('laravel.com');
    })->throws(ExpectationFailedException::class);
});
