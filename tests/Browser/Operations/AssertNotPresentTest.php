<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assert not present', function () {
    it('passes when the given element is not present', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertNotPresent('#unexistent-element-for-sure-not-to-be-present');
    });

    it('fails when the given element is present', function ($selector) {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertNotPresent($selector);
    })->with([
        ['#i-have-data-testid'],
        ['#invisible-element'],
    ])->throws(ExpectationFailedException::class);
});
