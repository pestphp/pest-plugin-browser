<?php

declare(strict_types=1);

use Symfony\Component\Process\Exception\ProcessFailedException;

describe('assert not present', function () {
    it('passes when the given element is not present', function () {
        $this->visit('https://laravel.com')
            ->assertNotPresent('img[alt=Symfony]');
    });

    it('fails when the given element is present', function () {
        $this->visit('https://laravel.com')
            ->assertNotPresent('img[alt=Laravel]');
    })->throws(ProcessFailedException::class);
});
