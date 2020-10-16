/* global feedzy, ajaxurl */
/* jshint unused:false */
(function($) {
    
    $(document).ready(function(){
        init();
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

})(jQuery, feedzy);
