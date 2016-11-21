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

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../js/feedzy-rss-feeds-admin.js', array( 'jquery' ), $this->version, false );
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

}
