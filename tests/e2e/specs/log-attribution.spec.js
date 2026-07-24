/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {
	CUSTOM_FEED_URL,
	INVALID_FEED_URL,
	clearLogs,
	createAndRunSampleImport,
	createSampleImport,
	deleteAllFeedImports,
	runImportByName,
	setLoggingLevel,
} from '../utils';

test.describe('Log attribution (#1278)', () => {
	test.beforeEach(async ({ requestUtils }) => {
		await deleteAllFeedImports(requestUtils);
		await requestUtils.deleteAllPosts();
		await requestUtils.deleteAllMedia();
		await clearLogs(requestUtils);
	});

	test('log entries identify the import that produced them', async ({
		page,
		admin,
	}) => {
		await setLoggingLevel(page, admin, 'debug');

		const importName = await createAndRunSampleImport(page);

		await admin.visitAdminPage('admin.php?page=feedzy-settings&tab=logs');
		await expect(
			page.getByRole('heading', { name: 'Recent Logs' })
		).toBeVisible();

		// Log entries were written for the run.
		expect(await page.locator('.fz-log-container').count()).toBeGreaterThan(
			0
		);

		// Entries are attributed to the import that produced them.
		expect(
			await page
				.locator('.fz-log-container .fz-log-context-import')
				.count()
		).toBeGreaterThan(0);
		expect(
			await page
				.locator('.fz-log-context-import', { hasText: importName })
				.count()
		).toBeGreaterThan(0);

		// Entries expose the feed source URL.
		expect(
			await page
				.locator('.fz-log-context-feed-url', {
					hasText: 'sample-feed.xml',
				})
				.count()
		).toBeGreaterThan(0);
	});

	test('errors from a failing feed are attributed to that import', async ({
		page,
		admin,
	}) => {
		await setLoggingLevel(page, admin, 'debug');

		const goodName = await createSampleImport(
			page,
			CUSTOM_FEED_URL,
			`Healthy import ${Date.now()}`
		);
		await runImportByName(page, goodName);

		const badName = await createSampleImport(
			page,
			INVALID_FEED_URL,
			`Broken import ${Date.now()}`
		);
		await runImportByName(page, badName, { expectSuccess: false });

		// Open the logs tab filtered down to errors only.
		await admin.visitAdminPage(
			'admin.php?page=feedzy-settings&tab=logs&logs_type=error'
		);

		expect(
			await page.locator('.fz-log-container--error').count()
		).toBeGreaterThan(0);

		// The error entries point at the broken import...
		expect(
			await page
				.locator('.fz-log-container--error .fz-log-context-import', {
					hasText: badName,
				})
				.count()
		).toBeGreaterThan(0);

		// ...and its feed URL.
		expect(
			await page
				.locator('.fz-log-container--error .fz-log-context-feed-url', {
					hasText: 'nonexistent-feed.xml',
				})
				.count()
		).toBeGreaterThan(0);

		// The healthy import is not blamed for them.
		expect(
			await page
				.locator('.fz-log-container--error .fz-log-context-import', {
					hasText: goodName,
				})
				.count()
		).toBe(0);
	});

	test('front-end fetches are not attributed to an import and unattributed entries still render', async ({
		page,
		admin,
		requestUtils,
	}) => {
		const frontEndFeedUrls =
			'https://example.com/missing-feed-2.xml, https://example.com/missing-feed-3.xml';

		await setLoggingLevel(page, admin, 'debug');

		// Import-scoped entries exist.
		await createAndRunSampleImport(page);

		// Trigger a front-end (shortcode) fetch of broken feeds, outside any import run.
		const post = await requestUtils.createPost({
			title: 'Feedzy shortcode page',
			content: `[feedzy-rss feeds="${frontEndFeedUrls}"]`,
			status: 'publish',
		});
		await page.goto(post.link);

		await admin.visitAdminPage('admin.php?page=feedzy-settings&tab=logs');

		// The front-end fetch error carries the feed URL badge but no import badge.
		expect(
			await page
				.locator('.fz-log-container', {
					has: page.locator('.fz-log-context-feed-url', {
						hasText: 'missing-feed-2.xml',
					}),
					hasNot: page.locator('.fz-log-context-import'),
				})
				.count()
		).toBeGreaterThan(0);

		// It is never falsely attributed to the import that ran earlier.
		expect(
			await page
				.locator('.fz-log-container', {
					has: page.locator('.fz-log-context-feed-url', {
						hasText: 'missing-feed-2.xml',
					}),
				})
				.locator('.fz-log-context-import')
				.count()
		).toBe(0);

		// Backward compatibility: entries whose context has no attribution keys
		// (this one only has a `feed_urls` array) render fine, without a badge row.
		const keylessEntry = page.locator('.fz-log-container', {
			hasText: 'No feeds could be fetched',
		});
		expect(await keylessEntry.count()).toBeGreaterThan(0);
		expect(
			await keylessEntry.locator('.fz-log-container__badges').count()
		).toBe(0);
	});
});
