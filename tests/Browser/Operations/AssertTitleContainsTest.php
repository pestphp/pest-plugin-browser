<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertTitleContains', function () {
    it('passes when the title contains the expected string', function () {
        $this->visit(playgroundUrl())
            ->assertTitleContains('Plugin Browser');
    });

    it('fails when the title does not contain the expected string', function () {
        $this->visit(playgroundUrl())
            ->assertTitleContains('Laravel');
    })->throws(ExpectationFailedException::class);
});
