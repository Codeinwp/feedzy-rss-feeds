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
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes
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
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes
 * @author     Themeisle <friends@themeisle.com>
 */
class Feedzy_Rss_Feeds {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected static $plugin_name;
	/**
	 * The current version of the plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected static $version;

	/**
	 * The main instance var.
	 *
	 * @var Feedzy_Rss_Feeds The one Feedzy_Rss_Feeds instance.
	 * @since 3.0.4
	 */
	private static $instance;
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      Feedzy_Rss_Feeds_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;
	/**
	 * The class responsible for all upgrading proceses.
	 *
	 * @since    3.0.3
	 * @access   protected
	 * @var      Feedzy_Rss_Feeds_Upgrader $upgrader Responsible for the upgrading processes.
	 */
	protected $upgrader;
	/**
	 * The class responsible for all admin processes.
	 *
	 * @since    3.0.3
	 * @access   protected
	 * @var      Feedzy_Rss_Feeds_Admin $admin Responsible for the admin processes.
	 */
	protected $admin;

	/**
	 * Init the main singleton instance class.
	 *
	 * @return Feedzy_Rss_Feeds Return the instance class
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Feedzy_Rss_Feeds ) ) {
			self::$instance = new Feedzy_Rss_Feeds;
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    3.0.0
	 * @access   public
	 */
	public function init() {
		self::$plugin_name = 'feedzy-rss-feeds';
		self::$version = '3.1.7';
		self::$instance->load_dependencies();
		self::$instance->set_locale();
		self::$instance->define_admin_hooks();

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
		include_once FEEDZY_ABSPATH . '/includes/feedzy-rss-feeds-feed-tweaks.php';
		self::$instance->loader   = new Feedzy_Rss_Feeds_Loader();
		self::$instance->upgrader = new Feedzy_Rss_Feeds_Upgrader();
		self::$instance->admin    = new Feedzy_Rss_Feeds_Admin( self::$instance->get_plugin_name(), self::$instance->get_version() );

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     3.0.0
	 * @access    public
	 * @return    string    The name of the plugin.
	 */
	public static function get_plugin_name() {
		return self::$plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     3.0.0
	 * @access    public
	 * @return    string    The version number of the plugin.
	 */
	public static function get_version() {
		return self::$version;
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
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		$plugin_i18n = new Feedzy_Rss_Feeds_i18n();
		self::$instance->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    3.0.0
	 * @updated  3.0.12
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_ui = new Feedzy_Rss_Feeds_Ui( self::$instance->get_plugin_name(), self::$instance->get_version(), self::$instance->loader );
		self::$instance->loader->add_action( 'init', $plugin_ui, 'register_init' );
		self::$instance->loader->add_action( 'init', self::$instance->admin, 'register_post_type' );
		self::$instance->loader->add_action( 'save_post', self::$instance->admin, 'save_feedzy_post_type_meta', 1, 2 );

		self::$instance->loader->add_action( 'manage_feedzy_categories_posts_custom_column', self::$instance->admin, 'manage_feedzy_category_columns', 10, 2 );
		self::$instance->loader->add_filter( 'manage_feedzy_categories_posts_columns', self::$instance->admin, 'feedzy_category_columns' );

		self::$instance->loader->add_action( 'admin_menu', self::$instance->admin, 'feedzy_menu_pages' );
		self::$instance->loader->add_filter( 'mce_external_languages', $plugin_ui, 'feedzy_add_tinymce_lang', 10, 1 );
		self::$instance->loader->add_filter( 'plugin_row_meta', self::$instance->admin, 'feedzy_filter_plugin_row_meta', 10, 2 );
		self::$instance->loader->add_filter( 'feedzy_default_image', self::$instance->admin, 'feedzy_define_default_image' );
		self::$instance->loader->add_filter( 'feedzy_default_error', self::$instance->admin, 'feedzy_default_error_notice', 9, 2 );
		self::$instance->loader->add_filter( 'feedzy_item_attributes', self::$instance->admin, 'feedzy_add_item_padding', 10, 2 );
		self::$instance->loader->add_filter( 'feedzy_item_attributes', self::$instance->admin, 'feedzy_classes_item', 99, 5 );
		self::$instance->loader->add_filter( 'feedzy_register_options', self::$instance->admin, 'register_options' );
		self::$instance->loader->add_filter( 'feedzy_summary_input', self::$instance->admin, 'feedzy_summary_input_filter', 9, 3 );
		self::$instance->loader->add_filter( 'feedzy_item_keyword', self::$instance->admin, 'feedzy_feed_item_keywords_title', 9, 4 );
		self::$instance->loader->add_filter( 'feedzy_get_feed_array', self::$instance->admin, 'get_feed_array', 10, 5 );
		self::$instance->loader->add_filter( 'feedzy_process_feed_source', self::$instance->admin, 'process_feed_source', 10, 1 );
		self::$instance->loader->add_filter( 'feedzy_get_feed_url', self::$instance->admin, 'get_feed_url', 10, 1 );
		add_shortcode( 'feedzy-rss', array( self::$instance->admin, 'feedzy_rss' ) );
		self::$instance->loader->add_action( 'wp_ajax_get_tinymce_form', self::$instance->admin, 'get_tinymce_form' );
		self::$instance->loader->add_action( 'wp_enqueue_scripts', self::$instance->admin, 'enqueue_styles' );
		self::$instance->loader->add_action( 'admin_enqueue_scripts', self::$instance->admin, 'enqueue_styles' );
		$plugin_widget = new feedzy_wp_widget();
		self::$instance->loader->add_action( 'widgets_init', $plugin_widget, 'registerWidget', 10 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    3.0.0
	 * @access   public
	 */
	public function run() {
		self::$instance->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     3.0.0
	 * @access    public
	 * @return    Feedzy_Rss_Feeds_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return self::$instance->loader;
	}

	/**
	 * The reference to the class that run the admin with the plugin.
	 *
	 * @since     3.0.0
	 * @access    public
	 * @return    Feedzy_Rss_Feeds_Admin    Orchestrates the admin of the plugin.
	 */
	public function get_admin() {
		return self::$instance->admin;
	}

}
