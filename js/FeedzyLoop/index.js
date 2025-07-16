/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

import { createBlock, registerBlockType } from '@wordpress/blocks';

import { InnerBlocks } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import './editor.scss';
import './style.scss';
import './extension';
import metadata from './block.json';
import variations from './variations';
import edit from './edit';
import './tracking';

const { name } = metadata;

registerBlockType(name, {
	...metadata,
	variations,
	transforms: {
		from: [
			{
				type: 'block',
				blocks: ['core/rss'],
				transform: (attributes) => {
					const { feedURL } = attributes;

					if (feedURL) {
						return createBlock(name, {
							feed: { type: 'url', source: [feedURL] },
						});
					}

					return createBlock(name);
				},
			},
			{
				type: 'block',
				blocks: ['feedzy-rss-feeds/feedzy-block'],
				transform: (attributes) => {
					const { feeds } = attributes;

					if (feeds) {
						return createBlock(name, {
							feed: { type: 'url', source: [feeds] },
						});
					}

					return createBlock(name);
				},
			},
		],
	},
	edit,
	save: () => {
		return <InnerBlocks.Content />;
	},
});
