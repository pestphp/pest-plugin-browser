<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertTitle', function () {
    it('passes when title matches', function () {
        $this->visit('https://laravel.com')
            ->assertTitle('Laravel - The PHP Framework For Web Artisans');
    });

    it('fails when title does not match', function () {
        $this->visit('https://laravel.com')
            ->assertTitle('Laravel - The PHP Framework For Cute Kitties');
    })->throws(ExpectationFailedException::class);
});
