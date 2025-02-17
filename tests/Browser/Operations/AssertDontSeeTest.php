<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertDontSee', function () {
    it('passes when the text is not present in the page', function () {
        $this->visit('https://laravel.com')
            ->assertDontSee('Non-existent text');
    });

    it('fails when the text is present in the page', function () {
        $this->visit('https://laravel.com')
            ->assertDontSee('Laravel');
    })->throws(ExpectationFailedException::class);
});
