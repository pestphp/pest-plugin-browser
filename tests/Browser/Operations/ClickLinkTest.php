<?php

declare(strict_types=1);

use Pest\Browser\Compiler;

use function Pest\Browser\visit;

it('clicks a link', function (): void {
    visit('https://laravel.com')
        ->clickLink('Get Started');

    expect(file_get_contents(Compiler::TEST_PATH))
        ->toContain("await page.locator('a').filter({ hasText: /Get Started/i }).click();");
});
