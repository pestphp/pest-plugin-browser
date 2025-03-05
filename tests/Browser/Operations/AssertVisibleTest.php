<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertVisible', function () {
    it('passes when the given element is visible', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertVisible('#i-have-data-testid');
    });

    it('fails when the given element is not visible', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertVisible('#invisible-element');
    })->throws(ExpectationFailedException::class);
});
