<?php

use function Pest\Browser\visit;

it('may have a title at playwright', function () {
    visit('https://playwright.dev/')
        ->toHaveTitle('Playwright');
});

it('may have a title at laravel', function () {
    visit('https://laravel.com')
        ->toHaveTitle('Laravel');
});

it('has no title pest', function () {
    visit('https://laravel.com')
        ->doesntHaveTitle('Pest');
});

it('has laravel in URL', function () {
    visit('https://laravel.com')
        ->toHaveURL('.*laravel.com');
});

it('has no title at laravel', function () {
    visit('https://laravel.com')
        ->doesntHaveURL('.*pest.com');
});
