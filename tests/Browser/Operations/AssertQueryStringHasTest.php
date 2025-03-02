<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertQueryStringHas', function () {
    it(
        'passes when the query string contains the given query param',
        function (string $path, string $expectedParam, ?string $expectedValue = null) {
            $this->visit(playgroundUrl($path))
                ->assertQueryStringHas($expectedParam, $expectedValue);
        }
    )->with([
        ['/?q=test', 'q', 'test'],
        ['/?q', 'q', null],
    ]);

    it('fails when the query string does\'t contain the given query param', function () {
        $this->visit(playgroundUrl('/?q=test'))
            ->assertQueryStringHas('s');
    })->throws(ExpectationFailedException::class);
});
