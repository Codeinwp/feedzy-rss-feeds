/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: https://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 *
 * @package feedzy-rss-feeds
 */
/* global tinymce */
/* jshint unused:false */
(function($) {
	tinymce.PluginManager.add('feedzy_mce_button', function( editor, url ) {
		editor.addButton( 'feedzy_mce_button', {
			title: getTranslation( editor, 'plugin_label' ),
			label: getTranslation( editor, 'plugin_label' ),
			icon: 'feedzy-icon',
			onclick: function() {
				editor.windowManager.open( {
					title: getTranslation( editor, 'plugin_title' ),
					url: getTranslation( editor, 'popup_url' ) + '&amp;action=get_tinymce_form',
					width: $( window ).width() * 0.7,
					height: ($( window ).height() - 36 - 50) * 0.7,
					inline: 1,
					id: 'feedzy-rss-insert-dialog',
					buttons: [{
						text: getTranslation( editor, 'pro_button' ),
						id: 'feedzy-rss-button-pro',
						onclick: function( e ) {
							openProLink( e, editor );
						},
					},
						{
							text: getTranslation( editor, 'cancel_button' ),
							id: 'feedzy-rss-button-cancel',
							onclick: 'close'
					},
						{
							text: getTranslation( editor, 'insert_button' ),
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

    /**
     * Gets the translation from the editor (when classic editor is enabled)
     * OR
     * from the settings array inside the editor (when classic block inside gutenberg)
     */
    function getTranslation(editor, slug){
        var string = editor.getLang('feedzy_tinymce_plugin.' + slug);
        // if the string is the same as the slug being requested for, look in the settings.
        if(string === '{#feedzy_tinymce_plugin.' + slug + '}'){
            string = editor.settings.feedzy_tinymce_plugin[slug];
        }
        return string;
    }

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
						shortCodeParams = eName + '="https://themeisle.com/feed" ';
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
		window.open( getTranslation( editor, 'pro_url' ), '_blank' );
	}
})(jQuery);
