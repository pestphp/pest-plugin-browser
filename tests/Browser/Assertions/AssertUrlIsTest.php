<?php

declare(strict_types=1);

use Pest\Browser\Compiler;

use function Pest\Browser\visit;

it('can verify the current page URL is matching the expected one', function (): void {
    visit('https://laravel.com')
        ->assertUrlIs('https://laravel.com');

    expect(file_get_contents(Compiler::TEST_PATH))
        ->toContain("await expect(page.url()).toBe('https://laravel.com');");
});
