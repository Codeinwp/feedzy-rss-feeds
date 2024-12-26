/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

import { registerBlockType } from '@wordpress/blocks';

import { InnerBlocks } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import './editor.scss';
import './style.scss';
import './extension';
import metadata from './block.json';
import edit from './edit';

const { name } = metadata;

registerBlockType(name, {
	...metadata,
	edit,
	save: () => {
		return <InnerBlocks.Content />;
	},
});
