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
class Feedzy_Rss_Feeds_Feed_Template_Block {

	/**
	 * A reference to an instance of this class.
	 *
	 * @var Feedzy_Rss_Feeds_Feed_Template_Block|null The one Feedzy_Rss_Feeds_Loop_Block instance.
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
	 * @return Feedzy_Rss_Feeds_Feed_Template_Block The instance of the class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Feedzy_Rss_Feeds_Feed_Template_Block();
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
		$metadata_file = trailingslashit( FEEDZY_ABSPATH ) . '/build/template/block.json';
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

		$wrapper_attributes = get_block_wrapper_attributes();

		return sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes,
			wp_kses_post( $feed_item[ $attributes['tag'] ] )
		);
	}
}
