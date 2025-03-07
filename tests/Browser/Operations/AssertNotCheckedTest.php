<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertNotChecked', function () {
    it('passes when checkbox is not checked', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertNotChecked('input[name="default-checkbox"]');
    });

    it('fails when checkbox is checked', function () {
        $this->visit(playgroundUrl('/test/form-inputs'))
            ->assertNotChecked('input[name="checked-checkbox"]');
    })->throws(ExpectationFailedException::class);
});
