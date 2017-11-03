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
	// jshint ignore: start
	config.taskMap['faq_builder'] = 'grunt-helpscout-faq';
	// jshint ignore: end
	loader(grunt, config).init();
};
