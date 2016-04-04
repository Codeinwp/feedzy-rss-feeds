<?php
/**
 * Plugin Name: FEEDZY RSS Feeds by b*web
 * Plugin URI: http://themeisle.com/plugins/feedzy-rss-feeds/
 * Description: FEEDZY RSS Feeds is a small and lightweight plugin. Fast and easy to use, it aggregates RSS feeds into your WordPress site through simple shortcodes.				
 * Author: Themeisle
 * Author URI: http://themeisle.com
 * Version: 2.7.1
 * Text Domain: feedzy_rss_translate
 * Text Domain Path: /langs
 */

/***************************************************************
 * SECURITY : Exit if accessed directly
***************************************************************/
if ( !defined( 'ABSPATH' ) ) {
	
	die( 'Direct access not allowed!' );
	
}


/***************************************************************
 * Define constant
 ***************************************************************/
if ( !defined( 'FEEDZY_VERSION' ) ) {
	
    define( 'FEEDZY_VERSION', '2.7.1' );
	
}


/***************************************************************
 * Load plugin textdomain
 ***************************************************************/
function feedzy_rss_load_textdomain() {
	
	$path = dirname(plugin_basename( __FILE__ )) . '/langs/';
	load_plugin_textdomain( 'feedzy_rss_translate', false, $path );
	
}
add_action( 'init', 'feedzy_rss_load_textdomain' );


/***************************************************************
 * Add custom meta link on plugin list page
 ***************************************************************/
function feedzy_meta_links( $links, $file ) {
	
	if ( $file === 'feedzy-rss-feeds/feedzy-rss-feed.php' ) {
		$links[] = '<a href="http://b-website.com/feedzy-rss-feeds-wordpress-plugin-using-simplepie" target="_blank" title="'. __( 'Documentation and examples', 'feedzy_rss_translate' ) .'">'. __( 'Documentation and examples', 'feedzy_rss_translate' ) .'</a>';
		$links[] = '<a href="http://b-website.com/category/plugins" target="_blank" title="'. __( 'More b*web Plugins', 'feedzy_rss_translate' ) .'">'. __( 'More b*web Plugins', 'feedzy_rss_translate' ) .'</a>';
		$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7Z6YVM63739Y8" target="_blank" title="' . __( 'Donate to this plugin &#187;' ) . '"><strong>' . __( 'Donate to this plugin &#187;' ) . '</strong></a>';
	}
	
	return $links;
	
}
add_filter( 'plugin_row_meta', 'feedzy_meta_links', 10, 2 );


/***************************************************************
 * Load plugin files
 ***************************************************************/
$feedzyFiles = array( 'functions', 'shortcode', 'widget','ui' );
foreach( $feedzyFiles as $feedzyFile ){
	
	require_once( plugin_dir_path( __FILE__ ) . 'feedzy-rss-feeds-' . $feedzyFile . '.php' );
	
}