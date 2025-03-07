<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertAttribute', function () {
    it('passes when element has attribute with specific value', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertAttribute('#i-have-data-testid', 'data-testid', 'i-exist-to-be-seen');
    });

    it('fails when element does not have attribute with specific value', function () {
        $this->visit(playgroundUrl('/test/interactive-elements'))
            ->assertAttribute('#i-have-data-testid', 'data-testid', 'non-existent-value');
    })->throws(ExpectationFailedException::class);
});
