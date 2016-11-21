(function(wpm) {
    var wpmmv, wpmc;

    wpmc = wpm.controller;
    wpmmv = wpm.model.feedzy_rss;

    wpmc.Feedzy_Rss = wpmc.State.extend({
        defaults: {
            toolbar: 'feedzy_rss',
            content: 'library',
            sidebar: 'feedzy_rss',
            router: 'feedzy_rss'
        },

        initialize: function() {
            this.library = new wpmmv.Templates();
        }
    });
})(wp.media);