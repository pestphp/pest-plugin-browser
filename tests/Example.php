<?php

declare(strict_types=1);

use function Pest\Browser\visit;

it('may have a title at playwright', function () {
    visit('https://playwright.dev/')
        ->toHaveTitle('Fast and reliable end-to-end testing for modern web apps | Playwright')
        ->toHaveTitle('/testing/')
        ->toHaveTitle('/.*Playwright$/');
});

it('may have a title at laravel', function () {
    visit('https://laravel.com')
        ->toHaveTitle('Laravel - The PHP Framework For Web Artisans')
        ->toHaveTitle('/Framework/')
        ->toHaveTitle('/.*Artisans$/');
});
