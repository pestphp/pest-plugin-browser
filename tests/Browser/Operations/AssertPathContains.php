<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertPathContains', function () {
    it('passes when the path contains expected value', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertPathContains('form');
    });

    it('fails when the path doesn\'t contain expected value', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertPathContains('laravel');
    })->throws(ExpectationFailedException::class);
});
