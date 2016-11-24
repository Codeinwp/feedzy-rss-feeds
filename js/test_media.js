jQuery(document).ready(function($){
    $('#uber-media-settings').on('click', '.uber-connect.connect', function(e) {
        e.preventDefault();
        if ($(this).is('.connect')) {
            $('body').addClass('uber-media-overlay');
            var self = this,
                w = $(this).data('w'),
                h = $(this).data('h'),
                source = $(this).data('source'),
                left = (screen.width/2)-(w/2),
                top = (screen.height/2)-(h/2),
                params = 'location=0,status=0,width=' + w + ',height=' + h + ',top=' + top + ', left=' + left;
            $(this).removeClass('connect');
            $(this).removeClass('button-primary');
            $('[data-source="' + source + '"]').text('Connecting...');
            this.oauth_window = window.open(this.href, 'Connect', params);

            this.interval = window.setInterval((function() {
                if (self.oauth_window.closed) {
                    window.clearInterval(self.interval);
                    $('body').removeClass('uber-media-overlay');
                    $.post(ajaxurl,
                        { action:'uber_check',
                            nonce: uber_media.nonce,
                            source: source },
                        function(data){
                            if(data.message == 'success') {
                                $('[data-source="' + source + '"]').text('Disconnect');
                                $('[data-source="' + source + '"]').addClass('disconnect');
                                var sourceTitle = $('[data-source="' + source + '"]').prop('title');
                                $('[data-source="' + source + '"]').prop('title', sourceTitle.replace("Connect", "Disconnect"));
                                $('[data-source="' + source + '"]').prop('href', '#');
                            } else {
                                $('[data-source="' + source + '"]').text('Connect');
                                $('[data-source="' + source + '"]').addClass('connect');
                                $('[data-source="' + source + '"]').addClass('button-primary');
                            }
                        }
                        , 'json');
                    return false;
                }
            }), 100);
        }
    });

    $('#uber-media-settings').on('click', '.uber-connect.disconnect', function(e) {
        e.preventDefault();
        var r= confirm($(this).prop('title') + '?');
        if (r == true) {
            $.post(ajaxurl,
                { action:'uber_disconnect',
                    nonce: uber_media.nonce,
                    source: $(this).data('source') },
                function(data){
                    if(data.message == 'success') location.reload();
                }
                , 'json');
            return false;
        }
    });
});

(function ($) {

    // for debug : trace every event
    /*
     var originalTrigger = wp.media.view.MediaFrame.Post.prototype.trigger;
     wp.media.view.MediaFrame.Post.prototype.trigger = function(){
     console.log('Event Triggered:', arguments);
     originalTrigger.apply(this, Array.prototype.slice.call(arguments));
     }
     */

    var extensions = wp.media.view.l10n.mmp_extensions;
    var importer = ($.inArray('media-manager-plus-importer', extensions) > 0) ? true : false;

    var UberImage = Backbone.Model.extend({
    });

    var UberImages = Backbone.Collection.extend({
        model: UberImage
    });

    var SelectedImages = Backbone.Collection.extend({
        model: UberImage
    });

    var UberImageView = Backbone.View.extend({
        tagName: "li",
        className: "uber-image attachment",
        template:  wp.media.template('uberimage'),
        render: function () {
            this.$el.html( this.template( this.model.toJSON() ) );
            return this;
        }
    });

    var UberImageSidebar = Backbone.View.extend({
        tagName: "div",
        className: 'uber-sidebar media-sidebar'
    });

    var UberImageSettings = Backbone.View.extend({
        tagName: "div",
        className: "uber-settings",
        template:  wp.media.template('uberimage-settings'),

        events: {
            'click button':    'updateHandler',
            'change [data-setting]':          'updateSetting',
            'change [data-setting] input':    'updateSetting',
            'change [data-setting] select':   'updateSetting',
            'change [data-setting] textarea': 'updateSetting',
        },
        render: function () {
            this.$el.html( this.template( this.model.toJSON() ) );

            var imgdata = this.model.toJSON();

            var that = this;

            var img = new Image();
            $(img).load(function () {
                $(this).attr('id', 'img-uber');
                $(this).attr('draggable', 'false');
                $(this).css('display', 'none');
                $(this).hide();
                $('#uberload').hide();
                $('.thumbnail').empty();
                $('.thumbnail').append(this);
                $(this).fadeIn( function() {
                    $("#uber-button").removeAttr('disabled'); });
                newimage = document.getElementById($(this).attr("id"));
                if(newimage != null) {
                    var height = document.getElementById($(this).attr("id")).naturalHeight;
                    var width = document.getElementById($(this).attr("id")).naturalWidth;
                    that.updateModel('height', height);
                    that.updateModel('width', width);
                }
            }).error(function () {

            }).attr('src', imgdata.selected_image.dataset.full);

            return this;
        },
        updateHandler: function( event ) {
            var $setting = $( event.target ).closest('[data-setting]'),
                value = event.target.value,
                userSetting;
            event.preventDefault();
            if ( $setting.hasClass('button-group') ) {
                $buttons = $setting.find('button').removeClass('active');
                $buttons.filter( '[value="' + value + '"]' ).addClass('active');
            }
        },
        updateSetting: function( event ) {
            var $setting = $( event.target ).closest('[data-setting]'),
                setting, value;

            if ( ! $setting.length )
                return;

            setting = $setting.data('setting');
            value   = event.target.value;

            if (event.target.type == 'checkbox') {
                if ($(event.target).is(":checked")) {
                    value = 'on';
                } else value = 'off';
            }

            this.updateModel(setting, value);
        },
        updateModel: function(setting, value) {
            var selectedimages = this.model.get( 'custom_data' );

            if (selectedimages) {
                var selection = new SelectedImages(selectedimages.models);

                var uber = selection.get($('#uber-id').val());
                selection.remove($('#uber-id').val());
                if ( uber.get('setting-' + setting) !== value ) {
                    uber.set('setting-' + setting, value);
                    selection.add(uber);
                    this.model.set( 'custom_data', selection );

                }
            }
            else var selection = new SelectedImages();


        }

    });

    wp.media.controller.UberMedia = wp.media.controller.State.extend({

        initialize: function(){
            this.props = new Backbone.Model({ custom_data: '', method: '', param: '', images: '', selected_id: '', selected_image: '', page: '', pagin: '', altpage: '', folder_path: '' });
            this.props.bind( 'change:custom_data', this.refresh, this );
        },
        refresh: function() {
            this.frame.toolbar.get().refresh();
        },

        importAction: function() {

            if (!importer) return;

            if($('#mmp-import-button').is('[disabled=disabled]')) return;

            $('div.ubermedia').addClass('uber-media-overlay');
            $('#method').attr("disabled", 'disabled');
            $('#param').attr("disabled", 'disabled');
            $('#pagination').attr("disabled", 'disabled');

            $('#mmp-import-button').attr("disabled", 'disabled');
            $('#uber-button').attr("disabled", 'disabled');
            $("#mmp-import-button").text('Importing...');

            var selectedimages = this.props.get( 'custom_data' );
            var selection = new SelectedImages(selectedimages.models);

            var that = this;
            var count = 0;

            var htmlstr = '';
            var jqHXRs = [];


            selection.each(function(model){
                count++;

                var modelAttr = model.attributes;
                modelAttr['setting-import'] = 'on';

                var fields = $.param(modelAttr);
                data = 'action=uber_pre_insert&nonce=' + uber_media.nonce + '&imgsrc=' + encodeURI(model.get('data-full')) + '&postid=' + $('#post_ID').val() + '&' + fields;

                var full = model.get('data-full');
                var imgstr;

                jqHXRs.push(
                    $.post(ajaxurl, data,
                        function(data){
                            if(data.message == 'success')
                                full = data.imgsrc;
                        }
                        , 'json')
                );
            });

            $.when.apply(this, jqHXRs).done(function(){

                that.props.set('custom_data', '');
                that.props.set( 'selected_id', '' );
                that.props.set( 'selected_image', '' );
                $("#mmp-import-button").text( count + ((count > 1) ? ' images' : ' image') + ' imported');

                setTimeout(function() {
                    $("ul#uberimages li").removeClass("selected").removeClass("details");
                    $(".uber-sidebar").empty();
                    $('div.ubermedia').removeClass('uber-media-overlay');

                    $('#method').removeAttr("disabled")
                    $('#param').removeAttr("disabled");
                    $('#pagination').removeAttr("disabled");
                    $("#mmp-import-button").text( wp.media.view.l10n.mmpImportButton);
                }, 2000);

            });
        },

        insertAction: function(){

            if($('#uber-button').is('[disabled=disabled]')) return;

            $('#uber-button').attr("disabled", 'disabled');
            if(importer) $('#mmp-import-button').attr("disabled", 'disabled');
            $("#uber-button").text('Inserting...');

            var uberimage = this.props.get('custom_data');

            var selectedimages = this.props.get( 'custom_data' );
            var selection = new SelectedImages(selectedimages.models);

            var selcount = selection.length;

            var that = this;
            var count = 0;

            var htmlstr = '';
            var jqHXRs = [];

            selection.each(function(model){

                var imgwidth = model.get('setting-width');
                var imgheight = model.get('setting-height');
                if (model.get('setting-width') == '0') {
                    var that = this;

                    var img = new Image();
                    $(img).load(function () {
                        $(this).attr('id', 'img-uber');
                        $(this).attr('draggable', 'false');
                        $(this).css('display', 'none');
                        $(this).hide();
                        newimage = document.getElementById($(this).attr("id"));
                        if(newimage != null) {
                            imgheight = document.getElementById($(this).attr("id")).naturalHeight;
                            imgwidth = document.getElementById($(this).attr("id")).naturalWidth;
                        }
                    }).error(function () {

                    }).attr('src', model.get('data-full'));
                }

                var modelAttr = model.attributes;
                var fields = $.param(modelAttr);

                data = 'action=uber_pre_insert&nonce=' + uber_media.nonce + '&imgsrc=' + encodeURI(model.get('data-full')) + '&postid=' + $('#post_ID').val() + '&' + fields;

                var full = model.get('data-full');
                var imgstr;

                jqHXRs.push(
                    $.post(ajaxurl, data,
                        function(data){
                            if(data.message == 'success')
                                full = data.imgsrc;
                            imgwidth = (data.imgwidth) ? data.imgwidth : imgwidth;
                            imgheight = (data.imgheight) ? data.imgheight : imgheight;

                            imgstr =  '<img src="' + full + '" width="' + imgwidth + '" height="' + imgheight + '" alt="' + model.get('setting-alt') + '" title="' + model.get('setting-title') + '" class="align' + model.get('setting-align') + '" />';

                            if (model.get('setting-link-to') != 'none')
                                imgstr = '<a href="' + model.get('setting-link-to') + '">' + imgstr + '</a>';

                            if (model.get('setting-caption') != '')
                                imgstr = '[caption width="' +imgwidth + '" align="align' + model.get('setting-align') + '"]' + imgstr + ' ' + model.get('setting-caption') + '[/caption]';

                            htmlstr = htmlstr + imgstr + "\n\n";

                        }
                        , 'json')
                );

            });

            $.when.apply(this, jqHXRs).done(function(){
                $("#uber-button").text( wp.media.view.l10n.ubermediaButton);
                $('#uber-button').removeAttr("disabled");
                htmlstr = htmlstr.replace(/^\s+|\s+$/g, '');
                wp.media.editor.insert(htmlstr);
                that.props.set('custom_data', '');
                that.props.set( 'selected_id', '' );
                that.props.set( 'selected_image', '' );
                that.frame.close();
            });
        }

    });

    wp.media.view.Toolbar.UberMedia = wp.media.view.Toolbar.extend({
        className: 'media-toolbar mmp-toolbar',
        initialize: function() {
            _.defaults( this.options, {
                event: 'mmp_event_insert',
                close: false,
                items: {
                    mmp_event_insert: {
                        text: wp.media.view.l10n.ubermediaButton,
                        style: 'primary',
                        id: 'uber-button',
                        priority: 80,
                        requires: false,
                        click: this.insertAction
                    },
                    mmp_event_import: {
                        text: wp.media.view.l10n.mmpImportButton,
                        style: 'secondary',
                        id: 'mmp-import-button',
                        priority: 100,
                        requires: false,
                        click: this.importAction
                    },
                }
            });

            wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );
        },

        refresh: function() {

            if (importer) $("#mmp-import-button").show();

            var custom_data = this.controller.state().props.get('custom_data');

            if (custom_data) {
                var selection = new SelectedImages(custom_data.models);
            } else var selection = new SelectedImages();

            var show = false;
            if (selection.length > 0) show = true;

            this.get('mmp_event_insert').model.set( 'disabled', ! show );
            if (!show) $("#uber-button").attr('disabled','disabled');

            if (!show && importer) {
                $("#mmp-import-button").attr('disabled','disabled');
                $("#mmp-import-button").text(wp.media.view.l10n.mmpImportButton);
            }
            else if	(show && importer) {
                $("#mmp-import-button").removeAttr('disabled');
                var imgtext = (selection.length > 1) ? ' images' : ' image';
                $("#mmp-import-button").text(wp.media.view.l10n.mmpImportButton + ' ' + selection.length + imgtext);
            }

            wp.media.view.Toolbar.prototype.refresh.apply( this, arguments );
            return this;
        },

        insertAction: function(){
            this.controller.state().insertAction();
        },

        importAction: function(){
            this.controller.state().importAction();
        },


    });

    wp.media.view.UberMedia = wp.media.View.extend({
        events: {
            "change select#method": "setFilter",
            "change input#param": "setParam",
            "change select#paramselect": "setParam",
            'click .uber-image img.image': 'toggleSelectionHandler',
            'click .uber-image img.folder': 'selectFolder',
            'click a#backfolder': 'backFolder',
            'click .uber-image a.check':  'removeFromSelection',
            'click .uber-image a':  'preventDefault',
            'click .uber-connect': 'oauthPopup',
            "click input#pagination": "getPagination",
        },

        initialize: function() {
            this.selection = new SelectedImages();
            this.sourceDetails = this.options.sourceDetails;
            this.source = this.options.source;
            this.imageError = '';
            if (this.sourceDetails.url != '#') {
                this.$connect = $('<a>', {
                    href: this.sourceDetails.url,
                    id: 'uber-btn',
                    class: 'button uber-connect',
                    "data-source": this.source,
                    "data-w": this.sourceDetails.w,
                    "data-h": this.sourceDetails.h
                }).append('Connect');
                this.connect = this.$connect[0];
                this.$el.append(this.connect);
            }
            this.createToolbar();
            if (this.sourceDetails.url != '#') {
                var toolbar = this.$el.find("#uber-toolbar");
                $(toolbar).hide();
            }

            this.createSidebar();
        },

        createToolbar: function() {

            var images;
            var content = this.$el.find(".ubermedia"),
                toolbar = $("<div/>", {
                    id: 'uber-toolbar'
                });
            this.$el.append(toolbar);
            this.$el.find('#uber-toolbar').append(this.createSelect());

            var filter = this.$el.find(".ubermedia"),
                paraminput = $("<input/>", {
                    id: 'param',
                    type: 'text'
                });

            this.$el.find('#uber-toolbar').append(paraminput);
            $("#param").hide();

            var filter = this.$el.find(".ubermedia"),
                paramselect = $("<select/>", {
                    id: 'paramselect',
                    html: ''
                });
            this.$el.find('#uber-toolbar').append(paramselect);
            $("#paramselect").hide();

            var filter = this.$el.find(".ubermedia"),
                spinner = $("<span/>", {
                    class: 'spinner',
                    id: 'uberspin'
                });
            this.$el.find('#uber-toolbar').append(spinner);

            $("#uberspin").show();
            var filter = this.$el.find(".ubermedia"),
                ubermsg = $("<div/>", {
                    id: 'uber-msg'
                });
            this.$el.append(ubermsg);
            $("#uber-msg").hide();

            if (this.model.get('method')) {
                stream = this.sourceDetails.settings[this.model.get('method')];
                this.filterType = this.model.get('method');
            } else {
                for(var key in this.sourceDetails.settings) break;
                stream = this.sourceDetails.settings[key];
                this.filterType = key;
            }

            var filter = this.$el.find(".ubermedia"),
                page = $("<input/>", {
                    id: 'page',
                    type: 'hidden',
                    value: '1'
                });
            this.$el.append(page);
            if (this.model.get( 'page')) {
                $(page).val(this.model.get( 'page'));
            }

            var filter = this.$el.find(".ubermedia"),
                altpage = $("<input/>", {
                    id: 'altpage',
                    type: 'hidden',
                    value: ''
                });
            this.$el.append(altpage);
            if (this.model.get( 'altpage')) {
                $(altpage).val(this.model.get( 'altpage'));
            }

            var show = this.displayParam(stream);

            if (this.model.get('param')) {
                var param = this.$el.find('#param');
                $(param).val(this.model.get('param'));
                //$(param).show();
            }

            var filter = this.$el.find(".ubermedia"),
                imagelist = $("<ul/>", {
                    id: 'uberimages'
                });
            this.$el.append(imagelist);

            this.clearImages();
            var filter = this.$el.find(".ubermedia"),
                paginli = $("<li/>", {
                    id: 'pagin'
                });
            var filter = this.$el.find(".ubermedia"),
                pagin = $("<input/>", {
                    id: 'pagination',
                    type: 'button',
                    class: 'button',
                    value: 'Load More'
                });
            $(paginli).append( pagin );
            this.$el.find("#uberimages").append( paginli );

            if (this.model.get('images')) {
                this.collection = new UberImages(images);
                this.collection.reset(this.model.get('images'));
                if (this.model.get( 'pagin')) {
                    this.$el.find("#pagination").hide();
                } else this.$el.find("#pagination").show();
                if (this.model.get('method')) this.displayPag(this.model.get('method'));

                if (this.model.get( 'folder_path')) this.createBackLink(this.model.get( 'folder_path'));
            } else {
                this.$el.find("#pagination").hide();
                this.collection = new UberImages(images);
                if (show.check && this.sourceDetails.url == '#') {
                    images = this.getImages(this, this.source, key, show.param, 1, '');
                }
            }

            this.on("change:filterType", this.filterByType, this);
            this.on("change:paramValue", this.filterByParam, this);

            this.collection.on("reset", this.render, this);
        },

        oauthPopup: function( event ) {
            event.preventDefault();
            $('body').addClass('uber-media-overlay');
            var uber = this;
            var self = event.currentTarget,
                w = $(event.currentTarget).data('w'),
                h = $(event.currentTarget).data('h'),
                source = $(event.currentTarget).data('source'),
                left = (screen.width/2)-(w/2),
                top = (screen.height/2)-(h/2),
                params = 'location=0,status=0,width=' + w + ',height=' + h + ',top=' + top + ', left=' + left;
            $(event.currentTarget).removeClass('connect');
            event.currentTarget.oauth_window = window.open(event.currentTarget.href, 'Connect', params);

            event.currentTarget.interval = window.setInterval((function() {
                if (self.oauth_window.closed) {
                    $('body').removeClass('uber-media-overlay');
                    window.clearInterval(self.interval);
                    $.post(ajaxurl,
                        { action:'uber_check',
                            nonce: uber_media.nonce,
                            source: source },
                        function(data){
                            if(data.message == 'success') {
                                uber.sourceDetails.url = '#'
                                $('#uber-btn').hide();
                                $('#uber-toolbar').show();
                                uber.filterType = $('#method option:selected').val();
                                uber.trigger("change:filterType");
                            }
                        }
                        , 'json');
                    return false;
                }
            }), 100);
        },

        clearImages: function() {
            this.$el.find('ul#uberimages li#pagin').prevAll().remove();
        },

        selectFolder: function(event) {
            var path = $("#" + event.target.id).data('link');
            this.selectFolderHandler(path);
        },

        createBackLink: function(link) {
            var backlink = $("<a/>", {
                class: 'back-link',
                id: 'backfolder',
                href: '#',
                text: 'Back',
                'data-link': link
            });
            this.$el.find('#uber-toolbar').append(backlink);
        },

        selectFolderHandler: function(path) {
            this.model.set( 'folder_path', '');
            if (path != '/') {
                var paths = path.split('/');
                paths.pop();
                var backpath = '/';
                if (paths.length > 1) backpath = paths.join('/');

                if ($('#backfolder').length == 0) {
                    this.createBackLink(backpath);
                } else {
                    $('#backfolder').data('link', backpath);
                }
                this.model.set( 'folder_path', backpath );

                this.$el.find('#backfolder').show();

            } else this.$el.find('#backfolder').hide();

            this.setParamManual(path);
        },

        backFolder: function(event) {
            event.preventDefault();
            this.clearSidebar();
            this.clearSidebar();
            this.model.set( 'selected_id', '' );
            this.model.set( 'selected_image', '' );
            this.model.set( 'custom_data', '' );
            this.setButtonStates('');

            var path = $(event.target).data('link');
            this.selectFolderHandler(path);
        },

        toggleSelectionHandler: function( event ) {
            if ($('div.ubermedia').hasClass('uber-media-overlay')) return;
            var method = '';
            if ( event.shiftKey )
                method = 'between';
            else if ( event.ctrlKey || event.metaKey )
                method = 'toggle';

            this.toggleSelection(method, event);
        },

        toggleSelection: function(method, event) {
            $("#uber-button").attr('disabled','disabled');

            $("ul#uberimages li").removeClass("details");
            if ($("#" + event.target.id).closest('li').hasClass("selected")) {
                $("#" + event.target.id).closest('li').addClass("details");
            } else {
                if (method == '') {
                    $("ul#uberimages li").removeClass("selected");
                    this.selection.reset();
                }
                $("#" + event.target.id).closest('li').addClass("selected details");
            }
            this.$el.find(".uber-sidebar").empty();

            check_id = event.target.id;
            var uber = $(event.target).getAttributes();
            uber['setting-title'] = uber['title'];
            uber['setting-alt'] = uber['title'];
            uber['setting-caption'] = '';
            uber['setting-align'] = 'none';
            uber['setting-link-to'] = 'none';
            uber['setting-width'] = '0';
            uber['setting-height'] = '0';

            var defaults = wp.media.view.l10n.mmp_defaults;
            _.each(defaults, function (value, key) {
                uber[key] = value;
            }, this);

            this.selection.add(uber);
            this.custom_update(this.selection, event);
            this.populateSidebar(this.model);
            this.setButtonStates(this.selection);
        },

        preventDefault: function( event ) {
            event.preventDefault();
        },

        clearSelection: function(selection, selected) {

            if (selected == true) {
                this.model.set( 'selected_id', '' );
                this.model.set( 'selected_image', '' );
            }
            if (selection) {
                this.model.set( 'custom_data', selection );
                this.setButtonStates(selection);
            }

        },

        setButtonStates: function(selection) {
            var show = false;
            if (selection != '' && selection.length > 0) show = true;

            if (!show) $("#uber-button").attr('disabled','disabled');
            if (!show && importer) {
                $("#mmp-import-button").attr('disabled','disabled');
                $("#mmp-import-button").text(wp.media.view.l10n.mmpImportButton);
            }
            else if (show && importer) {
                $("#mmp-import-button").removeAttr('disabled');
                var imgtext = (selection.length > 1) ? ' images' : ' image';
                $("#mmp-import-button").text(wp.media.view.l10n.mmpImportButton + ' ' + selection.length + imgtext);
            }

        },

        removeFromSelection: function( event ) {
            if ($('div.ubermedia').hasClass('uber-media-overlay')) return;

            var check_id = event.currentTarget.id;
            if (event.currentTarget.tagName == 'A') {
                var imageid = check_id.substring(11);
            } else {
                var imageid = check_id.substring(6);
            }

            this.selection.remove( this.selection.get(imageid) );

            $("#" + event.target.id).closest('li').removeClass("selected");
            $("#" + event.target.id).closest('li').removeClass("details");

            var ifselected = false;
            if (this.model.get('selected_id') == imageid) {
                ifselected = true;
                this.$el.find(".uber-sidebar").empty();
            }
            this.clearSelection(this.selection, ifselected);
        },

        clearSidebar: function() {
            this.clearImages();
            this.clearSelection();
            this.model.set( 'selected_id', '' );
            this.model.set( 'selected_image', '' );
            //$("ul#uberimages li").removeClass("selected");
            $(".uber-sidebar").empty();
        },

        render: function () {
            var that = this;
            if (this.collection) {
                if (this.collection.models.length > 0) {
                    this.clearImages();
                    _.each(this.collection.models, function (item) {
                        this.$el.find('#pagin').before(that.renderImage(item));
                    }, this);
                    this.$el.find("#uberspin").hide();
                } else {
                    if (this.imageError != '') {
                        $("#uber-msg").text(this.imageError);
                        $("#uber-msg").show();
                        this.$el.find("#uberspin").hide();
                    }
                }
                this.imageError = '';
            }
            if (this.model.get('selected_image')) {
                var that = this;
                var selectedimg = this.$el.find("img#" + this.model.get('selected_id'));
                $(selectedimg).closest('li').addClass("details");
                this.populateSidebar(this.model);
                if (this.model.get('custom_data')) {
                    var selectedimages = this.model.get( 'custom_data' );
                    var selection = new SelectedImages(selectedimages.models);
                    selection.each(function(model){
                        var selectimg = that.$el.find("img#" +  model.get('id'));
                        $(selectimg).closest('li').addClass("selected");
                    });

                }
                $("#uber-button").removeAttr('disabled');
            }

        },

        renderImage: function (item) {
            var imageView = new UberImageView({
                model: item
            });
            return imageView.render().el;
        },

        custom_update: function( selection, event ) {
            this.model.set( 'selected_id',  event.target.id );
            this.model.set( 'selected_image', event.target );
            this.model.set( 'custom_data', selection);
        },

        createSelect: function () {
            var filter = this.$el.find(".ubermedia"),
                select = $("<select/>", {
                    html: "",
                    id: 'method'
                });
            var that = this;
            _.each(this.sourceDetails.settings, function (settings, method) {
                if (that.model.get('method') && (that.model.get('method') == method)) {
                    var option = $("<option/>", {
                        value: method,
                        text: settings.name,
                        selected: 'selected'
                    }).appendTo(select);
                } else {
                    var option = $("<option/>", {
                        value: method,
                        text: settings.name
                    }).appendTo(select);
                }
            });
            return select;
        },

        getImages: function(collection, source, method, param, page, altpage) {
            this.$el.find("#uberspin").show();
            this.$el.find("#uber-msg").hide();
            this.$el.find("#pagination").val('Loading...');
            this.$el.find("#pagination").attr('disabled', 'disabled');
            if (page == 1) {
                this.$el.find("#pagination").hide();
                this.clearImages();
            }
            var images;
            $.post(ajaxurl,
                { action: 'uber_load_images',
                    source: source,
                    method: method,
                    param: param,
                    page: page,
                    altpage: altpage },
                function(response){
                    if (response.error) {
                        collection.imageError = response.message;
                        collection.$el.find("#pagination").hide();
                    }
                    else {
                        images = response.images;
                        if(method == collection.filterType) {
                            if (page == 1) {
                                collection.model.set( 'images', images);
                            }
                            else {
                                original = collection.model.get( 'images');
                                collection.model.set( 'images', original.concat(images));
                            }
                        }
                    }
                    if(method == collection.filterType) {
                        if (page == 1) collection.collection.reset(images);
                        else {
                            var pagCollection = new UberImages(images);
                            collection.collection.add(pagCollection.models);
                            _.each(pagCollection.models, function (item) {
                                collection.$el.find('#pagin').before(collection.renderImage(item));
                            }, this);
                        }
                        collection.displayPag(method);
                        collection.$el.find("#pagination").val('Load More');
                        collection.$el.find("#uberspin").hide();
                        if (response.pagin == 'end') collection.model.set( 'pagin', 'end');
                        else {
                            if (!response.error) {
                                collection.$el.find("#pagination").removeAttr('disabled');
                                collection.model.set( 'pagin', '');
                            } else 	collection.$el.find("#pagination").hide();
                        }
                        if (response.altpage ) {
                            collection.model.set( 'altpage', response.altpage);
                            $("#altpage").val(response.altpage);
                        }
                    }
                    else collection.$el.find("#uberspin").hide();
                }
                , 'json');


        },

        getPagination: function() {
            var page = $("#page").val();
            page++;
            $("#page").val(page);
            this.model.set( 'page', page);
            var altpage = $("#altpage").val();
            this.model.set( 'altpage', altpage);
            this.getImages(this, this.source, this.$el.find("#method").val(), this.model.get( 'param'), page, altpage);
        },

        displayPag: function(method) {
            this.$el.find("#pagination").show();
            stream = this.sourceDetails.settings[method];
            if (stream.nopagin) this.$el.find("#pagination").hide();
        },

        displayParam: function(stream) {
            //this.model.set( 'param', '');
            var show = new Object();
            show['check'] = false;
            show['param'] = '';

            paraminput = this.$el.find("#param").hide();
            paramselect = this.$el.find("#paramselect").hide();
            if(stream.param) {
                this.$el.find("#pagination").attr('disabled', 'disabled');
                var param_value = (stream.param_default) ? stream.param_default : '';
                show['param'] = param_value;
                this.$el.find("#param").val(param_value);
                this.$el.find("#param").attr("placeholder", stream.param_desc);
                if (stream.param_disabled) this.$el.find("#param").attr("disabled", "disabled");
                if (stream.param_type == 'text') {
                    this.$el.find("#param").show();
                    this.$el.find("#paramselect").hide();
                }
                else {
                    var that = this;

                    if (stream.param_dynamic && !stream.param_choices && this.sourceDetails.url == '#') {
                        this.$el.find("#uberspin").show();
                        $.post(ajaxurl,
                            { action: 'uber_param_choices',
                                source: that.source,
                                method: that.$el.find("#method").val(),
                            },
                            function(response){
                                that.populateParamSelect(response.choices, paramselect);
                                stream.param_choices = response.choices;
                                that.sourceDetails.settings[that.$el.find("#method").val()] = stream;

                            }
                            , 'json');
                    } else if (stream.param_dynamic && !stream.param_choices && this.sourceDetails.url != '#') {
                        this.populateParamSelect([], paramselect);
                    } else {
                        this.populateParamSelect(stream.param_choices, paramselect);
                    }
                }
            }
            this.$el.find("#pagination").show();
            if (stream.nopagin) this.$el.find("#pagination").hide();

            show['check'] = (stream.param_default) ? stream.param_default : !stream.param;
            return show;
        },

        populateParamSelect: function(choices, paramselect) {
            var that = this;
            _.each(choices, function (value, key) {
                if (that.model.get('param') && (that.model.get('param') == key)) {
                    var option = $("<option/>", {
                        value: key,
                        text: value,
                        selected: 'selected'
                    }).appendTo(paramselect);
                } else {
                    var option = $("<option/>", {
                        value: key,
                        text: value
                    }).appendTo(paramselect);
                }
            });
            this.$el.find("#param").hide();
            this.$el.find("#paramselect").show();
            this.$el.find("#uberspin").hide();

        },

        createSidebar: function() {
            var sidebar = new UberImageSidebar();
            this.$el.append(sidebar.render().el);
        },

        populateSidebar: function(item) {
            var imageSettings = new UberImageSettings({
                model: item
            });
            this.$el.find(".uber-sidebar").append(imageSettings.render().el);
        },

        setFilter: function (e) {
            this.clearSidebar();
            this.filterType = e.currentTarget.value;
            this.model.set( 'method', e.currentTarget.value);
            this.model.set( 'param', '');
            this.trigger("change:filterType");
        },

        setParam: function (e) {
            if (e.currentTarget.value != '') {
                this.setParamManual(e.currentTarget.value);
            }
        },

        setParamManual: function (paramValue) {
            this.clearSidebar();
            this.paramValue = paramValue;
            this.model.set( 'param', paramValue);
            $('#param').val(paramValue);
            this.trigger("change:paramValue");
        },

        filterByType: function () {
            this.clearSidebar();

            this.model.set( 'selected_id', '' );
            this.model.set( 'selected_image', '' );

            this.model.set( 'custom_data', '' );
            this.setButtonStates('');

            this.$el.find("#uberspin").hide();
            this.$el.find("#pagination").val('Load More');
            this.$el.find("#page").val('1');
            this.$el.find("#altpage").val('');
            stream = this.sourceDetails.settings[this.filterType];
            var show = this.displayParam(stream);
            if (show.check) {
                var images = this.getImages(this, this.source, this.filterType, show.param, 1, '');
            } else {
                this.$el.find("#pagination").hide();
                this.collection.reset();
            }
        },

        filterByParam: function() {
            this.$el.find("#page").val('1');
            this.$el.find("#altpage").val('');
            var images = this.getImages(this, this.source, this.$el.find("#method").val(), this.paramValue, 1, '');
        }

    });


    var oldMediaFrame = wp.media.view.MediaFrame.Post;
    wp.media.view.MediaFrame.Post = oldMediaFrame.extend({

        initialize: function() {
            oldMediaFrame.prototype.initialize.apply( this, arguments );

            var ubermedia_sources = wp.media.view.l10n.ubermedia;
            var mediaframe = this;

            var priority = 200;
            $.each(ubermedia_sources, function(source, source_details) {
                mediaframe.states.add([
                    new wp.media.controller.UberMedia({
                        id:         source,
                        menu:       wp.media.view.l10n.mmp_menu, // menu event = menu:render:default
                        content:    source + '-custom',
                        title:      wp.media.view.l10n.mmp_menu_prefix + source_details.name,
                        priority:   priority + 100,
                        toolbar:    source + '-action', // toolbar event = toolbar:create:main-my-action
                        type:       'link'
                    })
                ]);

                mediaframe.on( 'content:render:'+ source + '-custom',  _.bind(mediaframe.customContent, mediaframe, source, source_details));
                mediaframe.on( 'toolbar:create:'+ source + '-action', mediaframe.createCustomToolbar, mediaframe );
                mediaframe.on( 'toolbar:render:'+ source + '-action', mediaframe.renderCustomToolbar, mediaframe );

            });


        },

        createCustomToolbar: function(toolbar){
            toolbar.view = new wp.media.view.Toolbar.UberMedia({
                controller: this
            });
        },

        customContent: function(source, source_details){
            this.$el.addClass('hide-router');

            var view = new wp.media.view.UberMedia({
                controller: this,
                model: this.state().props,
                className: 'ubermedia media-' + source,
                sourceDetails: source_details,
                source: source
            });

            this.content.set( view );
        }

    });

} (jQuery));

(function($) {
    $.fn.getAttributes = function() {
        var attributes = {};

        if( this.length ) {
            $.each( this[0].attributes, function( index, attr ) {
                attributes[ attr.name ] = attr.value;
            } );
        }

        return attributes;
    };
})(jQuery);