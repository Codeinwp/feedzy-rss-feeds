/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: http://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 *
 * @package feedzy-rss-feeds
 */
/* global tinymce */
/* jshint unused:false */
(function($) {
	tinymce.PluginManager.add('feedzy_mce_button', function( editor, url ) {
		editor.addButton( 'feedzy_mce_button', {
			title: editor.getLang( 'feedzy_tinymce_plugin.plugin_label' ),
			label: editor.getLang( 'feedzy_tinymce_plugin.plugin_label' ),
			icon: 'feedzy-icon',
			onclick: function() {
				editor.windowManager.open( {
					title: editor.getLang( 'feedzy_tinymce_plugin.plugin_title' ),
					url: editor.getLang( 'feedzy_tinymce_plugin.popup_url' ) + '&amp;action=get_tinymce_form',
					width: $( window ).width() * 0.7,
					height: ($( window ).height() - 36 - 50) * 0.7,
					inline: 1,
					id: 'feedzy-rss-insert-dialog',
					buttons: [{
						text: editor.getLang( 'feedzy_tinymce_plugin.pro_button' ),
						id: 'feedzy-rss-button-pro',
						onclick: function( e ) {
							openProLink( e, editor );
						},
					},
						{
							text: editor.getLang( 'feedzy_tinymce_plugin.cancel_button' ),
							id: 'feedzy-rss-button-cancel',
							onclick: 'close'
					},
						{
							text: editor.getLang( 'feedzy_tinymce_plugin.insert_button' ),
							id: 'feedzy-rss-button-insert',
							class: 'insert',
							onclick: function( e ) {
								insertShortcode( e, editor );
							},

					}],
					}, {
						editor: editor,
						jquery: $,
						wp: wp,
				});
			}
		});
	});

	function insertShortcode( e, editor ) {
		var frame = $( e.currentTarget ).find( 'iframe' ).get( 0 );
		var content = frame.contentDocument;

		var feedzy_form = $( '*[data-feedzy]', content );
		var shortCode = '';
		$.each( feedzy_form, function( index, element ) {
			if ( ! $( element ).attr( 'disabled' ) ) {
				var shortCodeParams = '';
				var eName = $( element ).attr( 'data-feedzy' );
				var eValue = '';
				if ($( element ).is( 'input' )) {
					if ($( element ).attr( 'type' ) === 'radio' || $( element ).attr( 'type' ) === 'checkbox') {
						if ( $( element ).is( ':checked' ) ) {
							eValue = $( element ).val();
						}
					} else {
						eValue = $( element ).val();
					}
				} else {
					eValue = $( element ).val();
				}

				if ( eValue !== '' && typeof eValue !== 'undefined' ) {
					shortCodeParams = eName + '="' + eValue + '" ';
				} else {
					if ( eName === 'feeds' ) {
						shortCodeParams = eName + '="http://themeisle.com/feed" ';
					}
				}
				shortCode += shortCodeParams;
			}
		});
		editor.insertContent(
			'[feedzy-rss ' + shortCode + ']'
		);
		editor.windowManager.close();
	}

	function openProLink( e , editor ) {
		window.open( editor.getLang( 'feedzy_tinymce_plugin.pro_url' ), '_blank' );
	}
})(jQuery);
