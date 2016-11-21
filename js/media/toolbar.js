(function(wpmv, wpmvt) {
    var wpmvvl, wpmvtv, wpmvlvb;

    wpmvvl = wpmv.feedzy_rss.Library;
    wpmvlvb = wpmv.l10n.feedzy_rss.button;

    wpmv.toolbar = wpmv.toolbar || {};
    wpmvtv = wpmv.toolbar.feedzy_rss = {};

    /**
     * =========================================================================
     * Library Toolbar
     * =========================================================================
     */
    wpmvtv.Library = wpmvt.extend({
        initialize: function() {
            var self = this;

            _.defaults(self.options, {
                close: false,
                items: {
                    type_filter: new wpmvvl.Types({
                        controller: self.controller,
                        priority: -100
                    }),
                    pagination: new wpmvvl.Pagination({
                        controller: self.controller,
                        priority: 100
                    })
                }
            });

            wpmvt.prototype.initialize.apply(self, arguments);
        }
    });
})(wp.media.view, wp.media.view.Toolbar);