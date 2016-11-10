<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://themeisle.com
 * @since      3.0.0
 *
 * @package    Feedzy_Rss_Feeds
 * @subpackage Feedzy_Rss_Feeds/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      3.0.0
 * @package    Feedzy_Rss_Feeds
 * @subpackage Feedzy_Rss_Feeds/includes
 * @author     Themeisle <friends@themeisle.com>
 */
class Feedzy_Rss_Feeds {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      Feedzy_Rss_Feeds_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    3.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'feedzy-rss-feeds';
		$this->version = '3.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Feedzy_Rss_Feeds_Loader. Orchestrates the hooks of the plugin.
	 * - Feedzy_Rss_Feeds_i18n. Defines internationalization functionality.
	 * - Feedzy_Rss_Feeds_Admin. Defines all hooks for the admin area.
	 * - Feedzy_Rss_Feeds_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    3.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-feedzy-rss-feeds-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-feedzy-rss-feeds-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-feedzy-rss-feeds-admin.php';

		/**
		 * The class responsible for the UI part of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-feedzy-rss-feeds-ui.php';

		/**
		 * The class responsible for the Widget part of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-feedzy-rss-feeds-widget.php';

		$this->loader = new Feedzy_Rss_Feeds_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Feedzy_Rss_Feeds_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    3.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Feedzy_Rss_Feeds_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_ui = new Feedzy_Rss_Feeds_Ui( $this->get_plugin_name(), $this->get_version(), $this->loader );
		$this->loader->add_action( 'init', $plugin_ui, 'register_init' );
		$this->loader->add_filter( 'mce_external_languages', $plugin_ui, 'feedzy_add_tinymce_lang', 10, 1 );

		$plugin_admin = new Feedzy_Rss_Feeds_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin ,'feedzy_filter_plugin_row_meta', 10, 2 );
		$this->loader->add_filter( 'feedzy_default_image', $plugin_admin, 'feedzy_define_default_image' );
		$this->loader->add_filter( 'feedzy_default_error', $plugin_admin ,'feedzy_default_error_notice', 9, 2 );
		$this->loader->add_filter( 'feedzy_item_attributes', $plugin_admin, 'feedzy_add_item_padding', 10, 2 );
		$this->loader->add_filter( 'feedzy_item_attributes', $plugin_admin, 'feedzy_classes_item' );
		$this->loader->add_filter( 'feedzy_summary_input', $plugin_admin, 'feedzy_summary_input_filter', 9, 3 );
		$this->loader->add_filter( 'feedzy_item_keyword', $plugin_admin, 'feedzy_feed_item_keywords_title', 9, 4 );
		$this->loader->add_filter( 'the_excerpt_rss', $plugin_admin, 'feedzy_insert_thumbnail_rss' );
		$this->loader->add_filter( 'the_content_feed', $plugin_admin, 'feedzy_insert_thumbnail_rss' );
		add_shortcode( 'feedzy-rss', array( $plugin_admin, 'feedzy_rss' ) );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$plugin_widget = new Feedzy_Rss_Feeds_Widget( $plugin_admin );
		$this->loader->add_action( 'widgets_init', $plugin_widget, 'registerWidget' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    3.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     3.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     3.0.0
	 * @return    Feedzy_Rss_Feeds_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     3.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
