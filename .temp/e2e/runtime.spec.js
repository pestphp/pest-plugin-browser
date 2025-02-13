import { test, expect } from '@playwright/test';

test('runtime', async ({ page }) => {
    await page.goto('https://laravel.com');
await page.getByRole('link', { name: 'Watch Laracasts' }).click();
});