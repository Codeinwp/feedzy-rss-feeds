/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: http://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 *
 * @package feedzy-rss-feeds
 */
(function(wpm) {
	var wpmmf, wpmc;

	wpmc = wpm.controller;
	wpmmf = wpm.model.feedzy_rss;

	wpmc.Feedzy_Rss = wpmc.State.extend({
		defaults: {
			toolbar: 'feedzy_rss',
			content: 'library',
			sidebar: 'feedzy_rss',
			router: 'feedzy_rss'
		},

		initialize: function() {
			this.library = new wpmmf.Feeds();
			console.log('Controller.js');
			console.log(this);
		}
	});
})(wp.media);
