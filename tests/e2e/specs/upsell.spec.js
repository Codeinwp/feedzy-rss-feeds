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

    test( 'upgrade banner', async({ editor, page }) => {
        const bannerLinkElement = page.getByRole('link', { name: 'upgrading to Feedzy Pro', exact: true });
        await expect( bannerLinkElement ).toBeVisible();

        const bannerLink = new URL( await bannerLinkElement.getAttribute('href') );
        expect( bannerLink.host ).toBe( 'themeisle.com' );
        expect( bannerLink.searchParams.get( 'utm_source' ) ).toBe( 'wpadmin');
        expect( bannerLink.searchParams.get( 'utm_medium' ) ).toBe('import-screen');
        expect( bannerLink.searchParams.get( 'utm_content' ) ).toBe('feedzy-rss-feeds');
    });

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

    test( 'map content', async({ editor, page }) => {
        await page.getByRole('button', { name: 'Step 3 Map content' }).click({ force: true });

        const magicTagsUpsell = page.locator('p').filter({ hasText: 'Using magic tags, specify' }).getByRole('link');
        await expect( magicTagsUpsell ).toBeVisible();
        const upgradeLink = new URL( await magicTagsUpsell.getAttribute('href') );
        expect( upgradeLink.searchParams.get('utm_campaign') ).toBe('magictags');
    } );

    test( 'general settings', async({ editor, page }) => {
        await page.getByRole('button', { name: 'Step 4 General feed settings' }).click({ force: true });

        await page.locator('#feedzy_delete_days').hover({ force: true });
        let upgradeAlert = page.locator('#feedzy-import-form a[href*="utm_campaign=auto-delete"]');
        await expect( upgradeAlert ).toBeVisible();

        await page.locator('.fz-form-group:has( #feed-post-default-thumbnail )').hover({ force: true });
        upgradeAlert = page.locator('#feedzy-import-form a[href*="utm_campaign=fallback-image"]');
        await expect( upgradeAlert ).toBeVisible();

        await page.locator('.fz-form-group:has( #fz-event-execution )').hover({ force: true });
        upgradeAlert = page.locator('#feedzy-import-form a[href*="utm_campaign=schedule-import-job"]');
        await expect( upgradeAlert ).toBeVisible();
    } );
});
