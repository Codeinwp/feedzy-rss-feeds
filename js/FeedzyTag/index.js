/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import './editor.scss';
import metadata from './block.json';
import edit from './edit';

const { name } = metadata;

registerBlockType(name, {
	...metadata,
	edit,
	save() {
		// Rendering in PHP
		return null;
	},
});
