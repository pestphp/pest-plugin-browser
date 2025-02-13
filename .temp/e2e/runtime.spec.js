import { test, expect } from '@playwright/test';

test('runtime', async ({ page }) => {
    await page.goto('https://laravel.com');
await expect(page).not.toHaveURL(/.*pest.com/);
});