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
			if ( 'dark' === themeMode ) {
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
		if ( FeedzyElementorEditor.notice ) {
			if ( jQuery('.fz-pro-notice').length <= 0 && jQuery('.elementor-control-fz-referral-url').length > 0 ) {
				// Append notice.
				jQuery( FeedzyElementorEditor.notice ).insertAfter( jQuery('.elementor-control-fz-referral-url').parents('div.elementor-controls-stack') );
				// Set UI theme mode.
				if ( 'dark' === themeMode ) {
					jQuery('.fz-pro-notice').removeClass( 'fz-light-mode' );
				} else {
					jQuery('.fz-pro-notice').addClass( 'fz-light-mode' );
				}
			}
		}
	} );
} );
