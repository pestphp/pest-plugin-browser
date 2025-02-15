<?php

declare(strict_types=1);

use Pest\Browser\Compiler;

use function Pest\Browser\visit;

it('can verify the current page URL is matching the expected one', function (): void {
    visit('https://laravel.com')
        ->assertUrlIs('https://laravel.com');

    expect(file_get_contents(Compiler::TEST_PATH))
        ->toContain('await expect(page.url()).toMatch(/https\:\/\/laravel\.com/i);');
});

it('can use the asterisk wildcard', function (): void {
    visit('https://laravel.com/docs/11.x')
        ->assertUrlIs('https://laravel.com/*/11.x');

    expect(file_get_contents(Compiler::TEST_PATH))
        ->toContain('await expect(page.url()).toMatch(/https\:\/\/laravel\.com\/.*\/11\.x/i);');
});
