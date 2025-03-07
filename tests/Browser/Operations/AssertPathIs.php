<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertPathIs', function () {
    it('passes when the path equals expected value', function (string $path, string $expected) {
        $this->visit(playgroundUrl($path))
            ->assertPathIs($expected);
    })->with([
        ['/test/form-inputs', '/test/form-inputs'],
        ['/test/form-inputs', '/^.+form.+$/'],
    ]);

    it('fails when the path does not equal expected value', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertPathIs('/test/not-expected');
    })->throws(ExpectationFailedException::class);
});
