/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: http://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 *
 * @package feedzy-rss-feeds
 */
(function(wpmv) {
	var mediaFrame, wpmvtf;

	wpmvtf = wpmv.toolbar.feedzy_rss;
	mediaFrame = wpmv.MediaFrame.Post;

	wpmv.MediaFrame.Post = mediaFrame.extend({
		initialize: function() {
			var self = this;

			mediaFrame.prototype.initialize.apply( self, arguments );

			self.states.add([
			        new wp.media.controller.Feedzy_Rss({
						id: 'feedzy_rss',
						menu: 'default',
						title: wpmv.l10n.feedzy_rss.controller.title,
						priority: 200,
						type: 'link',
						src: wpmv.l10n.feedzy_rss.buildurl
					})
					]
			);

			self.on( 'router:create:feedzy_rss', self.createRouter, self );
			self.on( 'router:render:feedzy_rss', self.feedzyRssRouter, self );

			self.on( 'content:create:library', self.contentCreateLibrary, self );
			self.on( 'content:create:builder', self.iframeContent, self );
		},

		feedzyRssRouter: function(view) {
			view.set({
				builder: {
					text: wpmv.l10n.feedzy_rss.routers.create,
					priority: 40
				},
				library: {
					text: wpmv.l10n.feedzy_rss.routers.library,
					priority: 20
				}
			});
		},

		contentCreateLibrary: function(region) {
			var self = this;
			console.log( 'contentCreateLibrary::media.js' );
			console.log( self );

			self.toolbar.set( new wpmvtf.Library( {controller: self} ) );
			self.$el.removeClass( 'hide-toolbar' );

			region.view = new wpmv.feedzy_rss.Library({
				controller: self,
				collection: self.state().library
			});

			console.log( region.view );
		}
	});
})(wp.media.view);
