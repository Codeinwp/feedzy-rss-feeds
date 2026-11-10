/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Internal dependencies
 */
import { deleteAllFeedImports } from '../utils';

const DOCS = 'https://docs.themeisle.com/feedzy-rss-feeds/';

/**
 * Open a Feedzy admin page, retrying once past the first-visit redirect.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @param {string}                          path Admin page path and query.
 * @return {Promise<void>}
 */
const openPage = async (page, path) => {
	await page.goto(`/wp-admin/admin.php?${path}`);
	if (!page.url().includes(path)) {
		await page.goto(`/wp-admin/admin.php?${path}`);
	}
};

/**
 * Check that every docs link on the page goes to a current docs page with UTMs.
 *
 * The old /article/ and /category/ URLs redirect, and the redirect drops the UTMs.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @return {Promise<void>}
 */
const expectCurrentDocsLinks = async (page) => {
	const links = page.locator('a[href^="https://docs.themeisle.com/"]');
	await expect(links.first()).toBeAttached();
	const hrefs = await links.evaluateAll((items) =>
		items.map((item) => item.getAttribute('href'))
	);
	for (const href of hrefs) {
		expect(href).toMatch(
			/^https:\/\/docs\.themeisle\.com\/feedzy-rss-feeds\//
		);
		expect(href).toContain('utm_source=wpadmin');
	}
};

test.describe('Docs links', () => {
	test('settings Need help? opens the General settings docs with UTMs', async ({
		page,
	}) => {
		await openPage(page, 'page=feedzy-settings');

		await expect(
			page.getByRole('link', { name: 'Need help?' })
		).toHaveAttribute(
			'href',
			DOCS +
				'feedzy-rss-feeds-documentation?utm_source=wpadmin&utm_medium=settings&utm_campaign=general&utm_content=docsfeedzy-rss-feedsfeedzy-rss-feeds-documentation#general'
		);
	});

	test('Getting Started tab links to current docs pages with UTMs', async ({
		page,
	}) => {
		await openPage(page, 'page=feedzy-support&tab=help');

		await expect(
			page.getByRole('link', { name: 'Feedzy Widget' })
		).toHaveAttribute(
			'href',
			DOCS +
				'feedzy-rss-feeds-documentation?utm_source=wpadmin&utm_medium=tutorial&utm_campaign=widget&utm_content=docsfeedzy-rss-feedsfeedzy-rss-feeds-documentation#using-a-widget'
		);
		await expectCurrentDocsLinks(page);
	});

	test('Documentation tab links to current docs pages with UTMs', async ({
		page,
	}) => {
		await openPage(page, 'page=feedzy-support&tab=docs');

		await expectCurrentDocsLinks(page);
	});

	test('import screen passes the tag actions docs URL to the action popup', async ({
		page,
		requestUtils,
	}) => {
		// The free version allows one import, so a new import needs an empty list.
		await deleteAllFeedImports(requestUtils);
		await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');

		expect(
			await page.evaluate(() => window.feedzyData?.tagActionsDocsUrl)
		).toBe(
			DOCS +
				'how-to-use-feed-to-post-feature-in-feedzy?utm_source=wpadmin&utm_medium=import&utm_campaign=tag-actions&utm_content=docsfeedzy-rss-feedshow-to-use-feed-to-post-feature-in-feedzy#tag-actions'
		);
	});

	test('block editor passes the Feedzy Loop docs URL to the block', async ({
		admin,
		page,
	}) => {
		await admin.createNewPost();

		expect(await page.evaluate(() => window.feedzyData?.docsUrl)).toBe(
			DOCS +
				'feedzy-loop?utm_source=wpadmin&utm_medium=blockeditor&utm_campaign=feedzy-loop&utm_content=docsfeedzy-rss-feedsfeedzy-loop#magic-tags-reference'
		);
	});

	test('Plugins screen links to the docs with UTMs', async ({ page }) => {
		await page.goto('/wp-admin/plugins.php');

		await expect(
			page
				.locator('tr[data-slug="feedzy-rss-feeds"]')
				.getByRole('link', { name: 'Documentation and examples' })
		).toHaveAttribute(
			'href',
			DOCS +
				'?utm_source=wpadmin&utm_medium=plugins&utm_campaign=documentation&utm_content=docsfeedzy-rss-feeds'
		);
	});
});
