<?php
/**
 * The class that contains a custom implementation of SimplePie.
 *
 * @link       https://themeisle.com
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/util
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'SimplePie' ) ) {
	require_once ABSPATH . WPINC . '/class-simplepie.php';
	require_once ABSPATH . WPINC . '/class-wp-feed-cache-transient.php';
	require_once ABSPATH . WPINC . '/class-wp-simplepie-file.php';
}

/**
 * The class that contains a custom implementation of SimplePie.
 *
 * Class that contains a custom implementation of SimplePie.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/util
 * @author     Themeisle <friends@themeisle.com>
 */
class Feedzy_Rss_Feeds_Util_Feed extends SimplePie {

	/**
	 * The shortcode attributes.
	 *
	 * @access   private
	 * @var      array $sc The shortcode attributes.
	 */
	private static $sc;

	/**
	 * Whether custom sorting is enabled.
	 *
	 * @access   private
	 * @var      bool $custom_sorting Whether custom sorting is enabled.
	 */
	private static $custom_sorting = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @access  public
	 *
	 * @param   array $sc The shortcode attributes.
	 */
	public function __construct( $sc ) {
		self::$sc = $sc;
		if ( array_key_exists( 'sort', self::$sc ) && ! empty( self::$sc['sort'] ) ) {
			if ( 'date_desc' === self::$sc['sort'] ) {
				$this->enable_order_by_date( true );
			} else {
				self::$custom_sorting = true;
			}
		}
		parent::__construct();
	}

	/**
	 * Sorting callback for items
	 *
	 * @access public
	 * @param SimplePie $a The SimplePieItem.
	 * @param SimplePie $b The SimplePieItem.
	 * @return int
	 */
	public static function sort_items( $a, $b ) {
		if ( self::$custom_sorting ) {
			// integers, not booleans: PHP 8 deprecates a bool return from a comparison
			// callback. The ordering is the one PHP derived from the booleans before.
			switch ( self::$sc['sort'] ) {
				case 'title_desc':
					return strcmp( (string) $b->get_title(), (string) $a->get_title() );
				case 'title_asc':
					return strcmp( (string) $a->get_title(), (string) $b->get_title() );
				case 'date_asc':
					return (int) $a->get_date( 'U' ) <=> (int) $b->get_date( 'U' );
			}
		}
		// SimplePie's own comparator already returns -1/0/1; the stub types it as bool.
		return (int) parent::sort_items( $a, $b );
	}

	/**
	 * Return the filename (i.e. hash, without path and without extension) of the file to cache a given URL.
	 *
	 * @param string $url The URL of the feed to be cached.
	 * @return string A filename (i.e. hash, without path and without extension).
	 */
	public function get_cache_filename( $url ) {
		// Append custom parameters to the URL to avoid cache pollution in case of multiple calls with different parameters.
		$url .= $this->force_feed ? '#force_feed' : '';
		return call_user_func( $this->cache_name_function, $url );
	}
}
