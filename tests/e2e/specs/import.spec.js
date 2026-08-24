/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {
	tryCloseTourModal,
	deleteAllFeedImports,
	addFeeds,
	runFeedImport,
	addFeaturedImage,
	addContentMapping,
	getEmptyChainedActions,
	serializeChainedActions,
	wrapSerializedChainedActions,
	setItemLimit,
	getPostsByFeedzy,
} from '../utils';

test.describe('Feed Import', () => {
	const FEED_URL =
		'https://s3.amazonaws.com/verti-utils/sample-feed-import.xml';

	test.beforeEach(async ({ requestUtils }) => {
		await deleteAllFeedImports(requestUtils);
		await requestUtils.deleteAllPosts();
		await requestUtils.deleteAllMedia();
	});

	test('import feeds with shortcode', async ({ editor, page, admin }) => {
		const shortcode =
			"[feedzy-rss feeds='https://s3.amazonaws.com/verti-utils/sample-feed.xml' max='11' offset='1' feed_title='yes' refresh='1_hours' meta='yes' multiple_meta='yes' summary='yes' price='yes' mapping='price=im:price' thumb='yes' keywords_title='God, Mendes, Cyrus, Taylor' keywords_ban='Cyrus' template='style1']";

		await admin.createNewPost();

		// Insert a shortcode block.
		await editor.insertBlock({ name: 'core/shortcode' });
		await editor.canvas.getByPlaceholder('Write shortcode here…').fill(shortcode);

		const postId = await editor.publishPost();
		await page.goto(`/?p=${postId}`);

		// We should have some content.
		await expect(
			page.locator('.feedzy-rss').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			page.locator('.feedzy-rss .rss_item').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			page.locator('.feedzy-rss .rss_image').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			page.locator('.feedzy-rss .rss_content').count()
		).resolves.toBeGreaterThan(0);
	});

	test('import lazy loading feeds with shortcode', async ({
		editor,
		page,
		admin,
	}) => {
		const lazyShortcode =
			"[feedzy-rss feeds='https://s3.amazonaws.com/verti-utils/sample-feed.xml' max='2' offset='1' feed_title='yes' refresh='1_hours' meta='yes' multiple_meta='yes' summary='yes' price='yes' mapping='price=im:price' thumb='yes' keywords_title='God, Mendes, Cyrus, Taylor' keywords_ban='Cyrus' template='style1' lazy='yes']";

		await admin.createNewPost();

		// Insert a shortcode block.
		await editor.insertBlock({ name: 'core/shortcode' });
		await editor.canvas
			.getByPlaceholder('Write shortcode here…')
			.fill(lazyShortcode);

		const postId = await editor.publishPost();
		await page.goto(`/?p=${postId}`);

		await expect(page.locator('.feedzy-lazy')).toBeVisible();

		await page.waitForSelector('.rss_title', { timeout: 5000 });

		// We should have some content after lazy loading.
		await expect(
			page.locator('.feedzy-rss .rss_item').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			page.locator('.feedzy-rss .rss_image').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			page.locator('.feedzy-rss .rss_content').count()
		).resolves.toBeGreaterThan(0);
	});

	// Publish a post with a lazy shortcode. A unique max value gives a cold
	// lazy-load cache so the initial render shows the loading indicator.
	async function publishLazyPost(admin, editor, max) {
		await admin.createNewPost();
		await editor.insertBlock({ name: 'core/shortcode' });
		await editor.canvas
			.getByPlaceholder('Write shortcode here…')
			.fill(
				`[feedzy-rss feeds='https://s3.amazonaws.com/verti-utils/sample-feed.xml' max='${max}' feed_title='yes' refresh='1_hours' template='style1' lazy='yes']`
			);
		return editor.publishPost();
	}

	// The request promise is created before navigation so a request fired
	// during a slow page load cannot slip past the listener.
	async function expectLazyResult(page, postId, expectedText) {
		const lazyRequest = page.waitForRequest(/feedzy\/v\d+\/lazy/);
		await page.goto(`/?p=${postId}`);
		await lazyRequest;
		await expect(page.locator('.feedzy-lazy')).toContainText(
			expectedText,
			{ timeout: 10000 }
		);
	}

	test('lazy loading feed exits loading state when the request fails', async ({
		editor,
		page,
		admin,
	}) => {
		const postId = await publishLazyPost(admin, editor, 3);

		// Force the lazy REST request to fail at the network layer.
		await page.route(/feedzy\/v\d+\/lazy/, (route) =>
			route.abort('failed')
		);

		await expectLazyResult(page, postId, 'An error occurred while fetching the feed.');
	});

	test('lazy loading feed exits loading state on a malformed response', async ({
		editor,
		page,
		admin,
	}) => {
		const postId = await publishLazyPost(admin, editor, 4);

		// The REST callback can return a raw string (e.g. a fetch error)
		// instead of the expected { success, data } envelope.
		await page.route(/feedzy\/v\d+\/lazy/, (route) =>
			route.fulfill({
				status: 200,
				contentType: 'application/json',
				body: JSON.stringify('Feed fetch failed.'),
			})
		);

		await expectLazyResult(page, postId, 'Feed fetch failed.');
	});

	test('lazy loading feed shows an error on a server error response', async ({
		editor,
		page,
		admin,
	}) => {
		const postId = await publishLazyPost(admin, editor, 5);

		await page.route(/feedzy\/v\d+\/lazy/, (route) =>
			route.fulfill({
				status: 500,
				contentType: 'application/json',
				body: JSON.stringify({
					code: 'internal_server_error',
					message: 'Internal server error',
					data: { status: 500 },
				}),
			})
		);

		await expectLazyResult(page, postId, 'An error occurred while fetching the feed.');
	});

	test('lazy loading feed shows an error when the JSON response is corrupted', async ({
		editor,
		page,
		admin,
	}) => {
		const postId = await publishLazyPost(admin, editor, 6);

		// A PHP notice printed before the JSON payload breaks parsing.
		await page.route(/feedzy\/v\d+\/lazy/, (route) =>
			route.fulfill({
				status: 200,
				contentType: 'application/json',
				body: '<b>Notice</b>: Undefined variable {"success":true,"data":{"content":"x"}}',
			})
		);

		await expectLazyResult(page, postId, 'An error occurred while fetching the feed.');
	});

	test('lazy loading feed shows the server message on a json_error response', async ({
		editor,
		page,
		admin,
	}) => {
		const postId = await publishLazyPost(admin, editor, 7);

		// The shape produced by wp_send_json_error(), e.g. a failed nonce.
		await page.route(/feedzy\/v\d+\/lazy/, (route) =>
			route.fulfill({
				status: 200,
				contentType: 'application/json',
				body: JSON.stringify({
					success: false,
					data: { message: 'Security check failed.' },
				}),
			})
		);

		await expectLazyResult(page, postId, 'Security check failed.');
	});

	test('lazy loading feed shows an error when a successful response has no content', async ({
		editor,
		page,
		admin,
	}) => {
		const postId = await publishLazyPost(admin, editor, 8);

		await page.route(/feedzy\/v\d+\/lazy/, (route) =>
			route.fulfill({
				status: 200,
				contentType: 'application/json',
				body: JSON.stringify({ success: true, data: {} }),
			})
		);

		await expectLazyResult(page, postId, 'An error occurred while fetching the feed.');
	});

	test('import multiple feeds with shortcode', async ({
		editor,
		page,
		admin,
	}) => {
		const multipleFeedsShortCode =
			"[feedzy-rss feeds='https://s3.amazonaws.com/verti-utils/sample-feed-multiple1.xml, https://s3.amazonaws.com/verti-utils/sample-feed-multiple2.xml' max='1' feed_title='no' refresh='1_hours' meta='yes' multiple_meta='yes' summary='yes' thumb='yes' template='style1']";

		await admin.createNewPost();

		// Insert a shortcode block.
		await editor.insertBlock({ name: 'core/shortcode' });
		await editor.canvas
			.getByPlaceholder('Write shortcode here…')
			.fill(multipleFeedsShortCode);

		const postId = await editor.publishPost();
		await page.goto(`/?p=${postId}`);

		await expect(
			page.locator('.feedzy-rss').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			page.locator('.feedzy-rss .rss_item').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			page.locator('.feedzy-rss .rss_image').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			page.locator('.feedzy-rss .rss_content').count()
		).resolves.toBeGreaterThan(0);
	});

	test('import feeds with Gutenberg block', async ({
		editor,
		page,
		admin,
	}) => {
		await admin.createNewPost();

		// Insert a Feedzy block.
		await editor.insertBlock({ name: 'feedzy-rss-feeds/feedzy-block' });
		await editor.canvas
			.getByPlaceholder('Enter URL or group of your')
			.fill(FEED_URL);
		await editor.canvas.getByRole('button', { name: 'Load Feed' }).click();

		await editor.canvas.locator('.rss_header').waitFor();

		// We should have some preview content.
		await expect(
			editor.canvas.locator('.feedzy-rss').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			editor.canvas.locator('.feedzy-rss .rss_item').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			editor.canvas.locator('.feedzy-rss .rss_image').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			editor.canvas.locator('.feedzy-rss .rss_content').count()
		).resolves.toBeGreaterThan(0);

		const postId = await editor.publishPost();
		await page.goto(`/?p=${postId}`);

		await expect(
			page.locator('.feedzy-rss').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			page.locator('.feedzy-rss .rss_item').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			page.locator('.feedzy-rss .rss_image').count()
		).resolves.toBeGreaterThan(0);
		await expect(
			page.locator('.feedzy-rss .rss_content').count()
		).resolves.toBeGreaterThan(0);
	});

	test('importing feed from URL', async ({ editor, page }) => {
		const importName = 'Test Title: importing feed from URL';

		await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
		await tryCloseTourModal(page);

		await page
			.getByPlaceholder('Add a name for your import')
			.fill(importName);
		await addFeeds(page, [FEED_URL]);
		await page
			.getByRole('button', { name: 'Save & Activate importing' })
			.click({ force: true });

		await runFeedImport(page);
	});

	test('importing feed from URL with featured image', async ({
		admin,
		page,
	}) => {
		await admin.createNewPost();

		const importName =
			'Test Title: importing feed from URL with featured image';

		await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
		await tryCloseTourModal(page);

		await page
			.getByPlaceholder('Add a name for your import')
			.fill(importName);
		await addFeeds(page, [FEED_URL]);
		await addFeaturedImage(page, '[#item_image]');
		await page
			.getByRole('button', { name: 'Save & Activate importing' })
			.click({ force: true });

		await runFeedImport(page);

		// Select the first post created by feeds import. Check the featured image.
		await page
			.getByRole('link', { name: 'Posts', exact: true })
			.click({ force: true });
		await page
			.locator('#the-list tr')
			.first()
			.locator('a.row-title')
			.click({ force: true });
		await expect(
			page.locator('.editor-post-featured-image img')
		).toBeVisible(); // Featured image is added.
	});

	test('importing feed with chained actions', async ({
		admin,
		page,
		requestUtils,
	}) => {
		await admin.createNewPost();

		const importName =
			'Test Title: importing feed from URL with featured image';

		await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
		await tryCloseTourModal(page);

		await page
			.getByPlaceholder('Add a name for your import')
			.fill(importName);
		await addFeeds(page, [FEED_URL]);
		await addContentMapping(
			page,
			wrapSerializedChainedActions(
				serializeChainedActions([
					{
						id: 'trim',
						tag: 'item_content',
						data: {
							trimLength: '30',
						},
					},
				])
			)
		);
		await addFeaturedImage(page, getEmptyChainedActions('item_image'));

		await page
			.getByRole('button', { name: 'Save & Activate importing' })
			.click({ force: true });

		await runFeedImport(page);
		const posts = await getPostsByFeedzy(requestUtils);

		for (const post of posts) {
			expect(post.featured_media).toBeGreaterThan(0);
			expect(post.content.rendered.split(' ').length).toBeLessThanOrEqual(
				30
			);
		}
	});

	test('image gallery for feeds imported with featured image chained actions', async ({
		admin,
		page,
	}) => {
		await admin.createNewPost();

		const importName =
			'Test Title: image gallery for feeds imported with featured image chained actions';

		await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
		await tryCloseTourModal(page);

		await page
			.getByPlaceholder('Add a name for your import')
			.fill(importName);
		await addFeeds(page, [FEED_URL]);
		await addFeaturedImage(page, getEmptyChainedActions('item_image'));

		await page
			.getByRole('button', { name: 'Save & Activate importing' })
			.click({ force: true });

		await page.goto('/wp-admin/edit.php?post_type=feedzy_imports');

		await runFeedImport(page);

		// All the imported image should be available in the media library.
		await page.goto('/wp-admin/upload.php');
		await page.waitForSelector('#wp-media-grid');
		await expect(
			page.locator('.attachment').count()
		).resolves.toBeGreaterThan(0); // We should have some imported images.
	});

	test('save featured image action when Tagify has no persistence API', async ({
		page,
	}) => {
		const featuredImgInput =
			'input[name="feedzy_meta_data[import_post_featured_img]"]';
		const pageErrors = [];
		page.on('pageerror', (error) => pageErrors.push(error.message));

		await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
		await tryCloseTourModal(page);

		await page.getByRole('button', { name: 'Step 3 Map content ' }).click();

		// Simulate a Tagify build that does not expose the optional persistence API.
		await page.waitForFunction(
			(selector) => Boolean(window.jQuery(selector).data('tagify')),
			featuredImgInput
		);
		await page.evaluate((selector) => {
			const tagify = window.jQuery(selector).data('tagify');
			delete tagify.clearPersistedData;
		}, featuredImgInput);

		// Open the action popup for the featured image tag.
		await page.locator('.fz-image-action-tags .btn-add-fields').click();
		await page
			.locator('.fz-image-action-tags [data-action_popup="item_image"]')
			.click();

		await expect(
			page.getByRole('heading', { name: 'Add actions to this tag' })
		).toBeVisible();

		await page.getByRole('button', { name: 'Skip Actions' }).click();

		// The modal closes and the tag is persisted on the field.
		await expect(
			page.getByRole('heading', { name: 'Add actions to this tag' })
		).not.toBeVisible();
		await expect
			.poll(() =>
				page.evaluate(
					(selector) => document.querySelector(selector).value,
					featuredImgInput
				)
			)
			.toContain('item_image');
		expect(
			pageErrors.filter((message) => message.includes('PersistedData'))
		).toEqual([]);
	});

	test('close Feedzy Action modal when clicking outside', async ({
		page,
	}) => {
		await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
		await tryCloseTourModal(page);

		await page
			.getByRole('button', { name: 'Step 3 Map content ' })
			.click();

		await expect(
			page.getByText('Post Title item title Item')
		).toBeVisible();

		await page.getByTitle('item title').getByRole('link').click();

		await expect(
			page.getByRole('heading', { name: 'Add actions to this tag' })
		).toBeVisible();

		await page.locator('body').click({ position: { x: 0, y: 0 } });

		await expect(
			page.getByRole('heading', { name: 'Add actions to this tag' })
		).not.toBeVisible();
	});
});
