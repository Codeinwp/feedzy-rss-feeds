// jshint ignore: start
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Block dependencies
 */
import './style.scss';
import blockAttributes from './attributes.js';
import Editor from './Editor.js';

/**
 * Register block
 */
export default registerBlockType('feedzy-rss-feeds/feedzy-block', {
	title: __('Feedzy RSS Feeds (Classic)', 'feedzy-rss-feeds'),
	category: 'common',
	icon: 'rss',
	keywords: [
		__('Feedzy RSS Feeds', 'feedzy-rss-feeds'),
		__('RSS', 'feedzy-rss-feeds'),
		__('Feeds', 'feedzy-rss-feeds'),
	],
	supports: {
		html: false,
	},
	attributes: blockAttributes,
	edit: Editor,
	save() {
		// Rendering in PHP
		return null;
	},
});
