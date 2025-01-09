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
 */
class Feedzy_Rss_Feeds_Loop_Block {

	/**
	 * A reference to an instance of this class.
	 *
	 * @var Feedzy_Rss_Feeds_Loop_Block The one Feedzy_Rss_Feeds_Loop_Block instance.
	 */
	private static $instance;

	/**
	 * Instance of Feedzy_Rss_Feeds_Admin class.
	 *
	 * @var Feedzy_Rss_Feeds_Admin $admin The Feedzy_Rss_Feeds_Admin instance.
	 */
	private $admin;

	/**
	 * Feedzy RSS Feeds plugin version.
	 *
	 * @var string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Returns an instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Feedzy_Rss_Feeds_Loop_Block();
		}
		return self::$instance;
	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {
		$this->version = Feedzy_Rss_Feeds::get_version();
		$this->admin   = Feedzy_Rss_Feeds::instance()->get_admin();
		add_action( 'init', array( $this, 'register_block' ) );
		add_filter( 'feedzy_loop_item', array( $this, 'apply_magic_tags' ), 10, 2 );
	}

	/**
	 * Register Block
	 */
	public function register_block() {
		$metadata_file = trailingslashit( FEEDZY_ABSPATH ) . '/build/loop/block.json';
		register_block_type_from_metadata(
			$metadata_file,
			array(
				'render_callback' => array( $this, 'render_callback' ),
			)
		);

		wp_set_script_translations( 'feedzy-rss-feeds-loop-editor-script', 'feedzy-rss-feeds' );

		// Pass in REST URL
		wp_localize_script(
			'feedzy-rss-feeds-loop-editor-script',
			'feedzyData',
			array(
				'imagepath'    => esc_url( FEEDZY_ABSURL . 'img/' ),
				'defaultImage' => esc_url( FEEDZY_ABSURL . 'img/feedzy.svg' ),
				'isPro'        => feedzy_is_pro(),
			)
		);

		wp_localize_script(
			'feedzy-rss-feeds-loop-editor-script',
			'feedzyConditionsData',
			apply_filters(
				'feedzy_conditions_data',
				array(
					'isPro'            => feedzy_is_pro(),
					'operators'        => Feedzy_Rss_Feeds_Conditions::get_operators(),
				)
			)
		);
	}

	/**
	 * Render Callback
	 *
	 * @param array $attributes The block attributes.
	 * @param string $content The block content.
	 * @return string The block content.
	 */
	public function render_callback( $attributes, $content ) {
		$content    = empty( $content ) ? ( $attributes['innerBlocksContent'] ?? '' ) : $content;
		$is_preview = isset( $attributes['innerBlocksContent'] ) && ! empty( $attributes['innerBlocksContent'] );
		$feed_urls      = array();

		if ( isset( $attributes['feed']['type'] ) && 'group' === $attributes['feed']['type'] && isset( $attributes['feed']['source'] ) && is_numeric( $attributes['feed']['source'] ) ) {
			$group = $attributes['feed']['source'];
			$value = get_post_meta( $group, 'feedzy_category_feed', true );
			$value = trim( $value );
			$feed_urls = !empty( $value ) ? explode( ',', $value ) : array();
		}

		if ( isset( $attributes['feed']['type'] ) && 'url' === $attributes['feed']['type'] && isset( $attributes['feed']['source'] ) && is_array( $attributes['feed']['source'] ) ) {
			$feed_urls = $attributes['feed']['source'];
		}

		if ( empty( $feed_urls ) ) {
			return '<div>' . esc_html__( 'No feeds to display', 'feedzy-rss-feeds' ) . '</div>';
		}

		$column_count = isset($attributes['layout']) && isset($attributes['layout']['columnCount']) && !empty($attributes['layout']['columnCount']) ? $attributes['layout']['columnCount'] : 1;

		$default_query = array(
			'max'     => 5,
			'sort'    => 'default',
			'refresh' => '12_hours',
		);

		$query   = isset( $attributes['query'] ) ? wp_parse_args( $attributes['query'], $default_query ) : $default_query;
		$filters = isset( $attributes['conditions'] ) ? $attributes['conditions'] : array();

		$options = array(
			'feeds'         => implode( ',', $feed_urls ),
			'max'           => $query['max'],
			'sort'		    => $query['sort'],
			'offset'        => 0,
			'target'        => '_blank',
			'keywords_ban'  => '',
			'columns'       => '1',
			'thumb'         => 'auto',
			'default'       => '',
			'title'         => '',
			'meta'          => 'yes',
			'multiple_meta' => 'no',
			'summary'       => 'yes',
			'summarylength' => '',
			'filters'       => wp_json_encode( $filters ),
		);

		$sizes = array(
			'width'  => 300,
			'height' => 300,
		);

		$feed = $this->admin->fetch_feed( $feed_urls, $query['refresh'], $options );

		if ( isset( $feed->error ) && ! empty( $feed->error ) ) {
			return '<div>' . esc_html__( 'An error occurred while fetching the feed.', 'feedzy-rss-feeds' ) . '</div>';
		}

		$feed_items = apply_filters( 'feedzy_get_feed_array', array(), $options, $feed, implode( ',', $feed_urls ), $sizes );

		if ( empty( $feed_items ) ) {
			return '<div>' . esc_html__( 'No items to display.', 'feedzy-rss-feeds' ) . '</div>';
		}

		$loop       = '';

		foreach ($feed_items as $key => $item) {
			$loop .= apply_filters( 'feedzy_loop_item', $content, $item );
		}

		return sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes = get_block_wrapper_attributes( array(
				'class' => 'feedzy-loop-columns-' . $column_count,
			) ),
			$loop
		);
	}

	/**
	 * Magic Tags Replacement.
	 *
	 * @param string $content The content.
	 *
	 * @return string The content.
	 */
	public function apply_magic_tags( $content, $item ) {
		$pattern = '/\{\{feedzy_([^}]+)\}\}/';
		$content = str_replace(
			array(
				FEEDZY_ABSURL . 'img/feedzy.svg',
				'http://{{feedzy_url}}'
			),
			array(
				'{{feedzy_image}}',
				'{{feedzy_url}}'
			),
			$content
		);

		return preg_replace_callback( $pattern, function( $matches ) use ( $item ) {
			return isset( $matches[1] ) ? $this->get_value( $matches[1], $item ) : '';
		}, $content );
	}

	/**
	 * Get Dynamic Value.
	 *
	 * @param string $key The key.
	 * @param array $item Feed item.
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
