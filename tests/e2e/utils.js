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
