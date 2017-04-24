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
if ( ! class_exists( 'Feedy_Rss_Feeds_Options' ) ) {
	/**
	 * Singleton class for options wrapper
	 */
	class Feedzy_Rss_Feeds_Options {

		/**
		 * The main instance var.
		 *
		 * @var Feedzy_Rss_Feeds_Options The one Feedy_Rss_Feeds_Options istance.
		 * @since 3.0.3
		 */
		private static $instance;

		/**
		 * The main options array.
		 *
		 * @var array The options array.
		 * @since 3.0.3
		 */
		private $options;

		/**
		 * Init the main singleton instance class.
		 *
		 * @return Feedzy_Rss_Feeds_Options Return the instance class
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Feedzy_Rss_Feeds_Options ) ) {
				self::$instance = new Feedzy_Rss_Feeds_Options;
				self::$instance->init();
			}

			return self::$instance;
		}

		/**
		 *  Init the default values of the options class.
		 */
		public function init() {
			self::$instance->options = get_option( Feedzy_Rss_Feeds::get_plugin_name() );
		}

		/**
		 * Get the key option value from DB.
		 *
		 * @param string $key The key name of the option.
		 *
		 * @return bool|mixed The value of the option
		 */
		public function get_var( $key ) {
			if ( isset( self::$instance->options[ $key ] ) ) {
				return self::$instance->options[ $key ];
			}

			return false;
		}

		/**
		 * Setter method for updating the options array.
		 *
		 * @param string $key The name of option.
		 * @param string $value The value of the option.
		 *
		 * @return bool|mixed The value of the option.
		 */
		public function set_var( $key, $value = '' ) {
			self::$instance->options[ $key ] = apply_filters( 'feedzy_pre_set_option_' . $key, $value );

			return update_option( Feedzy_Rss_Feeds::get_plugin_name(), self::$instance->options );

		}
	}
}// End if().
