window.addEventListener( 'elementor/init', function() {

	/**
	 * Handle select layout template
	 */
	var fzLayoutTemplate = elementor.modules.controls.BaseData.extend({
		onReady() {
			var self = this;
			this.template_control = this.$el.find('.fz-layout-choices input[type="radio"]');
			this.template_control.change( function() {
				self.saveValue( jQuery( this ).val() );
			} );
			self.changeUiImage();
		},
    	saveValue(value) {
			this.setValue(value);
		},
		changeUiImage() { // Set UI theme mode.
			var themeMode = elementor.settings.editorPreferences.model.get('ui_theme');
			var userPrefersDark = matchMedia('(prefers-color-scheme: dark)').matches;

			if ( 'dark' === themeMode || userPrefersDark ) {
				this.$el.removeClass( 'fz-el-light-mode' ).addClass( 'fz-el-dark-mode' );
			} else {
				this.$el.addClass( 'fz-el-light-mode' ).removeClass( 'fz-el-dark-mode' );
			}

			this.$el.find( '.img img' ).each( function() {
				if ( 'dark' === themeMode ) {
					jQuery( this ).attr( 'src', jQuery( this ).attr( 'src' ).replace( '{{ui_mode}}', 'dark' ) );
				} else {
					jQuery( this ).attr( 'src', jQuery( this ).attr( 'src' ).replace( '{{ui_mode}}', 'light' ) );
				}
			} );
		}
	});
	elementor.addControlView( 'fz-layout-template', fzLayoutTemplate );

	// Edit widget event.
	elementor.hooks.addAction( 'panel/open_editor/widget/feedzy-rss-feeds', function( panel, model, view ) {
		var themeMode = elementor.settings.editorPreferences.model.get('ui_theme');
		var userPrefersDark = matchMedia('(prefers-color-scheme: dark)').matches;

		if ( FeedzyElementorEditor.notice ) {
			if ( jQuery('.fz-pro-notice').length <= 0 && jQuery('.elementor-control-fz-referral-url').length > 0 ) {
				// Append notice.
				jQuery( FeedzyElementorEditor.notice ).insertAfter( jQuery('.elementor-control-fz-referral-url').parents('div.elementor-controls-stack') );
				// Set UI theme mode.
				var fzLogo = jQuery('.fz-pro-notice .fz-logo img');
				if ( 'dark' === themeMode || userPrefersDark ) {
					fzLogo.attr( 'src', fzLogo.attr( 'src' ).replace( '{{ui_mode}}', 'dark' ) );
					jQuery('.fz-pro-notice').removeClass( 'fz-light-mode' );
				} else {
					jQuery('.fz-pro-notice').addClass( 'fz-light-mode' );
					fzLogo.attr( 'src', fzLogo.attr( 'src' ).replace( '{{ui_mode}}', 'light' ) );
				}
			}
		}

		if( '' !== FeedzyElementorEditor.upsell_notice ) {
			if ( 'dark' === themeMode || userPrefersDark ) {
				jQuery( FeedzyElementorEditor.upsell_notice ).addClass( 'dark-mode' ).removeClass( 'light-mode' ).insertAfter( '.elementor-panel-navigation' );
			} else {
				jQuery( FeedzyElementorEditor.upsell_notice ).addClass( 'light-mode' ).removeClass( 'dark-mode' ).insertAfter( '.elementor-panel-navigation' );
			}

			jQuery( document ).on( 'click', '.remove-alert', function() {
				var upSellNotice = jQuery(this).parents( '.fz-upsell-notice' );
				upSellNotice.fadeOut( 500,
					function() {
						upSellNotice.remove();
						jQuery.post(
							ajaxurl,
							{
								security: FeedzyElementorEditor.security,
								action: "feedzy",
								_action: "remove_upsell_notice"
							}
						);
					}
				);
			} );
		}
	} );

	var proTitleText = function() {
		if ( jQuery( '.fz-feat-locked:not(.elementor-control-type-section)' ).length > 0 ) {
			jQuery( '.fz-feat-locked:not(.elementor-control-type-section)' ).attr( 'title', FeedzyElementorEditor.pro_title_text );
		}
	};

	elementor.channels.editor.on( 'section:activated', function() {
		proTitleText();
	} );
} );
