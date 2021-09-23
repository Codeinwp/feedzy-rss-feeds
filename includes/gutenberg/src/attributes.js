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
	lazy: {
		type: 'boolean',
		default: false,
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
	keywords_inc_on: {
		type: 'string',
	},
	keywords_ban: {
		type: 'string',
	},
	keywords_exc_on: {
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
	http: {
		type: 'string',
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
	route: {
		type: 'string',
		default: 'home',
	},
	feedData: {
		type: 'object',
	},
	categories: {
		type: 'object',
	},
};

export default attributes;