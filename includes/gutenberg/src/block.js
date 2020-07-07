// jshint ignore: start

/**
 * Block dependencies
 */
import './style.scss';
import blockAttributes from './attributes';
import Editor from './Editor.js';

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;

const { registerBlockType } = wp.blocks;

const { date } = wp.date;

/**
 * Register block
 */
export default registerBlockType( 'feedzy-rss-feeds/feedzy-block', {
	title: __( 'Feedzy RSS Feeds' ),
	category: 'common',
	icon: 'rss',
	keywords: [
		__( 'Feedzy RSS Feeds' ),
		__( 'RSS' ),
		__( 'Feeds' ),
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
