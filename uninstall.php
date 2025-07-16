<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * @link       https://themeisle.com
 * @since      3.0.0
 *
 * @package    Feedzy_Rss_Feeds
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


// clean up after ourselves, that's a good plugin!
delete_option( 'feedzy_rss_feeds_logger_flag' );
delete_option( 'feedzy_logger_flag' );
delete_option( 'feedzy-settings' );
delete_option( 'feedzy-rss-feeds' );
delete_option( 'feedzy_fresh_install' );
delete_option( 'feedzy_wizard_data' );
delete_option( 'feedzy_usage' );
