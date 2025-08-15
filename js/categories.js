/* global feedzy, ajaxurl */
/* jshint unused:false */
(function($) {
    
    $(document).ready(function(){
        init();
        validateFeeds();
    });

    function init(){
        // listen to the validate button
        $('button.validate-category').on('click', function(e){
            e.preventDefault();
            var button = $(this);
            button.html(feedzy.l10n.validating);
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'feedzy_categories',
                    _action: 'validate_clean',
                    security: feedzy.ajax.security,
                    id: button.attr('data-category-id')
                },
                success: function(data){
                    button.html(feedzy.l10n.validated.replace('#', data.data.invalid));
                },
                complete: function(){
                    setTimeout(function(){
                        button.html(feedzy.l10n.validate);
                    }, 5000 );
                }
            });
        });
    }

    // validate feeds group.
    function validateFeeds() {
        $('#feedzy_category_feeds textarea[name="feedzy_category_feed"]').on('input', function() {
            $('.validate-feeds').attr('disabled', 1 > $(this).val().length );
        });

        $('#feedzy_category_feeds .button.validate-feeds').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            button.attr('disabled', true);
            $('#feedzy_category_feeds .spinner').addClass('is-active');
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'feedzy_categories',
                    _action: 'validate_feeds_group',
                    security: feedzy.ajax.security,
                    feeds: $('textarea[name="feedzy_category_feed"]').val()
                },
                success: function(data) {
                    if (data.success) {
                        $('textarea[name="feedzy_category_feed"]').val(data.data.valid.join(', '));
                        $('#feedzy_category_feeds .spinner').removeClass('is-active');
                    }
                }
            })
        });
    }

})(jQuery, feedzy);
