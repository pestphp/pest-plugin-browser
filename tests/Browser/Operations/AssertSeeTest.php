<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;

describe('assertSee', function () {
    it('passes when the text is present in the page', function () {
        $this->visit('https://laravel.com')
            ->assertSee('The PHP Framework')
            ->assertSee('the php framework', true);
    });

    it('fails when the text is not present in the page', function () {
        $this->visit('https://laravel.com')
            ->assertSee('Non-existent text');
    })->throws(ExpectationFailedException::class);
});
