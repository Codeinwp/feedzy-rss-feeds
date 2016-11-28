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
            title: 'Feedzy Lite',
            label: 'Feedzy Lite',
            icon: 'feedzy-icon',
            onclick: function() {
                //console.log(ajaxurl);
                editor.windowManager.open( {
                    title: editor.getLang( 'feedzy_tinymce_plugin.plugin_title' ),
                    url: editor.getLang( 'feedzy_tinymce_plugin.popup_url' ) + 'form/form.php',
                    width: $( window ).width() * 0.7,
                    height: ($( window ).height() - 36 - 50) * 0.7,
                    inline: 1,
                    id: 'feedzy-rss-insert-dialog',
                    buttons: [{
                        text: 'Insert',
                        id: 'feedzy-rss-button-insert',
                        class: 'insert',
                        onclick: function( e ) {
                            insertShortcode( e, editor );
                        },
                    },
                        {
                            text: 'Cancel',
                            id: 'feedzy-rss-button-cancel',
                            onclick: 'close'
                    },
                        {
                            text: 'Go Pro',
                            id: 'feedzy-rss-button-pro',
                            onclick: function( e ) {
                                openProLink( e, editor);
                            },
                    }],
                });
            }
        });
    });

    function insertShortcode( e, editor ) {
        console.log('Insert Shortcode Cliked');
        var frame = $(e.currentTarget).find("iframe").get(0);
        var content = frame.contentDocument;

        var feeds = $('input[name="feeds"]', content).val();
        var maximum = $('input[name="maximum"]', content).val();
        var feed_title = $('select[name="feed_title"]', content).val();
        var target = $('select[name="target"]', content).val();
        var title = $('input[name="title"]', content).val();
        var meta = $('select[name="meta"]', content).val();
        var summary = $('select[name="summary"]', content).val();
        var summarylength = $('input[name="summarylength"]', content).val();
        var thumb = $('select[name="thumb"]', content).val();
        var defaultimg = $('input[name="defaultimg"]', content).val();
        var size = $('input[name="size"]', content).val();
        var keywords_title = $('input[name="keywords_title"]', content).val();



        if (feeds !== '') {
            feeds = 'feeds="' + feeds + '" ';
        } else {
            feeds = 'feeds="http://themeisle.com/feed" ';
        }
        if (maximum !== '') {
            maximum = 'max="' + maximum + '" ';
        }
        if (feed_title !== '') {
            feed_title = 'feed_title="' + feed_title + '" ';
        }
        if (target !== '') {
            target = 'target="' + target + '" ';
        }
        if (title !== '') {
            title = 'title="' + title + '" ';
        }
        if (meta !== '') {
            meta = 'meta="' + meta + '" ';
        }
        if (summary !== '') {
            summary = 'summary="' + summary + '" ';
        }
        if (summarylength !== '') {
            summarylength = 'summarylength="' + summarylength + '" ';
        }
        if (thumb !== '') {
            thumb = 'thumb="' + thumb + '" ';
        }
        if (defaultimg !== '') {
            defaultimg = 'default="' + defaultimg + '" ';
        }
        if (size !== '') {
            size = 'size="' + size + '" ';
        }
        if (keywords_title !== '') {
            keywords_title = 'keywords_title="' + keywords_title + '" ';
        }
        editor.insertContent(
            '[feedzy-rss ' +
            feeds +
            maximum +
            feed_title +
            target +
            title +
            meta +
            summary +
            summarylength +
            thumb +
            defaultimg +
            size +
            keywords_title +
            ']'
        );
        editor.windowManager.close();
    }

    function openProLink( e , editor ) {

    }
})(jQuery);

