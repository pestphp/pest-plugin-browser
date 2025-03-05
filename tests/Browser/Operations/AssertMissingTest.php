<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertMissing', function () {
    it('passes when the given element is not visible', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertMissing('#invisible-element');
    });

    it('fails when the given element is visible', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertMissing('#i-have-data-testid');
    })->throws(ExpectationFailedException::class);
});
