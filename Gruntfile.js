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
	config.files.js.push( '!includes/gutenberg/build/*.js' );
	config.files.js.push( '!includes/gutenberg/src/Editor.js' );
	config.files.js.push( '!cypress/**/*.js' );
	// jshint ignore: end
	loader(grunt, config).init();
};
