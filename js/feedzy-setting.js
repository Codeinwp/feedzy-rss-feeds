/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: http://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 *
 * @package feedzy-rss-feeds
 */
/* global feedzy_setting */
/* jshint unused:false */
jQuery( function( $ ) {
  // on upload button click
  $( 'body' ).on( 'click', '.feedzy-open-media', function( e ) {
    e.preventDefault();
    var button = $( this ),
    wp_media_uploader = wp.media( {
      title: feedzy_setting.l10n.media_iframe_title,
      library : {
        type : 'image'
      },
      button: {
        text: feedzy_setting.l10n.media_iframe_button
      },
      multiple: false
    } ).on( 'select', function() { // it also has "open" and "close" events
      var attachment = wp_media_uploader.state().get( 'selection' ).first().toJSON();
      var attachmentUrl = attachment.url;
      if ( attachment.sizes.thumbnail ) {
        attachmentUrl = attachment.sizes.thumbnail.url;
      }
      if ( $( '.feedzy-media-preview' ).length ) {
        $( '.feedzy-media-preview' ).find( 'img' ).attr( 'src', attachmentUrl );
      } else {
        $( '<div class="fz-form-group feedzy-media-preview"><img src="' + attachmentUrl + '"></div>' ).insertBefore( button.parent() );
      }
      button.parent().find( '.feedzy-remove-media' ).addClass( 'is-show' );
      button.parent().find( 'input:hidden' ).val( attachment.id ).trigger( 'change' );
    } ).open();
  });

  // on remove button click
  $( 'body' ).on( 'click', '.feedzy-remove-media', function( e ) {
    e.preventDefault();
    var button = $( this );
    button.parent().prev( '.feedzy-media-preview' ).remove();
    button.removeClass( 'is-show' );
    button.parent().find( 'input:hidden' ).val( '' ).trigger( 'change' );
  });

  // Unsaved form exit confirmation.
  var unsaved = false;
  $( ':input' ).change(function () {         
    unsaved = true;
  });
  $( '#feedzy-settings-submit, #check_wordai_api, #check_spinnerchief_api, #check_aws_api' ).on( 'click', function() {
    unsaved = false;
  } );
  window.addEventListener( 'beforeunload', function( e ) {
    if ( unsaved ) {
      e.preventDefault();
      e.returnValue = '';
    }
  });

  // Select cron execution time.
  $( document ).on( 'change', '#fz-event-execution', function() {
      $( '#fz-execution-offset' ).val( new Date().getTimezoneOffset() / 60 );
  } );
});
