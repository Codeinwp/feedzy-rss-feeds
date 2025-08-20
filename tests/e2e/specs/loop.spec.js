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

		await page.getByLabel('Display curated RSS content').first().click();
		await page.waitForTimeout(1000);

		// Now that we have tested we can insert URL, we can test the Feed Group.

		await page.locator('.wp-block-feedzy-rss-feeds-loop').nth(1).click();
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

	test('check validation for invalid URL', async ({
		editor,
		page,
		admin,
	}) => {
		await admin.createNewPost();

		await editor.insertBlock({
			name: 'feedzy-rss-feeds/loop',
		});

		await page
			.getByPlaceholder('Enter URLs or select a Feed')
			.fill('http://invalid-url.com/feed');
		await page.getByRole('button', { name: 'Load Feed' }).click();

		await page.waitForSelector('.feedzy-validation-results');

		await expect(
			page
				.locator('.feedzy-validation-results .is-error')
				.getByText('http://invalid-url.com/feed', { exact: true })
		).toBeVisible();
	});

	test('check validation for invalid and valid url', async ({
		editor,
		page,
		admin,
	}) => {
		await admin.createNewPost();

		await editor.insertBlock({
			name: 'feedzy-rss-feeds/loop',
		});

		await page
			.getByPlaceholder('Enter URLs or select a Feed')
			.fill(
				'http://invalid-url.com/feed, https://www.nasa.gov/feeds/iotd-feed/'
			);
		await page.getByRole('button', { name: 'Load Feed' }).click();

		await page.waitForSelector('.feedzy-validation-results');

		await expect(
			page
				.locator('.feedzy-validation-results .is-error')
				.getByText('http://invalid-url.com/feed', { exact: true })
		).toBeVisible();

		await expect(
			page
				.locator('.feedzy-validation-results .is-success')
				.getByText('https://www.nasa.gov/feeds/iotd-feed/', {
					exact: true,
				})
		).toBeVisible();
	});

	test('check thumbnail display', async ({ editor, page, admin }) => {
		await admin.createNewPost();

		await editor.insertBlock({
			name: 'feedzy-rss-feeds/loop',
			attributes: {
				feed: {
					type: 'url',
					source: 'https://www.nasa.gov/feeds/iotd-feed/',
				},
				query: {
					max: 1,
				},
			},
		});

		await page
			.getByLabel('Display curated RSS content')
			.click({ force: true });

		await page.waitForSelector('.feedzy-loop-columns-1');

		expect(
			await page
				.locator(`.wp-block-feedzy-rss-feeds-loop img[src*="https"]`)
				.count()
		).toBeGreaterThan(0);
	});

	test('check no thumbnail display', async ({ editor, page, admin }) => {
		await admin.createNewPost();

		await editor.insertBlock({
			name: 'feedzy-rss-feeds/loop',
			attributes: {
				feed: {
					type: 'url',
					source: 'https://www.nasa.gov/feeds/iotd-feed/',
				},
				query: {
					max: 1,
				},
				thumb: 'no',
			},
		});

		await page
			.getByLabel('Display curated RSS content')
			.click({ force: true });

		await page.waitForSelector('.feedzy-loop-columns-1');

		expect(
			await page
				.locator(`.wp-block-feedzy-rss-feeds-loop img[src*="https"]`)
				.count()
		).toBe(0);
	});

	test('check default SVG thumbnail display', async ({
		editor,
		page,
		admin,
	}) => {
		await admin.createNewPost();

		await editor.insertBlock({
			name: 'feedzy-rss-feeds/loop',
			attributes: {
				feed: {
					type: 'url',
					source: 'https://fasterthanli.me/index.xml',
				},
				query: {
					max: 1,
				},
				thumb: 'yes',
			},
		});

		await page
			.getByLabel('Display curated RSS content')
			.click({ force: true });

		await page.waitForSelector('.feedzy-loop-columns-1');

		expect(
			await page
				.locator(`.wp-block-feedzy-rss-feeds-loop img[src*=".svg"]`)
				.count()
		).toBeGreaterThan(0);
	});
});
