import { subscribe, select } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import domReady from '@wordpress/dom-ready';

/**
 * Stores the current count of each watched block type.
 * @type {Object.<string, number>}
 */
let blockCounts = {};

/**
 * Flag to track if the initial block count has been established.
 * Used to skip logging on first load.
 * @type {boolean}
 */
let isInitialized = false;

/**
 * Array of block types to monitor for changes.
 * @type {string[]}
 * @constant
 */
const watchedBlockTypes = [
	'feedzy-rss-feeds/loop',
	'feedzy-rss-feeds/feedzy-block',
	'core/rss',
];

/**
 * Recursively flattens the block tree to get all blocks including nested ones.
 *
 * @return {Object[]} Array of all blocks in the editor (flattened)
 */
function getAllBlocks() {
	function flattenBlocks(blocks) {
		let allBlocks = [];
		blocks.forEach((block) => {
			allBlocks.push(block);
			if (block.innerBlocks?.length > 0) {
				allBlocks = allBlocks.concat(flattenBlocks(block.innerBlocks));
			}
		});
		return allBlocks;
	}

	return flattenBlocks(select(blockEditorStore).getBlocks());
}

/**
 * Updates the block counts and tracks changes for analytics.
 * Skips logging during initial initialization to avoid false positives.
 *
 * @fires window.tiTrk.with - Sends analytics data when block counts change if telemetry is enabled.
 */
function updateBlockCounts() {
	const allBlocks = getAllBlocks();

	// Reset counts.
	const newCounts = {};
	watchedBlockTypes.forEach((type) => {
		newCounts[type] = 0;
	});

	// Count blocks.
	allBlocks.forEach((block) => {
		if (watchedBlockTypes.includes(block.name)) {
			newCounts[block.name]++;
		}
	});

	if (isInitialized) {
		// Check for changes.
		watchedBlockTypes.forEach((blockType) => {
			const oldCount = blockCounts[blockType] || 0;
			const newCount = newCounts[blockType] || 0;

			if (oldCount === newCount) {
				return;
			}

			const change = newCount - oldCount;
			window?.tiTrk?.with('feedzy')?.set(`${blockType}:${Date()}`, {
				feature: 'block-usage',
				featureComponent: blockType,
				featureValue: change,
			});
		});
	} else {
		isInitialized = true;
	}

	blockCounts = newCounts;
}

domReady(() => {
	if (window.tiTrk) {
		window.tiTrk?.start();
		window.tiTrk.eventsLimit = 60 * 1000; // Check for events every minute.
	}

	// Note: Add a delay for a better initialization for existing blocks.
	setTimeout(() => {
		const unsubscribe = subscribe(updateBlockCounts, blockEditorStore);
	}, 1000);
});
