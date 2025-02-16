<?php

declare(strict_types=1);

describe('click', function () {
    it('clicks on the given element', function () {
        $this->visit('https://laravel.com')
            ->click('header a[href="/docs/11.x"]:visible')
            ->assertUrlIs('https://laravel.com/docs/11.x');
    })->only();
});
