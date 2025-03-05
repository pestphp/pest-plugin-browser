<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertAttributeContains', function () {
    it('passes when attribute contains specified text', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertAttributeContains('#i-have-data-testid', 'data-testid', 'to-be-seen');
    });

    it('fails when attribute does not contain specified text', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertAttributeContains('#i-have-data-testid', 'data-testid', 'non-existent-text');
    })->throws(ExpectationFailedException::class);
});
