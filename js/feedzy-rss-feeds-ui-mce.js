/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: http://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 */
(function() {
	tinymce.PluginManager.add('feedzy_mce_button', function( editor, url ) {
		editor.addButton( 'feedzy_mce_button', {
			icon: 'feedzy-icon',
			onclick: function() {
				editor.windowManager.open( {
					title: editor.getLang('feedzy_tinymce_plugin.plugin_title'),
					body: [
						{
							type: 'textbox',
							name: 'feeds',
							classes : 'feedzy-validator', //necessary to insert the validator link
							label: editor.getLang('feedzy_tinymce_plugin.feeds'),
							value: ''
						},
						{
							type: 'textbox',
							name: 'maximum',
							label: editor.getLang('feedzy_tinymce_plugin.maximum'),
							value: ''
						},
						{
							type: 'listbox',
							name: 'feed_title',
							label: editor.getLang('feedzy_tinymce_plugin.feed_title'),
							'values': [
								{text: editor.getLang('feedzy_tinymce_plugin.text_default'), value: ''},
								{text: editor.getLang('feedzy_tinymce_plugin.text_no'), value: 'no'},
								{text: editor.getLang('feedzy_tinymce_plugin.text_yes'), value: 'yes'},
							]
						},
						{
							type: 'listbox',
							name: 'target',
							label: editor.getLang('feedzy_tinymce_plugin.target'),
							'values': [
								{text: editor.getLang('feedzy_tinymce_plugin.text_default'), value: ''},
								{text: '_blank', value: '_blank'},
								{text: '_self', value: '_self'},
								{text: '_parent', value: '_parent'},
								{text: '_top', value: '_top'},
								{text: 'framename', value: 'framename'}
							]
						},
						{
							type: 'textbox',
							name: 'title',
							label: editor.getLang('feedzy_tinymce_plugin.title'),
							value: ''
						},
						{
							type: 'listbox',
							name: 'meta',
							label: editor.getLang('feedzy_tinymce_plugin.meta'),
							'values': [
								{text: editor.getLang('feedzy_tinymce_plugin.text_default'), value: ''},
								{text: editor.getLang('feedzy_tinymce_plugin.text_no'), value: 'no'},
								{text: editor.getLang('feedzy_tinymce_plugin.text_yes'), value: 'yes'},
							]
						},
						{
							type: 'listbox',
							name: 'summary',
							label: editor.getLang('feedzy_tinymce_plugin.summary'),
							'values': [
								{text: editor.getLang('feedzy_tinymce_plugin.text_default'), value: ''},
								{text: editor.getLang('feedzy_tinymce_plugin.text_no'), value: 'no'},
								{text: editor.getLang('feedzy_tinymce_plugin.text_yes'), value: 'yes'},
							]
						},
						{
							type: 'textbox',
							name: 'summarylength',
							label: editor.getLang('feedzy_tinymce_plugin.summarylength'),
							value: ''
						},
						{
							type: 'listbox',
							name: 'thumb',
							label: editor.getLang('feedzy_tinymce_plugin.thumb'),
							'values': [
								{text: editor.getLang('feedzy_tinymce_plugin.text_default'), value: ''},
								{text: editor.getLang('feedzy_tinymce_plugin.text_no'), value: 'no'},
								{text: editor.getLang('feedzy_tinymce_plugin.text_yes'), value: 'yes'},
								{text: editor.getLang('feedzy_tinymce_plugin.text_auto'), value: 'auto'},
							]
						},
						{
							type: 'textbox',
							name: 'defaultimg',
							classes : 'feedzy-media', //necessary to call the media library
							label: editor.getLang('feedzy_tinymce_plugin.defaultimg'),
							value: ''
						},
						{
							type: 'textbox',
							name: 'size',
							label: editor.getLang('feedzy_tinymce_plugin.size'),
							value: ''
						},
						{
							type: 'textbox',
							name: 'keywords_title',
							label: editor.getLang('feedzy_tinymce_plugin.keywords_title'),
							value: ''
						}
					],
					onsubmit: function( e ) {
						if(e.data.feeds != ''){
							e.data.feeds = 'feeds="' + e.data.feeds + '" ';
						} else {
							e.data.feeds = 'feeds="http://themeisle.com/feed" ';
						}
						if(e.data.maximum != ''){
							e.data.maximum = 'max="' + e.data.maximum + '" ';
						}
						if(e.data.feed_title != ''){
							e.data.feed_title = 'feed_title="' + e.data.feed_title + '" ';
						}
						if(e.data.target != ''){
							e.data.target = 'target="' + e.data.target + '" ';
						}
						if(e.data.title != ''){
							e.data.title = 'title="' + e.data.title + '" ';
						}
						if(e.data.meta != ''){
							e.data.meta = 'meta="' + e.data.meta + '" ';
						}
						if(e.data.summary != ''){
							e.data.summary = 'summary="' + e.data.summary + '" ';
						}
						if(e.data.summarylength != ''){
							e.data.summarylength = 'summarylength="' + e.data.summarylength + '" ';
						}
						if(e.data.thumb != ''){
							e.data.thumb = 'thumb="' + e.data.thumb + '" ';
						}
						if(e.data.defaultimg != ''){
							e.data.defaultimg = 'default="' + e.data.defaultimg + '" ';
						}
						if(e.data.size != ''){
							e.data.size = 'size="' + e.data.size + '" ';
						}
						if(e.data.keywords_title != ''){
							e.data.keywords_title = 'keywords_title="' + e.data.keywords_title + '" ';
						}
						editor.insertContent( 
							'[feedzy-rss '
								+ e.data.feeds
								+ e.data.maximum
								+ e.data.feed_title
								+ e.data.target
								+ e.data.title
								+ e.data.meta
								+ e.data.summary
								+ e.data.summarylength
								+ e.data.thumb
								+ e.data.defaultimg
								+ e.data.size
								+ e.data.keywords_title
							+ ']'
						);
					}
				});
			}
		});
	});
})();