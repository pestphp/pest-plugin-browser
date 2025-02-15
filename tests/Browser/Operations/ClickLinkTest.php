<?php

declare(strict_types=1);

use Pest\Browser\Compiler;

use function Pest\Browser\visit;

it('clicks a link using its text', function (): void {
    visit('https://laravel.com')
        ->clickLink('Get Started');

    expect(file_get_contents(Compiler::TEST_PATH))
        ->toContain("await page.locator('a').filter({ hasText: /Get Started/i }).click();");
});

it('clicks a link using its text and a given selector', function (): void {
    visit('https://laravel.com')
        ->clickLink('Get Started', 'a.border-red-500');

    expect(file_get_contents(Compiler::TEST_PATH))
        ->toContain("await page.locator('a.border-red-500').filter({ hasText: /Get Started/i }).click();");
});
