import { test, expect } from '@playwright/test';

test('runtime', async ({ page }) => {
    await page.goto('https://laravel.com');
await page.locator('footer').screenshot({ path: '/Users/franciscobarrento/Codex/open-source/pest/pest-plugin-browser/tests/Feature/section.png' });
});