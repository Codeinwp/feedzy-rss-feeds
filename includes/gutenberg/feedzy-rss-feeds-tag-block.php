<?php
/**
 * Class for functionalities related to Loop block.
 *
 * Defines the functions that need to be used for Loop block,
 * and REST router.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/guteneberg
 * @author     Themeisle <friends@themeisle.com>
 *
 * @since 5.1.0
 */
class Feedzy_Rss_Feeds_Tag_Block {

	/**
	 * A reference to an instance of this class.
	 *
	 * @var Feedzy_Rss_Feeds_Tag_Block|null The one Feedzy_Rss_Feeds_Loop_Block instance.
	 */
	private static $instance;

	/**
	 * Feedzy RSS Feeds plugin version.
	 *
	 * @var string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Returns an instance of this class.
	 * 
	 * @return Feedzy_Rss_Feeds_Tag_Block The instance of the class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Feedzy_Rss_Feeds_Tag_Block();
		}
		return self::$instance;
	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {
		$this->version = Feedzy_Rss_Feeds::get_version();
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register Block.
	 * 
	 * @return void
	 */
	public function register_block() {
		$metadata_file = trailingslashit( FEEDZY_ABSPATH ) . '/build/tag/block.json';
		register_block_type_from_metadata(
			$metadata_file,
			array(
				'render_callback' => array( $this, 'render_callback' ),
			)
		);
	}

	/**
	 * Render Callback
	 *
	 * @param array<string, mixed> $attributes The block attributes.
	 * @param string               $content The block content.
	 * @param WP_Block             $block The block instance.
	 * @return string The block content.
	 */
	public function render_callback( $attributes, $content, $block ) {
		if (
			! isset( $attributes['tag'] ) || empty( $attributes['tag'] ) ||
			empty( $block->context ) ||
			! isset( $block->context['feedzy-rss-feeds/feedItem'] ) || empty( $block->context['feedzy-rss-feeds/feedItem'] )
		) {
			return '';
		}

		$feed_item = $block->context['feedzy-rss-feeds/feedItem'];

		if ( ! isset( $feed_item[ $attributes['tag'] ] ) ) {
			return '';
		}

		return wp_kses_post( $feed_item[ $attributes['tag'] ] );
	}

	/**
	 * Magic Tags Replacement.
	 *
	 * @param string               $content The content.
	 * @param array<string, mixed> $item The feed item.
	 *
	 * @return string The content.
	 */
	public function apply_magic_tags( $content, $item ) {
		$pattern = '/\{\{feedzy_([^}]+)\}\}/';
		$content = str_replace(
			array(
				FEEDZY_ABSURL . 'img/feedzy.svg',
				'http://{{feedzy_url}}',
			),
			array(
				'{{feedzy_image}}',
				'{{feedzy_url}}',
			),
			$content
		);

		return preg_replace_callback(
			$pattern,
			function ( $matches ) use ( $item ) {
				return $this->get_value( $matches[1], $item );
			},
			$content 
		);
	}

	/**
	 * Get Dynamic Value.
	 *
	 * @param string               $key The key.
	 * @param array<string, mixed> $item Feed item.
	 *
	 * @return string The value.
	 */
	public function get_value( $key, $item ) {
		switch ( $key ) {
			case 'title':
				return isset( $item['item_title'] ) ? $item['item_title'] : '';
			case 'url':
				return isset( $item['item_url'] ) ? $item['item_url'] : '';
			case 'date':
				$item_date = isset( $item['item_date'] ) ? wp_date( get_option( 'date_format' ), $item['item_date'] ) : '';
				return $item_date;
			case 'time':
				$item_date = isset( $item['item_date'] ) ? wp_date( get_option( 'time_format' ), $item['item_date'] ) : '';
				return $item_date;
			case 'datetime':
				$item_date = isset( $item['item_date'] ) ? wp_date( get_option( 'date_format' ), $item['item_date'] ) : '';
				$item_time = isset( $item['item_date'] ) ? wp_date( get_option( 'time_format' ), $item['item_date'] ) : '';
				/* translators: 1: date, 2: time */
				$datetime = sprintf( __( '%1$s at %2$s', 'feedzy-rss-feeds' ), $item_date, $item_time );
				return $datetime;
			case 'author':
				if ( isset( $item['item_author'] ) && is_string( $item['item_author'] ) ) {
					return $item['item_author'];
				} elseif ( isset( $item['item_author'] ) && is_object( $item['item_author'] ) ) {
					return $item['item_author']->get_name();
				}
				return '';
			case 'description':
				return isset( $item['item_description'] ) ? $item['item_description'] : '';
			case 'content':
				return isset( $item['item_content'] ) ? $item['item_content'] : '';
			case 'meta':
				return isset( $item['item_meta'] ) ? $item['item_meta'] : '';
			case 'categories':
				return isset( $item['item_categories'] ) ? $item['item_categories'] : '';
			case 'image':
				$settings = apply_filters( 'feedzy_get_settings', array() );
				if ( $settings && ! empty( $settings['general']['default-thumbnail-id'] ) ) {
					$default_img = wp_get_attachment_image_src( $settings['general']['default-thumbnail-id'], 'full' );
					$default_img = ! empty( $default_img ) ? reset( $default_img ) : '';
				} else {
					$default_img = FEEDZY_ABSURL . 'img/feedzy.svg';
				}

				return isset( $item['item_img_path'] ) ? $item['item_img_path'] : $default_img;
			case 'media':
				return isset( $item['item_media']['src'] ) ? $item['item_media']['src'] : '';
			case 'price':
				return isset( $item['item_price'] ) ? $item['item_price'] : '';
			case 'source':
				return isset( $item['item_source'] ) ? $item['item_source'] : '';
			default:
				return '';
		}
	}
}
