/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {tryCloseTourModal, deleteAllFeedImports} from '../utils';

test.describe( 'Upsell', () => {

    const FEED_URL = 'https://s3.amazonaws.com/verti-utils/sample-feed-import.xml';

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
        expect( bannerLink.host ).toBe('themeisle.com');
        expect( bannerLink.searchParams.get('utm_source') ).toBe('wpadmin');
        expect( bannerLink.searchParams.get('utm_medium') ).toBe('import-screen');
        expect( bannerLink.searchParams.get('utm_content') ).toBe('feedzy-rss-feeds');
    });

    test( 'filters', async({ editor, page }) => {
        await page.getByRole('button', { name: 'Step 2 Filters' }).click({ force: true });

        // Hover over text named Filter by Keyword
        const filtersTab = page.locator('#feedzy-import-form > div.feedzy-accordion > div:nth-child(2)');

        // It should have 3 elements with .only-pro-content class.
        await expect( filtersTab.locator('.only-pro-content').count() ).resolves.toBe(3);

        const filterByKeywordAlert = await filtersTab.locator('.upgrade-alert').first();
        let upgradeLink = new URL( await filterByKeywordAlert.locator('a').first().getAttribute('href') );
        expect( upgradeLink.searchParams.get('utm_campaign') ).toBe('filter-keyword');

        const excludeItemsAlert = await filtersTab.locator('.upgrade-alert').nth(1);
        upgradeLink = new URL( await excludeItemsAlert.locator('a').first().getAttribute('href') );
        expect( upgradeLink.searchParams.get('utm_campaign') ).toBe('exclude-items');

        const filterByTimeRangeAlert = await filtersTab.locator('.upgrade-alert').nth(2);
        upgradeLink = new URL( await filterByTimeRangeAlert.locator('a').first().getAttribute('href') );
        expect( upgradeLink.searchParams.get('utm_campaign') ).toBe('filter-time-range');
    } );

    test( 'map content', async({ editor, page }) => {
        await page.getByRole('button', { name: 'Step 3 Map content' }).click({ force: true });

        const magicTagsUpsell = page.getByTitle('upgrading to Feedzy Pro');
        await expect( magicTagsUpsell ).toBeVisible();
        const upgradeLink = new URL( await magicTagsUpsell.getAttribute('href') );
        expect( upgradeLink.searchParams.get('utm_campaign') ).toBe('magictags');
    } );
});
