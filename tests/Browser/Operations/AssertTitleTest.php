<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertTitle', function () {
    it('passes when the title matches', function (): void {
        $this->visit(playgroundUrl())
            ->assertTitle('Pest Plugin Browser - Playground');
    });

    it('fails when the title does not match', function (): void {
        $this->visit(playgroundUrl())
            ->assertTitle('Best Plugin Browser - Playground');
    })->throws(ExpectationFailedException::class);
});
