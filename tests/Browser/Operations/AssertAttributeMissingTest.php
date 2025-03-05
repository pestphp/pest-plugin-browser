<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertAttributeMissing', function () {
    it('passes when attribute missing', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertAttributeMissing('#i-have-data-testid', 'attr-that-does-not-exist-on-the-element');
    });

    it('fails when attribute present', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertAttributeMissing('#i-have-data-testid', 'data-testid');
    })->throws(ExpectationFailedException::class);
});
