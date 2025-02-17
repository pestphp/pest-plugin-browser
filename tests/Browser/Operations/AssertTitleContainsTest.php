<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertTitleContains', function () {
    it('passes when title contains expected string', function () {
        $this->visit('https://laravel.com')
            ->assertTitleContains('Laravel');
    });

    it('fails when title does not contain expected string', function () {
        $this->visit('https://laravel.com')
            ->assertTitleContains('Symfony');
    })->throws(ExpectationFailedException::class);
});
