<?php
/**
 * Fired during plugin activation
 *
 * @link       https://themeisle.com
 * @since      3.0.0
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      3.0.0
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes
 * @author     Themeisle <friends@themeisle.com>
 */
class Feedzy_Rss_Feeds_Activator {

	/**
	 * Plugin activation action.
	 *
	 * Triggers the plugin activation action on plugin activate.
	 *
	 * @since    3.0.0
	 * @access   public
	 */
	public static function activate() {
		$options           = get_option( Feedzy_Rss_Feeds::get_plugin_name(), array() );
		$is_fresh_install  = get_option( 'feedzy_fresh_install', false );
		$old_logger_option = get_option( 'feedzy_logger_flag', 'no' );
		if ( 'yes' === $old_logger_option ) {
			update_option( 'feedzy_rss_feeds_logger_flag', 'yes' );
			update_option( 'feedzy_logger_flag', 'no' );
		}
		if ( ! isset( $options['is_new'] ) ) {
			update_option(
				Feedzy_Rss_Feeds::get_plugin_name(),
				array(
					'is_new' => 'yes',
				)
			);
		}
		if ( ! defined( 'TI_CYPRESS_TESTING' ) && false === $is_fresh_install ) {
			update_option( 'feedzy_fresh_install', '1' );
		}
		add_option( 'feedzy-activated', true );

		if ( Feedzy_Rss_Feeds_Usage::get_instance()->is_new_user() ) {
			Feedzy_Rss_Feeds_Usage::get_instance()->update_usage_data(
				array(
					'can_track_first_usage' => true,
				)
			);
		}
		self::add_feeds_group();
	}

	/**
	 * Adds a default feeds group with some popular WordPress-related feeds.
	 *
	 * This method checks if a feeds group already exists, and if not, creates one
	 * with a set of predefined feeds.
	 *
	 * @return void
	 */
	public static function add_feeds_group() {
		if ( get_option( '_feedzy_news_group_id', false ) ) {
			return;
		}

		$group_args = array(
			'post_title'   => __( 'News sites', 'feedzy-rss-feeds' ),
			'post_type'    => 'feedzy_categories',
			'post_status'  => 'publish',
			'post_content' => '',
		);

		$news_group = wp_insert_post( $group_args );

		$default_feed_urls = array(
			'https://themeisle.com/blog/feed/',
			'https://wptavern.com/feed/',
			'https://www.wpbeginner.com/feed/',
			'https://wpshout.com/feed/',
			'https://planet.wordpress.org/feed/',
		);

		$feed_groups = implode( ', ', array_map( 'esc_url', $default_feed_urls ) );

		add_post_meta( $news_group, 'feedzy_category_feed', $feed_groups );

		update_option( '_feedzy_news_group_id', $news_group );
	}
}
