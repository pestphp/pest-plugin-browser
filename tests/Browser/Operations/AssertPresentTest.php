<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assert present', function () {
    it('passes when the given element is present', function ($selector) {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertPresent($selector);
    })->with([
        ['#i-have-data-testid'],
        ['#invisible-element'],
    ]);

    it('fails when the given element is not present', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertPresent('#unexistent-element-for-sure-not-to-be-present');
    })->throws(ExpectationFailedException::class);
});
