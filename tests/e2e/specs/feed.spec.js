/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {
	tryCloseTourModal,
	deleteAllFeedImports,
	addFeeds,
	runFeedImport,
} from '../utils';

test.describe('Feed Settings', () => {
	const FEED_URL =
		'https://s3.amazonaws.com/verti-utils/sample-feed-import.xml';

	test.beforeEach(async ({ requestUtils }) => {
		await deleteAllFeedImports(requestUtils);
		await requestUtils.deleteAllPosts();
		await requestUtils.deleteAllMedia();
	});

	test('adding an URL feed', async ({ editor, page }) => {
		const importName = 'Test Title: adding an URL feed';

		await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
		await tryCloseTourModal(page);

		await page
			.getByPlaceholder('Add a name for your import')
			.fill(importName);

		// Add feed URL via tag input.
		await addFeeds(page, [FEED_URL]);

		await page
			.getByRole('button', { name: 'Save', exact: true })
			.click({ force: true, clickCount: 1 });

		await expect(
			page.getByRole('cell', { name: importName })
		).toBeVisible();

		await page.getByRole('cell', { name: importName }).hover(); // Display the actions.
		await page.getByLabel(`Edit “${importName}`).click();

		expect(
			await page
				.getByPlaceholder('Add a name for your import')
				.inputValue()
		).toBe(importName);
	});
});
