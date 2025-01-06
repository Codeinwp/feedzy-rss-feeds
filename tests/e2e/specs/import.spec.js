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
	getPostsByFeedzy
} from '../utils';

test.describe( 'Feed Import', () => {

	const FEED_URL = 'https://s3.amazonaws.com/verti-utils/sample-feed-import.xml';

	test.beforeEach( async ( { requestUtils } ) => {
        await deleteAllFeedImports( requestUtils );
		await requestUtils.deleteAllPosts();
		await requestUtils.deleteAllMedia();
    } );

	test( 'import feeds with shortcode', async({ editor, page, admin }) => {
		const shortcode = "[feedzy-rss feeds='https://s3.amazonaws.com/verti-utils/sample-feed.xml' max='11' offset='1' feed_title='yes' refresh='1_hours' meta='yes' multiple_meta='yes' summary='yes' price='yes' mapping='price=im:price' thumb='yes' keywords_title='God, Mendes, Cyrus, Taylor' keywords_ban='Cyrus' template='style1']";

		await admin.createNewPost();

		// Insert a shortcode block.
		await editor.insertBlock({ name: 'core/shortcode' });
		await page.getByPlaceholder('Write shortcode here…').fill(shortcode);

		const postId = await editor.publishPost();
		await page.goto(`/?p=${postId}`);

		// We should have some content.
		await expect( page.locator('.feedzy-rss').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_item').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_image').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_content').count() ).resolves.toBeGreaterThan(0);
	});

	test( 'import lazy loading feeds with shortcode', async({ editor, page, admin }) => {
		const lazyShortcode = "[feedzy-rss feeds='https://s3.amazonaws.com/verti-utils/sample-feed.xml' max='2' offset='1' feed_title='yes' refresh='1_hours' meta='yes' multiple_meta='yes' summary='yes' price='yes' mapping='price=im:price' thumb='yes' keywords_title='God, Mendes, Cyrus, Taylor' keywords_ban='Cyrus' template='style1' lazy='yes']";

		await admin.createNewPost();

		// Insert a shortcode block.
		await editor.insertBlock({ name: 'core/shortcode' });
		await page.getByPlaceholder('Write shortcode here…').fill(lazyShortcode);

		const postId = await editor.publishPost();
		await page.goto(`/?p=${postId}`);

		await expect(  page.locator('.feedzy-lazy') ).toBeVisible();

		await page.waitForSelector('.rss_title', { timeout: 5000 });

		// We should have some content after lazy loading.
		await expect( page.locator('.feedzy-rss .rss_item').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_image').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_content').count() ).resolves.toBeGreaterThan(0);
	} );

	test( 'import multiple feeds with shortcode', async({ editor, page, admin }) => {
		const multipleFeedsShortCode = "[feedzy-rss feeds='https://s3.amazonaws.com/verti-utils/sample-feed-multiple1.xml, https://s3.amazonaws.com/verti-utils/sample-feed-multiple2.xml' max='1' feed_title='no' refresh='1_hours' meta='yes' multiple_meta='yes' summary='yes' thumb='yes' template='style1']";

		await admin.createNewPost();

		// Insert a shortcode block.
		await editor.insertBlock({ name: 'core/shortcode' });
		await page.getByPlaceholder('Write shortcode here…').fill(multipleFeedsShortCode);

		const postId = await editor.publishPost();
		await page.goto(`/?p=${postId}`);

		await expect( page.locator('.feedzy-rss').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_item').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_image').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_content').count() ).resolves.toBeGreaterThan(0)
	} );

	test('import feeds with Gutenberg block', async({ editor, page, admin }) => {
		await admin.createNewPost();

		// Insert a Feedzy block.
		await editor.insertBlock({ name: 'feedzy-rss-feeds/feedzy-block' });
		await page.getByPlaceholder('Enter URL or group of your').fill(FEED_URL);
		await page.getByRole('button', { name: 'Load Feed' }).click();

		await page.waitForSelector('.rss_header');

		// We should have some preview content.
		await expect( page.locator('.feedzy-rss').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_item').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_image').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_content').count() ).resolves.toBeGreaterThan(0);

		const postId = await editor.publishPost();
		await page.goto(`/?p=${postId}`);

		await expect( page.locator('.feedzy-rss').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_item').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_image').count() ).resolves.toBeGreaterThan(0);
		await expect( page.locator('.feedzy-rss .rss_content').count() ).resolves.toBeGreaterThan(0);
	});

	test( 'importing feed from URL', async({ editor, page }) => {

		const importName = 'Test Title: importing feed from URL';

		await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
		await tryCloseTourModal( page );

		await page.getByPlaceholder('Add a name for your import').fill(importName);
		await addFeeds( page, [FEED_URL] );
		await setItemLimit(page, 1);
		await page.getByRole('button', { name: 'Save & Activate importing' }).click({ force: true });

		await runFeedImport( page );
	});

	test( 'importing feed from URL with featured image', async({ admin, page }) => {
		await admin.createNewPost();

		const importName = 'Test Title: importing feed from URL with featured image';

		await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
		await tryCloseTourModal( page );

		await page.getByPlaceholder('Add a name for your import').fill(importName);
		await addFeeds( page, [FEED_URL] );
		await addFeaturedImage( page, '[#item_image]' );
		await setItemLimit(page, 1);
		await page.getByRole('button', { name: 'Save & Activate importing' }).click({ force: true });

		await runFeedImport( page );

		// Select the first post created by feeds import. Check the featured image.
		await page.getByRole('link', { name: 'Posts', exact: true }).click({ force: true });
		await page.locator('#the-list tr').first().locator('a.row-title').click({ force: true });
		await expect( page.locator('.editor-post-featured-image img') ).toBeVisible(); // Featured image is added.
	});

	test( 'importing feed with chained actions', async({ admin, page, requestUtils }) => {
		await admin.createNewPost();

		const importName = 'Test Title: importing feed from URL with featured image';

		await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
		await tryCloseTourModal( page );

		await page.getByPlaceholder('Add a name for your import').fill(importName);
		await addFeeds( page, [FEED_URL] );
		await addContentMapping( page, wrapSerializedChainedActions( serializeChainedActions( [
			{
				"id": "trim",
				"tag": "item_content",
				"data": {
					"trimLength": "30"
				}
			}
		] ) ) );
		await addFeaturedImage( page, getEmptyChainedActions( 'item_image' ) );
		await setItemLimit(page, 1);

		await page.getByRole('button', { name: 'Save & Activate importing' }).click({ force: true });

		await runFeedImport( page );
		const posts = await getPostsByFeedzy( requestUtils );

		for ( const post of posts ) {
			expect( post.featured_media ).toBeGreaterThan(0);
			expect( post.content.rendered.split(' ').length ).toBeLessThanOrEqual(30);
		}
	});

	test( 'image gallery for feeds imported with featured image chained actions', async({ admin, page }) => {
		await admin.createNewPost();

		const importName = 'Test Title: image gallery for feeds imported with featured image chained actions';

		await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
		await tryCloseTourModal( page );

		await page.getByPlaceholder('Add a name for your import').fill(importName);
		await addFeeds( page, [FEED_URL] );
		await addFeaturedImage( page, getEmptyChainedActions( 'item_image' ) );

		await page.getByRole('button', { name: 'Save & Activate importing' }).click({ force: true });

		await page.goto('/wp-admin/edit.php?post_type=feedzy_imports');

		await runFeedImport( page );

		// All the imported image should be available in the media library.
		await page.goto('/wp-admin/upload.php');
		await page.waitForSelector('#wp-media-grid');
		await expect( page.locator('.attachment').count() ).resolves.toBeGreaterThan(0); // We should have some imported images.
	});
});
