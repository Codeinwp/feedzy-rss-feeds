/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { createAndRunSampleImport, deleteAllFeedImports } from '../utils';

test.describe('Logger', () => {
	test.beforeEach(async ({ requestUtils }) => {
		await deleteAllFeedImports(requestUtils);
		await requestUtils.deleteAllPosts();
		await requestUtils.deleteAllMedia();
	});

	test('check settings rendering', async ({ page, admin }) => {
		await admin.visitAdminPage('admin.php?page=feedzy-settings');

		await expect(
			page.locator('select[name="logs-logging-level"]')
		).toBeVisible();

		await expect(page.getByText('Report errors via email')).toBeVisible();
	});

	test('check logs tabs', async ({ page, admin }) => {
		await admin.visitAdminPage('admin.php?page=feedzy-settings');

		await page
			.locator('select[name="logs-logging-level"]')
			.selectOption('debug');

		await page
			.getByRole('button', { name: 'Save Settings' })
			.click({ force: true });

		// Create some logs via a sample import.
		await createAndRunSampleImport(page);

		await admin.visitAdminPage('admin.php?page=feedzy-settings&tab=logs');

		await expect(
			page.getByRole('heading', { name: 'Recent Logs' })
		).toBeVisible();

		// Check that logs are displayed.
		expect(
			await page.locator('.fz-log-container--info').count()
		).toBeGreaterThan(0);
		expect(
			await page.locator('.fz-log-container--debug').count()
		).toBeGreaterThan(0);

		// Filter messages by Debug.
		await page.getByRole('link', { name: 'Debug' }).click();
		expect(await page.locator('.fz-log-container--info').count()).toBe(0);
		expect(
			await page.locator('.fz-log-container--debug').count()
		).toBeGreaterThan(0);
	});
});
