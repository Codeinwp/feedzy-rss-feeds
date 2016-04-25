<?php
/***************************************************************
 * SECURITY : Exit if accessed directly
***************************************************************/
if ( !defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed!' );
}


/***************************************************************
 * Hooks custom TinyMCE button function
 ***************************************************************/ 
function feedzy_add_mce_button() {

	if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) )
	return;
	
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		
		add_filter( 'mce_external_plugins', 'feedzy_tinymce_plugin' );
		add_filter( 'mce_buttons', 'feedzy_register_mce_button' );
		
		// Load stylesheet for tinyMCE button only
		wp_enqueue_style( 'feedzy-rss-feeds', plugin_dir_url( __FILE__ ) . 'css/feedzy-rss-feeds.css', array(), FEEDZY_VERSION );
		wp_enqueue_script( 'feedzy-rss-feeds-ui-scripts', plugin_dir_url( __FILE__ ) . 'js/feedzy-rss-feeds-ui-scripts.js', array( 'jquery' ), FEEDZY_VERSION );
	}
	
}
add_action( 'init', 'feedzy_add_mce_button' );


/***************************************************************
 * Load plugin translation for - TinyMCE API
 ***************************************************************/ 
function feedzy_add_tinymce_lang( $arr ){
    $arr[] = plugin_dir_path( __FILE__ ) . 'feedzy-rss-feeds-ui-lang.php';
    return $arr;
}
add_filter( 'mce_external_languages', 'feedzy_add_tinymce_lang', 10, 1 );


/***************************************************************
 * Load custom js options - TinyMCE API
 ***************************************************************/ 
function feedzy_tinymce_plugin( $plugin_array ) {
	$plugin_array[ 'feedzy_mce_button' ] = plugin_dir_url( __FILE__ ) . '/js/feedzy-rss-feeds-ui-mce.js';
	return $plugin_array;
}


/***************************************************************
 * Register new button in the editor
 ***************************************************************/ 
function feedzy_register_mce_button( $buttons ) {
	array_push( $buttons, 'feedzy_mce_button' );
	return $buttons;
}