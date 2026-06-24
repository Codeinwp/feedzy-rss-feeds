/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe('Feedzy Classic Block', () => {
	test('check validation for invalid URL', async ({
		editor,
		page,
		admin,
	}) => {
		await admin.createNewPost();

		await editor.insertBlock({
			name: 'feedzy-rss-feeds/feedzy-block',
		});

		await page
			.getByPlaceholder('Enter URL or group of your')
			.fill('http://invalid-url.com/feed');

		await page.getByRole('button', { name: 'Load Feed' }).click();

		await page.waitForSelector('.feedzy-validation-results', { timeout: 30000 });

		await expect(
			page
				.locator('.feedzy-validation-results .is-error')
				.getByText('http://invalid-url.com/feed', { exact: true })
		).toBeVisible();
	});

	test('check validation for invalid and valid URL', async ({
		editor,
		page,
		admin,
	}) => {
		await admin.createNewPost();

		await editor.insertBlock({
			name: 'feedzy-rss-feeds/feedzy-block',
		});

		await page
			.getByPlaceholder('Enter URL or group of your')
			.fill(
				'http://invalid-url.com/feed, https://www.nasa.gov/feeds/iotd-feed/'
			);

		await page.getByRole('button', { name: 'Load Feed' }).click();

		await page.waitForSelector('.feedzy-validation-results', { timeout: 30000 });

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

	test('check aspect ratio default', async ({ editor, page, admin }) => {
		await admin.createNewPost();

		await editor.insertBlock({
			name: 'feedzy-rss-feeds/feedzy-block',
			attributes: {
				feeds: 'https://www.nasa.gov/feeds/iotd-feed/',
				max: 1,
			},
		});

		const postId = await editor.publishPost();
		await page.goto(`/?p=${postId}`);

		const image = page.locator('.feedzy-rss .rss_image img');
		await expect(image).toHaveAttribute(
			'style',
			'height:150px;width:150px;'
		);
	});

	test('check aspect ratio (3/2)', async ({ editor, page, admin }) => {
		await admin.createNewPost();

		await editor.insertBlock({
			name: 'feedzy-rss-feeds/feedzy-block',
			attributes: {
				aspectRatio: '3/2',
				feeds: 'https://www.nasa.gov/feeds/iotd-feed/',
				max: 1,
			},
		});

		const postId = await editor.publishPost();
		await page.goto(`/?p=${postId}`);

		const image = page.locator('.feedzy-rss .rss_image img');
		await expect(image).toHaveAttribute('style', /aspect-ratio:\s*3\/2;/i);
	});

	test('check aspect ratio auto', async ({ editor, page, admin }) => {
		await admin.createNewPost();

		await editor.insertBlock({
			name: 'feedzy-rss-feeds/feedzy-block',
			attributes: {
				aspectRatio: 'auto',
				feeds: 'https://www.nasa.gov/feeds/iotd-feed/',
				max: 1,
			},
		});

		const postId = await editor.publishPost();
		await page.goto(`/?p=${postId}`);

		const image = page.locator('.feedzy-rss .rss_image img');
		await expect(image).toHaveAttribute('style', /aspect-ratio:\s*auto;/i);
	});

	test('embed youtube video', async ({ editor, page, admin }) => {
		await admin.createNewPost();

		await editor.insertBlock({
			name: 'feedzy-rss-feeds/feedzy-block',
			attributes: {
				feeds: 'https://www.youtube.com/feeds/videos.xml?channel_id=UCSHmNs-_UuU1CfPhSbilTZQ',
				max: 1,
			},
		});

		const postId = await editor.publishPost();
		await page.goto(`/?p=${postId}`);

		const rssContainer = page.locator('.rss_item').first();
		await expect(rssContainer).toBeVisible();

		const youtubeLink = rssContainer
			.locator('a[href*="youtube.com/"]')
			.first();
		await expect(youtubeLink).toBeVisible();

		const image = rssContainer.locator('img').first();
		await expect(image).toBeVisible();
	});

	test('create feedzy group with multiple URLs and load in classic block', async ({
		editor,
		page,
		admin,
	}) => {
		const FEED_URL_1 = 'https://themeisle.com/blog/feed/';
		const FEED_URL_2 = 'https://s3.amazonaws.com/verti-utils/sample-feed-import.xml';
		const GROUP_NAME = `Multi URL Group ${Math.floor(Math.random() * 10000)}`;
		const POST_TITLE = `Feedzy Classic Multi URL Test ${Math.floor(Math.random() * 10000)}`;

		await page.goto('/wp-admin/post-new.php?post_type=feedzy_categories');
		await page.locator('#title').click();
		await page.keyboard.type(GROUP_NAME);

		await page.locator('textarea[name="feedzy_category_feed"]').click();
		await page.keyboard.type(`${FEED_URL_1}, ${FEED_URL_2}`);
		
		await Promise.all([
			page.waitForURL(/post\.php\?post=\d+&action=edit/),
			page.getByRole('button', { name: 'Publish', exact: true }).click(),
		]);

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

		await editor.canvas.getByLabel('Add title').click();
		await page.keyboard.type(POST_TITLE);

		await editor.insertBlock({ name: 'feedzy-rss-feeds/feedzy-block' });

		await page.getByPlaceholder('Enter URL or group of your').click();
		await page.keyboard.type(GROUP_NAME);
		await page.waitForTimeout(500);

		const loadFeedButton = await page.getByRole('button', {
			name: 'Load Feed',
			exact: true,
		});
		const isDisabled = await loadFeedButton.isDisabled();
		expect(isDisabled).toBe(false);
		await loadFeedButton.click();
		await page.waitForTimeout(2000);

		const postId = await editor.publishPost();

		await page.goto(`/?p=${postId}`);
		await page.waitForTimeout(2000);

		const feedzyBlock = await page.$('.feedzy-rss');
		expect(feedzyBlock).not.toBeNull();

		const rssItems = await page.$$('.feedzy-rss .rss_item');
		expect(rssItems.length).toBeGreaterThan(0);

		const firstItem = page.locator('.feedzy-rss .rss_item').first();
		await expect(firstItem).toBeVisible();
	});
});
