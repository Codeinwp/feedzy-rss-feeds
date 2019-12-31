// jshint ignore: start

const attributes = {
	feeds: {
		type: 'string',
	},
	max: {
		type: 'number',
		default: 5,
	},
	offset: {
		type: 'number',
		default: 0,
	},
	feed_title: {
		type: 'boolean',
		default: true,
	},
	refresh: {
		type: 'string',
		default: '12_hours',
	},
	sort: {
		type: 'string',
		default: 'default',
	},
	target: {
		type: 'string',
		default: '_blank',
	},
	title: {
		type: 'number',
	},
	meta: {
		type: 'boolean',
		default: true,
	},
	metafields: {
		type: 'string',
		default: '',
	},
	multiple_meta: {
		type: 'string',
		default: '',
	},
	summary: {
		type: 'boolean',
		default: true,
	},
	summarylength: {
		type: 'number',
	},
	keywords_title: {
		type: 'string',
	},
	keywords_ban: {
		type: 'string',
	},
	thumb: {
		type: 'string',
		default: 'auto',
	},
	default: {
		type: 'object',
	},
	size: {
		type: 'number',
		default: 150,
	},
	referral_url: {
		type: 'string',
	},
	columns: {
		type: 'number',
		default: 1,
	},
	template: {
		type: 'string',
		default: 'default',
	},
	price: {
		type: 'boolean',
		default: true,
	},
	status: {
		// 0 - Initial State
		// 1 - Feed Loading
		// 2 - Feed Loaded
		// 3 - Feed Invalid
		type: 'number',
		default: 0,
	},
	feedData: {
		type: 'object',
	},
	categories: {
		type: 'object',
	},
};

export default attributes;