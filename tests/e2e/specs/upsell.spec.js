/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { tryCloseTourModal, deleteAllFeedImports } from '../utils';

test.describe( 'Upsell', () => {
    test.beforeEach( async ( { requestUtils, page } ) => {
        await deleteAllFeedImports( requestUtils );
        await requestUtils.deleteAllPosts();

        await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
        await tryCloseTourModal( page );
    } );


    test( 'filters', async({ editor, page }) => {
        await page.getByRole('button', { name: 'Step 2 Filters' }).click({ force: true });

        // Hover over text named Filter by Keyword
        const filtersTab = page.locator('#feedzy-import-form > div.feedzy-accordion > div:nth-child(2)');

        // It should have 1 elements with .only-pro-content class.
        await expect( filtersTab.locator('.only-pro-content').count() ).resolves.toBe(1);

        const filterByKeywordAlert = await filtersTab.locator('.upgrade-alert').first();
        let upgradeLink = new URL( await filterByKeywordAlert.locator('a').first().getAttribute('href') );
        expect( upgradeLink.searchParams.get( 'utm_campaign' ) ).toBe('filters');
    } );


    test( 'general settings', async({ editor, page }) => {
        await page.getByRole('button', { name: 'Step 4 General feed settings' }).click({ force: true });



        await page.locator('.fz-form-group:has( #feed-post-default-thumbnail )').hover({ force: true });
        let upgradeAlert = page.locator('#feedzy-import-form a[href*="utm_campaign=fallback-image"]');
        await expect( upgradeAlert ).toBeVisible();

        await page.locator('.fz-form-group:has( #fz-event-execution )').hover({ force: true });
        upgradeAlert = page.locator('#feedzy-import-form a[href*="utm_campaign=schedule-import-job"]');
        await expect( upgradeAlert ).toBeVisible();

        await page.locator('#delete-attached-media').hover({ force: true });
        let upgradeAlert = page.locator('#feedzy-import-form a[href*="utm_campaign=delete-featured-image"]');
        await expect( upgradeAlert ).toBeVisible();
    } );
});
