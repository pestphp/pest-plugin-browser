<?php

declare(strict_types=1);

it('clicks a link using its text and a given selector', function (): void {
    $this->visit('https://laravel.com')
        ->clickLink('Get Started', 'a.border-red-500');

    expect(file_get_contents(Compiler::TEST_PATH))
        ->toContain("await page.locator('a.border-red-500').filter({ hasText: /Get Started/i }).click();");
});
