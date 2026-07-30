/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {
	tryCloseTourModal,
	deleteAllFeedImports,
	addFeeds,
	runFeedImport,
	addContentMapping,
	serializeChainedActions,
	wrapSerializedChainedActions,
	getPostsByFeedzy,
} from '../utils';

// Served by tests/e2e/mu-plugins/feedzy-e2e-ai-mock.php (issue #1298):
// one item with >3,000 bytes of multibyte content ending in FEEDZY-E2E-END-MARKER.
const LONG_FEED_URL = 'https://example.com/feedzy-e2e-long-feed.xml';
const MOCK_ENDPOINT = '/feedzy-e2e/v1/ai-mock';

const rewriteAction = wrapSerializedChainedActions(
	serializeChainedActions([
		{
			id: 'chat_gpt_rewrite',
			tag: 'item_content',
			data: { ChatGPT: 'Rewrite this: {content}' },
		},
	])
);

async function createAndRunAiImport(page, importName) {
	await page.goto('/wp-admin/post-new.php?post_type=feedzy_imports');
	await tryCloseTourModal(page);

	await page.getByPlaceholder('Add a name for your import').fill(importName);
	await addFeeds(page, [LONG_FEED_URL]);
	await addContentMapping(page, rewriteAction);

	await page
		.getByRole('button', { name: 'Save & Activate importing' })
		.click({ force: true });

	await runFeedImport(page);
}

test.describe('AI rewrite content handling', () => {
	test.beforeEach(async ({ requestUtils }) => {
		await deleteAllFeedImports(requestUtils);
		await requestUtils.deleteAllPosts();
		// Reset the AI mock: BYOK mode, no forced failure.
		await requestUtils.rest({
			method: 'POST',
			path: MOCK_ENDPOINT,
			data: { managed: false, fail: false },
		});
	});

	test('managed AI receives the full source content beyond 3,000 bytes', async ({
		page,
		requestUtils,
	}) => {
		await requestUtils.rest({
			method: 'POST',
			path: MOCK_ENDPOINT,
			data: { managed: true, fail: false },
		});

		await createAndRunAiImport(page, 'AI rewrite: managed full content');

		const posts = await getPostsByFeedzy(requestUtils);
		expect(posts.length).toBeGreaterThan(0);
		expect(posts[0].content.rendered).toContain('MANAGED_REWRITTEN_CONTENT');

		const state = await requestUtils.rest({ path: MOCK_ENDPOINT });
		expect(state.managed.action).toBe('rewrite');
		expect(state.managed.text_bytes).toBeGreaterThan(3000);
		expect(state.managed.valid_utf8).toBe(true);
	});

	test('failed managed AI rewrite keeps the original content, HTML intact', async ({
		page,
		requestUtils,
	}) => {
		await requestUtils.rest({
			method: 'POST',
			path: MOCK_ENDPOINT,
			data: { managed: true, fail: true },
		});

		await createAndRunAiImport(page, 'AI rewrite: managed failure fallback');

		const posts = await getPostsByFeedzy(requestUtils);
		expect(posts.length).toBeGreaterThan(0);

		const content = posts[0].content.rendered;
		expect(content).not.toContain('MANAGED_REWRITTEN_CONTENT');
		// The marker sits past byte 3,000 of the source: it survives only without truncation.
		expect(content).toContain('FEEDZY-E2E-END-MARKER');
		expect(content).toContain('<strong>');
	});

	test('legacy BYOK rewrite is capped without breaking UTF-8', async ({
		page,
		requestUtils,
	}) => {
		await createAndRunAiImport(page, 'AI rewrite: BYOK truncation');

		const posts = await getPostsByFeedzy(requestUtils);
		expect(posts.length).toBeGreaterThan(0);
		expect(posts[0].content.rendered).toContain('BYOK_REWRITTEN_CONTENT');

		const state = await requestUtils.rest({ path: MOCK_ENDPOINT });
		// Received string is the prompt with the capped content interpolated.
		expect(state.byok.bytes).toBeLessThanOrEqual(3000 + 'Rewrite this: '.length);
		expect(state.byok.bytes).toBeGreaterThan(2900);
		expect(state.byok.valid_utf8).toBe(true);
	});
});
