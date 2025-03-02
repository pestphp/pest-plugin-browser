<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertQueryStringMissing', function () {
    it('passes when the query string does not have the given query param', function () {
        $this->visit(playgroundUrl('/?q=test'))
            ->assertQueryStringHas('q')
            ->assertQueryStringHas('q', 'test')
            ->assertQueryStringMissing('q', 'test-1');
    });

    it('fails when the query string has the given query param', function () {
        $this->visit(playgroundUrl('/?q=test'))
            ->assertQueryStringMissing('q');
    })->throws(ExpectationFailedException::class);
});
