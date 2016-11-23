/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: http://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 *
 * @package feedzy-rss-feeds
 */
/*jshint scripturl:true*/
/* global google */
/* global feedzy_rss */
/* global showNotice */
(function($, wpm) {
	var libraryWidth, libraryHeight, wpmf, wpmVFD, wpmvf, wpmvfl, wpmvfb, l10n;

	wpmf = wpm.view;;
	wpmVFD = wpm.View;
	wpmvf = wpmf.feedzy_rss = {};
	l10n = wpmf.l10n.feedzy_rss;

	console.log( 'view.js' );
	console.log( wpm.view );

	/**
	 * =========================================================================
	 * COMMON
	 * =========================================================================
	 */

	if ( ! _.isFunction( wpmVFD.prototype.make )) {
		wpmVFD.prototype.make = function(tag, attrs, val) {
			var html, attr;

			html = '<' + tag;
			for (attr in attrs) {
				html += ' ' + attr + '="' + attrs[attr] + '"';
			}
			html += '>' + val + '</' + tag + '>';

			return html;
		};
	}

	wpmvf.Template = wpmVFD.extend({
		className: 'feedzy_rss_library_template_canvas',

		constructor: function(options) {
			this.id = 'feedzy-library-feed-' + options.model.get( 'id' );
			wpmVFD.apply( this, arguments );
		},

		render: function() {

		}
	});

	/**
	 * =========================================================================
	 * LIBRARY
	 * =========================================================================
	 */

	wpmvfl = wpmvf.Library = wpmVFD.extend({
		id: 'feedzy_rss_library_view',
		className: 'feedzy_rss_clearfix',
		template: wpm.template( 'feedzy-library-empty' ),

		initialize: function() {
			var self = this;
			console.log( 'initialize::view.js' );
			console.log( self );
			console.log( this );
			console.log( wpm.template( 'feedzy-library-empty' ) );

			_.defaults(self.options, {
				filter: 'all',
				page: 1
			});

			self.controller.on( 'feedzy_rss:library:filter', self.onFilterChanged, self );
			self.controller.on( 'feedzy_rss:library:page', self.onPageChanged, self );
			self.collection.on( 'reset', self.renderCollection, self );

			self.resetCollection();
		},

		onFilterChanged: function(filter) {
			this.options.filter = filter;
			this.options.page = 1;
			this.resetCollection();
		},

		onPageChanged: function(page) {
			this.options.page = page;
			this.resetCollection();
		},

		render: function() {},

		renderCollection: function() {
			var self = this;
			console.log( 'renderCollection::view.js' );
			console.log( self );

			if (self.collection.length > 0) {
				self.$el.html( '' );
				self.collection.each( self.addTemplate, self );
			} else {
				self.$el.html( self.template( {} ) );
			}
		},

		addTemplate: function(chart) {
			var self = this,
				view = new wpmvfl.Template( { model: template } );

			self.$el.append( view.$el );
			self.views.set( '#feedzy-library-feed-' + template.get( 'id' ), view, { silent: true } );
			view.render();
		},

		resetCollection: function() {
			var self = this,
				controller = self.controller,
				content = controller.$el.find( controller.content.selector );

			content.lock();
			self.collection.fetch({
				silent: false,
				data: {
					filter: self.options.filter,
					page: self.options.page
				},
				statusCode: {
					200: function(response) {
						var paginationView = controller.toolbar.get( 'toolbar' ).get( 'pagination' );

						if (self.options.page > response.total) {
							self.options.page = response.total;
							self.resetCollection();
						} else {
							paginationView.options.page = self.options.page;
							paginationView.options.total = response.total || 1;
							paginationView.render();
						}

						self.renderCollection();
						content.unlock();
					}
				}
			});
		}
	});

	wpmvfl.Template = wpmVFD.extend({
		className: 'feedzy-library-feed',
		template: wpm.template( 'feedzy-library-feed' ),

		events: {
			'click .feedzy_rss_library_template_delete': 'deleteTemplate',
			'click .feedzy_rss_library_template_insert': 'insertTemplate',
			'click .feedzy_rss_library_template_shortcode': 'selectShortcode'
		},

		initialize: function() {
			var self = this;

			if ( ! libraryWidth && ! libraryHeight) {
				libraryWidth = $( '#feedzy_rss_library_view' ).width() / 3 - 40;
				libraryHeight = libraryWidth * 3 / 4;

				libraryWidth = Math.floor( libraryWidth );
				libraryHeight = Math.floor( libraryHeight );
			}

			self._view = new wpmvf.Template({
				model: self.model,
				width: libraryWidth,
				height: libraryHeight
			});

			self.$el.html( self.template( self.model.toJSON() ) ).prepend( self._view.$el );
			self.views.set( '#' + self._view.id, self._view, { silent: true } );
		},

		render: function() {
			this._view.render();
		},

		deleteTemplate: function() {
			var self = this;

			if (showNotice.warn()) {
				self.model.destroy({
					wait: true,
					success: function() {
						self.views.parent.resetCollection();
					}
				});
			}
		},

		insertTemplate: function() {
			wpm.editor.insert( '[feedzy_rss id="' + this.model.get( 'id' ) + '"]' );
		},

		selectShortcode: function(e) {
			var range, selection;

			if (window.getSelection && document.createRange) {
				selection = window.getSelection();
				range = document.createRange();
				range.selectNodeContents( e.target );
				selection.removeAllRanges();
				selection.addRange( range );
			} else if (document.selection && document.body.createTextRange) {
				range = document.body.createTextRange();
				range.moveToElementText( e.target );
				range.select();
			}
		}
	});

	wpmvfl.Types = wpmVFD.extend({
		tagName: 'select',
		className: 'feedzy_rss_library_filters',

		events: {
			change: 'onFilterChange'
		},

		initialize: function() {
			var self = this;

			self.createFilters();
			self.$el.html(_.chain( self.filters ).map(function(filter) {
				return {
					el: self.make( 'option', {value: filter.key}, filter.text ),
					priority: filter.priority || 50
				};
			}).sortBy( 'priority' ).pluck( 'el' ).value());
		},

		createFilters: function() {
			var self = this;

			self.filters = {};
			_.each(['all', 'no-template', 'default', 'example'], function(type, i) {
				self.filters[type] = {
					text: l10n.library.filters[type],
					key: type,
					priority: (i + 1) * 10
				};
			});
		},

		onFilterChange: function() {
			this.controller.trigger( 'feedzy_rss:library:filter', this.el.value );
		}
	});

	wpmvfl.Pagination = wpmVFD.extend({
		id: 'feedzy_rss_library_pagination',
		tagName: 'ul',

		events: {
			'click a.feedzy_rss_library_pagination_page': 'onPageChange'
		},

		initialize: function() {
			_.defaults(this.options, {
				total: 1,
				page: 1
			});
		},

		render: function() {
			var self, items;

			self = this;
			if (self.options.page <= 1 && self.options.total <= 1) {
				self.$el.html( '' );
				return;
			}

			items = self._pagination( self.options.page, self.options.total, 7 );

			self.$el.html(_.chain( items ).map(function(item) {
				var content, className;

				content = item === '...' || item === self.options.page ? self.make( 'span', { class: 'feedzy_rss_library_pagination_page' }, item ) : self.make( 'a', { class: 'feedzy_rss_library_pagination_page', href: 'javascript:;', 'data-page': item }, item );

				className = item === self.options.page ? 'feedzy_rss_library_pagination_item feedzy_rss_library_pagination_active' : 'feedzy_rss_library_pagination_item';

				return self.make( 'li', { class: className }, content );
			}).value());
		},

		_pagination: function(current, total, max) {
			var i, tmp, pagenation = [];

			if ( total <= max ) {
				for ( i = 1; i <= total; i++ ) {
					pagenation.push( i );
				}
			} else {
				tmp = current - Math.floor( max / 2 );

				if ( max % 2 === 0 ) {
					tmp++;
				}

				if ( tmp < 1 ) {
					tmp = 1;
				}

				if ( tmp + max > total ) {
					tmp = total - max + 1;
				}

				for ( i = 1; i <= max; i++ ) {
					pagenation.push( tmp++ );
				}

				if ( pagenation[0] !== 1 ) {
					pagenation[0] = 1;
					pagenation[1] = '...';
				}

				if ( pagenation[max - 1] !== total ) {
					pagenation[max - 1] = total;
					pagenation[max - 2] = '...';
				}
			}

			return pagenation;
		},

		onPageChange: function(e) {
			this.controller.trigger( 'feedzy_rss:library:page', $( e.target ).data( 'page' ) );
		}
	});
})(jQuery, wp.media);

(function($) {
	$.fn.lock = function() {
		$( this ).each(function() {
			var locker = $( '<div class="locker"></div>' ),
				loader = $( '<div class="locker-loader"></div>' ),
				$this = $( this ),
				position = $this.css( 'position' );

			if ($this.find( '.locker' ).length > 0) {
				return;
			}

			if ( ! position) {
				position = 'static';
			}

			$this.css( 'overflow', 'hidden' );
			switch (position) {
				case 'absolute':
				case 'relative':
					break;
				default:
					$this.css( 'position', 'relative' );
					break;
			}
			$this.data( 'position', position );

			locker.css( 'top', $this.scrollTop() + 'px' ).append( loader );
			$this.append( locker );
		});

		return $( this );
	};

	$.fn.unlock = function() {
		$( this ).each(function() {
			var $this = $( this );

			$this.css({
				position: $this.data( 'position' ),
				overflow: 'auto'
			}).find( '.locker' ).remove();
		});

		return $( this );
	};
})(jQuery);
