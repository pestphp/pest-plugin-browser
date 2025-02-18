<?php

declare(strict_types=1);

use Symfony\Component\Process\Exception\ProcessFailedException;

describe('assert missing', function () {
    it('passes when the given element is not visible', function () {
        $this->visit('https://laravel.com')
            ->assertMissing('.DocSearch-Button-Keys');
    });

    it('fails when the given element is visible', function () {
        $this->visit('https://laravel.com')
            ->assertMissing('body');
    })->throws(ProcessFailedException::class);
});
