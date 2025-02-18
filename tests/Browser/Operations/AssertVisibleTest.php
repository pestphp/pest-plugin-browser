<?php

declare(strict_types=1);

use Symfony\Component\Process\Exception\ProcessFailedException;

describe('assert visible', function () {
    it('passes when the given element is visible', function () {
        $this->visit('https://laravel.com')
            ->assertVisible('body');
    });

    it('fails when the given element is not visible', function () {
        $this->visit('https://laravel.com')
            ->assertVisible('.DocSearch-Button-Keys');
    })->throws(ProcessFailedException::class);
});
