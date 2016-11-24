/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: http://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 *
 * @package feedzy-rss-feeds
 */
/* global console */
(function($, wpmm, wpmvlf) {
	wpmm.feedzy_rss = {};

	wpmm.feedzy_rss.Feed = Backbone.Model.extend({
		sync: function(method, model, options) {
			console.log( 'model.js' );
			console.log( method );
			console.log( model );
			console.log( options );
			if ('delete' === method) {
				options = options || {};
				options.data = _.extend( options.data || {}, {
					action:  wpmvlf.actions.delete_feed,
					chart: model.get( 'id' ),
					nonce: wpmvlf.nonce
				});

				return wp.media.ajax( options );
			} else {
				return;
			}
		}
	});
	console.log( 'model.js' );
	console.log( wpmm );
})(jQuery, wp.media.model, wp.media.view.l10n.feedzy_rss);
