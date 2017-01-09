<?php
/**
 * The Options main wrapper class.
 *
 * @link       http://themeisle.com
 * @since      3.0.3
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
 */
if ( ! class_exists( 'Feedzy_Rss_Feeds_Upgrader' ) ) {
	/**
	 * Class Feedzy_Rss_Feeds_Upgrader for upgrading processes
	 */
	class Feedzy_Rss_Feeds_Upgrader {
		/**
		 * Feedzy_Rss_Feeds_Upgrader constructor.
		 */
		public function __construct() {
			$php_version = Feedzy_Rss_Feeds::get_version();
			$db_version  = feedzy_options()->get_var( 'db_version' );
			if ( $db_version === false ) {
				feedzy_options()->set_var( 'db_version', $php_version );
			}
			if ( version_compare( $db_version, $php_version ) === - 1 ) {
				do_action( 'feedzy_upgrade_to_' . self::version_to_hook( $php_version ), $db_version );
			}
		}

		/**
		 * Normalize version to be used in hooks.
		 *
		 * @param string $version In format 2.0.0.
		 *
		 * @return string Version format 2_0_0.
		 */
		public static function version_to_hook( $version ) {
			return str_replace( '.', '_', $version );
		}
	}
}
