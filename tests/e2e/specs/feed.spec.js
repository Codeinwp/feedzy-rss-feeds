/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {tryCloseTourModal, deleteAllFeedImports, addFeeds, runFeedImport, addContentMapping} from '../utils';

test.describe( 'Feed Settings', () => {

    const FEED_URL = 'https://s3.amazonaws.com/verti-utils/sample-feed-import.xml';

    test.beforeEach( async ( { requestUtils } ) => {
        await deleteAllFeedImports( requestUtils );
        await requestUtils.deleteAllPosts();
    } );

    test( 'adding an URL feed', async({ editor, page }) => {

        const importName = 'Test Title: adding an URL feed';

        await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
        await tryCloseTourModal( page );

        await page.getByPlaceholder('Add a name for your import').fill(importName);

        // Add feed URL via tag input.
        await page.getByPlaceholder('Paste your feed URL and click').fill(FEED_URL);
        await page.getByPlaceholder('Paste your feed URL and click').press('Enter');
        await expect( page.getByText( FEED_URL ) ).toBeVisible();

        await addFeeds( page, [FEED_URL] );

        await page.getByRole('button', { name: 'Save', exact: true }).click({ force: true, clickCount: 1 });

        await expect( page.getByRole('cell', { name: importName }) ).toBeVisible();

        await page.getByRole('cell', { name: importName }).hover(); // Display the actions.
        await page.getByLabel(`Edit “${importName}`).click();

        expect( await page.getByPlaceholder('Add a name for your import').inputValue() ).toBe(importName);
        await expect( page.getByText( FEED_URL ) ).toBeVisible();
    });

    test( 'changing General Feed Settings', async({ editor, page }) => {

        const importName = 'Test Title: changing General Feed Settings';

        await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
        await tryCloseTourModal( page );

        await page.getByPlaceholder('Add a name for your import').fill(importName);
        await addFeeds( page, [FEED_URL] );

        await page.getByRole('button', { name: 'Step 4 General feed settings' }).click({ force: true });

        // Change duplicated items setting.
        const duplicatedItemsDefault = await page.getByLabel('Remove Duplicate Items').isChecked();
        await page.getByLabel('Remove Duplicate Items').click();
        await expect( page.getByLabel('Remove Duplicate Items').isChecked() ).resolves.toBe(!duplicatedItemsDefault);

        // Change item counts setting.
        await page.locator('#feedzy_item_limit').fill('3');
        await expect( page.locator('#feedzy_item_limit').inputValue() ).resolves.toBe('3');

        // await page.waitForTimeout(1000); // Wait for the feed to be added.

        await page.getByRole('button', { name: 'Save & Activate importing' }).click({ force: true });

        await runFeedImport( page );
        await expect( page.locator('#ui-id-1').locator('li a').count() ).resolves.toBe(3);
    });

    test( 'changing Map Content', async({ editor, page, admin }) => {
        await admin.createNewPost();

        const importName = 'Test Title: changing General Feed Settings';

        await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
        await tryCloseTourModal( page );

        await page.getByPlaceholder('Add a name for your import').fill(importName);
        await addFeeds( page, [FEED_URL] );

        await page.getByRole('button', { name: 'Step 3 Map content' }).click({ force: true });

        // Do not import feed content.
        await addContentMapping( page, '' );

        await page.getByRole('button', { name: 'Save & Activate importing' }).click({ force: true });

        await runFeedImport( page );

        // Select the first post created by feeds import. We should have no feed content imported (which is usually saved in `core/html` block).
        await page.getByRole('link', { name: 'Posts', exact: true }).click({ force: true });
        await page.locator('#the-list tr').first().locator('a.row-title').click({ force: true });
        const blocks = await editor.getBlocks();
        expect(blocks).toHaveLength(0); // No content.
    });
});
