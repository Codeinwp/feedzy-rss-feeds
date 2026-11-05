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

	test.describe('dashboard tabs', () => {
		/** @type {string[]} */
		let pageErrors;

		/**
		 * Open a Dashboard tab, retrying once past the first-visit redirect.
		 *
		 * @param {import('@playwright/test').Page} page Playwright page.
		 * @param {string}                          tab  Tab slug.
		 * @return {Promise<void>}
		 */
		const openTab = async (page, tab) => {
			const url = `/wp-admin/admin.php?page=feedzy-support&tab=${tab}`;
			await page.goto(url);
			if (!page.url().includes(`tab=${tab}`)) {
				await page.goto(url);
			}
		};

		test.beforeEach(({ page }) => {
			pageErrors = [];
			page.on('pageerror', (error) => pageErrors.push(error.message));
		});

		test('license tab loads without script errors', async ({ page }) => {
			await openTab(page, 'license');

			await expect(page.locator('.fz-form-wrap')).toBeAttached();
			expect(pageErrors).toEqual([]);
		});

		test('improve tab opens the feedback survey', async ({ page }) => {
			await openTab(page, 'improve');

			await page.getByRole('button', { name: 'Take the Survey' }).click();

			await expect(
				page.getByRole('dialog', {
					name: "What's one thing you need in Feedzy?",
				})
			).toBeVisible();
			expect(pageErrors).toEqual([]);
		});
	});
});
