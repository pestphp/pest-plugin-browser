<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertPathEndsWith', function () {
    it('passes when the path ends with expected value', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertPathEndsWith('inputs');
    });

    it('fails when the path does not end with expected value', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertPathEndsWith('laravel');
    })->throws(ExpectationFailedException::class);
});
