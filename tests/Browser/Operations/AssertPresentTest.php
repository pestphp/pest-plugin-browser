<?php

declare(strict_types=1);

use Symfony\Component\Process\Exception\ProcessFailedException;

describe('assert present', function () {
    it('passes when the given element is present', function () {
        $this->visit('https://laravel.com')
            ->assertPresent('img[alt=Laravel]');
    });

    it('fails when the given element is not present', function () {
        $this->visit('https://laravel.com')
            ->assertPresent('img[alt=Symfony]');
    })->throws(ProcessFailedException::class);
});
