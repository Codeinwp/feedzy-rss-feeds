<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://themeisle.com
 * @since      3.0.0
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      3.0.0
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes
 * @author     Themeisle <friends@themeisle.com>
 */
class Feedzy_Rss_Feeds_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    3.0.0
	 * @access   public
	 */
	public static function deactivate() {
		self::try_send_logs();
		delete_option( 'feedzy-activated' );
	}

	/**
	 * Send the logs with the plugin usage if telemetry is active.
	 *
	 * @return void
	 */
	public static function try_send_logs() {
		if ( 'yes' !== get_option( 'feedzy_rss_feeds_logger_flag' ) ) {
			return;
		}

		do_action( 'feedzy_rss_feeds_log_activity' );
	}
}
