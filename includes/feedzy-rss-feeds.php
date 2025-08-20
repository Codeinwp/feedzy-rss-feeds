<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://themeisle.com
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
			self::$instance = new Feedzy_Rss_Feeds();
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
		self::$version     = '5.1.0';
		self::$instance->load_dependencies();
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
	 * @return    string    The name of the plugin.
	 * @since     3.0.0
	 * @access    public
	 */
	public static function get_plugin_name() {
		return self::$plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     3.0.0
	 * @access    public
	 */
	public static function get_version() {
		return self::$version;
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
		self::$instance->loader->add_action( 'admin_init', $plugin_ui, 'register_init' );

		self::$instance->loader->add_action( 'wp_head', $plugin_ui, 'add_feedzy_global_style', 10, 1 );
		self::$instance->loader->add_action( 'admin_init', self::$instance->admin, 'register_admin_capabilities' );
		self::$instance->loader->add_action( 'init', self::$instance->admin, 'register_post_type' );
		self::$instance->loader->add_action( 'admin_footer', self::$instance->admin, 'add_modals' );
		self::$instance->loader->add_action( 'save_post', self::$instance->admin, 'save_feedzy_post_type_meta', 1, 2 );
		self::$instance->loader->add_action( 'feedzy_pre_http_setup', self::$instance->admin, 'pre_http_setup', 10, 1 );
		self::$instance->loader->add_action( 'feedzy_post_http_teardown', self::$instance->admin, 'post_http_teardown', 10, 1 );
		self::$instance->loader->add_action( 'admin_init', self::$instance->admin, 'admin_init', 10, 1 );
		self::$instance->loader->add_action( 'manage_feedzy_categories_posts_custom_column', self::$instance->admin, 'manage_feedzy_category_columns', 10, 2 );
		self::$instance->loader->add_action( 'admin_menu', self::$instance->admin, 'feedzy_menu_pages', 8 );
		self::$instance->loader->add_action( 'admin_menu', self::$instance->admin, 'rss_to_social_menu', 20 );
		self::$instance->loader->add_action( 'wp_ajax_get_tinymce_form', self::$instance->admin, 'get_tinymce_form' );
		self::$instance->loader->add_action( 'wp_enqueue_scripts', self::$instance->admin, 'enqueue_styles' );
		self::$instance->loader->add_action( 'admin_enqueue_scripts', self::$instance->admin, 'enqueue_styles_admin', 99 );
		self::$instance->loader->add_action( 'wp_ajax_feedzy_categories', self::$instance->admin, 'ajax' );
		self::$instance->loader->add_action( 'admin_action_feedzy_dismiss_wizard', self::$instance->admin, 'feedzy_dismiss_wizard' );

		self::$instance->loader->add_filter( 'manage_feedzy_categories_posts_columns', self::$instance->admin, 'feedzy_category_columns' );
		self::$instance->loader->add_filter( 'plugin_row_meta', self::$instance->admin, 'feedzy_filter_plugin_row_meta', 10, 2 );
		self::$instance->loader->add_filter( 'feedzy_default_image', self::$instance->admin, 'feedzy_define_default_image' );
		self::$instance->loader->add_filter( 'feedzy_default_error', self::$instance->admin, 'feedzy_default_error_notice', 9, 3 );
		self::$instance->loader->add_filter( 'feedzy_item_attributes', self::$instance->admin, 'feedzy_add_item_padding', 10, 2 );
		self::$instance->loader->add_filter( 'feedzy_item_attributes', self::$instance->admin, 'feedzy_classes_item', 99, 5 );
		self::$instance->loader->add_filter( 'feedzy_register_options', self::$instance->admin, 'register_options' );
		self::$instance->loader->add_filter( 'feedzy_summary_input', self::$instance->admin, 'feedzy_summary_input_filter', 9, 3 );
		self::$instance->loader->add_filter( 'feedzy_get_feed_array', self::$instance->admin, 'get_feed_array', 10, 5 );
		self::$instance->loader->add_filter( 'feedzy_process_feed_source', self::$instance->admin, 'process_feed_source', 10, 1 );
		self::$instance->loader->add_filter( 'feedzy_get_feed_url', self::$instance->admin, 'get_feed_url', 10, 1 );
		self::$instance->loader->add_filter( 'feedzy_get_settings', self::$instance->admin, 'get_settings', 10, 1 );
		self::$instance->loader->add_filter( 'feedzy_rss_feeds_logger_data', self::$instance->admin, 'get_usage_data', 10 );
		self::$instance->loader->add_filter( 'feedzy_check_source_validity', self::$instance->admin, 'check_source_validity', 10, 4 );
		self::$instance->loader->add_filter( 'feedzy_get_source_validity_error', self::$instance->admin, 'get_source_validity_error', 10, 3 );
		self::$instance->loader->add_filter( 'post_row_actions', self::$instance->admin, 'add_feedzy_category_actions', 10, 2 );
		self::$instance->loader->add_filter( 'admin_footer', self::$instance->admin, 'handle_upgrade_submenu' );
		self::$instance->loader->add_action( 'current_screen', self::$instance->admin, 'handle_legacy' );
		self::$instance->loader->add_action( 'init', self::$instance->admin, 'register_settings' );
		self::$instance->loader->add_action( 'wp_ajax_feedzy_validate_feed', self::$instance->admin, 'validate_feed' );
		self::$instance->loader->add_action( 'wp_ajax_feedzy_dashboard_subscribe', self::$instance->admin, 'feedzy_dashboard_subscribe' );
		self::$instance->loader->add_filter( 'feedzy_internal_cron_schedule_slugs', self::$instance->admin, 'internal_cron_schedule_slugs', 10, 1 );
		self::$instance->loader->add_filter( 'cron_schedules', self::$instance->admin, 'append_custom_cron_schedules' );

		// do not load this with the loader as this will need a corresponding remove_filter also.
		add_filter( 'update_post_metadata', array( self::$instance->admin, 'validate_category_feeds' ), 10, 5 );
		add_filter( 'add_post_metadata', array( self::$instance->admin, 'validate_category_feeds' ), 10, 5 );

		add_shortcode( 'feedzy-rss', array( self::$instance->admin, 'feedzy_rss' ) );

		add_action(
			'widgets_init',
			function () {
				register_widget( 'feedzy_wp_widget' );
			}
		);

		self::$instance->loader->add_action( 'rest_api_init', self::$instance->admin, 'rest_route', 10 );

		// Wizard screen setup.
		self::$instance->loader->add_action( 'admin_body_class', self::$instance->admin, 'add_wizard_classes', 20 );
		self::$instance->loader->add_action( 'wp_ajax_feedzy_wizard_step_process', self::$instance->admin, 'feedzy_wizard_step_process' );

		// do not include import feature if this is a pro version that does not know of this new support.
		if ( ! feedzy_is_pro( false ) || has_filter( 'feedzy_free_has_import' ) ) {

			$plugin_import = new Feedzy_Rss_Feeds_Import( self::$instance->get_plugin_name(), self::$instance->get_version() );
			self::$instance->loader->add_action( 'feedzy_upsell_class', $plugin_import, 'upsell_class', 10, 1 );
			self::$instance->loader->add_action( 'feedzy_upsell_content', $plugin_import, 'upsell_content', 10, 3 );
			self::$instance->loader->add_action( 'admin_enqueue_scripts', $plugin_import, 'enqueue_styles' );
			self::$instance->loader->add_action( 'init', $plugin_import, 'register_import_post_type', 9, 1 );
			self::$instance->loader->add_action( 'add_meta_boxes', $plugin_import, 'add_feedzy_import_metaboxes', 1, 2 );
			self::$instance->loader->add_action( 'feedzy_cron', $plugin_import, 'run_cron', 10, 2 );
			self::$instance->loader->add_action( 'save_post_feedzy_imports', $plugin_import, 'save_feedzy_import_feed_meta', 1, 2 );
			self::$instance->loader->add_action( 'wp_ajax_feedzy', $plugin_import, 'ajax' );
			self::$instance->loader->add_action( 'manage_feedzy_imports_posts_custom_column', $plugin_import, 'manage_feedzy_import_columns', 10, 2 );
			self::$instance->loader->add_action( 'wp', $plugin_import, 'wp' );
			self::$instance->loader->add_filter( 'pre_get_posts', $plugin_import, 'pre_get_posts', 10, 1 );

			self::$instance->loader->add_filter( 'feedzy_items_limit', $plugin_import, 'items_limit', 10, 2 );
			self::$instance->loader->add_filter( 'feedzy_settings_tabs', $plugin_import, 'settings_tabs', 10, 1 );
			self::$instance->loader->add_filter( 'feedzy_integration_tabs', $plugin_import, 'integration_tabs', 10, 1 );
			self::$instance->loader->add_filter( 'redirect_post_location', $plugin_import, 'redirect_post_location', 10, 2 );
			self::$instance->loader->add_filter( 'manage_feedzy_imports_posts_columns', $plugin_import, 'feedzy_import_columns' );
			self::$instance->loader->add_action( 'admin_notices', $plugin_import, 'admin_notices' );
			self::$instance->loader->add_action( 'init', $plugin_import, 'add_cron' );
			self::$instance->loader->add_filter( 'feedzy_item_filter', $plugin_import, 'add_data_to_item', 10, 5 );
			self::$instance->loader->add_filter( 'feedzy_display_tab_settings', $plugin_import, 'display_tab_settings', 10, 2 );
			self::$instance->loader->add_filter( 'feedzy_save_tab_settings', $plugin_import, 'save_tab_settings', 10, 2 );
			self::$instance->loader->add_filter( 'feedzy_render_magic_tags', $plugin_import, 'render_magic_tags', 10, 3 );
			self::$instance->loader->add_filter( 'feedzy_magic_tags_title', $plugin_import, 'magic_tags_title' );
			self::$instance->loader->add_filter( 'feedzy_magic_tags_date', $plugin_import, 'magic_tags_date' );
			self::$instance->loader->add_filter( 'feedzy_magic_tags_content', $plugin_import, 'magic_tags_content' );
			self::$instance->loader->add_filter( 'feedzy_magic_tags_image', $plugin_import, 'magic_tags_image' );
			self::$instance->loader->add_filter( 'feedzy_retrieve_categories', $plugin_import, 'retrieve_categories', 10, 2 );
			self::$instance->loader->add_filter( 'feedzy_is_license_of_type', $plugin_import, 'feedzy_is_license_of_type', 10, 2 );
			self::$instance->loader->add_filter( 'post_row_actions', $plugin_import, 'add_import_actions', 10, 2 );
			self::$instance->loader->add_filter( 'wp_kses_allowed_html', $plugin_import, 'feedzy_wp_kses_allowed_html' );
			self::$instance->loader->add_filter( 'feedzy_magic_tags_post_excerpt', $plugin_import, 'magic_tags_post_excerpt', 11 );
			self::$instance->loader->add_action( 'admin_action_feedzy_clone_import_job', $plugin_import, 'feedzy_clone_import_job' );
			self::$instance->loader->add_action( 'admin_notices', $plugin_import, 'feedzy_import_clone_success_notice' );
			self::$instance->loader->add_action( 'load-edit.php', $plugin_import, 'load_edit_screen' );
			// Remove elementor feature.
			self::$instance->loader->add_action( 'elementor/experiments/feature-registered', self::$instance->admin, 'feedzy_remove_elementor_feature', 10, 2 );
			// Remove widget.
			self::$instance->loader->add_filter( 'elementor/widgets/black_list', self::$instance->admin, 'feedzy_remove_elementor_widgets' );
			// Register elementor widget.
			$plugin_elementor_widget = new Feedzy_Rss_Feeds_Elementor();
			$this->loader->add_action( 'elementor/widgets/register', $plugin_elementor_widget, 'feedzy_elementor_widgets_registered' );
			$this->loader->add_action( 'elementor/controls/register', $plugin_elementor_widget, 'feedzy_elementor_register_datetime_local_control' );
			$this->loader->add_action( 'elementor/frontend/before_enqueue_styles', $plugin_elementor_widget, 'feedzy_elementor_before_enqueue_scripts' );

			$plugin_conditions = new Feedzy_Rss_Feeds_Conditions();
			$this->loader->add_action( 'feedzy_filter_conditions_migration', $plugin_conditions, 'migrate_conditions' );
			$this->loader->add_action( 'feedzy_filter_conditions_attribute', $plugin_conditions, 'convert_filter_string_to_json' );
			$this->loader->add_action( 'feedzy_item_keyword', $plugin_conditions, 'evaluate_conditions', 10, 5 );
		}

		$plugin_slug = FEEDZY_DIRNAME . '/' . basename( FEEDZY_BASEFILE );
		$this->loader->add_filter( "plugin_action_links_$plugin_slug", self::$instance->admin, 'plugin_actions', 10, 2 );

		add_action(
			'feedzy_log',
			function ( $log_data ) {
				$level   = isset( $log_data['level'] ) ? $log_data['level'] : 'debug';
				$message = isset( $log_data['message'] ) ? $log_data['message'] : '';
				$context = isset( $log_data['context'] ) ? $log_data['context'] : array();

				if ( ! isset( Feedzy_Rss_Feeds_Log::PRIORITIES_MAPPING[ $level ] ) ) {
					return;
				}
			
				Feedzy_Rss_Feeds_Log::log( Feedzy_Rss_Feeds_Log::PRIORITIES_MAPPING[ $level ], $message, $context );
			} 
		);
		( new Feedzy_Rss_Feeds_Task_Manager() )->register_actions();

		add_filter(
			'feedzy_disable_db_cache',
			function ( $a, $b ) {
				return true;
			}, 
			10,
			2 
		);

		if ( ! defined( 'TI_UNIT_TESTING' ) ) {
			add_action(
				'plugins_loaded',
				function () {
					if ( function_exists( 'register_block_type' ) ) {
						Feedzy_Rss_Feeds_Gutenberg_Block::get_instance();
						Feedzy_Rss_Feeds_Loop_Block::get_instance();
					}
				}
			);
		}
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
	 * @return    Feedzy_Rss_Feeds_Loader    Orchestrates the hooks of the plugin.
	 * @since     3.0.0
	 * @access    public
	 */
	public function get_loader() {
		return self::$instance->loader;
	}

	/**
	 * The reference to the class that run the admin with the plugin.
	 *
	 * @return    Feedzy_Rss_Feeds_Admin    Orchestrates the admin of the plugin.
	 * @since     3.0.0
	 * @access    public
	 */
	public function get_admin() {
		return self::$instance->admin;
	}
}
