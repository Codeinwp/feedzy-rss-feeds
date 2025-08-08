/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe('Feedzy Classic Block', () => {
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
});
