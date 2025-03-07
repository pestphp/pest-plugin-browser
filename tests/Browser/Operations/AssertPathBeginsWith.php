<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertPathBeginsWith', function () {
    it('passes when the path starts with the expected value', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertPathBeginsWith('/test');
    });

    it('fails when the path does not start with the expected value', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertPathBeginsWith('/form');
    })->throws(ExpectationFailedException::class);
});
