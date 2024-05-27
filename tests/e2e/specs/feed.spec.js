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

    test( 'chained action for feed content', async({ editor, page, admin }) => {
        const importName = 'Test Title: changing General Feed Settings';

        await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
        await tryCloseTourModal( page );

        await page.getByPlaceholder('Add a name for your import').fill(importName);
        await addFeeds( page, [FEED_URL] );

        await page.getByRole('button', { name: 'Step 3 Map content' }).click({ force: true });

        await page.getByRole('button', { name: 'Insert Tag' }).nth(2).click({ force: true });
        await page.getByRole('link', { name: 'Item Content [#item_content]' }).click({ force: true });

        await page.getByRole('button', { name: 'Add new' }).click({ force: true });

        await page.getByText('Trim Content').click({ force: true });
        await expect( page.getByRole('button', { name: 'Trim Content' }) ).toBeVisible();
        await page.getByRole('button', { name: 'Trim Content' }).click({ force: true });
        await page.getByPlaceholder('45').fill('10');

        await page.getByRole('button', { name: 'Add new' }).click({ force: true });

        await page.getByText('Search / Replace').click({ force: true });
        await page.getByRole('button', { name: 'Search and Replace' }).click({ force: true });
        await page.getByLabel('Search').fill('Lorem');
        await page.getByLabel('Replace with').fill('Ipsum');

        await page.getByRole('list').getByRole('button').nth(1).click({ force: true }); // Delete the first action.
        await expect( page.getByRole('button', { name: 'Trim Content' }) ).toBeHidden();

        await page.getByRole('button', { name: 'Save Actions' }).click({ force: true });
        await expect( page.getByRole('heading', { name: 'Add actions to this tag' }) ).toBeHidden(); // The modal is closed.

        await expect( page.getByTitle('item content').getByRole('link') ).toBeVisible(); // The action tag is added.
        await page.getByTitle('remove tag').click({ force: true });
        await expect( page.getByTitle('item content').getByRole('link') ).toBeHidden();
    });
});
