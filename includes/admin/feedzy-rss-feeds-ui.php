<?php
/**
 * The UI functionality of the plugin.
 *
 * @link       http://themeisle.com
 * @since      3.0.0
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
 */

/**
 * The UI functionality of the plugin.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
 * @author     Themeisle <friends@themeisle.com>
 */
class Feedzy_Rss_Feeds_Ui {

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
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The loader class.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      Feedzy_Rss_Feeds_Loader    $loader    The loader class of the plugin.
	 */
	private $loader;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      3.0.0
	 * @access     public
	 * @param      string                  $plugin_name    The name of this plugin.
	 * @param      string                  $version        The version of this plugin.
	 * @param      Feedzy_Rss_Feeds_Loader $loader         The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, Feedzy_Rss_Feeds_Loader $loader ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->loader = $loader;

	}

	/**
	 * Initialize the hooks and filters for the tinymce button
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function register_init() {
		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
			if ( 'true' == get_user_option( 'rich_editing' ) ) {

				$this->loader->add_filter( 'mce_external_plugins', $this, 'feedzy_tinymce_plugin', 10, 1 );
				$this->loader->add_filter( 'mce_buttons', $this, 'feedzy_register_mce_button', 10, 1 );

				$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts', 10 );

				$this->loader->run();
			}
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since   3.0.0
	 * @access  public
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
	}

	/**
	 * Load plugin translation for - TinyMCE API
	 *
	 * @since   3.0.0
	 * @access  public
	 * @param   array $arr  The tinymce_lang array.
	 * @return  array
	 */
	public function feedzy_add_tinymce_lang( $arr ) {
		$feedzy_rss_feeds_ui_lang = FEEDZY_ABSPATH . '/includes/admin/feedzy-rss-feeds-ui-lang.php';
		$feedzy_rss_feeds_ui_lang = apply_filters( 'feedzy_rss_feeds_ui_lang_filter', $feedzy_rss_feeds_ui_lang );
		$arr[] = $feedzy_rss_feeds_ui_lang;
		return $arr;
	}

	/**
	 * Load custom js options - TinyMCE API
	 *
	 * @since   3.0.0
	 * @access  public
	 * @param   array $plugin_array  The tinymce plugin array.
	 * @return  array
	 */
	public function feedzy_tinymce_plugin( $plugin_array ) {
		$plugin_array['feedzy_mce_button'] = FEEDZY_ABSURL . 'js/feedzy-rss-feeds-ui-mce.js';
		return $plugin_array;
	}

	/**
	 * Register new button in the editor
	 *
	 * @since   3.0.0
	 * @access  public
	 * @param   array $buttons  The tinymce buttons array.
	 * @return  array
	 */
	public function feedzy_register_mce_button( $buttons ) {
		array_push( $buttons, 'feedzy_mce_button' );
		return $buttons;
	}
}
