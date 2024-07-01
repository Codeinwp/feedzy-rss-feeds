import {expect} from "@wordpress/e2e-test-utils-playwright";

/**
 * WordPress dependencies
 */
const {RequestUtils } = require( '@wordpress/e2e-test-utils-playwright' )

/**
 * Close the tour modal if it is visible.
 *
 * @param {import('playwright').Page} page The page object.
 */
export async function tryCloseTourModal( page ) {
	if (await page.getByRole('button', { name: 'Skip' }).isVisible()) {
		await page.getByRole('button', { name: 'Skip' }).click();
		await page.waitForTimeout(500);
	}
}

/**
 * Add feeds to the import on Feed Edit page.
 *
 * @param {import('playwright').Page} page The page object.
 * @param {string[]} feedURLs The feed URLs to add in the input.
 * @returns {Promise<void>} The promise that resolves when the feeds are added.
 */
export async function addFeeds( page, feedURLs ) {
    await page.evaluate( ( urls ) => {
        document.querySelector( 'input[name="feedzy_meta_data[source]"]' ).value = urls?.join(', ');
    }, feedURLs );
}

/**
 * Add tags to the Featured Image on Feed Edit page.
 *
 * @param {import('playwright').Page} page The page object.
 * @param {string} feedTag The tag that import the image from the feed.
 * @returns {Promise<void>} The promise that resolves when the tags are added.
 */
export async function addFeaturedImage( page, feedTag ) {
    await page.evaluate( ( feedTag ) => {
        document.querySelector( 'input[name="feedzy_meta_data[import_post_featured_img]"]' ).value = feedTag;
    }, feedTag );
}

/**
 * Add content mapping to the import on Feed Edit page.
 * @param page The page object.
 * @param mapping The content mapping to add.
 * @returns {Promise<void>}
 */
export async function addContentMapping( page, mapping ) {
    await page.evaluate( ( mapping ) => {
        document.querySelector( 'textarea[name="feedzy_meta_data[import_post_content]"]' ).value = mapping;
    }, mapping );
}

/**
 * Set the item limit on the Feed Edit page.
 * @param {import('playwright').Page} page The page object.
 * @param {number} limit The limit to set.
 * @returns {Promise<void>}
 */
export async function setItemLimit( page, limit ) {
    await page.evaluate( ( limit ) => {
        document.querySelector( 'input[name="feedzy_meta_data[import_feed_limit]"]' ).value = limit;
    } , limit );
}

/**
 * Create an empty chained actions.
 * @param {string} defaultFeedTag The default feed tag.
 * @returns {string} The empty chained actions.
 */
export function getEmptyChainedActions( defaultFeedTag ) {
    return wrapSerializedChainedActions(serializeChainedActions([ { id: '', tag: defaultFeedTag, data: {} } ] ) );
}

/**
 * Serialize the chained actions.
 * @param {any[]} actions The actions to serialize.
 * @returns {string} The serialized actions.
 */
export function serializeChainedActions( actions ) {
    return encodeURIComponent( JSON.stringify( actions ) );
}

/**
 * Wrap the serialized chained actions to match the format used in the input.
 * @param {string} actions The serialized actions.
 * @returns {string} The wrapped actions.
 */
export function wrapSerializedChainedActions( actions ) {
    return `[[{"value":"${actions}"}]]`;
}

/**
 * Run the feed import.
 *
 * @param {import('playwright').Page} page The page object.
 * @returns {Promise<void>} The promise that resolves when the feed is imported.
 */
export async function runFeedImport( page ) {
    await page.goto('/wp-admin/edit.php?post_type=feedzy_imports');
    await page.waitForSelector('.feedzy-import-status-row');

    await page.getByRole('button', { name: 'Run Now' }).click();

    const runNowResponse = await page.waitForResponse(
        response => (
            response.url().includes('/wp-admin/admin-ajax.php')&&
            response.request().method() === 'POST' &&
            response.request().postData().includes('action=feedzy&_action=run_now')
        )
    );

    expect( runNowResponse ).not.toBeNull();
    const responseBody = await runNowResponse.json();
    expect( responseBody.success ).toBe(true);
    expect( responseBody.data.import_success ).toBe(true);

    // Reload the page to check the status.
    await page.reload();
    await page.waitForSelector('.feedzy-items');

    // We should have some imported posts in the stats.
    const feedzyCumulative = parseInt(await page.$eval('.feedzy-items a', (element) => element.innerText));
    expect(feedzyCumulative).toBeGreaterThan(0);

    // Open the dialog with the imported feeds.
    await page.locator('.feedzy-items a').click();
    await expect( page.locator('#ui-id-1').locator('li a').count() ).resolves.toBeGreaterThan(0);
    await page.getByRole('button', { name: 'Ok' }).click();
}

/**
 * Delete all feed imports.
 *
 * @param {RequestUtils} requestUtils The request utils object.
 */
export async function deleteAllFeedImports( requestUtils ) {
    const feeds = await requestUtils.rest( {
        path: '/wp/v2/feedzy_imports',
        params: {
            per_page: 100,
            status: 'publish,future,draft,pending,private,trash',
        },
    } );

    await Promise.all(
        feeds.map( ( post ) =>
            requestUtils.rest( {
                method: 'DELETE',
                path: `/wp/v2/feedzy_imports/${ post.id }`,
                params: {
                    force: true,
                },
            } )
        )
    );
}

/**
 * Get post created with Feedzy.
 * @param {RequestUtils} requestUtils The request utils object.
 * @returns {Promise<*>}
 */
export async function getPostsByFeedzy( requestUtils ) {
    return await requestUtils.rest({
        path: '/wp/v2/posts',
        params: {
            per_page: 100,
            status: 'publish',
            meta_key: 'feedzy',
            meta_value: 1,
            meta_compare: '=',
        },
    });
}
