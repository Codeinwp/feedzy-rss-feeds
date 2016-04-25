/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: http://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 */
jQuery(document).ready(function($) {
	
	function feedzyMediaLibrary(){
		$(document).on('click', 'i.mce-i-feedzy-icon', function(){
				setTimeout(function() {
				$('.mce-feedzy-validator').parent('div').find('label').append( 
					' <a href="https://validator.w3.org/feed/" target="_blank" style="font-weight: 800;" title="Validate my feed!">Validate my feed!</a>'
				);
				$('.mce-feedzy-media').after( 
					'<span class="mce-feedzy-media-button">+</span>' +
					'<style>.mce-feedzy-media { padding-right: 25px; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; height: 30px !important; }' + 
					'.mce-feedzy-media-button { cursor: pointer; width: 28px; height: 28px; display: block; -webkit-font-smoothing: antialiased; float: right; background-color: #F7F7F7; border: 1px solid #DDD; font-size: 30px; color: #777; position: relative; line-height: 28px; text-align: center;' +
					' }</style>'
				);
			}, 100);
		});
				
		$(document).on('click', '.mce-feedzy-media-button', function(){
			var $this = $(this);
			 var wireframe;
			 if (wireframe) {
				 wireframe.open();
				 return;
			 }
	
			 wireframe = wp.media.frames.wireframe = wp.media({
				 /*title: 'Media Library Title',
				 button: {
					 text: 'Media Library Button Title'
				 },*/
				 multiple: false
			 });
	
			 wireframe.on('select', function() {
				attachment = wireframe.state().get('selection').first().toJSON();
				$this.parent().find('.mce-feedzy-media').val(attachment.url);
			 });
	
			 wireframe.open();
		});
	};
	feedzyMediaLibrary();
							
});