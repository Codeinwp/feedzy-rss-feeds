/**
 * Grunt File
 *
 * @package feedzy-rss-feeds
 */
/* jshint node:true */
/* global require */
module.exports = function (grunt) {
	'use strict';

	var loader = require('load-project-config'),
		config = require('grunt-plugin-fleet');
	config = config();
	config.taskMap['faq_builder'] = 'grunt-helpscout-faq';

	loader(grunt, config).init();
};
