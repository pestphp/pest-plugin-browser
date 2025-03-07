<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertChecked', function () {
    it('passes when checkbox is checked', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertChecked('input[name="checked-checkbox"]');
    });

    it('fails when checkbox is not checked', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertChecked('input[name="default-checkbox"]');
    })->throws(ExpectationFailedException::class);
});
