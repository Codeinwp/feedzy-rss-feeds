/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: https://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 *
 * @package feedzy-rss-feeds
 */
/* global feedzy */
/* jshint unused:false */
(function($) {

    // load all attributes into the ajax call.
    $('.feedzy-lazy:not(.loading)').each(function() {
        var $feedzy_block = $(this);
        var $attributes = {};
        $.each(this.attributes, function() {
            if(this.specified && this.name.includes('data-')) {
                $attributes[this.name.replace('data-', '')] = this.value;
            }
        });

        if ( 'true' === $attributes.has_valid_cache ) {
            return;
        }
        delete $attributes.has_valid_cache;
        setTimeout( function(){
            $feedzy_block.addClass('loading');
            $.ajax({
                url: feedzy.url,
                method: 'POST',
                data: {
                    action: 'feedzy',
                    _action: 'lazy',
                    args: $attributes,
                    nonce: feedzy.nonce
                },
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', feedzy.rest_nonce);
                },
                success: function(data){
                    if(data.success){
                        $feedzy_block.empty().append(data.data.content);
                    }
                },
                complete: function(){
                    $feedzy_block.removeClass('loading');
                }
            });
        }, 1000 );
    });
})(jQuery, feedzy);
