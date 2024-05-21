/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { tryCloseTourModal, deleteAllFeedImports } from '../utils';

test.describe( 'Feed Import', () => {

	const FEED_URL = 'https://s3.amazonaws.com/verti-utils/sample-feed-import.xml';

	test.beforeEach( async ( { requestUtils } ) => {
        await deleteAllFeedImports( requestUtils );
    } );

	test( 'import simple feed', async({ editor, page }) => {

		const importName = 'Test Title: import simple feed';

		await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');

		await tryCloseTourModal( page );

		await page.getByPlaceholder('Add a name for your import').fill(importName);
		await page.getByPlaceholder('Paste your feed URL and click').fill(FEED_URL);
		await page.getByPlaceholder('Paste your feed URL and click').press('Enter');

		await expect( page.getByText(FEED_URL) ).toBeVisible();

		await page.getByRole('button', { name: 'Save', exact: true }).click({ force: true, clickCount: 1 });

		await expect( page.getByRole('cell', { name: importName }) ).toBeVisible();

		// Set hover status to the row.
		await page.getByRole('cell', { name: importName }).hover();
		await page.getByLabel(`Edit “${importName}`).click();

		expect( await page.getByPlaceholder('Add a name for your import').inputValue() ).toBe(importName);
		await expect( page.getByText(FEED_URL) ).toBeVisible();
	});


});
