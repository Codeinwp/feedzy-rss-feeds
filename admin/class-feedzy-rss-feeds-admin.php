<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://themeisle.com
 * @since      3.0.0
 *
 * @package    Feedzy_Rss_Feeds
 * @subpackage Feedzy_Rss_Feeds/admin
 */
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Feedzy_Rss_Feeds
 * @subpackage Feedzy_Rss_Feeds/admin
 * @author     Themeisle <friends@themeisle.com>
 */
require_once( 'class-abstract-feedzy-rss-feeds-admin.php' );

/**
 * Class Feedzy_Rss_Feeds_Admin
 */
class Feedzy_Rss_Feeds_Admin extends Feedzy_Rss_Feeds_Abstract {

	/**
	 * The ID of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string    $version    The current version of this plugin.
	 */
	protected $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    3.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    3.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Feedzy_Rss_Feeds_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Feedzy_Rss_Feeds_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../css/feedzy-rss-feeds.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    3.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Feedzy_Rss_Feeds_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Feedzy_Rss_Feeds_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		global $typenow;

		if ( post_type_supports( $typenow, 'editor' ) ) {
			//wp_enqueue_style( $this->plugin_name . '-media', FEEDZY_ABSURL . 'css/media.css', array( 'media-views' ), $this->version );

            //wp_enqueue_script( $this->plugin_name .'-library', FEEDZY_ABSURL . 'js/library.js', array( 'jquery', $this->plugin_name . '-media' ), $this->version, true );

			wp_enqueue_script( $this->plugin_name . '-media-model',      FEEDZY_ABSURL . 'js/media/model.js',      null,                                              $this->version, true );
			wp_enqueue_script( $this->plugin_name . '-media-collection', FEEDZY_ABSURL . 'js/media/collection.js', array( $this->plugin_name . '-media-model' ),      $this->version, true );
			wp_enqueue_script( $this->plugin_name . '-media-controller', FEEDZY_ABSURL . 'js/media/controller.js', array( $this->plugin_name . '-media-collection' ), $this->version, true );
			wp_enqueue_script( $this->plugin_name . '-media-view',       FEEDZY_ABSURL . 'js/media/view.js',       array( $this->plugin_name . '-media-controller' ), $this->version, true );
			wp_enqueue_script( $this->plugin_name . '-media-toolbar',    FEEDZY_ABSURL . 'js/media/toolbar.js',    array( $this->plugin_name . '-media-view' ),       $this->version, true );
			wp_enqueue_script( $this->plugin_name . '-media',            FEEDZY_ABSURL . 'js/media.js',            array( $this->plugin_name . '-media-toolbar' ),    $this->version, true );
		}
	}

	/**
	 * The custom plugin_row_meta function
	 * Adds additional links on the plugins page for this plugin
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param   array  $links  The array having default links for the plugin.
	 * @param   string $file   The name of the plugin file.
	 *
	 * @return  array
	 */
	public function feedzy_filter_plugin_row_meta( $links, $file ) {

		if ( strpos( $file, 'feedzy-rss-feeds.php' ) !== false ) {
			$new_links = array(
				'doc' => '<a href="http://docs.themeisle.com/article/277-feedzy-rss-feeds-hooks" target="_blank" title="' . __( 'Documentation and examples', 'feedzy_rss_translate' ) . '">' . __( 'Documentation and examples', 'feedzy_rss_translate' ) . '</a>',
				'more_plugins' => '<a href="http://themeisle.com/wordpress-plugins/" target="_blank" title="' . __( 'More Plugins', 'feedzy_rss_translate' ) . '">' . __( 'More Plugins', 'feedzy_rss_translate' ) . '</a>',
			);

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}

	/**
	 * Returns associated array of Feedzy templates and localized names.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access private
	 * @return array The associated array of Feedzy templates with localized names.
	 */
	public static function _get_template_names_localized() {
		$templates  = array(
			'all'             => esc_html__( 'No Template', 'feedzy_rss_translate' ),
			'default'         => esc_html__( 'Default', 'feedzy_rss_translate' ),
			'example'         => esc_html__( 'Example', 'feedzy_rss_translate' ),
		);

		$templates  = apply_filters( 'feedzy_rss_feeds_pro_templates', $templates );

		return $templates;
	}

	/**
	 * Extends media view strings with Feedzy Rss Feeds strings.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $strings The array of media view strings.
	 * @return array The extended array of media view strings.
	 */
	public function setup_media_view_strings( $strings ) {
		$strings['feedzy_rss'] = array(
			'actions' => array(
				'get_template'   => 'feedzy-get-action-function',
				'delete_template' => 'feedzy-delete-function',
			),
			'controller' => array(
				'title' => esc_html__( 'Feedzy RSS Feeds', 'feedzy_rss_translate' ),
			),
			'routers' => array(
				'library' => esc_html__( 'Saved Feeds', 'feedzy_rss_translate' ),
				'create'  => esc_html__( 'New Feed', 'feedzy_rss_translate' ),
			),
			'library' => array(
				'filters' => $this->_get_template_names_localized(),
			),
			'nonce'    => wp_create_nonce(),
			'buildurl' => add_query_arg( 'action', 'create-feed-function', admin_url( 'admin-ajax.php' ) ),
		);

		return $strings;
	}
}
