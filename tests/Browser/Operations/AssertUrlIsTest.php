<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertUrlIs', function () {
    it('passes when url matches', function () {
        $this->visit('https://laravel.com')
            ->assertUrlIs('https://laravel.com');
    });

    it('fails when url does not match', function () {
        $this->visit('https://laravel.com')
            ->assertUrlIs('https://symfony.com');
    })->throws(ExpectationFailedException::class);
});
