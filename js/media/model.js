(function($, wpmm, wpmvlv) {
    wpmm.feedzy_rss = {};

    wpmm.feedzy_rss.Template = Backbone.Model.extend({
        sync: function(method, model, options) {
            if ('delete' === method) {
                options = options || {};
                options.data = _.extend( options.data || {}, {
                    action:  wpmvlv.actions.delete_template,
                    chart: model.get('id'),
                    nonce: wpmvlv.nonce
                });

                return wp.media.ajax( options );
            } else {
                return;
            }
        }
    });
})(jQuery, wp.media.model, wp.media.view.l10n.feedzy_rss);
