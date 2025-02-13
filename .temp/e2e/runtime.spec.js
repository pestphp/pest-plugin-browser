import { test, expect } from '@playwright/test';

test('runtime', async ({ page }) => {
	await page.goto('https://laravel.com');
	await page.locator('a').filter({ hasText: /Get Started/i }).click();

    const response = await page.reload();
    test.info().annotations.push({
        type: '_response',
        description: JSON.stringify({
            headers: response.headers(),
            status: response.status(),
            url: response.url(),
        })
    });
});