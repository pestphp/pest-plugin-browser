<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertAttributeDoesntContain', function () {
    it('passes when attribute does not contain specified text', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertAttributeDoesntContain('#i-have-data-testid', 'data-testid', 'non-existent-text');
    });

    it('fails when attribute contains specified text', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertAttributeDoesntContain('#i-have-data-testid', 'data-testid', 'to-be-seen');
    })->throws(ExpectationFailedException::class);
});
