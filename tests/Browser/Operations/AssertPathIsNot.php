<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertPathIsNot', function () {
    it('passes when the current path does not match the given path', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertPathIsNot('/docs/11.x');
    });

    it('fails when the current path matches the given path', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertPathIsNot('/test/form-inputs');
    })->throws(ExpectationFailedException::class);
});
