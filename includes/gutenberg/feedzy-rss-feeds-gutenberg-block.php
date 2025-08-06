<?php
/**
 * Class for functionalities related to Gutenberg block.
 *
 * Defines the functions that need to be used for Gutenberg block,
 * and REST router.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/guteneberg
 * @author     Themeisle <friends@themeisle.com>
 */
class Feedzy_Rss_Feeds_Gutenberg_Block {

	/**
	 * A reference to an instance of this class.
	 *
	 * @var Feedzy_Rss_Feeds_Gutenberg_Block The one Feedzy_Rss_Feeds_Gutenberg_Block instance.
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
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Feedzy_Rss_Feeds_Gutenberg_Block();
		}
		return self::$instance;
	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {
		$this->version = Feedzy_Rss_Feeds::get_version();
		// Add a filter to load functions when all plugins have been loaded
		add_action( 'enqueue_block_editor_assets', array( $this, 'feedzy_gutenberg_scripts' ) );
		add_action( 'rest_api_init', array( $this, 'feedzy_register_rest_route' ) );
		add_action( 'init', array( $this, 'feedzy_register_block_type' ) );
	}

	/**
	 * Enqueue front end and editor JavaScript and CSS
	 */
	public function feedzy_gutenberg_scripts() {
		// Dependent WordPress core libraries.
		$asset_file = include FEEDZY_ABSPATH . '/build/block/index.asset.php';
		$depends    = array_merge( $asset_file['dependencies'], array( 'wp-i18n', 'wp-blocks', 'wp-components', 'wp-compose', 'wp-editor', 'wp-api', 'lodash', 'wp-hooks', 'jquery-ui-autocomplete' ) );

		// Remove "wp-editor" script for widget block.
		if ( wp_script_is( 'wp-edit-widgets' ) && wp_use_widgets_block_editor() ) {
			$index = array_search( 'wp-editor', $depends, true );
			if ( false !== $index ) {
				unset( $depends[ $index ] );
			}
		}

		// Enqueue the bundled block JS file
		wp_enqueue_script( 'feedzy-gutenberg-block-js', FEEDZY_ABSURL . 'build/block/index.js', $depends, $asset_file['version'], true );

		wp_set_script_translations( 'feedzy-gutenberg-block-js', 'feedzy-rss-feeds' );

		// Pass in REST URL
		wp_localize_script(
			'feedzy-gutenberg-block-js',
			'feedzyjs',
			array(
				'imagepath'             => esc_url( FEEDZY_ABSURL . 'img/' ),
				'isPro'                 => feedzy_is_pro(),
				'upsellLinkBlockEditor' => esc_url( tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'keywordsfilter', 'blockeditor' ) ) ),
				'nonce'                 => wp_create_nonce( FEEDZY_BASEFILE ),
				'url'                   => admin_url( 'admin-ajax.php' ),
			)
		);

		// Enqueue editor block styles
		wp_enqueue_style( 'feedzy-block-css', FEEDZY_ABSURL . 'css/feedzy-rss-feeds.css', array(), $this->version );
		wp_enqueue_style( 'feedzy-gutenberg-block-css', FEEDZY_ABSURL . 'build/block/style-index.css', array(), $this->version );

		if ( ! defined( 'TE2E_TESTING' ) ) {
			add_filter( 'themeisle_sdk_enable_telemetry', '__return_true' );
		}
	}

	/**
	 * Hook server side rendering into render callback
	 */
	public function feedzy_register_block_type() {
		register_block_type(
			'feedzy-rss-feeds/feedzy-block',
			array(
				'render_callback' => array( $this, 'feedzy_gutenberg_block_callback' ),
				'attributes'      => array(
					'feeds'           => array(
						'type' => 'string',
					),
					'max'             => array(
						'type'    => 'number',
						'default' => '5',
					),
					'offset'          => array(
						'type'    => 'number',
						'default' => '0',
					),
					'feed_title'      => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'refresh'         => array(
						'type'    => 'string',
						'default' => '12_hours',
					),
					'sort'            => array(
						'type'    => 'string',
						'default' => 'default',
					),
					'target'          => array(
						'type'    => 'string',
						'default' => '_blank',
					),
					'title'           => array(
						'type' => 'number',
					),
					'meta'            => array(
						'type' => 'boolean',
					),
					'lazy'            => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'metafields'      => array(
						'type' => 'string',
					),
					'multiple_meta'   => array(
						'type' => 'string',
					),
					'summary'         => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'summarylength'   => array(
						'type' => 'number',
					),
					'keywords_title'  => array(
						'type' => 'string',
					),
					'keywords_inc_on' => array(
						'type'    => 'string',
						'default' => 'title',
					),
					'keywords_ban'    => array(
						'type' => 'string',
					),
					'keywords_exc_on' => array(
						'type'    => 'string',
						'default' => 'title',
					),
					'thumb'           => array(
						'type'    => 'string',
						'default' => 'auto',
					),
					'default'         => array(
						'type' => 'object',
					),
					'size'            => array(
						'type'    => 'number',
						'default' => 150,
					),
					'aspectRatio'     => array(
						'type'    => 'string',
						'default' => '1',
					),
					'price'           => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'referral_url'    => array(
						'type' => 'string',
					),
					'columns'         => array(
						'type'    => 'number',
						'default' => 1,
					),
					'template'        => array(
						'type'    => 'string',
						'default' => 'default',
					),
					'from_datetime'   => array(
						'type' => 'string',
					),
					'to_datetime'     => array(
						'type' => 'string',
					),
					'itemTitle'       => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'disableStyle'    => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'follow'          => array(
						'type'    => 'string',
						'default' => 'no',
					),
					'error_empty'     => array(
						'type'    => 'string',
						'default' => '',
					),
					'className'       => array(
						'type'    => 'string',
						'default' => '',
					),
					'_dryrun_'        => array(
						'type'    => 'string',
						'default' => 'no',
					),
					'_dry_run_tags_'  => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);
	}

	/**
	 * Feedzy Gutenberg Block Callback Function
	 * 
	 * @param array<string, mixed> $attr THe block attributes.
	 * 
	 * @return string The block html.
	 */
	public function feedzy_gutenberg_block_callback( $attr ) {
		if ( is_admin() ) {
			$attr['gutenberg'] = true;
		}
		$attr['default'] = ( ! empty( $attr['default'] ) ? $attr['default']['url'] : '' );
		if ( ! empty( $attr['feed_title'] ) ) {
			$attr['feed_title'] = 'yes';
		}
		if ( ! empty( $attr['meta'] ) ) {
			$attr['meta'] = 'yes';
		}
		if ( ! empty( $attr['metafields'] ) ) {
			$attr['meta'] = $attr['metafields'];
		}
		if ( ! empty( $attr['multiple_meta'] ) ) {
			$attr['multiple_meta'] = $attr['multiple_meta'];
		}
		if ( ! empty( $attr['summary'] ) ) {
			$attr['summary'] = 'yes';
		}
		if ( ! empty( $attr['price'] ) ) {
			$attr['price'] = 'yes';
		}
		if ( ! empty( $attr['sort'] ) && 'default' === $attr['sort'] ) {
			unset( $attr['sort'] );
		}
		if ( empty( $attr['itemTitle'] ) ) {
			$attr['title'] = 0;
		}
		if ( ! empty( $attr['disableStyle'] ) ) {
			$attr['disable_default_style'] = 'yes';
		}
		$params = wp_parse_args( $attr );
		return feedzy_rss( $params );
	}

	/**
	 * Register Rest Route for Feedzy
	 */
	public function feedzy_register_rest_route() {
		register_rest_route(
			'feedzy/v1',
			'/feed/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'feedzy_rest_route' ),
				'permission_callback' => function () {
					return is_user_logged_in();
				},
				'args'                => array(
					'url'      => array(
						'sanitize_callback' => array( $this, 'feedzy_sanitize_feeds' ),
					),
					'category' => array(
						'sanitize_callback' => array( $this, 'feedzy_sanitize_categories' ),
					),
				),
			)
		);
	}

	/**
	 * Output Feed JSON
	 * 
	 * @param array $data The feeds data.
	 * 
	 * @return array The return output.
	 */
	public function feedzy_rest_route( $data ) {

		$feed = $data;
		if ( isset( $data['feeds'] ) ) {
			$feed_category = $this->feedzy_sanitize_categories( $data['feeds'] );
			if ( $feed_category ) {
				$data['url'] = $feed_category;
			}
		}
		if ( ! empty( $data['url'] ) ) {
			$feed = $data['url'];
		} elseif ( ! empty( $data['category'] ) ) {
			$feed = $data['category'];
		}

		$url = '';
		if ( ! $feed instanceof \WP_REST_Request ) {
			$url = $feed;
		}

		$meta_args = array(
			'date_format' => get_option( 'date_format' ),
			'time_format' => get_option( 'time_format' ),
		);

		$instance = Feedzy_Rss_Feeds::instance();
		$admin    = $instance->get_admin();
		$feed     = $admin->fetch_feed( $url, '12_hours', array( '' ) );
		$feedy    = array();

		if ( ! $feed->init() ) {
			$feedy['error'] = __( 'Invalid Feed URL', 'feedzy-rss-feeds' );
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
			return $feedy;
		}

		if ( ( ! $feed->get_title() ) && ( ! $feed->get_title() ) && ( ! $feed->get_title() ) ) {
			$feedy['channel'] = null;
		} else {
			$feedy['channel'] = array(
				'title'       => ( ( $feed->get_title() ) ? $feed->get_title() : null ),
				'description' => ( ( $feed->get_description() ) ? $feed->get_description() : null ),
				'permalink'   => ( ( $feed->get_permalink() ) ? $feed->get_permalink() : null ),
			);
		}

		$feedy['items'] = array();
		$items          = $feed->get_items();
		$is_multiple    = ! empty( $feed->multifeed_url ) && is_array( $feed->multifeed_url );
		foreach ( $items as $item ) {
			$item_attrs = apply_filters( 'feedzy_item_filter', array(), $item );

			array_push(
				$feedy['items'],
				array(
					'title'       => ( ( $item->get_title() ) ? $item->get_title() : null ),
					'link'        => ( ( $item->get_permalink() ) ? $item->get_permalink() : null ),
					'creator'     => ( ( $item->get_author() ) ? $item->get_author()->get_name() : null ),
					'source'      => $is_multiple && $item->get_feed()->get_title() ? $item->get_feed()->get_title() : '',
					'pubDate'     => ( ( $item->get_date() ) ? $item->get_date( 'U' ) : null ),
					'date'        => ( ( $item->get_date() ) ? date_i18n( $meta_args['date_format'], $item->get_date( 'U' ) ) : null ),
					'time'        => ( ( $item->get_date() ) ? date_i18n( $meta_args['time_format'], $item->get_date( 'U' ) ) : null ),
					'description' => isset( $item_attrs['item_description'] ) ? $item_attrs['item_description'] : ( $item->get_description() ? $item->get_description() : null ),
					'thumbnail'   => $admin->feedzy_retrieve_image( $item ),
					'default_img' => apply_filters( 'feedzy_default_image', '', $url ),
					'price'       => isset( $item_attrs['item_price'] ) ? $item_attrs['item_price'] : null,
					'media'       => isset( $item_attrs['item_media'] ) ? $item_attrs['item_media'] : null,
					'categories'  => isset( $item_attrs['item_categories'] ) ? $item_attrs['item_categories'] : null,
				)
			);
		}

		// manually delete the transient so that correct cache time can be used.
		if ( ! defined( 'TI_CYPRESS_TESTING' ) ) {
			$url = md5( is_array( $url ) ? implode( ', ', $url ) : $url );
			delete_transient( 'feed_' . md5( $url ) );
		}

		header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$output = $feedy;
		return $output;
	}

	/**
	 * Sanitize Rest API Return
	 * 
	 * @param array $input The feeds.
	 * 
	 * @return string|array The sanitized feeds.
	 */
	public function feedzy_sanitize_feeds( $input ) {
		if ( count( $input ) === 1 ) {
			$feed = esc_url( $input[0] );
			return $feed;
		} else {
			$feeds = array();
			foreach ( $input as $item ) {
				$feeds[] = esc_url( $item );
			}
			return $feeds;
		}
	}

	/**
	 * Sanitize Rest API Return
	 * 
	 * @param string $input The page path.
	 * 
	 * @return array|string The Feedzy categories.
	 */
	public function feedzy_sanitize_categories( $input ) {
		$post = get_page_by_path( $input, OBJECT, 'feedzy_categories' );
		if ( null !== $post ) {
			$id    = $post->ID;
			$value = get_post_meta( $id, 'feedzy_category_feed', true );
			$value = trim( $value );
			$value = explode( ',', $value );
			if ( count( $value ) === 1 ) {
				$value = esc_url( $value[0] );
				return $value;
			} else {
				return $value;
			}
		}
		return array();
	}
}
