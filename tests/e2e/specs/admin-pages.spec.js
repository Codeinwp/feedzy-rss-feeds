/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe('Admin pages health', () => {
	test('import posts list page renders', async ({ page }) => {
		await page.goto('/wp-admin/edit.php?post_type=feedzy_imports');

		await expect(
			page.getByRole('heading', { name: 'Import Posts' })
		).toBeVisible();
	});

	test('feed groups list page renders', async ({ page }) => {
		await page.goto('/wp-admin/edit.php?post_type=feedzy_categories');

		await expect(
			page.getByRole('heading', { name: 'Feed Groups' })
		).toBeVisible();
	});

	test('settings page renders its tabs and save control', async ({
		page,
	}) => {
		await page.goto('/wp-admin/admin.php?page=feedzy-settings');
		if (page.url().includes('feedzy-support')) {
			await page.goto('/wp-admin/admin.php?page=feedzy-settings');
		}

		const tabs = page.locator('#fz-features');
		await expect(
			tabs.getByRole('link', { name: 'General' })
		).toBeVisible();
		await expect(tabs.getByRole('link', { name: 'Logs' })).toBeVisible();
		await expect(
			page.getByRole('button', { name: 'Save Settings' })
		).toBeVisible();
	});
});
