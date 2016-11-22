(function(wpmv, wpmvt) {
    var wpmvfl, wpmvtf, wpmvlfb;

    wpmvfl = wpmv.feedzy_rss.Library;
    wpmvlfb = wpmv.l10n.feedzy_rss.button;

    wpmv.toolbar = wpmv.toolbar || {};
    wpmvtf = wpmv.toolbar.feedzy_rss = {};

    /**
     * =========================================================================
     * Library Toolbar
     * =========================================================================
     */
    wpmvtf.Library = wpmvt.extend({
        initialize: function() {
            var self = this;

            _.defaults(self.options, {
                close: false,
                items: {
                    type_filter: new wpmvfl.Types({
                        controller: self.controller,
                        priority: -100
                    }),
                    pagination: new wpmvfl.Pagination({
                        controller: self.controller,
                        priority: 100
                    })
                }
            });

            wpmvt.prototype.initialize.apply(self, arguments);
        }
    });
})(wp.media.view, wp.media.view.Toolbar);