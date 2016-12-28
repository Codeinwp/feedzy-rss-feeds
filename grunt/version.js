/**
 * Version File for Grunt
 *
 * @package feedzy-rss-feeds-pro
 */
/* jshint node:true */
// https://github.com/kswedberg/grunt-version
module.exports = {
	options: {
		pkg: {
			version: '<%= package.version %>'
		}
	},
	project: {
		src: [
			'package.json'
		]
	},
	style: {
		options: {
			prefix: 'Version\\:\.*\\s'
		},
		src: [
			'feedzy-rss-feed.php',
			'css/feedzy-rss-feeds.css',
		]
	},
	class: {
		options: {
			prefix: '\\$this->version\.*\\s=\.*\\s\''
		},
		src: [
			'includes/feedzy-rss-feeds.php',
		]
	}
};
