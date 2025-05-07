/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe('Feedzy Loop', () => {
	const FEED_URL =
		'https://s3.amazonaws.com/verti-utils/sample-feed-import.xml';
	const POST_TITLE = `Feedzy Loop Test ${Math.floor(Math.random() * 1000)}`;

	test('add Feedzy Loop Block', async ({ editor, page }) => {
		await page.goto('/wp-admin/post-new.php?post_type=feedzy_categories');
		await page.getByLabel('Add title').click();
		await page.keyboard.type('Group One');

		await page.locator('textarea[name="feedzy_category_feed"]').click();
		await page.keyboard.type(FEED_URL);
		await page
			.getByRole('button', { name: 'Publish', exact: true })
			.click();
		await page.waitForTimeout(1000);

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
		await page.keyboard.type(POST_TITLE);

		await page.getByLabel('Add block').click();

		await page.getByPlaceholder('Search').click();
		await page.keyboard.type('Feedzy Loop');
		await page.waitForTimeout(1000);

		await page.getByRole('option', { name: ' Feedzy Loop' }).click();
		await page.waitForTimeout(1000);

		await page.getByPlaceholder('Enter URLs or select a').click();
		await page.keyboard.type(FEED_URL);

		const loadFeedButton = await page.getByRole('button', {
			name: 'Load Feed',
			exact: true,
		});
		const isDisabled = await loadFeedButton.isDisabled();
		expect(isDisabled).toBe(false);
		await loadFeedButton.click();
		await page.waitForTimeout(1000);

		await page.getByLabel('Display curated RSS content').click();
		await page.waitForTimeout(1000);

		// Now that we have tested we can insert URL, we can test the Feed Group.

		await page
			.getByLabel('Block: Feedzy Loop')
			.locator('div')
			.nth(1)
			.click();
		await page.getByRole('button', { name: 'Edit Feed' }).click();

		await page.getByRole('button', { name: 'Select Feed Group' }).click();
		await page.locator('.fz-dropdown-item').first().click();

		await page
			.getByRole('button', { name: 'Load Feed', exact: true })
			.click();
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
		await page.getByLabel('View “' + POST_TITLE + '”').click();

		await page.waitForTimeout(5000);

		// We want to confirm .wp-block-feedzy-rss-feeds-loop is present and it has 5 children
		const feedzyLoop = await page.$('.wp-block-feedzy-rss-feeds-loop');
		expect(feedzyLoop).not.toBeNull();

		const feedzyLoopChildren = await feedzyLoop.$$(':scope > *');
		expect(feedzyLoopChildren.length).toBe(5);
	});
});
