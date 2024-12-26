/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe('Feedzy Loop', () => {
	const FEED_URL =
		'https://s3.amazonaws.com/verti-utils/sample-feed-import.xml';

	test('add Feedzy Loop Block', async ({ editor, page }) => {
		await page.goto('/wp-admin/post-new.php');

		if (
			(await page.$(
				'.edit-post-welcome-guide .components-modal__header button'
			)) !== null
		) {
			await page.click(
				'.edit-post-welcome-guide .components-modal__header button'
			);
		}

		await page.getByLabel('Add title').click();
		await page.keyboard.type('Feedzy Loop Test');

		await page.getByLabel('Toggle block inserter').click();

		await page.getByPlaceholder('Search').click();
		await page.keyboard.type('Feedzy Loop');
		await page.waitForTimeout(1000);

		await page.getByRole('option', { name: ' Feedzy Loop' }).click();
		await page.waitForTimeout(1000);
		await page.getByLabel('Feed URL').click();

		await page.getByPlaceholder('Enter feed URLs separated by').click();
		await page.keyboard.type(FEED_URL);

		await page.getByRole('button', { name: 'Save', exact: true }).click();
		await page.waitForTimeout(1000);

		await page
			.getByRole('button', { name: 'Publish', exact: true })
			.click();
		await page.waitForTimeout(1000);

		await page
			.getByLabel('Editor publish')
			.getByRole('button', { name: 'Publish', exact: true })
			.click();
		await page.waitForTimeout(5000);

		const snackbar = await page.getByTestId('snackbar');
		const snackbarText = await snackbar.textContent();
		expect(snackbarText).toContain('Post published.');

		await page.goto('/wp-admin/edit.php');

		const postTitle = await page.locator('a.row-title').first();
		await postTitle.hover();
		await page.getByLabel('View “Feedzy Loop Test”').click();

		await page.waitForTimeout(5000);

		// We want to confirm .wp-block-feedzy-rss-feeds-loop is present and it has 5 children
		const feedzyLoop = await page.$('.wp-block-feedzy-rss-feeds-loop');
		expect(feedzyLoop).not.toBeNull();

		const feedzyLoopChildren = await feedzyLoop.$$(':scope > *');
		expect(feedzyLoopChildren.length).toBe(5);
	});
});
