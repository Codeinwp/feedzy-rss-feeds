/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: http://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 *
 * @package feedzy-rss-feeds
 */
(function(wpmm) {
	wpmm.feedzy_rss.Templates = Backbone.Collection.extend({
		model: wpmm.feedzy_rss.Template,

		sync: function(method, model, options) {
			console.log( 'collection.js' );
			console.log( method );
			console.log( model );
			console.log( options );
			if ('read' === method) {
				options = options || {};
				options.type = 'GET';
				options.data = _.extend( options.data || {}, {
					action:  wp.media.view.l10n.feedzy_rss.actions.get_template
				});

				return wp.media.ajax( options );
			} else {
				return Backbone.sync.apply( this, arguments );
			}
		}
	});
})(wp.media.model);
