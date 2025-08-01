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
        await expect( filtersTab.locator('.pro-label').count() ).resolves.toBe(1);

    } );


    test( 'general settings', async({ editor, page }) => {
        await page.getByRole('button', { name: 'Step 4 General feed settings' }).click({ force: true });



        await page.locator('.fz-form-group:has( #feed-post-default-thumbnail )').hover({ force: true });
        let upgradeAlert = page.locator('#feedzy-import-form a[href*="utm_campaign=fallback-image"]');
        await expect( upgradeAlert ).toBeVisible();

        await page.locator('.fz-form-group:has( #fz-event-schedule )').scrollIntoViewIfNeeded()
        await page.locator('.fz-form-group:has( #fz-event-schedule )').hover({ force: true });
        upgradeAlert = page.locator('#feedzy-import-form a[href*="utm_campaign=schedule-import-job"]');
        await expect( upgradeAlert ).toBeVisible();

        // Click the advanced settings tab.
        await page.click('[data-id="fz-advanced-settings"]');

        await page.locator('.fz-form-group:has( #feedzy_mark_duplicate )').hover({ force: true });
        upgradeAlert = page.locator('#feedzy-import-form a[href*="utm_campaign=remove-duplicates"]');
        await expect( upgradeAlert ).toBeVisible();
    } );
});

test.describe( 'List Page Upsell', () => {
    test.beforeEach( async ( { requestUtils, page } ) => {
        await page.goto('/wp-admin/edit.php?post_type=feedzy_imports');
    } );

    test('Import/Export', async ({ editor, page }) => {
        // Locate and click the "Import Job" link
        const importButton = page.locator('.fz-export-import-btn');
        await expect(importButton).toBeVisible();
        await importButton.click();

        // Wait for the popup to become visible
        const upsellPopup = page.locator('#fz_import_export_upsell');
        await expect(upsellPopup).toBeVisible();

        // Locate and check the "Upgrade to PRO" link inside the popup
        const upgradeToProLink = upsellPopup.locator('a', { hasText: 'Upgrade to PRO' });
        await expect(upgradeToProLink).toBeVisible();

        // Get the URL from the "Upgrade to PRO" link
        const upsellLink = new URL(await upgradeToProLink.getAttribute('href'));

        // Validate the URL parameters
        expect(upsellLink.host).toBe('themeisle.com');
        expect(upsellLink.searchParams.get('utm_source')).toBe('wpadmin');
        expect(upsellLink.searchParams.get('utm_medium')).toBe('edit');
        expect(upsellLink.searchParams.get('utm_content')).toBe('feedzy-rss-feeds');
    });
});
