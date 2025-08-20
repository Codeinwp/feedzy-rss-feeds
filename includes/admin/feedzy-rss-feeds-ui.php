<?php
/**
 * The UI functionality of the plugin.
 *
 * @link       https://themeisle.com
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
		$this->version     = $version;
		$this->loader      = $loader;
	}

	/**
	 * Checks if this is being called inside the Gutenberg block editor.
	 *
	 * @since   3.3.2
	 * @access  private
	 */
	private function is_block_editor() {
		require_once ABSPATH . 'wp-admin/includes/screen.php';
		global $current_screen;
		$current_screen = get_current_screen(); //phpcs:ignore
		return method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor();
	}

	/**
	 * Initialize the hooks and filters for the tinymce button
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function register_init() {
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( feedzy_current_user_can() && 'true' == get_user_option( 'rich_editing' ) ) {
			$this->loader->add_filter( 'mce_external_plugins', $this, 'feedzy_tinymce_plugin', 10, 1 );
			$this->loader->add_filter( 'mce_buttons', $this, 'feedzy_register_mce_button', 10, 1 );
			$this->loader->add_filter( 'mce_external_languages', $this, 'feedzy_add_tinymce_lang', 10, 1 );
			$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts', 10 );
			$this->loader->add_filter( 'tiny_mce_before_init', $this, 'get_strings_for_block', 10, 1 );
			$this->loader->add_action( 'in_admin_header', $this, 'feedzy_post_import_admin_header', 10 );
			$this->loader->run();
		}
	}

	/**
	 * Add the strings required for the TinyMCE buttons for the classic block (not the classic editor).
	 *
	 * @param array<string, mixed> $settings The block settings.
	 * 
	 * @return array<string, mixed> The strings.
	 */
	public function get_strings_for_block( $settings ) {
		$feedzy_lang_class = new Feedzy_Rss_Feeds_Ui_Lang();
		$strings           = $feedzy_lang_class->get_strings();
		$array             = array( 'feedzy_tinymce_plugin' => wp_json_encode( $strings ) );
		return array_merge( $settings, $array );
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
		$arr[]                    = $feedzy_rss_feeds_ui_lang;
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

	/**
	 * Add global style.
	 */
	public function add_feedzy_global_style() {
		?>
<style type="text/css">
.feedzy-rss-link-icon:after {
	content: url("<?php echo esc_url( FEEDZY_ABSURL . 'img/external-link.png' ); ?>");
	margin-left: 3px;
}
</style>
		<?php
	}

	/**
	 * Feedzy import post screen header.
	 */
	public function feedzy_post_import_admin_header() {
		global $pagenow;
		if ( 'edit.php' === $pagenow ) {
			return;
		}
		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( $current_screen && 'feedzy_imports' === $current_screen->post_type ) {
			?>
			<div class="feedzy-header">
				<div class="feedzy-container">
					<div class="page-title h1"><?php echo isset( $_GET['action'] ) && 'edit' === $_GET['action'] ? esc_html__( 'Edit Import', 'feedzy-rss-feeds' ) : esc_html__( 'New Import', 'feedzy-rss-feeds' ); // phpcs:ignore WordPress.Security.NonceVerification ?></div>
					<div class="feedzy-logo">
						<div class="feedzy-version"><?php echo esc_html( Feedzy_Rss_Feeds::get_version() ); ?></div>
						<div class="feedzy-logo-icon"><img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/feedzy.svg' ); ?>" width="60" height="60" alt=""></div>
					</div>
				</div>
			</div>
			<?php
			add_filter( 'screen_options_show_screen', '__return_false' );
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
			add_action( 'admin_notices', array( $this, 'feedzy_import_post_title_section' ) );
		}
	}

	/**
	 * Check if the user has dismissed the notice.
	 *
	 * @return bool
	 */
	public static function had_dismissed_notice() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		return get_user_meta( get_current_user_id(), 'feedzy_import_upsell_notice', true ) === 'dismissed';
	}
	/**
	 * Load feedzy import post screen.
	 */
	public function feedzy_import_post_title_section() {
		global $post;
		?>
		<div class="feedzy-wrap">
			<div class="feedzy-container fz-import-field-item">
				<?php if ( ! feedzy_is_pro() && ( time() - feedzy_install_time() ) > ( 2 * DAY_IN_SECONDS ) && ! self::had_dismissed_notice() ) : ?>
					<div class="upgrade-alert mb-24">
						<?php
							$upgrade_url = tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'post_title', 'import-screen' ) );

							$content = __( 'You are using Feedzy Lite.', 'feedzy-rss-feeds' ) . ' ';
							// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
							$content .= wp_sprintf( __( 'Unlock more powerful features, by %1$s upgrading to Feedzy Pro %2$s and get 50%% off.', 'feedzy-rss-feeds' ), '<a href="' . esc_url( $upgrade_url ) . '" target="_blank">', '</a>' );

							echo wp_kses_post( $content );
						?>
						<button type="button" class="remove-alert"><span class="dashicons dashicons-no-alt"></span></button>
					</div>
				<?php endif; ?>
				<div class="fz-form-wrap">
					<div class="fz-form-group pb-30">
						<label class="form-label"><?php esc_html_e( 'Import Name', 'feedzy-rss-feeds' ); ?></label>
						<input type="text" class="form-control" id="post_title" value="<?php echo $post ? esc_attr( $post->post_title ) : ''; ?>" placeholder="<?php esc_attr_e( 'Add a name for your import', 'feedzy-rss-feeds' ); ?>">
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
