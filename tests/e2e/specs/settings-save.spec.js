/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe('Settings save', () => {
	test('general settings persist after saving', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=feedzy-settings');
		if (page.url().includes('feedzy-support')) {
			await page.goto('/wp-admin/admin.php?page=feedzy-settings');
		}

		const toggle = page.locator('#disable-default-style');
		await toggle.waitFor({ state: 'attached' });
		const initialState = await toggle.isChecked();

		// Flip the toggle and save.
		await page
			.locator('label[for="disable-default-style"]')
			.click({ force: true });
		await page
			.getByRole('button', { name: 'Save Settings' })
			.click({ force: true });
		await expect(
			page.getByText('Your settings were saved.')
		).toBeVisible();

		// The new value survives a reload.
		await page.goto('/wp-admin/admin.php?page=feedzy-settings');
		await toggle.waitFor({ state: 'attached' });
		expect(await toggle.isChecked()).toBe(!initialState);

		// Restore the original value.
		await page
			.locator('label[for="disable-default-style"]')
			.click({ force: true });
		await page
			.getByRole('button', { name: 'Save Settings' })
			.click({ force: true });
		await expect(
			page.getByText('Your settings were saved.')
		).toBeVisible();
	});
});
