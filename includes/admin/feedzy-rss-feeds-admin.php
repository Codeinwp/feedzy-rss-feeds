<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://themeisle.com
 * @since      3.0.0
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
 * @author     Themeisle <friends@themeisle.com>
 */

/**
 * Class Feedzy_Rss_Feeds_Admin
 */
class Feedzy_Rss_Feeds_Admin extends Feedzy_Rss_Feeds_Admin_Abstract {

	/**
	 * Any notice we want to show in the settings screen.
	 *
	 * @access   public
	 * @var      string $notice The notice.
	 */
	public $notice;
	/**
	 * Any error we want to show in the settings screen.
	 *
	 * @access   public
	 * @var      string $error The error.
	 */
	public $error;
	/**
	 * The version of this plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string $version The current version of this plugin.
	 */
	protected $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $plugin_name The name of this plugin.
	 * @param   string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		if ( ! defined( 'E2E_TESTING' ) ) {
			add_filter(
				'themeisle-sdk/survey/' . $this->plugin_name,
				function ( $data, $page_slug ) {
					// Show survey only on `Help us improve` tab on Support page.
					if ( false !== strpos( $page_slug, 'support' ) && 'support-improve' !== $page_slug ) {
						return $data;
					}
					return $this->get_survey_data();
				},
				10,
				2
			);
		}

		add_filter( 'themeisle_sdk_blackfriday_data', array( $this, 'add_black_friday_data' ) );

		/**
		 * Load SDK dependencies.
		 */
		add_action(
			'themeisle_internal_page',
			function ( $product_name, $page_slug ) {
				if ( $product_name !== $this->plugin_name ) {
					return;
				}

				if ( in_array( $page_slug, array( 'imports', 'categories' ), true ) ) {
					$this->add_banner_anchor();
				}

				if (
					in_array( $page_slug, array( 'imports', 'new-category', 'settings' ), true )
					&& 'yes' === get_option( 'feedzy_rss_feeds_logger_flag', false )
				) {
					$this->enable_telemetry();
				}
			},
			10,
			2
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since   3.0.0
	 * @access  public
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

		if ( is_admin() ) {
			return;
		}
		wp_register_style( $this->plugin_name, FEEDZY_ABSURL . 'css/feedzy-rss-feeds.css', array(), $this->version, 'all' );
	}
	
	/**
	 * Helper function to enqueue the license script with localization
	 *
	 * @access  public
	 * @return  void
	 */
	private function enqueue_license_script() {
		wp_enqueue_script( 
			$this->plugin_name . '_license', 
			FEEDZY_ABSURL . 'js/feedzy-license.js', 
			array( 'jquery' ), 
			$this->version, 
			true 
		);

		wp_localize_script(
			$this->plugin_name . '_license',
			'feedzyLicense',
			array(
				'l10n' => array(
					'licenseKey' => __( 'License Key', 'feedzy-rss-feeds' ),
					'checkBtn'   => __( 'Check License', 'feedzy-rss-feeds' ),
					'errorMsg'   => __( 'An error occurred while checking the license. Please try again.', 'feedzy-rss-feeds' ),
				),
			)
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since   3.3.6
	 * @access  public
	 */
	public function enqueue_styles_admin() {
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

		if ( ! is_admin() ) {
			return;
		}
		$screen = get_current_screen();
		if ( empty( $screen ) ) {
			return;
		}
		if ( ! isset( $screen->base ) ) {
			return;
		}

		if ( 'feedzy_imports' === $screen->post_type && 'edit' === $screen->base ) {
			$this->do_internal_page( 'imports' );
		}

		if ( 'feedzy_categories' === $screen->post_type ) {
			wp_enqueue_script(
				$this->plugin_name . '_categories',
				FEEDZY_ABSURL . 'js/categories.js',
				array(
					'jquery',
				),
				$this->version,
				true
			);
			wp_localize_script(
				$this->plugin_name . '_categories',
				'feedzy',
				array(
					'ajax' => array(
						'security' => wp_create_nonce( FEEDZY_NAME ),
					),
					'l10n' => array(
						'validate'   => __( 'Validate & Clean', 'feedzy-rss-feeds' ),
						'validating' => __( 'Validating', 'feedzy-rss-feeds' ) . '...',
						'validated'  => __( 'Removed # URL(s)!', 'feedzy-rss-feeds' ),
					),
				)
			);
		}

		if ( 'feedzy_page_feedzy-settings' === $screen->base || 'feedzy_page_feedzy-integration' === $screen->base ) {
			if ( ! did_action( 'wp_enqueue_media' ) && 'feedzy_page_feedzy-settings' === $screen->base ) {
				wp_enqueue_media();
			}
			wp_enqueue_script( $this->plugin_name . '_setting', FEEDZY_ABSURL . 'js/feedzy-setting.js', array( 'jquery' ), $this->version, true );
			wp_localize_script(
				$this->plugin_name . '_setting',
				'feedzy_setting',
				array(
					'l10n' => array(
						'media_iframe_title'  => __( 'Select image', 'feedzy-rss-feeds' ),
						'media_iframe_button' => __( 'Set default image', 'feedzy-rss-feeds' ),
						'action_btn_text_1'   => __( 'Choose image', 'feedzy-rss-feeds' ),
						'action_btn_text_2'   => __( 'Replace image', 'feedzy-rss-feeds' ),
						'delete_btn_label'    => __( 'Delete', 'feedzy-rss-feeds' ),
					),
				)
			);

			$this->enqueue_license_script();
		}

		$upsell_screens = array( 'feedzy-rss_page_feedzy-settings', 'feedzy-rss_page_feedzy-admin-menu-pro-upsell' );
		if ( 'feedzy_imports' === $screen->post_type && 'edit' !== $screen->base ) {

			$asset_file = include FEEDZY_ABSPATH . '/build/action-popup/index.asset.php';
			wp_enqueue_script( $this->plugin_name . '_action_popup', FEEDZY_ABSURL . 'build/action-popup/index.js', array_merge( $asset_file['dependencies'], array( 'wp-editor', 'wp-api' ) ), $asset_file['version'], true );

			$openai_model         = '';
			$open_router_model    = '';
			$integration_settings = get_option( 'feedzy-rss-feeds-settings', array() );

			$all_open_ai_models        = apply_filters( 'feedzy_openai_models', array() );
			$deprecated_open_ai_models = apply_filters( 'feedzy_openai_deprecated_models', array() );
			$active_open_ai_models     = array_values( array_diff( $all_open_ai_models, $deprecated_open_ai_models ) );

			if ( ! empty( $integration_settings['openai_api_model'] ) ) {
				$openai_model = $integration_settings['openai_api_model'];
			}

			if ( ! empty( $integration_settings['openrouter_api_model'] ) ) {
				$open_router_model = $integration_settings['openrouter_api_model'];
			}

			wp_localize_script(
				$this->plugin_name . '_action_popup',
				'feedzyData',
				array(
					'isPro'                  => feedzy_is_pro(),
					'isBusinessPlan'         => apply_filters( 'feedzy_is_license_of_type', false, 'business' ),
					'isAgencyPlan'           => apply_filters( 'feedzy_is_license_of_type', false, 'agency' ),
					'apiLicenseStatus'       => $this->api_license_status(),
					'isHighPrivileges'       => current_user_can( 'manage_options' ),
					'languageList'           => $this->get_lang_list(),
					'integrationSettings'    => get_option( 'feedzy-rss-feeds-settings' ),
					'integrations'           => array(
						'openAIModel'     => $openai_model,
						'openRouterModel' => $open_router_model,
					),
					'activeOpenAIModels'     => $active_open_ai_models,
					'deprecatedOpenAIModels' => $deprecated_open_ai_models,
				)
			);

			wp_set_script_translations( $this->plugin_name . '_action_popup', 'feedzy-rss-feeds' );

			$asset_file = include FEEDZY_ABSPATH . '/build/conditions/index.asset.php';
			wp_enqueue_script( $this->plugin_name . '_conditions', FEEDZY_ABSURL . 'build/conditions/index.js', array_merge( $asset_file['dependencies'], array( 'wp-editor', 'wp-api' ) ), $asset_file['version'], true );

			// Add wp_localize_script to pass variables to the JS file with a filter over the data.
			wp_localize_script(
				$this->plugin_name . '_conditions',
				'feedzyConditionsData',
				apply_filters(
					'feedzy_conditions_data',
					array(
						'isPro'            => feedzy_is_pro(),
						'isBusinessPlan'   => apply_filters( 'feedzy_is_license_of_type', false, 'business' ),
						'isAgencyPlan'     => apply_filters( 'feedzy_is_license_of_type', false, 'agency' ),
						'apiLicenseStatus' => $this->api_license_status(),
						'isHighPrivileges' => current_user_can( 'manage_options' ),
						'operators'        => Feedzy_Rss_Feeds_Conditions::get_operators(),
					)
				)
			);

			wp_enqueue_style( 'wp-block-editor' );

			wp_set_script_translations( $this->plugin_name . '_conditions', 'feedzy-rss-feeds' );
			$this->enable_telemetry();
		}
		if ( ! defined( 'TI_CYPRESS_TESTING' ) && ( 'edit' !== $screen->base && 'feedzy_imports' === $screen->post_type && feedzy_show_import_tour() ) ) {
			$asset_file = include FEEDZY_ABSPATH . '/build/onboarding/index.asset.php';
			wp_enqueue_script( $this->plugin_name . '_on_boarding', FEEDZY_ABSURL . 'build/onboarding/index.js', array_merge( $asset_file['dependencies'], array( 'wp-editor', 'wp-api' ) ), $asset_file['version'], true );
			wp_set_script_translations( $this->plugin_name . '_on_boarding', 'feedzy-rss-feeds' );
			$this->enable_telemetry();
		}

		if ( 'feedzy_page_feedzy-settings' === $screen->base ) {
			$this->do_internal_page( 'settings' );
		}

		if ( 'feedzy_page_feedzy-integration' === $screen->base ) {
			$this->do_internal_page( 'integrations' );
		}

		if ( 'feedzy_categories' === $screen->post_type ) {
			$this->do_internal_page( 'add' === $screen->action ? 'new-category' : 'categories' );
		}

		if ( ! in_array( $screen->base, $upsell_screens, true ) && false === strpos( $screen->id, 'feedzy' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';

		if ( 'feedzy_page_feedzy-support' === $screen->base &&
			(
				( 'improve' === $tab )
				|| ( 'edit' !== $screen->base && 'feedzy_imports' === $screen->post_type )
				|| ( 'license' === $tab )
			) 
		) {
			$asset_file = include FEEDZY_ABSPATH . '/build/feedback/index.asset.php';
			wp_enqueue_script( $this->plugin_name . '_feedback', FEEDZY_ABSURL . 'build/feedback/index.js', array_merge( $asset_file['dependencies'], array( 'wp-editor', 'wp-api', 'lodash' ) ), $asset_file['version'], true );
			wp_enqueue_style( 'wp-block-editor' );

			wp_localize_script(
				$this->plugin_name . '_feedback',
				'feedzyObj',
				array(
					'assetsUrl'     => FEEDZY_ABSURL,
					'pluginVersion' => $this->version,
				)
			);

			$this->enqueue_license_script();

			wp_set_script_translations( $this->plugin_name . '_feedback', 'feedzy-rss-feeds' );
		}

		if ( 'feedzy_page_feedzy-support' === $screen->base ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$tab    = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
			$suffix = ! empty( $tab ) ? '-' . $tab : '';
			$this->do_internal_page( 'support' . $suffix );
		}

		if ( ( 'edit' !== $screen->base && 'feedzy_imports' === $screen->post_type ) ) {
			$this->do_internal_page( 'new-import' );
		}

		if ( 'feedzy_imports' === $screen->post_type && 'edit' === $screen->base && feedzy_show_review_notice() ) {
			$asset_file = include FEEDZY_ABSPATH . '/build/review/index.asset.php';
			wp_enqueue_script( $this->plugin_name . '_review', FEEDZY_ABSURL . 'build/review/index.js', $asset_file['dependencies'], $asset_file['version'], true );
			wp_set_script_translations( $this->plugin_name . '_review', 'feedzy-rss-feeds' );
		}

		wp_enqueue_style( $this->plugin_name . '-settings', FEEDZY_ABSURL . 'css/settings.css', array(), $this->version );
		wp_enqueue_style( $this->plugin_name . '-metabox', FEEDZY_ABSURL . 'css/metabox-settings.css', array( $this->plugin_name . '-settings' ), $this->version );
	}

	/**
	 * Add action links on plugin listing screen.
	 *
	 * @param array  $actions Actions array.
	 * @param string $plugin_file Plugin file.
	 *
	 * @return mixed
	 */
	public function plugin_actions( $actions, $plugin_file ) {
		$actions['settings'] = '<a href="' . admin_url( 'admin.php?page=feedzy-settings' ) . '">' . __( 'Settings', 'feedzy-rss-feeds' ) . '</a>';

		return $actions;
	}

	/**
	 * Get number of imports.
	 *
	 * @return int
	 */
	public static function get_no_of_imports() {
		$args  = array(
			'post_type'      => 'feedzy_imports',
			'posts_per_page' => 100,
			'post_status'    => array( 'any', 'trash' ),
			'fields'         => 'ids',
		);
		$query = new WP_Query( $args );

		return $query->found_posts;
	}

	/**
	 * Handle legacy logic.
	 *
	 * @return void
	 */
	public function handle_legacy() {
		// We can increment this when we reach a new legacy milestone.

		$current_legacy_version = (int) get_option( 'feedzy_legacyv5', 0 );

		if ( 0 === $current_legacy_version ) {
			$current_legacy_version = self::get_no_of_imports() > 0 ? 1 : -1;
			update_option( 'feedzy_legacyv5', $current_legacy_version );
		}
		if ( function_exists( 'get_current_screen' ) && 'feedzy_imports' === get_current_screen()->post_type && 'add' === get_current_screen()->action && ! feedzy_is_pro() && -1 === $current_legacy_version && self::get_no_of_imports() >= 1 ) {
			wp_safe_redirect( 'edit.php?post_type=feedzy_imports' );
			exit;
		}
		if ( -1 === $current_legacy_version && ! feedzy_is_pro() && self::get_no_of_imports() >= 1 ) {
			add_action(
				'admin_head',
				function () {
					?>
				<script type="text/javascript">
					jQuery(function () {
						jQuery('a[href*="post-new.php?post_type=feedzy_imports"]').addClass('feedzy-import-limit').prepend('<span class="dashicons dashicons-lock"></span> ');
					});
				</script>
					<?php
				}
			);
		}
	}

	/**
	 * Add modals for the plugin.
	 */
	public function add_modals() {
		?>
			<script type="text/javascript">
				jQuery(function () {
					jQuery('a.feedzy-clone-disabled').on('click', function (event) {
						openModal('#feedzy-clone-modal');
						event.preventDefault();
					});
					jQuery('a.feedzy-edit-disabled').on('click', function (event) {
						openModal('#feedzy-renew-edit');
						event.preventDefault();
					});
					jQuery('a.feedzy-import-limit').on('click', function (event) {
						openModal('#feedzy-add-new-import');
						event.preventDefault();
					});
					
					// Function to open the modal
					function openModal(modal) {
						jQuery(modal).show();
					}

					// Function to close the modal
					function closeModal(e) {
						jQuery('.feedzy-modal')
							.hide();
					}

					// Close modal when close button or overlay is clicked
					jQuery('.close-modal').on('click', function () {
						closeModal();
					});

					// Close modal when Esc key is pressed
					jQuery(document).on('keyup', function (e) {
						if (e.key === "Escape") closeModal();
					});

				});
			</script>
			<div id="feedzy-add-new-import" class="wp-core-ui feedzy-modal" style="display:none;">
				<div class="modal-content">
					<button type="button" class="fz-notice close-modal">
						<span class="dashicons dashicons-no-alt"></span>
						<span class="screen-reader-text">
							<?php esc_html_e( 'Dismiss this dialog', 'feedzy-rss-feeds' ); ?>
						</span>
					</button>
					<div class="modal-header">
						<h2>
							<?php esc_html_e( 'Upgrade to Use Unlimited Imports', 'feedzy-rss-feeds' ); ?>
						</h2>
						<p style="color: red;">
							<?php esc_html_e( 'Import limit reached', 'feedzy-rss-feeds' ); ?>
							<span>
								<?php
								// translators: %1$s is the number of imports used, %2$s is the total number of imports allowed.
								echo esc_html( '(' . sprintf( __( '%1$s/%2$s used', 'feedzy-rss-feeds' ), '1', '1' ) . ')' );
								?>
							</span>
						</p>
					</div>
					<div class="modal-body">
						<p>
							<?php esc_html_e( 'Your current plan supports only one import setup. Upgrade to unlock unlimited import configurations and make the most of Feedzy\'s powerful features!', 'feedzy-rss-feeds' ); ?>
						</p>
					</div>
					<div class="modal-footer">
						<div class="button-container">
							<a
								href="<?php echo esc_url( tsdk_utmify( tsdk_translate_link( FEEDZY_UPSELL_LINK ), 'add-new-import' ) ); ?>"
								target="_blank" rel="noopener "
								class="button button-primary button-large"
							>
								<?php esc_html_e( 'Upgrade to PRO', 'feedzy-rss-feeds' ); ?>
							</a>
						</div>
						<span>
							<?php
							esc_html_e( '30-day money-back guarantee. No questions asked.', 'feedzy-rss-feeds' );
							?>
						</span>
					</div>
				</div>
			</div>
			
		<?php
		$license_key       = apply_filters( 'product_feedzy_license_key', '' );
		$renew_license_url = tsdk_utmify( tsdk_translate_link( FEEDZY_UPSELL_LINK ), 'renew' );
		if ( ! empty( $license_key ) ) {
			$renew_license_url = tsdk_utmify( 'https://store.themeisle.com/?edd_license_key=' . $license_key . '&download_id=6306666', 'feedzy_renew_link' );
		}

		?>
			<div id="feedzy-renew-edit" class="wp-core-ui feedzy-modal" style="display:none;">
				<div class="modal-content">
					<button type="button" class="fz-notice close-modal">
						<span class="dashicons dashicons-no-alt"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this dialog', 'feedzy-rss-feeds' ); ?></span>
					</button>
					<div class="modal-header">
						<h2>
							<?php esc_html_e( 'Alert!', 'feedzy-rss-feeds' ); ?>
						</h2>
					</div>
					<div class="modal-body">
						<p>
							<?php esc_html_e( 'In order to edit premium import setups, benefit from updates and support for Feedzy Premium plugin, please renew your license code or activate it.', 'feedzy-rss-feeds' ); ?>
						</p>
					</div>
					<div class="modal-footer">
						<div class="button-container">
							<a
								href="<?php echo esc_url( $renew_license_url ); ?>"
								target="_blank"
								rel="noopener "
								class="button button-primary button-large"
							>
								<?php esc_html_e( 'Renew License', 'feedzy-rss-feeds' ); ?><span aria-hidden="true" class="dashicons dashicons-external"></span>
							</a>
							<a
								href="<?php echo esc_url( admin_url( 'options-general.php#feedzy_rss_feeds_pro_license' ) ); ?>"
								target="_blank"
								rel="noopener "
								class="button button-secondary button-large"
							>
								<?php esc_html_e( 'Activate License', 'feedzy-rss-feeds' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
			<div id="feedzy-clone-modal" class="wp-core-ui feedzy-modal" style="display:none;">
				<div class="modal-content">
					<button type="button" class="fz-notice close-modal">
						<span class="dashicons dashicons-no-alt"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this dialog', 'feedzy-rss-feeds' ); ?></span>
					</button>
					<div class="modal-header">
						<h2>
							<?php esc_html_e( 'Unlock Cloning with Feedzy PRO', 'feedzy-rss-feeds' ); ?>
						</h2>
						<p>
							<?php esc_html_e( 'Duplicate your imports with one click', 'feedzy-rss-feeds' ); ?>
						</p>
					</div>
					<div class="modal-body">
						<p>
							<?php esc_html_e( 'Cloning import setups are not available on your plan. Upgrade to the Pro plan to unlock this feature and streamline your import setup process.', 'feedzy-rss-feeds' ); ?>
						</p>
					</div>
					<div class="modal-footer">
						<div class="button-container">
							<a
								href="<?php echo esc_url( tsdk_utmify( tsdk_translate_link( FEEDZY_UPSELL_LINK ), 'clone' ) ); ?>"
								target="_blank" rel="noopener "
								class="button button-primary button-large"
							>
								<?php esc_html_e( 'Upgrade to PRO', 'feedzy-rss-feeds' ); ?>
							</a>
						</div>
						<span>
							<?php
							esc_html_e( '30-day money-back guarantee. No questions asked.', 'feedzy-rss-feeds' );
							?>
						</span>
					</div>
				</div>
			</div>
			<div id="fz_import_export_upsell" class="wp-core-ui feedzy-modal" style="display:none;">
				<div class="modal-content">
					<span class="fz-notice close-modal">
						<span class="dashicons dashicons-no-alt"></span>
						<span class="screen-reader-text">
							<?php esc_html_e( 'Dismiss this dialog', 'feedzy-rss-feeds' ); ?>
						</span>
					</span>
					<div class="modal-header">
						<h2>
							<?php esc_html_e( 'Unlock Upload & Export with Feedzy PRO', 'feedzy-rss-feeds' ); ?>
						</h2>
						<p>
							<?php esc_html_e( 'Save time by reusing your import setups', 'feedzy-rss-feeds' ); ?>
						</p>
					</div>
					<div class="modal-body">
						<p>
							<?php esc_html_e( 'Upload/export of import configuration is not available on your plan. Please upgrade to the Pro plan to unlock all these features.', 'feedzy-rss-feeds' ); ?>
						</p>
					</div>
					<div class="modal-footer">
						<div class="button-container">
							<a
								href="<?php echo esc_url( tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'importExport' ) ) ); ?>"
								target="_blank" rel="noopener "
								class="button button-primary button-large"
							>
								<?php esc_html_e( 'Upgrade to PRO', 'feedzy-rss-feeds' ); ?>
							</a>
						</div>
						<span>
							<?php
							esc_html_e( '30-day money-back guarantee. No questions asked.', 'feedzy-rss-feeds' );
							?>
						</span>
					</div>
				</div>
			</div>
			<style>

				.feedzy-import-limit, .page-title-action.only-pro{
					opacity:0.7;
				}
				.feedzy-quick-link{
					opacity:0.7;
				}
				.feedzy-quick-link .dashicons{

					font-size: 13px;
					width: 13px;
					height: 15px;
					vertical-align: middle;
				}
				.feedzy-modal {
					position: fixed;
					z-index: 100000;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;

					overflow-x: hidden;
					overflow-y: auto;
					background: rgba(0, 0, 0, 0.7);
				}

				.feedzy-modal .modal-content {
					position: relative;
					background: #fff;
					padding: 0;
					border-radius: 8px;
					max-width: 500px;
					width: auto;
					margin: 6.75rem auto;
				}

				.feedzy-modal .modal-body {
					padding: 0 25px;
					padding-top: 25px;
				}

				.feedzy-modal .modal-body p {
					margin: 0;
					font-size: 16px;
					line-height: 1.2;
				}

				.feedzy-modal .modal-header {
					display: flex;
					flex-direction: column;
					align-items: flex-start;
					gap: 0.5rem;

					position: relative;
					background-color: rgb(248, 250, 252);
					padding: 20px 25px;
					border-radius: 8px;
				}

				.feedzy-modal .modal-header .dashicons {
					font-size: 1.3em;
					line-height: inherit;
				}

				.feedzy-modal .modal-header :is(h2,p) {
					margin: 0;
					font-weight: 500;
				}

				:is( .feedzy-modal, #poststuff ) .modal-header h2 {
					font-size: 20px;
					margin: 0;
					padding: 0;
				}

				.feedzy-modal .close-modal {
					position: absolute;
					top: 0;
					right: 0;
					z-index: 10;
					background: transparent;
					border: none;
					cursor: pointer;
					padding: 10px;
				}

				.feedzy-modal .modal-footer .dashicons {

					vertical-align: middle;
					font-size: initial;
				}

				.feedzy-modal .modal-footer {
					padding: 25px;
					text-align: center;
					flex-direction: column;
					display: flex;
					gap: 1rem;
				}

				.feedzy-modal .modal-footer a {
					display: flex;
					align-items: center;
					justify-content: center;
					background-color: rgb(89, 127, 230);
					padding: 10px;
					border-radius: 6px;
					font-size: medium;
				}

				#toplevel_page_feedzy-admin-menu span.tsdk-upg-menu-item{
					color:#fff;
				}
				#toplevel_page_feedzy-admin-menu a:has(span.tsdk-upg-menu-item){
					background-color:#00A32A!important;
					margin-bottom:-7px;
					color:#fff!important;
				}
			</style>

			<?php
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
	 * Method to register custom post type for
	 * Feedzy RSS Feeds Categories.
	 *
	 * @since   3.0.12
	 * @access  public
	 */
	public function register_post_type() {
		$labels   = array(
			'name'               => __( 'Feed Groups', 'feedzy-rss-feeds' ),
			'singular_name'      => __( 'Feed Group', 'feedzy-rss-feeds' ),
			'add_new'            => __( 'Add Group', 'feedzy-rss-feeds' ),
			'add_new_item'       => __( 'Add Group', 'feedzy-rss-feeds' ),
			'edit_item'          => __( 'Edit Group', 'feedzy-rss-feeds' ),
			'new_item'           => __( 'New Feed Group', 'feedzy-rss-feeds' ),
			'view_item'          => __( 'View Group', 'feedzy-rss-feeds' ),
			'search_items'       => __( 'Search Group', 'feedzy-rss-feeds' ),
			'not_found'          => __( 'No groups found', 'feedzy-rss-feeds' ),
			'not_found_in_trash' => __( 'No groups in the trash', 'feedzy-rss-feeds' ),
		);
		$supports = array(
			'title',
		);
		$args     = array(
			'labels'                => $labels,
			'supports'              => $supports,
			'public'                => true,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'show_in_nav_menus'     => false,
			'capability_type'       => 'post',
			'rewrite'               => array( 'slug' => 'feedzy-category' ),
			'show_in_menu'          => 'feedzy-admin-menu',
			'register_meta_box_cb'  => array( $this, 'add_feedzy_post_type_metaboxes' ),
			'show_in_rest'          => true,
			'rest_base'             => 'feedzy_categories',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'map_meta_cap'          => true,
			'capabilities'          => array(
				'edit_post'          => 'edit_feedzy_category',
				'read_post'          => 'read_feedzy_category',
				'delete_post'        => 'delete_feedzy_category',
				'edit_posts'         => 'edit_feedzy_categories',
				'edit_others_posts'  => 'edit_others_feedzy_categories',
				'publish_posts'      => 'publish_feedzy_categories',
				'read_private_posts' => 'read_private_feedzy_categories',
			),
		);
		$args     = apply_filters( 'feedzy_post_type_args', $args );
		register_post_type( 'feedzy_categories', $args );
	}

	/**
	 * Only allow admin to modify or delete categories.
	 *
	 * @return void
	 */
	public function register_admin_capabilities() {
		$admin_role = get_role( 'administrator' );
		$admin_role->add_cap( 'edit_feedzy_category' );
		$admin_role->add_cap( 'read_feedzy_category' );
		$admin_role->add_cap( 'delete_feedzy_category' );
		$admin_role->add_cap( 'edit_feedzy_categories' );
		$admin_role->add_cap( 'edit_others_feedzy_categories' );
		$admin_role->add_cap( 'publish_feedzy_categories' );
		$admin_role->add_cap( 'read_private_feedzy_categories' );
	}

	/**
	 * Method to add a meta box to `feedzy_categories`
	 * custom post type.
	 *
	 * @since   3.0.12
	 * @access  public
	 */
	public function add_feedzy_post_type_metaboxes() {
		add_meta_box(
			'feedzy_category_feeds',
			__( 'Group Feeds', 'feedzy-rss-feeds' ),
			array(
				$this,
				'feedzy_category_feed',
			),
			'feedzy_categories',
			'normal',
			'high'
		);
		if ( ! feedzy_is_pro() ) {
			add_meta_box(
				'feedzy_category_feeds_rn',
				__( 'Extend Feedzy', 'feedzy-rss-feeds' ),
				array(
					$this,
					'render_upsell_rn',
				),
				'feedzy_categories',
				'side',
				'low'
			);
		}

		if ( 'publish' === get_post_status() ) {
			add_meta_box(
				'feedzy_category_feeds_preview',
				__( 'Feed Preview', 'feedzy-rss-feeds' ),
				array(
					$this,
					'render_feed_preview',
				),
				'feedzy_categories',
				'side'
			);
		}
	}

	/**
	 * Render RN upsell metabox.
	 */
	public function render_upsell_rn() {
		echo '<strong>' . esc_html__( 'Get access to more features.', 'feedzy-rss-feeds' ) . '</strong>';
		echo '<ul>
			<li>- ' . esc_html__( 'Auto add referral parameters to links', 'feedzy-rss-feeds' ) . '</li>
			<li>- ' . esc_html__( 'Full Text Import', 'feedzy-rss-feeds' ) . '</li>
			<li>- ' . esc_html__( 'Paraphrase content', 'feedzy-rss-feeds' ) . '</li>
			<li>- ' . esc_html__( 'Translate content', 'feedzy-rss-feeds' ) . '</li>
			<li>- ' . esc_html__( 'Elementor Templates support', 'feedzy-rss-feeds' ) . '</li>
		</ul>';
		echo '<a class="button button-primary  " href="' . esc_url( tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'metabox', 'new-category' ) ) ) . '" target="_blank">' . esc_html__( 'View more details', 'feedzy-rss-feeds' ) . '</a>';
	}

	/**
	 * Meta box callback function to display a textarea
	 * inside the custom post edit page.
	 *
	 * @since   3.0.12
	 * @access  public
	 * @return mixed
	 */
	public function feedzy_category_feed() {
		global $post;
		$nonce   = wp_create_nonce( FEEDZY_BASEFILE );
		$feed    = get_post_meta( $post->ID, 'feedzy_category_feed', true );
		$invalid = $this->get_source_validity_error( '', $post );

		$output = '
            <input type="hidden" name="feedzy_category_meta_noncename" id="feedzy_category_meta_noncename" value="' . $nonce . '" />
			<strong>' .
			sprintf(
				// translators: %1$s and %2$s are placeholders for HTML anchor tags.
				__( 'Please be aware that multiple feeds, when mashed together, may sometimes not work as expected as explained %1$s here %2$s.', 'feedzy-rss-feeds' ),
				'<a href="' . esc_url( 'https://simplepie.org/wiki/faq/typical_multifeed_gotchas' ) . '" target="_blank">',
				'</a>'
			)
			. '</strong><br/><br/>'
			. $invalid
			. '<textarea name="feedzy_category_feed" rows="10" class="widefat" placeholder="themeisle.com/blog/feed/, https://wptavern.com/feed/, https://www.wpbeginner.com/feed/, https://wpshout.com/feed/, https://planet.wordpress.org/feed/" >' . $feed . '</textarea>
			<div class="validate-feeds-actions">
			<span class="spinner"></span>
			<button class="button validate-feeds" ' . esc_attr( empty( $feed ) ? 'disabled' : '' ) . '>' . __( 'Validate & Remove Invalid Feeds', 'feedzy-rss-feeds' ) . '</button>
			</div>
        ';
		echo wp_kses( $output, apply_filters( 'feedzy_wp_kses_allowed_html', array() ) );
	}

	/**
	 * Render the feed preview metabox.
	 *
	 * @return void
	 */
	public function render_feed_preview() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['action'] ) && 'edit' !== $_GET['action'] ) {
			return;
		}
		$feeds     = get_post_meta( get_the_ID(), 'feedzy_category_feed', true );
		$feed_urls = $this->normalize_urls( $feeds );
		if ( empty( $feed_urls ) ) {
			echo '<p>' . esc_html__( 'No feeds available for preview.', 'feedzy-rss-feeds' ) . '</p>';
			return;
		}
		$shortcode   = array(
			'sort'  => 'date_desc',
			'thumb' => 'no',
			'max'   => 0,
		);
		$atts        = $this->get_short_code_attributes( $shortcode );
		$atts        = $this->sanitize_attr( $atts, $feed_urls );
		$sizes       = array(
			'width'  => 0,
			'height' => 0,
		);
		$feed        = $this->fetch_feed( $feed_urls, $atts['refresh'], $atts );
		$feed_items  = apply_filters( 'feedzy_get_feed_array', array(), $atts, $feed, $feed_urls, $sizes );
		$total_items = count( $feed_items );

		$max_items_preview_count = 5;
		$preview_feed_items      = array_slice( $feed_items, 0, $max_items_preview_count );
		?>
		<strong>
			<?php
				echo esc_html(
					sprintf(
						// translators: %1$s the number of maximum displayed items, %2$s is the total number of items available in the feed.
						__( 'Latest %1$s feed items out of %2$s available from', 'feedzy-rss-feeds' ),
						$max_items_preview_count,
						$total_items
					)
				);
			?>
		</strong>
		<div>
			<ul class="feedzy-preview-list">
			<?php
			foreach ( $preview_feed_items as $item ) {
				$datetime     = date_i18n( 'c', $item['item_date'] );
				$time_content = date_i18n( 'Y-m-d', $item['item_date'] );
				?>
					<li <?php echo esc_attr( $item['itemAttr'] ); ?>>
						<a href="<?php echo esc_url( $item['item_url'] ); ?>" target="_blank">
							<?php echo esc_html( $item['item_title'] ); ?>
						</a>
						<br/>
						<time
							datetime="<?php echo esc_attr( $datetime ); ?>"
							content="<?php echo esc_attr( $time_content ); ?>"
						>
							<?php echo esc_html( $this->get_humman_readable_time_diff( $item['item_date'] ) ); ?>
						</time>
					</li>
				<?php
			}
			?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Get human readable time difference.
	 *
	 * @param int $item_publish_time The item publish time.
	 *
	 * @return string
	 */
	private function get_humman_readable_time_diff( $item_publish_time ) {
		$array     = current_datetime();
		$localtime = $array->getTimestamp() + $array->getOffset();

		return sprintf(
			// translators: %s is the time difference.
			__( '%s ago', 'feedzy-rss-feeds' ),
			human_time_diff( $item_publish_time, $localtime )
		);
	}

	/**
	 * Utility method to save metabox data to
	 * custom post type.
	 *
	 * @since   3.0.12
	 * @access  public
	 *
	 * @param   integer $post_id The active post ID.
	 * @param   object  $post The post object.
	 *
	 * @return mixed|integer
	 */
	public function save_feedzy_post_type_meta( $post_id, $post ) {
		if ( empty( $_POST ) ) {
			return $post_id;
		}
		if (
			empty( $_POST ) ||
			! isset( $_POST['feedzy_category_meta_noncename'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['feedzy_category_meta_noncename'] ) ), FEEDZY_BASEFILE ) ||
			! current_user_can( 'edit_post', $post_id )
		) {
			return $post_id;
		}

		$category_meta['feedzy_category_feed'] = array();
		if ( isset( $_POST['feedzy_category_feed'] ) ) {
			$feedzy_category_feed                  = wp_strip_all_tags( wp_unslash( $_POST['feedzy_category_feed'] ) );
			$feedzy_category_feed                  = preg_replace( '/\s*,\s*|\R/', ',', $feedzy_category_feed );
			$category_meta['feedzy_category_feed'] = $feedzy_category_feed;
		}

		if ( empty( get_post_meta( $post_id, 'usage_counted', true ) ) ) {
			Feedzy_Rss_Feeds_Usage::get_instance()->track_import_creation();
			update_post_meta( $post_id, 'usage_counted', 'yes' );
		}

		if ( 'revision' === $post->post_type ) {
			return true;
		} else {
			foreach ( $category_meta as $key => $value ) {
				$value = array_map( 'sanitize_url', (array) $value );
				$value = implode( ',', (array) $value );
				if ( get_post_meta( $post_id, $key, false ) ) {
					update_post_meta( $post_id, $key, $value );
				} else {
					add_post_meta( $post_id, $key, $value );
				}
				if ( ! $value ) {
					delete_post_meta( $post_id, $key );
				}
			}

			return true;
		}
	}

	/**
	 * Method for adding `slug` column to post type
	 * table and internalize the `title`. Used for
	 * table head.
	 *
	 * @since   3.0.12
	 * @access  public
	 *
	 * @param   array $columns The default columns array.
	 *
	 * @return array
	 */
	public function feedzy_category_columns( $columns ) {
		$columns['title'] = __( 'Group Title', 'feedzy-rss-feeds' );
		$new_columns      = $this->array_insert_before( 'date', $columns, 'slug', __( 'Slug', 'feedzy-rss-feeds' ) );
		if ( $new_columns ) {
			$columns = $new_columns;
		} else {
			$columns['slug'] = __( 'Slug', 'feedzy-rss-feeds' );
		}

		$new_columns = $this->array_insert_before( 'date', $columns, 'actions', __( 'Actions', 'feedzy-rss-feeds' ) );
		if ( $new_columns ) {
			$columns = $new_columns;
		} else {
			$columns['actions'] = __( 'Actions', 'feedzy-rss-feeds' );
		}

		$new_columns = $this->array_insert_before( 'slug', $columns, 'source', __( 'Source', 'feedzy-rss-feeds' ) );

		return $columns;
	}

	/**
	 * Add/remove row actions for each category.
	 *
	 * @param array   $actions Actions array.
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	public function add_feedzy_category_actions( $actions, $post ) {
		if ( 'feedzy_categories' === $post->post_type ) {
			// don't need quick edit.
			unset( $actions['inline hide-if-no-js'] );
		}
		return $actions;
	}

	/**
	 * Method for displaying post type data in custom
	 * added columns.
	 *
	 * @since   3.0.12
	 * @access  public
	 *
	 * @param   string  $column The column string.
	 * @param   integer $post_id The active post ID.
	 *
	 * @return mixed
	 */
	public function manage_feedzy_category_columns( $column, $post_id ) {
		global $post;
		switch ( $column ) {
			case 'slug':
				$slug = $post->post_name;
				if ( empty( $slug ) ) {
					echo esc_html__( 'Undefined', 'feedzy-rss-feeds' );
				} else {
					echo wp_kses_post( '<code>' . $slug . '</code>' );
				}
				break;
			case 'actions':
				echo wp_kses_post( sprintf( '<button class="button button-primary validate-category" title="%s" data-category-id="%d">%s</button>', __( 'Click to remove invalid URLs from this category', 'feedzy-rss-feeds' ), $post_id, __( 'Validate & Clean', 'feedzy-rss-feeds' ) ) );
				break;
			case 'source':
				$src = get_post_meta( $post_id, 'feedzy_category_feed', true );
				if ( empty( $src ) ) {
					$src = __( 'No Source Configured', 'feedzy-rss-feeds' );
				} else {
					$urls = $this->normalize_urls( $src );
					$src  = '';
					if ( is_array( $urls ) ) {

						foreach ( $urls as $key => $url ) {
							$too_long = 130;
							if ( strlen( $src ) > $too_long ) {
								$src .= '...';
								break;
							} else {
								$src .= '<a href="' . $url . '" target="_blank" title="' . __( 'Click to view', 'feedzy-rss-feeds' ) . '">' . $url . '</a>';
								if ( count( $urls ) > $key + 1 ) {
									$src .= ', ';
								}
							}
						}
					} else {
						$src .= '<a href="' . esc_url( $urls ) . '" target="_blank" title="' . __( 'Click to view', 'feedzy-rss-feeds' ) . '">' . esc_html( $urls ) . '</a>';
					}
				}
				echo wp_kses_post( $src );
				break;
			default:
				break;
		}
	}

	/**
	 * The custom plugin_row_meta function
	 * Adds additional links on the plugins page for this plugin
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   array  $links The array having default links for the plugin.
	 * @param   string $file The name of the plugin file.
	 *
	 * @return  array
	 */
	public function feedzy_filter_plugin_row_meta( $links, $file ) {
		if ( strpos( $file, 'feedzy-rss-feed.php' ) !== false ) {
			$new_links        = array();
			$new_links['doc'] = '<a href="https://docs.themeisle.com/article/658-feedzy-rss-feeds" target="_blank" title="' . __( 'Documentation and examples', 'feedzy-rss-feeds' ) . '">' . __( 'Documentation and examples', 'feedzy-rss-feeds' ) . '</a>';

			if ( ! feedzy_is_pro() ) {
				$new_links['more_features'] = '<a style="color: #009E29; font-weight: 700;"  onmouseout="this.style.color=\'#009528\';"  onmouseover="this.style.color=\'#008a20\';" href="' . esc_url( tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'rowmeta', 'plugins' ) ) ) . '" target="_blank" title="' . __( 'More Features', 'feedzy-rss-feeds' ) . '">' . __( 'Upgrade to Pro', 'feedzy-rss-feeds' ) . '<i style="width: 17px; height: 17px; margin-left: 4px; color: #ffca54; font-size: 17px; vertical-align: -3px;" class="dashicons dashicons-unlock more-features-icon"></i></a>';
			} elseif ( false === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) {
				$new_links['more_features'] = '<a href="' . esc_url( tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'rowmetamore', 'plugins' ) ) ) . '" target="_blank" title="' . __( 'More Features', 'feedzy-rss-feeds' ) . '">' . __( 'Upgrade your license', 'feedzy-rss-feeds' ) . '<i style="width: 17px; height: 17px; margin-left: 4px; color: #ffca54; font-size: 17px; vertical-align: -3px;" class="dashicons dashicons-unlock more-features-icon"></i></a>';
			}
			$links = array_merge( $links, $new_links );
		}

		return $links;
	}

	/**
	 * Method to register pages for admin menu.
	 *
	 * @since   3.0.12
	 * @access  public
	 * @return  void
	 */
	public function feedzy_menu_pages() {
		$capability = feedzy_current_user_can();
		if ( ! $capability ) {
			return;
		}
		$svg_base64_icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iNzdweCIgaGVpZ2h0PSI3N3B4IiB2aWV3Qm94PSIwIDAgNzcgNzciIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8IS0tIEdlbmVyYXRvcjogU2tldGNoIDUyLjYgKDY3NDkxKSAtIGh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaCAtLT4KICAgIDx0aXRsZT5Db21iaW5lZC1TaGFwZTwvdGl0bGU+CiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4KICAgIDxnIGlkPSJQcm9kdWN0LVBhZ2UiIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPgogICAgICAgIDxnIGlkPSJXb3JkUHJlc3MtcGx1Z2lucyIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTE5Ni4wMDAwMDAsIC05NTcuMDAwMDAwKSIgZmlsbD0iIzQyNjhDRiI+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMzQuNSwxMDM0IEMyMTMuMjM3MDM3LDEwMzQgMTk2LDEwMTYuNzYyOTYgMTk2LDk5NS41IEMxOTYsOTc0LjIzNzAzNyAyMTMuMjM3MDM3LDk1NyAyMzQuNSw5NTcgQzI1NS43NjI5NjMsOTU3IDI3Myw5NzQuMjM3MDM3IDI3Myw5OTUuNSBDMjczLDEwMTYuNzYyOTYgMjU1Ljc2Mjk2MywxMDM0IDIzNC41LDEwMzQgWiBNMjM4LjM4OTA4NywxMDAzLjYxMDkxIEMyMzYuMjQxMjU2LDEwMDEuNDYzMDggMjMyLjc1ODg1MSwxMDAxLjQ2Mjk3IDIzMC42MTA5NDMsMTAwMy42MTA4OCBDMjI4LjQ2MzAzNSwxMDA1Ljc1ODc5IDIyOC40NjMwMjEsMTAwOS4yNDEyIDIzMC42MTA5MTMsMTAxMS4zODkwOSBDMjMyLjc1ODgwNCwxMDEzLjUzNjk4IDIzNi4yNDExNDksMTAxMy41MzcwMyAyMzguMzg5MDU3LDEwMTEuMzg5MTIgQzI0MC41MzY5NjUsMTAwOS4yNDEyMSAyNDAuNTM2OTc5LDEwMDUuNzU4OCAyMzguMzg5MDg3LDEwMDMuNjEwOTEgWiBNMjUxLjE5OTE5Niw5OTYuNTI0MjY5IEMyNDEuNzE2MDEsOTg4LjAxMzQwOSAyMjcuMjk0MTQzLDk4OC4wMDQzMDcgMjE3LjgwMDg1OSw5OTYuNTI0MjE0IEMyMTcuMjQwNDk2LDk5Ny4wMjcwNzkgMjE3LjIyMjEwOCw5OTcuODk5Nzc3IDIxNy43NTQ0OCw5OTguNDMyMTUgTDIyMC41NTE4NzksMTAwMS4yMjk1NSBDMjIxLjA0MTU5NCwxMDAxLjcxOTI2IDIyMS44Mjk5NjcsMTAwMS43NTIyNiAyMjIuMzUwNDA4LDEwMDEuMjk1MzcgQzIyOS4yODI0MDEsOTk1LjIxMTE3IDIzOS43MDI4MSw5OTUuMTk4MjA5IDI0Ni42NDk1NDYsMTAwMS4yOTU0MSBDMjQ3LjE3MDA0NywxMDAxLjc1MjI1IDI0Ny45NTg0MiwxMDAxLjcxOTI1IDI0OC40NDgwNzUsMTAwMS4yMjk1OSBMMjUxLjI0NTQ2NSw5OTguNDMyMjA1IEMyNTEuNzc3OTUyLDk5Ny44OTk4MzQgMjUxLjc1OTU2MSw5OTcuMDI3MTM2IDI1MS4xOTkxOTYsOTk2LjUyNDI2OSBaIE0yNTkuNTE3NDgxLDk4OC4wNjI4MTggQzI0NS43NTQ2NjIsOTc1LjI1MzkxIDIyNC4zMTI1MzEsOTc1LjE5MTM3NCAyMTAuNDgyNDY0LDk4OC4wNjI4NzMgQzIwOS45NTA5Niw5ODguNTU3NTU3IDIwOS45NDA4NDUsOTg5LjM5NjY4OSAyMTAuNDU0MjIyLDk4OS45MTAwNjYgTDIxMy4xODU0ODksOTkyLjY0MTMzMyBDMjEzLjY3NTU2OSw5OTMuMTMxNDEzIDIxNC40NjI4MjQsOTkzLjE0MTkyNCAyMTQuOTcyNjIyLDk5Mi42NzIzNTUgQzIyNi4yODEwMjksOTgyLjI1NDc4NiAyNDMuNzIwODA0LDk4Mi4yNTY0MTUgMjU5LjUxNzQ4MSw5ODguMDYyODE4IFoiIGlkPSJDb21iaW5lZC1TaGFwZSI+PC9wYXRoPgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+';
		add_menu_page( __( 'Feedzy', 'feedzy-rss-feeds' ), __( 'Feedzy', 'feedzy-rss-feeds' ), apply_filters( 'feedzy_admin_menu_capability', 'publish_posts' ), 'feedzy-admin-menu', '', $svg_base64_icon, 98.7666 );

		add_submenu_page(
			'feedzy-admin-menu',
			__( 'Dashboard', 'feedzy-rss-feeds' ),
			__( 'Dashboard', 'feedzy-rss-feeds' ),
			'manage_options',
			'feedzy-support',
			array(
				$this,
				'render_support',
			)
		);
		add_submenu_page(
			'feedzy-admin-menu',
			__( 'Settings', 'feedzy-rss-feeds' ),
			'↳ ' . __( 'Settings', 'feedzy-rss-feeds' ),
			'manage_options',
			'feedzy-settings',
			array(
				$this,
				'feedzy_settings_page',
			)
		);
		add_submenu_page(
			'feedzy-admin-menu',
			__( 'Integration', 'feedzy-rss-feeds' ),
			'↳ ' . __( 'Integration', 'feedzy-rss-feeds' ),
			'manage_options',
			'feedzy-integration',
			array(
				$this,
				'feedzy_integration_page',
			)
		);

		if ( ! feedzy_is_pro() && get_option( 'feedzy_fresh_install', false ) ) {
			$hook = add_submenu_page(
				'feedzy-admin-menu',
				__( 'Setup Wizard', 'feedzy-rss-feeds' ),
				__( 'Setup Wizard', 'feedzy-rss-feeds' ),
				'manage_options',
				'feedzy-setup-wizard',
				array(
					$this,
					'feedzy_setup_wizard_page',
				)
			);
			add_action( "load-$hook", array( $this, 'feedzy_load_setup_wizard_page' ) );
			add_action( 'adminmenu', array( $this, 'feedzy_hide_wizard_menu' ) );
		}
	}

	/**
	 * Handle the RSS to Social menu item.
	 *
	 * @access  public
	 * @return  void
	 */
	public function rss_to_social_menu() {
		$capability = feedzy_current_user_can();
		if ( ! $capability ) {
			return;
		}

		if ( defined( 'REVIVE_NETWORK_VERSION' ) && ! feedzy_is_pro() ) {
			return;
		}

		$rss_to_social = __( 'RSS to Social', 'feedzy-rss-feeds' ) . '<span id="feedzy-rn-menu" class="dashicons dashicons-external" style="font-size:initial;"></span>';
		add_action(
			'admin_footer',
			function () {
				?>
				<script type="text/javascript">
					jQuery(document).ready(function ($) {
						$('#feedzy-rn-menu').parent().attr('target', '_blank');
					});
				</script>
				<?php
			}
		);

		global $submenu;
		if ( isset( $submenu['feedzy-admin-menu'] ) ) {
			if ( isset( $submenu['feedzy-admin-menu'][0] ) ) {
				unset( $submenu['feedzy-admin-menu'][0] );
			}
			array_splice(
				$submenu['feedzy-admin-menu'],
				5,
				0,
				array(
					array(
						$rss_to_social,
						'manage_options',
						tsdk_utmify( 'https://revive.social/plugins/revive-network', 'feedzy-menu' ),
					),
				)
			);
		}
	}

	/**
	 * Method to register the settings page.
	 *
	 * @access  public
	 */
	public function feedzy_settings_page() {
		if (
			isset( $_POST['feedzy-settings-submit'] ) && isset( $_POST['tab'] ) && isset( $_POST['nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), sanitize_text_field( wp_unslash( $_POST['tab'] ) ) )
		) {
			$this->save_settings();
			$this->notice = __( 'Your settings were saved.', 'feedzy-rss-feeds' );
		}

		$settings = apply_filters( 'feedzy_get_settings', array() );
		include FEEDZY_ABSPATH . '/includes/layouts/settings.php';
	}

	/**
	 * Method to register the integration page.
	 *
	 * @access  public
	 */
	public function feedzy_integration_page() {
		if (
			isset( $_POST['feedzy-integration-submit'] ) && isset( $_POST['tab'] ) && isset( $_POST['nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), sanitize_text_field( wp_unslash( $_POST['tab'] ) ) )
		) {
			$this->save_settings();
			$this->notice = __( 'Your settings were saved.', 'feedzy-rss-feeds' );
		}

		$settings = apply_filters( 'feedzy_get_settings', array() );
		include FEEDZY_ABSPATH . '/includes/layouts/integration.php';
	}

	/**
	 * Method to save the settings.
	 *
	 * @access  private
	 */
	private function save_settings() {
		if ( ! isset( $_POST['tab'] ) ) {
			return;
		}
		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), sanitize_text_field( wp_unslash( $_POST['tab'] ) ) )
		) {
			return;
		}
		$post_tab = isset( $_POST['tab'] ) ? sanitize_text_field( wp_unslash( $_POST['tab'] ) ) : '';
		$settings = apply_filters( 'feedzy_get_settings', array() );

		switch ( $post_tab ) {
			case 'general':
				$auto_categories_raw = array();

				if ( isset( $_POST['auto-categories'] ) && is_array( $_POST['auto-categories'] ) ) {
					// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$auto_categories_raw = wp_unslash( $_POST['auto-categories'] );
				}

				$auto_categories = array_filter(
					$auto_categories_raw,
					function ( $item ) {
						return isset( $item['keywords'], $item['category'] ) && ! empty( $item['keywords'] ) && is_numeric( $item['category'] );
					}
				);

				$auto_categories = array_map(
					function ( $item ) {
						return array(
							'keywords' => sanitize_text_field( $item['keywords'] ),
							'category' => absint( $item['category'] ),
						);
					},
					$auto_categories
				);

				$auto_categories = array_values( $auto_categories );

				$settings['general']['disable-default-style'] = isset( $_POST['disable-default-style'] ) ? absint( wp_unslash( $_POST['disable-default-style'] ) ) : '';
				$settings['general']['default-thumbnail-id']  = isset( $_POST['default-thumbnail-id'] ) ? absint( wp_unslash( $_POST['default-thumbnail-id'] ) ) : 0;
				$settings['general']['fz_cron_schedule']      = isset( $_POST['fz_cron_schedule'] ) ? sanitize_text_field( wp_unslash( $_POST['fz_cron_schedule'] ) ) : 'hourly';
				$settings['general']['auto-categories']       = array_values( $auto_categories );
				$settings['general']['feedzy-telemetry']      = isset( $_POST['feedzy-telemetry'] ) ? absint( wp_unslash( $_POST['feedzy-telemetry'] ) ) : '';

				$settings['logs']['level']             = isset( $_POST['logs-logging-level'] ) ? sanitize_text_field( wp_unslash( $_POST['logs-logging-level'] ) ) : '';
				$settings['logs']['email']             = isset( $_POST['feedzy-email-error-address'] ) ? sanitize_email( wp_unslash( $_POST['feedzy-email-error-address'] ) ) : '';
				$settings['logs']['send_email_report'] = isset( $_POST['feedzy-email-error-enabled'] ) ? absint( wp_unslash( $_POST['feedzy-email-error-enabled'] ) ) : '';
				$settings['logs']['email_frequency']   = isset( $_POST['logs-email-frequency'] ) ? sanitize_text_field( wp_unslash( $_POST['logs-email-frequency'] ) ) : '';
				break;
			case 'proxy':
				$settings['proxy'] = array(
					'host' => isset( $_POST['proxy-host'] ) ? sanitize_text_field( wp_unslash( $_POST['proxy-host'] ) ) : '',
					'port' => isset( $_POST['proxy-port'] ) ? absint( wp_unslash( $_POST['proxy-port'] ) ) : '',
					'user' => isset( $_POST['proxy-user'] ) ? sanitize_text_field( wp_unslash( $_POST['proxy-user'] ) ) : '',
					'pass' => isset( $_POST['proxy-pass'] ) ? sanitize_text_field( wp_unslash( $_POST['proxy-pass'] ) ) : '',
				);
				break;
			case 'schedules':
				if ( feedzy_is_pro() ) {
					// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$custom_schedules             = isset( $_POST['fz-custom-schedule-interval'] ) ? (array) wp_unslash( $_POST['fz-custom-schedule-interval'] ) : array();
					$settings['custom_schedules'] = array();
					if ( ! empty( $custom_schedules ) ) {
						$cron_timout = defined( 'WP_CRON_LOCK_TIMEOUT' ) ? WP_CRON_LOCK_TIMEOUT : 60;

						foreach ( $custom_schedules as $key => $value ) {
							$interval = isset( $value['interval'] ) ? absint( $value['interval'] ) : $cron_timout;
							$display  = isset( $value['display'] ) ? sanitize_text_field( $value['display'] ) : '';

							if (
								is_numeric( $interval ) && $cron_timout <= $interval &&
								! empty( $display )
							) {
								$settings['custom_schedules'][ $key ] = array(
									'interval' => $interval,
									'display'  => $display,
								);
							}
						}
					}
				}
				break;
			default:
				$settings = apply_filters( 'feedzy_save_tab_settings', $settings, $post_tab );
		}

		update_option( 'feedzy-settings', $settings );
		if ( ! empty( $settings['general'] ) ) {
			update_option( 'feedzy_rss_feeds_logger_flag', $settings['general']['feedzy-telemetry'] ? 'yes' : false );
		}
	}

	/**
	 * Method to get the settings.
	 *
	 * @access  public
	 */
	public function get_settings() {
		$settings = get_option( 'feedzy-settings' );

		return $settings;
	}

	/**
	 * Set up the HTTP parameters/headers.
	 * 
	 * @param string $url The URl.
	 *
	 * @access  public
	 */
	public function pre_http_setup( $url ) {
		$this->add_proxy( $url );
		add_filter( 'http_headers_useragent', array( $this, 'add_user_agent' ) );

		// phpcs:ignore WordPressVIPMinimum.Hooks.RestrictedHooks.http_request_args
		add_filter( 'http_request_args', array( $this, 'http_request_args' ) );
	}

	/**
	 * Add the proxy settings as specified in the settings.
	 * 
	 * @param string $url The url.
	 * 
	 * @return void
	 *
	 * @access  private
	 */
	private function add_proxy( $url ) {
		$settings = apply_filters( 'feedzy_get_settings', null );
		if ( $settings && isset( $settings['proxy'] ) && is_array( $settings['proxy'] ) && ! empty( $settings['proxy'] ) ) {
			// if even one constant is defined, escape.
			if ( defined( 'WP_PROXY_HOST' ) || defined( 'WP_PROXY_PORT' ) || defined( 'WP_PROXY_USERNAME' ) || defined( 'WP_PROXY_PASSWORD' ) ) {
				Feedzy_Rss_Feeds_Log::info(
					'Some proxy constants already defined; ignoring proxy settings',
					array(
						'url'      => $url,
						'settings' => $settings['proxy'],
					)
				);

				return;
			}

			$proxied = false;
			if ( isset( $settings['proxy']['host'] ) && ! empty( $settings['proxy']['host'] ) ) {
				$proxied = true;
				define( 'WP_PROXY_HOST', $settings['proxy']['host'] );
			}
			if ( isset( $settings['proxy']['port'] ) && ! empty( $settings['proxy']['port'] ) ) {
				$proxied = true;
				define( 'WP_PROXY_PORT', $settings['proxy']['port'] );
			}
			if ( isset( $settings['proxy']['user'] ) && ! empty( $settings['proxy']['user'] ) ) {
				$proxied = true;
				define( 'WP_PROXY_USERNAME', $settings['proxy']['user'] );
			}
			if ( isset( $settings['proxy']['pass'] ) && ! empty( $settings['proxy']['pass'] ) ) {
				$proxied = true;
				define( 'WP_PROXY_PASSWORD', $settings['proxy']['pass'] );
			}

			// temporary constant for use in the pre_http_send_through_proxy filter.
			if ( $proxied && ! defined( 'FEEZY_URL_THRU_PROXY' ) ) {
				define( 'FEEZY_URL_THRU_PROXY', $url );
			}
			add_filter( 'pre_http_send_through_proxy', array( $this, 'send_through_proxy' ), 10, 4 );
		}
	}

	/**
	 * Add additional HTTP request args.
	 *
	 * @param array $args HTTP request arguments.
	 * @return array
	 */
	public function http_request_args( $args ) {
		// allow private IPs.
		$args['reject_unsafe_urls'] = false;
		// allow SSLs to go through without certificate verification.
		$args['sslverify'] = false;
		// Set timeout. (Manual review: timeout is high for VIP).
		$args['timeout'] = 300; // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout

		return $args;
	}

	/**
	 * Add the user agent if specified in the settings.
	 *
	 * @param string $ua User agent.
	 * @return string
	 */
	public function add_user_agent( $ua ) {
		$settings = apply_filters( 'feedzy_get_settings', null );
		if ( $settings && isset( $settings['header']['user-agent'] ) && ! empty( $settings['header']['user-agent'] ) ) {
			Feedzy_Rss_Feeds_Log::info(
				'Overriding user-agent',
				array(
					'old_user_agent' => $ua,
					'new_user_agent' => $settings['header']['user-agent'],
				)
			);

			$ua = $settings['header']['user-agent'];
		}

		return $ua;
	}

	/**
	 * Check if the uri should go through the proxy.
	 *
	 * @param mixed  $return_value Return value.
	 * @param string $uri URI.
	 * @param mixed  $check Check value.
	 * @param mixed  $home Home value.
	 * @return bool
	 */
	public function send_through_proxy( $return_value, $uri, $check, $home ) {
		$proxied = defined( 'FEEZY_URL_THRU_PROXY' ) ? FEEZY_URL_THRU_PROXY : null;
		if ( $proxied && ( ( is_array( $proxied ) && in_array( $uri, $proxied, true ) ) || $uri === $proxied ) ) {
			Feedzy_Rss_Feeds_Log::info(
				'Sending through proxy',
				array(
					'uri'   => $uri,
					'check' => $check,
					'home'  => $home,
				)
			);

			return true;
		}

		return false;
	}

	/**
	 * Teardown the HTTP parameters/headers.
	 * 
	 * @param string $url The URL.
	 * 
	 * @return void
	 *
	 * @access  public
	 */
	public function post_http_teardown( $url ) {
		remove_filter( 'http_headers_useragent', array( $this, 'add_user_agent' ) );
	}

	/**
	 * Check if plugin has been activated and then redirect to the correct page.
	 *
	 * @access  public
	 */
	public function admin_init() {
		if ( defined( 'TI_UNIT_TESTING' ) ) {
			return;
		}

		// Check if we're on the import page and our importer is being run.
		if ( isset( $_GET['import'] ) && 'feedzy-rss-feeds' === $_GET['import'] && current_user_can( 'import' ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( feedzy_is_legacyv5() || self::get_no_of_imports() < 1 ) {
				wp_safe_redirect( admin_url( 'post-new.php?post_type=feedzy_imports' ) );
				exit;
			} else {
				wp_safe_redirect( admin_url( 'edit.php?post_type=feedzy_imports' ) );
				exit;
			}
			delete_option( 'feedzy-activated' );
		}
		if ( get_option( 'feedzy-activated' ) ) {
			delete_option( 'feedzy-activated' );
			$reference_key = get_option( 'feedzy_reference_key', '' );
			if ( ! headers_sent() ) {
				$redirect_url = add_query_arg(
					array(
						'page' => 'feedzy-support',
						'tab'  => 'help#shortcode',
					),
					admin_url( 'admin.php' )
				);
				if ( str_starts_with( $reference_key, 'i-' ) ) {
					$redirect_url = admin_url( 'post-new.php?post_type=feedzy_imports' );
				} elseif ( str_starts_with( $reference_key, 'e-' ) ) {
					return;
				} elseif ( ! feedzy_is_pro() && ! empty( get_option( 'feedzy_fresh_install', false ) ) ) {
					$redirect_url = add_query_arg(
						array(
							'page' => 'feedzy-setup-wizard',
							'tab'  => '#step-1',
						),
						admin_url( 'admin.php' )
					);
				}
				wp_safe_redirect( $redirect_url );
				exit;
			}
		}
	}

	/**
	 * Validates the URLs and removes the ones that were found to be invalid.
	 *
	 * @param mixed  $check      Check value.
	 * @param int    $object_id  Object ID.
	 * @param string $meta_key   Meta key.
	 * @param mixed  $meta_value Meta value.
	 * @param mixed  $prev_value Previous value.
	 * @return mixed
	 */
	public function validate_category_feeds( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		if ( 'feedzy_category_feed' === $meta_key && 'feedzy_categories' === get_post_type( $object_id ) ) {
			remove_filter( current_filter(), array( $this, 'validate_category_feeds' ) );
			$valid = $this->check_source_validity( $meta_value, $object_id, true, true );
			update_post_meta( $object_id, $meta_key, empty( $valid ) ? '' : implode( ', ', $valid ) );
			return true;
		}

		return $check;
	}

	/**
	 * Validates the source (category or URL(s)) and returns only the ones that were found to be valid.
	 *
	 * @param string $src                   Source string.
	 * @param int    $post_id               Post ID.
	 * @param bool   $add_pseudo_transient  Add pseudo transient.
	 * @param bool   $return_valid          Return valid.
	 * @return array
	 */
	public function check_source_validity( $src, $post_id, $add_pseudo_transient, $return_valid ) {
		$urls_in   = $src;
		$post_type = get_post_type( $post_id );
		if ( 'feedzy_imports' === $post_type && false === strpos( $src, 'http' ) && false === strpos( $src, 'https' ) ) {
			// category.
			$category = get_page_by_path( $src, OBJECT, 'feedzy_categories' );
			if ( $category ) {
				$urls_in = get_post_meta( $category->ID, 'feedzy_category_feed', true );
			}
		}

		// This method is fired through ajax when the category title is updated,
		// even without clicking the publish button,
		// thereby sending empty urls.
		if ( empty( $urls_in ) ) {
			return array();
		}

		$urls = $this->normalize_urls( $urls_in );
		if ( ! is_array( $urls ) ) {
			$urls = array( $urls );
		}
		$valid   = $this->get_valid_source_urls( $urls, '1_mins', false );
		$invalid = array_diff( $urls, $valid );
		if ( $add_pseudo_transient && ( empty( $valid ) || ! empty( $invalid ) ) ) {
			// Let's save the invalid urls in a pseudo-transient so that we can show it in the import edit screen.
			switch ( $post_type ) {
				case 'feedzy_categories':
					update_post_meta( $post_id, '__transient_feedzy_category_feed', $invalid );
					break;
				case 'feedzy_imports':
					$invalid_dc_namespace  = get_post_meta( $post_id, '__transient_feedzy_invalid_dc_namespace', true );
					$invalid_source_errors = get_post_meta( $post_id, '__transient_feedzy_invalid_source_errors', true );
					if ( empty( $invalid_dc_namespace ) && empty( $invalid_source_errors ) ) {
						update_post_meta( $post_id, '__transient_feedzy_invalid_source', $invalid );
					}
					break;
			}
		}

		if ( is_null( $return_valid ) ) {
			return array(
				'valid'   => $valid,
				'invalid' => $invalid,
			);
		}

		if ( $return_valid ) {
			return $valid;
		}

		return $invalid;
	}

	/**
	 * Returns the error message to display if invalid URLs are found in the source (category or URL(s)).
	 *
	 * @param string $message Message.
	 * @param object $post    Post object.
	 * @param string $css_class   CSS class.
	 * @return string
	 */
	public function get_source_validity_error( $message = '', $post = '', $css_class = '' ) {
		$invalid = null;
		$text    = null;
		switch ( $post->post_type ) {
			case 'feedzy_categories':
				$text    = __( 'We found the following invalid or unreachable by WordPress SimplePie URLs that we have removed from the list', 'feedzy-rss-feeds' );
				$invalid = get_post_meta( $post->ID, '__transient_feedzy_category_feed', true );
				delete_post_meta( $post->ID, '__transient_feedzy_category_feed' );
				break;
			case 'feedzy_imports':
				$invalid_source        = get_post_meta( $post->ID, '__transient_feedzy_invalid_source', true );
				$invalid_dc_namespace  = get_post_meta( $post->ID, '__transient_feedzy_invalid_dc_namespace', true );
				$invalid_source_errors = get_post_meta( $post->ID, '__transient_feedzy_invalid_source_errors', true );
				if ( $invalid_source ) {
					$text    = __( 'This source has invalid or unreachable by WordPress SimplePie URLs. Please correct/remove the following', 'feedzy-rss-feeds' );
					$invalid = $invalid_source;
					delete_post_meta( $post->ID, '__transient_feedzy_invalid_source' );
				} elseif ( $invalid_dc_namespace ) {
					$text    = __( 'Please enter a valid feed URL to import the author', 'feedzy-rss-feeds' );
					$invalid = $invalid_dc_namespace;
					delete_post_meta( $post->ID, '__transient_feedzy_invalid_dc_namespace' );
				} elseif ( $invalid_source_errors ) {
					$source_type = ! empty( $invalid_source_errors['source_type'] ) ? $invalid_source_errors['source_type'] : '';
					$text        = join( ', ', $invalid_source_errors['errors'] );
					$text        = $source_type . preg_replace( '/\.$/', '', $text );
					$invalid     = $invalid_source_errors['source'];
					delete_post_meta( $post->ID, '__transient_feedzy_invalid_source_errors' );
				}
				break;
			default:
				return $message;
		}

		if ( $invalid ) {
			if ( empty( $css_class ) ) {
				$css_class = 'notice notice-error notice-alt fz-notice feedzy-error-critical';
			}
			$message .= '<div class="' . $css_class . '"><p style="color: inherit"><i class="dashicons dashicons-warning"></i>' . $text . ': <ol style="color: inherit">';
			foreach ( $invalid as $url ) {
				$message .= '<li>' . ( empty( $url ) ? __( 'Empty URL', 'feedzy-rss-feeds' ) : esc_html( $url ) ) . '</li>';
			}
			$message .= '</ol></p></div>';
		}
		return $message;
	}

	/**
	 * AJAX single-entry method.
	 *
	 * @since   3.4.1
	 * @access  public
	 */
	public function ajax() {
		check_ajax_referer( FEEDZY_NAME, 'security' );

		$post_action = isset( $_POST['_action'] ) ? sanitize_text_field( wp_unslash( $_POST['_action'] ) ) : '';
		$post_id     = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : '';

		switch ( $post_action ) {
			case 'validate_clean':
				// remove invalid URLs from this category.
				$urls    = get_post_meta( $post_id, 'feedzy_category_feed', true );
				$return  = $this->check_source_validity( $urls, $post_id, false, null );
				$valid   = $return['valid'];
				$invalid = $return['invalid'];
				if ( ! empty( $valid ) ) {
					remove_filter( 'update_post_metadata', array( $this, 'validate_category_feeds' ) );
					update_post_meta( $post_id, 'feedzy_category_feed', implode( ', ', $valid ) );
				}
				wp_send_json_success( array( 'invalid' => count( $invalid ) ) );
				break;
			case 'validate_feeds_group':
				$feeds = isset( $_POST['feeds'] ) ? sanitize_text_field( wp_unslash( $_POST['feeds'] ) ) : '';
				if ( empty( $feeds ) ) {
					wp_send_json_error( __( 'No feeds provided for validation.', 'feedzy-rss-feeds' ) );
				}
				$feeds = $this->normalize_urls( $feeds );
				if ( ! is_array( $feeds ) ) {
					$feeds = array( $feeds );
				}
				$valid   = $this->get_valid_source_urls( $feeds, '1_mins', false );
				$invalid = array_diff( $feeds, $valid );
				wp_send_json_success(
					array(
						'valid'   => $valid,
						'invalid' => $invalid,
					)
				);
		}
	}

	/**
	 * Remove elementor register feature.
	 *
	 * @param object $manager_object Manager class object.
	 * @param array  $experimental_data Experimental data.
	 */
	public function feedzy_remove_elementor_feature( $manager_object, $experimental_data ) {
		$manager_object->remove_feature( 'e_hidden_wordpress_widgets' );
	}

	/**
	 * Remove legacy widget.
	 *
	 * @param array<string> $black_list Black list widgets.
	 * @return array
	 */
	public function feedzy_remove_elementor_widgets( $black_list ) {
		global $post;

		if ( ! defined( 'ELEMENTOR_PATH' ) || ! class_exists( '\Elementor\Plugin', false ) ) {
			return $black_list;
		}

		if ( ! $post || ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return $black_list;
		}

		if ( ! method_exists( \Elementor\Plugin::$instance->documents, 'get' ) ) {
			return $black_list;
		}

		$exclude_widgets = array( 'feedzy_wp_widget' );
		$data            = \Elementor\Plugin::$instance->documents->get( $post->ID )->get_elements_data();

		if ( ! empty( $data ) ) {
			\Elementor\Plugin::$instance->db->iterate_data(
				$data,
				function ( $element ) use ( &$exclude_widgets ) {
					if ( ! empty( $element['widgetType'] ) && 'wp-widget-feedzy_wp_widget' === $element['widgetType'] ) {
						$exclude_widgets = array();
					}
				}
			);
		}
		return array_merge( $black_list, $exclude_widgets );
	}

	/**
	 * Add classes to make the wizard full screen.
	 *
	 * @param string $classes Body classes.
	 * @return string
	 */
	public function add_wizard_classes( $classes ) {
		if ( get_option( 'feedzy_fresh_install', false ) ) {
			$classes .= ' feedzy-wizard-fullscreen';
		}
		return trim( $classes );
	}

	/**
	 * Method to register the setup wizard page.
	 *
	 * @access  public
	 */
	public function feedzy_setup_wizard_page() {
		include FEEDZY_ABSPATH . '/includes/layouts/setup-wizard.php';
	}

	/**
	 * Load setup wizard page.
	 */
	public function feedzy_load_setup_wizard_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && 'feedzy-setup-wizard' === $_GET['page'] ) {
			remove_all_actions( 'admin_notices' );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'feedzy_enqueue_setup_wizard_scripts' ) );
	}

	/**
	 * Enqueue setup wizard required scripts.
	 */
	public function feedzy_enqueue_setup_wizard_scripts() {
		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		wp_enqueue_style( $this->plugin_name . '_chosen' );
		wp_enqueue_style( $this->plugin_name . '_smart_wizard', FEEDZY_ABSURL . 'css/smart_wizard_all.min.css', array(), $this->version );
		wp_enqueue_style( $this->plugin_name . '_setup_wizard', FEEDZY_ABSURL . 'includes/views/css/style-wizard.css', array( $this->plugin_name . '-settings' ), $this->version, 'all' );

		wp_enqueue_script( $this->plugin_name . '_jquery_smart_wizard', FEEDZY_ABSURL . 'js/jquery.smartWizard.min.js', array( 'jquery', 'clipboard', $this->plugin_name . '_chosen_script' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name . '_setup_wizard', FEEDZY_ABSURL . 'js/feedzy-setup-wizard.js', array( 'jquery' ), $this->version, true );
		wp_localize_script(
			$this->plugin_name . '_setup_wizard',
			'feedzySetupWizardData',
			array(
				'adminPage'           => add_query_arg( 'post_type', 'feedzy_imports', admin_url( 'edit.php' ) ),
				'ajax'                => array(
					'url'      => admin_url( 'admin-ajax.php' ),
					'security' => wp_create_nonce( FEEDZY_BASEFILE ),
				),
				'errorMessages'       => array(
					'requiredEmail' => __( 'This field is required.', 'feedzy-rss-feeds' ),
					'invalidEmail'  => __( 'Please enter a valid email address.', 'feedzy-rss-feeds' ),
				),
				'nextButtonText'      => __( 'Next Step', 'feedzy-rss-feeds' ),
				'backButtonText'      => __( 'Back', 'feedzy-rss-feeds' ),
				'draftPageButtonText' => array(
					'firstButtonText'  => __( 'Create Page', 'feedzy-rss-feeds' ),
					'secondButtonText' => __( 'Do not create', 'feedzy-rss-feeds' ),
				),
				'mediaUploadText'     => array(
					'iframeTitle'           => __( 'Select image', 'feedzy-rss-feeds' ),
					'iframeButton'          => __( 'Set default image', 'feedzy-rss-feeds' ),
					'actionButtonTextOne'   => __( 'Choose image', 'feedzy-rss-feeds' ),
					'actionButtonTextTwo'   => __( 'Replace image', 'feedzy-rss-feeds' ),
					'actionButtonTextThree' => __( 'Remove image', 'feedzy-rss-feeds' ),
				),
				'dryRun'              => array(
					'loading' => '<p class="hide-when-loaded">' . __( 'Processing the source and loading the items that will be imported when it runs', 'feedzy-rss-feeds' ) . '...</p>'
							. '<p class="loading-img hide-when-loaded"><img src="' . includes_url( 'images/wpspin-2x.gif' ) . '"></p><div></div>',
					'title'   => __( 'Importable Items', 'feedzy-rss-feeds' ),
				),
			)
		);
	}

	/**
	 * Dismiss setup wizard.
	 *
	 * @param bool $redirect_to_dashboard Redirect to dashboard.
	 * @return bool|void
	 */
	public function feedzy_dismiss_wizard( $redirect_to_dashboard = true ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_REQUEST['status'] ) ? (int) $_REQUEST['status'] : 0;
		update_option( 'feedzy_fresh_install', $status );
		delete_option( 'feedzy_wizard_data' );
		if ( false !== $redirect_to_dashboard ) {

			$cleaned_url = remove_query_arg( array( 'page', 'action', 'status' ) );
			$parsed_url  = wp_parse_url( $cleaned_url );

			// Default to dashboard if no page is set.
			if (
				isset( $parsed_url['path'] ) && 
				strpos( $parsed_url['path'], '/wp-admin/admin.php' ) !== false && 
				empty( $parsed_url['query'] )
			) {
				$cleaned_url = add_query_arg( 'page', 'feedzy-support', $cleaned_url );
			}
		
			wp_safe_redirect( $cleaned_url );
			exit;
		}
		return true;
	}

	/**
	 * Setup wizard process.
	 */
	public function feedzy_wizard_step_process() {
		if ( ! feedzy_current_user_can() ) {
			return wp_send_json( array( 'status' => 0 ) );
		}

		check_ajax_referer( FEEDZY_BASEFILE, 'security' );
		$step = ! empty( $_POST['step'] ) ? sanitize_text_field( $_POST['step'] ) : 1;
		switch ( $step ) {
			case 'step_2':
				$this->setup_wizard_import_feed();
				break;
			case 'step_3':
				$this->setup_wizard_install_plugin();
				break;
			case 'step_4':
				$this->setup_wizard_subscribe_process();
				break;
			case 'create_draft_page':
				$this->setup_wizard_create_draft_page();
				break;
			default:
				wp_send_json( array( 'status' => 0 ) );
				break;
		}
	}

	/**
	 * Step: 2 import feed.
	 */
	private function setup_wizard_import_feed() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$feed_url = ! empty( $_POST['feed'] ) ? sanitize_text_field( $_POST['feed'] ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$integrate_with = ! empty( $_POST['integrate_with'] ) ? sanitize_text_field( $_POST['integrate_with'] ) : '';

		$feed_url = $this->normalize_urls( $feed_url );
		if ( ! is_array( $feed_url ) ) {
			$feed_url = array( $feed_url );
		}
		$response = array(
			'status' => 1,
		);
		$feed     = $this->fetch_feed( $feed_url, '1_mins', array( '' ) );
		if ( empty( $feed->error() ) ) {
			$wizard_data = array(
				'feed'           => implode( ',', $feed_url ),
				'integrate_with' => $integrate_with,
			);
			update_option( 'feedzy_wizard_data', $wizard_data );
			// Create draft page.
			if ( 'page_builder' === $integrate_with ) {
				$type = 'block_editor';
				if ( defined( 'ELEMENTOR_PATH' ) && class_exists( 'Elementor\Widget_Base' ) ) {
					$type = 'elementor_editor';
				}
				$this->setup_wizard_create_draft_page( $type, true );
			}
		} else {
			$response['status']  = 0;
			$response['message'] = sprintf( '<p><strong>%s</strong></p>', apply_filters( 'feedzy_default_error', $feed->error(), $feed, $feed_url ) );
		}
		wp_send_json( $response );
	}

	/**
	 * Step: 3 Install plugin.
	 */
	private function setup_wizard_install_plugin() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$slug = ! empty( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';

		if ( empty( $slug ) ) {
			wp_send_json(
				array(
					'status'  => 0,
					'message' => __( 'No plugin specified.', 'feedzy-rss-feeds' ),
				)
			);
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json(
				array(
					'status'  => 0,
					'message' => __( 'Sorry, you are not allowed to install plugins on this site.', 'feedzy-rss-feeds' ),
				)
			);
		}

		if ( ! empty( $slug ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'fields' => array(
						'sections' => false,
					),
				)
			);

			if ( is_wp_error( $api ) ) {
				wp_send_json(
					array(
						'status'  => 0,
						'message' => $api->get_error_message(),
					)
				);
			}

			$skin     = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$result   = $upgrader->install( $api->download_link );
			if ( is_wp_error( $result ) ) {
				wp_send_json(
					array(
						'status'  => 0,
						'message' => $api->get_error_message(),
					)
				);
			} elseif ( is_wp_error( $skin->result ) ) {
				if ( 'folder_exists' !== $skin->result->get_error_code() ) {
					wp_send_json(
						array(
							'status'  => 0,
							'message' => $skin->result->get_error_message(),
						)
					);
				}
			} elseif ( $skin->get_errors()->has_errors() ) {
				if ( 'folder_exists' !== $skin->get_error_code() ) {
					wp_send_json(
						array(
							'status'  => 0,
							'message' => $skin->get_error_message(),
						)
					);
				}
			} elseif ( is_null( $result ) ) {
				global $wp_filesystem;
				$status            = array();
				$status['message'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'feedzy-rss-feeds' );

				// Pass through the error from WP_Filesystem if one was raised.
				if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors() ) {
					$status['message'] = esc_html( $wp_filesystem->errors->get_error_message() );
				}

				wp_send_json( $status );
			}

			activate_plugin( 'optimole-wp/optimole-wp.php' );
			delete_transient( 'optml_fresh_install' );
			$wizard_data = get_option( 'feedzy_wizard_data', array() );

			$wizard_data['enable_perfomance'] = true;
			update_option( 'feedzy_wizard_data', $wizard_data );

			wp_send_json(
				array(
					'status' => 1,
				)
			);
		}
	}

	/**
	 * Step: 4 skip and subscribe process.
	 */
	private function setup_wizard_subscribe_process() {
		check_ajax_referer( FEEDZY_BASEFILE, 'security' );

		$segment        = 0;
		$wizard_data    = get_option( 'feedzy_wizard_data', array() );
		$integrate_with = ! empty( $wizard_data['integrate_with'] ) ? $wizard_data['integrate_with'] : '';
		$page_id        = ! empty( $wizard_data['page_id'] ) ? $wizard_data['page_id'] : '';
		$job_id         = ! empty( $wizard_data['job_id'] ) ? $wizard_data['job_id'] : '';
		$response       = array(
			'status'      => 0,
			'redirect_to' => '',
			'message'     => '',
		);

		$with_subscribe = ! empty( $_POST['with_subscribe'] ) ? (bool) $_POST['with_subscribe'] : '';
		$email          = ! empty( $_POST['email'] ) ? filter_input( INPUT_POST, 'email', FILTER_SANITIZE_EMAIL ) : '';
		if ( empty( $integrate_with ) ) {
			$integrate_with = ! empty( $_POST['integrate_with'] ) ? sanitize_text_field( $_POST['integrate_with'] ) : '';
		}

		if ( 'feed' === $integrate_with ) {
			$segment  = 1;
			$response = array(
				'status'      => 1,
				'redirect_to' => add_query_arg(
					array(
						'page' => 'feedzy-support',
					),
					admin_url( 'admin.php' )
				),
				'message'     => __( 'Redirecting to Feedzy dashboard', 'feedzy-rss-feeds' ),
			);

			if ( $job_id ) {
				$response['redirect_to'] = add_query_arg(
					array(
						'post'   => $job_id,
						'action' => 'edit',
					),
					admin_url( 'post.php' )
				);
				$response['message']     = __( 'Redirecting to import feed', 'feedzy-rss-feeds' );
			}
		} elseif ( 'shortcode' === $integrate_with ) {
			$segment = 2;
			if ( ! empty( $page_id ) ) {
				$response = array(
					'status'      => 1,
					'redirect_to' => get_edit_post_link( $page_id, 'db' ),
					'message'     => __( 'Redirecting to draft page', 'feedzy-rss-feeds' ),
				);
			} else {
				$response = array(
					'status'      => 1,
					'redirect_to' => add_query_arg(
						array(
							'page' => 'feedzy-support',
						),
						admin_url( 'admin.php' )
					),
					'message'     => __( 'Redirecting to Feedzy dashboard', 'feedzy-rss-feeds' ),
				);
			}
		} elseif ( 'page_builder' === $integrate_with ) {
			if ( empty( $page_id ) ) {
				$response = array(
					'status'      => 1,
					'redirect_to' => add_query_arg(
						array(
							'page' => 'feedzy-support',
						),
						admin_url( 'admin.php' )
					),
					'message'     => __( 'Redirecting to Feedzy dashboard', 'feedzy-rss-feeds' ),
				);
			} else {
				$post_edit_link = get_edit_post_link( $page_id, 'db' );
				// Get elementor edit page link.
				if ( defined( 'ELEMENTOR_PATH' ) && class_exists( 'Elementor\Widget_Base' ) ) {
					$segment        = 3;
					$post_edit_link = add_query_arg(
						array(
							'post'   => $page_id,
							'action' => 'elementor',
						),
						admin_url( 'post.php' )
					);
				} else {
					$segment = 4;
				}
				$response = array(
					'status'      => 1,
					'redirect_to' => $post_edit_link,
					'message'     => __( 'Redirecting to draft page', 'feedzy-rss-feeds' ),
				);
			}
		}
		if ( $with_subscribe && is_email( $email ) ) {
			update_option( 'feedzy_rss_feeds_logger_flag', 'yes' );

			$extra_data = array(
				'segment' => $segment,
			);

			if ( ! empty( $integrate_with ) ) {
				$extra_data['integration_scope_onboarding'] = $integrate_with;
			}

			$request_res = wp_remote_post(
				FEEDZY_SUBSCRIBE_API,
				array(
					'timeout' => 100, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
					'headers' => array(
						'Content-Type'  => 'application/json',
						'Cache-Control' => 'no-cache',
						'Accept'        => 'application/json, */*;q=0.1',
					),
					'body'    => wp_json_encode(
						array(
							'slug'  => 'feedzy-rss-feeds',
							'site'  => home_url(),
							'email' => $email,
							'data'  => $extra_data,
						)
					),
				)
			);
			if ( ! is_wp_error( $request_res ) ) {
				$body = json_decode( wp_remote_retrieve_body( $request_res ) );
				if ( 'success' === $body->code ) {
					update_option( 'feedzy_onboarding_user_subscribed', 'yes' );
					$this->feedzy_dismiss_wizard( false );
					wp_send_json( $response );
				}
			}
			wp_send_json(
				array(
					'status'      => 0,
					'redirect_to' => '',
					'message'     => '',
				)
			);
		} else {
			$this->feedzy_dismiss_wizard( false );
			wp_send_json( $response );
		}
	}

	/**
	 * AJAX method to subscribe user to Feedzy newsletter via dashboard notice.
	 *
	 * @since 5.1.0
	 * @access public
	 * @return void
	 */
	public function feedzy_dashboard_subscribe() {
		check_ajax_referer( 'feedzy_subscribe_nonce', '_wpnonce' );
		
		$email = ! empty( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$skip  = ! empty( $_POST['skip'] ) ? sanitize_text_field( wp_unslash( $_POST['skip'] ) ) : '';

		if ( 'yes' === $skip ) {
			$this->dismiss_subscribe_notice();
			wp_send_json_success();
		}

		if ( empty( $email ) || ! is_email( $email ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Validation failed', 'feedzy-rss-feeds' ),
				)
			);
		}
		
		update_option( 'feedzy_rss_feeds_logger_flag', 'yes' );
		
		$request_res = wp_remote_post(
			FEEDZY_SUBSCRIBE_API,
			array(
				'timeout' => 100, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'slug'  => 'feedzy-rss-feeds',
						'site'  => home_url(),
						'email' => $email,
						'data'  => array( 'source' => 'dashboard-notice' ),
					)
				),
			)
		);
		
		if ( ! is_wp_error( $request_res ) ) {
			$this->dismiss_subscribe_notice();
			wp_send_json_success();
		} else {
			wp_send_json_error(
				array(
					'message' => $request_res->get_error_message(),
				)
			);
		}
	}

	/**
	 * Dismiss subscribe notice.
	 *
	 * @since 5.1.0
	 * @return void
	 */
	public function dismiss_subscribe_notice() {
		update_option( 'feedzy_dismiss_subscribe_notice_dashboard', 'yes' );
	}

	/**
	 * Create draft page.
	 *
	 * @param string $type Page type.
	 * @param bool   $return_page_id Page ID.
	 */
	private function setup_wizard_create_draft_page( $type = 'shortcode', $return_page_id = false ) {
		$add_basic_shortcode = ! empty( $_POST['add_basic_shortcode'] ) ? sanitize_text_field( wp_unslash( $_POST['add_basic_shortcode'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$add_basic_shortcode = 'true' === $add_basic_shortcode ? true : false;
		$basic_shortcode     = ! empty( $_POST['basic_shortcode'] ) ? sanitize_text_field( wp_unslash( $_POST['basic_shortcode'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// Do not create draft page.
		if ( 'shortcode' === $type && false === $add_basic_shortcode ) {
			wp_send_json(
				array(
					'status' => 1,
				)
			);
		}

		$wizard_data = get_option( 'feedzy_wizard_data', array() );
		if ( 'block_editor' === $type ) {
			$add_basic_shortcode = true;
			$basic_shortcode     = $this->feedzy_block_editor_content( $wizard_data );
		}
		$post_title = __( 'Feedzy Demo Page', 'feedzy-rss-feeds' );
		$page_id    = post_exists( $post_title, '', '', 'page' );
		$args       = array(
			'post_type'    => 'page',
			'post_title'   => $post_title,
			'post_content' => $add_basic_shortcode ? $basic_shortcode : '',
			'post_status'  => 'draft',
		);
		if ( ! $page_id ) {
			$page_id = wp_insert_post( $args );
		} else {
			$args['ID'] = $page_id;
			$page_id    = wp_update_post( $args );
		}

		if ( $page_id ) {
			// Delete previous meta data.
			$meta = get_post_meta( $page_id );
			foreach ( $meta as $key => $value ) {
				delete_post_meta( $page_id, $key );
			}
			// Create elementor page with feedzy widgets.
			if ( 'elementor_editor' === $type ) {
				$this->feedzy_make_elementor_page( $wizard_data, $page_id );
			}
			// Update wizard data.
			$wizard_data['page_id'] = $page_id;
			update_option( 'feedzy_wizard_data', $wizard_data );
		}
		if ( $return_page_id ) {
			return $page_id;
		}
		wp_send_json(
			array(
				'status' => $page_id,
			)
		);
	}

	/**
	 * Create post content for block editor.
	 *
	 * @param array $wizard_data Setup wizard data.
	 * @return string
	 */
	public function feedzy_block_editor_content( $wizard_data = array() ) {
		$post_content = '';
		if ( empty( $wizard_data['feed'] ) ) {
			return $post_content;
		}

		$feed_url     = $wizard_data['feed'];
		$post_content = '<!-- wp:feedzy-rss-feeds/loop {"feed":{"type":"url","source":["' . esc_url( $feed_url ) . '"]}} --><!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30","right":"var:preset|spacing|30"},"margin":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} --><div class="wp-block-group" style="margin-top:var(--wp--preset--spacing--30);margin-bottom:var(--wp--preset--spacing--30);padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--30)"><!-- wp:image --><figure class="wp-block-image"><a href="{{feedzy_url}}"><img src="' . esc_url( FEEDZY_ABSURL . 'img/feedzy.svg' ) . '" alt="{{feedzy_title}}"/></a></figure><!-- /wp:image --><!-- wp:paragraph --><p><a href="{{feedzy_url}}">{{feedzy_title}}</a></p><!-- /wp:paragraph --><!-- wp:paragraph {"fontSize":"medium"} --><p class="has-medium-font-size">{{feedzy_meta}}</p><!-- /wp:paragraph --><!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">{{feedzy_description}}</p><!-- /wp:paragraph --></div><!-- /wp:group --><!-- /wp:feedzy-rss-feeds/loop -->';
		return $post_content;
	}

	/**
	 * Create elementor page with feedzy widget.
	 *
	 * @param array $wizard_data Setup wizard data.
	 * @param int   $page_id Page ID.
	 * @return int Page ID.
	 */
	public function feedzy_make_elementor_page( $wizard_data = array(), $page_id = 0 ) {
		if ( empty( $wizard_data['feed'] ) ) {
			return $page_id;
		}
		$feed_url = $wizard_data['feed'];
		update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			update_post_meta( $page_id, '_elementor_version', ELEMENTOR_VERSION );
		}
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			update_post_meta( $page_id, '_elementor_pro_version', ELEMENTOR_PRO_VERSION );
		}
		$widget_type    = Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'feedzy-rss-feeds' );
		$settings       = array(
			'fz-source' => $feed_url,
		);
		$elementor_data = array(
			array(
				'id'       => Elementor\Utils::generate_random_string(),
				'elType'   => 'section',
				'elements' => array(
					array(
						'id'       => Elementor\Utils::generate_random_string(),
						'elType'   => 'column',
						'settings' => array(
							'_column_size' => 100,
							'_inline_size' => '',
						),
						'elements' => array(
							array(
								'id'         => Elementor\Utils::generate_random_string(),
								'elType'     => $widget_type::get_type(),
								'widgetType' => $widget_type->get_name(),
								'settings'   => $settings,
							),
						),
					),
				),
			),
		);
		update_post_meta( $page_id, '_elementor_data', $elementor_data );
		return $page_id;
	}

	/**
	 * Hide setup wizard menu.
	 */
	public function feedzy_hide_wizard_menu() {
		?>
		<style>
			.toplevel_page_feedzy-admin-menu ul.wp-submenu li:nth-child(6) {
				display: none;
			}
		</style>
		<?php
	}

	/**
	 * Handle upgrade submenu.
	 */
	public function handle_upgrade_submenu() {
		if ( feedzy_is_pro() ) {
			return;
		}
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
				$( '.toplevel_page_feedzy-admin-menu ul.wp-submenu' ).on( 'click', 'a[href*="plugins/feedzy-rss-feeds/upgrade/"]', function( event ) {
					event.preventDefault();
					window.open( $( this ).attr( 'href' ), '_blank').focus();
				} );
			} );
		</script>
		<?php
	}

	/**
	 * API license status.
	 *
	 * @return array
	 */
	public function api_license_status() {
		$pro_options = get_option( 'feedzy-rss-feeds-settings', array() );
		$data        = array(
			'spinnerChiefStatus' => false,
			'wordaiStatus'       => false,
			'openaiStatus'       => false,
			'amazonStatus'       => false,
			'openRouterStatus'   => false,
		);

		if ( ! feedzy_is_pro() ) {
			return $data;
		}

		if ( apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) {
			if ( isset( $pro_options['spinnerchief_licence'] ) && 'yes' === $pro_options['spinnerchief_licence'] ) {
				$data['spinnerChiefStatus'] = true;
			}
			if ( isset( $pro_options['wordai_licence'] ) && 'yes' === $pro_options['wordai_licence'] ) {
				$data['wordaiStatus'] = true;
			}
		}

		if ( isset( $pro_options['openai_licence'] ) && 'yes' === $pro_options['openai_licence'] ) {
			if ( apply_filters( 'feedzy_is_license_of_type', false, 'business' ) || apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) {
				$data['openaiStatus'] = true;
			}
		}

		if ( isset( $pro_options['openrouter_licence'] ) && 'yes' === $pro_options['openrouter_licence'] ) {
			if ( apply_filters( 'feedzy_is_license_of_type', false, 'business' ) || apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) {
				$data['openRouterStatus'] = true;
			}
		}

		if ( ! empty( $pro_options['amazon_access_key'] ) && ! empty( $pro_options['amazon_secret_key'] ) ) {
			$data['amazonStatus'] = true;
		}
		return $data;
	}

	/**
	 * Get the plan category for the product plan ID.
	 *
	 * @param object $license_data The license data.
	 * @return int
	 */
	public static function plan_category( $license_data ) {

		if ( ! isset( $license_data->plan ) || ! is_numeric( $license_data->plan ) ) {
			return 0; // Free.
		}

		$plan             = (int) $license_data->plan;
		$current_category = -1;

		$categories = array(
			'1' => array( 1, 4, 9 ), // Personal.
			'2' => array( 2, 5, 8 ), // Business/Developer.
			'3' => array( 3, 6, 7, 10 ), // Agency.
		);

		foreach ( $categories as $category => $plans ) {
			if ( in_array( $plan, $plans, true ) ) {
				$current_category = (int) $category;
				break;
			}
		}

		return $current_category;
	}

	/**
	 * Get the data used for the survey.
	 *
	 * @return array
	 * @see survey.js
	 */
	public function get_survey_data() {
		$license_data       = get_option( 'feedzy_rss_feeds_pro_license_data', array() );
		$integration_status = $this->api_license_status();

		$days_since_install = round( ( time() - get_option( 'feedzy_rss_feeds_install', time() ) ) / DAY_IN_SECONDS );

		$import_jobs_num = get_transient( 'feedzy_import_jobs_num' );

		if ( false === $import_jobs_num ) {
			$args = array(
				'post_type'              => 'feedzy_imports',
				'posts_per_page'         => 50,
				'post_status'            => array( 'publish', 'draft' ),
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			);

			$query           = new WP_Query( $args );
			$import_jobs_num = count( $query->posts );
			set_transient( 'feedzy_import_jobs_num', $import_jobs_num, 50 >= $import_jobs_num ? WEEK_IN_SECONDS : HOUR_IN_SECONDS );
		} else {
			$import_jobs_num = intval( $import_jobs_num );
		}

		$survey_data = array(
			'environmentId' => 'clskgehf78eu5podwdrnzciti',
			'attributes'    => array(
				'free_version'        => $this->version,
				'pro_version'         => defined( 'FEEDZY_PRO_VERSION' ) ? FEEDZY_PRO_VERSION : '',
				'openai'              => $integration_status['openaiStatus'] ? 'valid' : 'invalid',
				'amazon'              => $integration_status['amazonStatus'] ? 'valid' : 'invalid',
				'spinnerchief'        => $integration_status['spinnerChiefStatus'] ? 'valid' : 'invalid',
				'wordai'              => $integration_status['wordaiStatus'] ? 'valid' : 'invalid',
				'plan'                => $this->plan_category( $license_data ),
				'install_days_number' => $days_since_install,
				'license_status'      => ! empty( $license_data->license ) ? $license_data->license : 'invalid',
				'import_jobs_num'     => $import_jobs_num,
			),
		);

		if ( isset( $license_data->key ) ) {
			$survey_data['attributes']['license_key'] = apply_filters( 'themeisle_sdk_secret_masking', $license_data->key );
		}

		return $survey_data;
	}

	/**
	 * Add banner anchor for promotions.
	 *
	 * @return void
	 */
	public function add_banner_anchor() {
		add_action(
			'admin_notices',
			function () {
				echo '<div id="tsdk_banner" class="notice fz-notice feedzy-banner-dashboard"></div>';
			},
			999
		);
	}

	/**
	 * List of languages supported for translations.
	 */
	public function get_lang_list() {
		$target_lang = array(
			''         => __( 'Choose Language', 'feedzy-rss-feeds' ),
			'eng_Latn' => __( 'English', 'feedzy-rss-feeds' ),
			'ace_Arab' => __( 'Acehnese Arab', 'feedzy-rss-feeds' ),
			'ace_Latn' => __( 'Acehnese Latin', 'feedzy-rss-feeds' ),
			'acm_Arab' => __( 'Mesopotamian Arabic', 'feedzy-rss-feeds' ),
			'acq_Arab' => __( 'Ta’izzi-Adeni Arabic', 'feedzy-rss-feeds' ),
			'aeb_Arab' => __( 'Tunisian Arabic', 'feedzy-rss-feeds' ),
			'afr_Latn' => __( 'Afrikaans', 'feedzy-rss-feeds' ),
			'ajp_Arab' => __( 'South Levantine Arabic', 'feedzy-rss-feeds' ),
			'aka_Latn' => __( 'Akan', 'feedzy-rss-feeds' ),
			'amh_Ethi' => __( 'Amharic', 'feedzy-rss-feeds' ),
			'apc_Arab' => __( 'North Levantine Arabic', 'feedzy-rss-feeds' ),
			'arb_Arab' => __( 'Modern Standard Arabic', 'feedzy-rss-feeds' ),
			'arb_Latn' => __( 'Modern Standard Arabic (Romanized)', 'feedzy-rss-feeds' ),
			'ars_Arab' => __( 'Najdi Arabic', 'feedzy-rss-feeds' ),
			'ary_Arab' => __( 'Moroccan Arabic', 'feedzy-rss-feeds' ),
			'arz_Arab' => __( 'Egyptian Arabic', 'feedzy-rss-feeds' ),
			'asm_Beng' => __( 'Assamese', 'feedzy-rss-feeds' ),
			'ast_Latn' => __( 'Asturian', 'feedzy-rss-feeds' ),
			'awa_Deva' => __( 'Awadhi', 'feedzy-rss-feeds' ),
			'ayr_Latn' => __( 'Central Aymara', 'feedzy-rss-feeds' ),
			'azb_Arab' => __( 'South Azerbaijani', 'feedzy-rss-feeds' ),
			'azj_Latn' => __( 'North Azerbaijani', 'feedzy-rss-feeds' ),
			'bak_Cyrl' => __( 'Bashkir', 'feedzy-rss-feeds' ),
			'bam_Latn' => __( 'Bambara', 'feedzy-rss-feeds' ),
			'ban_Latn' => __( 'Balinese', 'feedzy-rss-feeds' ),
			'bel_Cyrl' => __( 'Belarusian', 'feedzy-rss-feeds' ),
			'bem_Latn' => __( 'Bemba', 'feedzy-rss-feeds' ),
			'ben_Beng' => __( 'Bengali', 'feedzy-rss-feeds' ),
			'bho_Deva' => __( 'Bhojpuri', 'feedzy-rss-feeds' ),
			'bjn_Arab' => __( 'Banjar Arab', 'feedzy-rss-feeds' ),
			'bjn_Latn' => __( 'Banjar Latn', 'feedzy-rss-feeds' ),
			'bod_Tibt' => __( 'Standard Tibetan', 'feedzy-rss-feeds' ),
			'bos_Latn' => __( 'Bosnian', 'feedzy-rss-feeds' ),
			'bug_Latn' => __( 'Buginese Latn', 'feedzy-rss-feeds' ),
			'bul_Cyrl' => __( 'Bulgarian', 'feedzy-rss-feeds' ),
			'cat_Latn' => __( 'Catalan', 'feedzy-rss-feeds' ),
			'ceb_Latn' => __( 'Cebuano', 'feedzy-rss-feeds' ),
			'ces_Latn' => __( 'Czech', 'feedzy-rss-feeds' ),
			'cjk_Latn' => __( 'Chokwe', 'feedzy-rss-feeds' ),
			'ckb_Arab' => __( 'Central Kurdish', 'feedzy-rss-feeds' ),
			'crh_Latn' => __( 'Crimean Tatar', 'feedzy-rss-feeds' ),
			'cym_Latn' => __( 'Welsh', 'feedzy-rss-feeds' ),
			'dan_Latn' => __( 'Danish', 'feedzy-rss-feeds' ),
			'deu_Latn' => __( 'German', 'feedzy-rss-feeds' ),
			'dik_Latn' => __( 'Southwestern Dinka', 'feedzy-rss-feeds' ),
			'dyu_Latn' => __( 'Dyula', 'feedzy-rss-feeds' ),
			'dzo_Tibt' => __( 'Dzongkha', 'feedzy-rss-feeds' ),
			'ell_Grek' => __( 'Greek', 'feedzy-rss-feeds' ),
			'epo_Latn' => __( 'Esperanto', 'feedzy-rss-feeds' ),
			'est_Latn' => __( 'Estonian', 'feedzy-rss-feeds' ),
			'eus_Latn' => __( 'Basque', 'feedzy-rss-feeds' ),
			'ewe_Latn' => __( 'Ewe', 'feedzy-rss-feeds' ),
			'fao_Latn' => __( 'Faroese', 'feedzy-rss-feeds' ),
			'fij_Latn' => __( 'Fijian', 'feedzy-rss-feeds' ),
			'fin_Latn' => __( 'Finnish', 'feedzy-rss-feeds' ),
			'fon_Latn' => __( 'Fon', 'feedzy-rss-feeds' ),
			'fra_Latn' => __( 'French', 'feedzy-rss-feeds' ),
			'fur_Latn' => __( 'Friulian', 'feedzy-rss-feeds' ),
			'fuv_Latn' => __( 'Nigerian Fulfulde', 'feedzy-rss-feeds' ),
			'gla_Latn' => __( 'Scottish Gaelic', 'feedzy-rss-feeds' ),
			'gle_Latn' => __( 'Irish', 'feedzy-rss-feeds' ),
			'glg_Latn' => __( 'Galician', 'feedzy-rss-feeds' ),
			'grn_Latn' => __( 'Guarani', 'feedzy-rss-feeds' ),
			'guj_Gujr' => __( 'Gujarati', 'feedzy-rss-feeds' ),
			'hat_Latn' => __( 'Haitian Creole', 'feedzy-rss-feeds' ),
			'hau_Latn' => __( 'Hausa', 'feedzy-rss-feeds' ),
			'heb_Hebr' => __( 'Hebrew', 'feedzy-rss-feeds' ),
			'hin_Deva' => __( 'Hindi', 'feedzy-rss-feeds' ),
			'hne_Deva' => __( 'Chhattisgarhi', 'feedzy-rss-feeds' ),
			'hrv_Latn' => __( 'Croatian', 'feedzy-rss-feeds' ),
			'hun_Latn' => __( 'Hungarian', 'feedzy-rss-feeds' ),
			'hye_Armn' => __( 'Armenian', 'feedzy-rss-feeds' ),
			'ibo_Latn' => __( 'Igbo', 'feedzy-rss-feeds' ),
			'ilo_Latn' => __( 'Ilocano', 'feedzy-rss-feeds' ),
			'ind_Latn' => __( 'Indonesian', 'feedzy-rss-feeds' ),
			'isl_Latn' => __( 'Icelandic', 'feedzy-rss-feeds' ),
			'ita_Latn' => __( 'Italian', 'feedzy-rss-feeds' ),
			'jav_Latn' => __( 'Javanese', 'feedzy-rss-feeds' ),
			'jpn_Jpan' => __( 'Japanese', 'feedzy-rss-feeds' ),
			'kab_Latn' => __( 'Kabyle', 'feedzy-rss-feeds' ),
			'kac_Latn' => __( 'Jingpho', 'feedzy-rss-feeds' ),
			'kam_Latn' => __( 'Kamba', 'feedzy-rss-feeds' ),
			'kan_Knda' => __( 'Kannada', 'feedzy-rss-feeds' ),
			'kas_Arab' => __( 'Kashmiri Arab', 'feedzy-rss-feeds' ),
			'kas_Deva' => __( 'Kashmiri Devanagari', 'feedzy-rss-feeds' ),
			'kat_Geor' => __( 'Georgian', 'feedzy-rss-feeds' ),
			'knc_Arab' => __( 'Central Kanuri Arab', 'feedzy-rss-feeds' ),
			'knc_Latn' => __( 'Central Kanuri _Latn', 'feedzy-rss-feeds' ),
			'kaz_Cyrl' => __( 'Kazakh', 'feedzy-rss-feeds' ),
			'kbp_Latn' => __( 'Kabiyè', 'feedzy-rss-feeds' ),
			'kea_Latn' => __( 'Kabuverdianu', 'feedzy-rss-feeds' ),
			'khm_Khmr' => __( 'Khmer', 'feedzy-rss-feeds' ),
			'kik_Latn' => __( 'Kikuyu', 'feedzy-rss-feeds' ),
			'kin_Latn' => __( 'Kinyarwanda', 'feedzy-rss-feeds' ),
			'kir_Cyrl' => __( 'Kyrgyz', 'feedzy-rss-feeds' ),
			'kmb_Latn' => __( 'Kimbundu', 'feedzy-rss-feeds' ),
			'kmr_Latn' => __( 'Northern Kurdish', 'feedzy-rss-feeds' ),
			'kon_Latn' => __( 'Kikongo', 'feedzy-rss-feeds' ),
			'kor_Hang' => __( 'Korean', 'feedzy-rss-feeds' ),
			'lao_Laoo' => __( 'Lao', 'feedzy-rss-feeds' ),
			'lij_Latn' => __( 'Ligurian', 'feedzy-rss-feeds' ),
			'lim_Latn' => __( 'Limburgish', 'feedzy-rss-feeds' ),
			'lin_Latn' => __( 'Lingala', 'feedzy-rss-feeds' ),
			'lit_Latn' => __( 'Lithuanian', 'feedzy-rss-feeds' ),
			'lmo_Latn' => __( 'Lombard', 'feedzy-rss-feeds' ),
			'ltg_Latn' => __( 'Latgalian', 'feedzy-rss-feeds' ),
			'ltz_Latn' => __( 'Luxembourgish', 'feedzy-rss-feeds' ),
			'lua_Latn' => __( 'Luba-Kasai', 'feedzy-rss-feeds' ),
			'lug_Latn' => __( 'Ganda', 'feedzy-rss-feeds' ),
			'luo_Latn' => __( 'Luo', 'feedzy-rss-feeds' ),
			'lus_Latn' => __( 'Mizo', 'feedzy-rss-feeds' ),
			'lvs_Latn' => __( 'Standard Latvian', 'feedzy-rss-feeds' ),
			'mag_Deva' => __( 'Magahi', 'feedzy-rss-feeds' ),
			'mai_Deva' => __( 'Maithili', 'feedzy-rss-feeds' ),
			'mal_Mlym' => __( 'Malayalam', 'feedzy-rss-feeds' ),
			'mar_Deva' => __( 'Marathi', 'feedzy-rss-feeds' ),
			'min_Arab' => __( 'Minangkabau Arab', 'feedzy-rss-feeds' ),
			'min_Latn' => __( 'Minangkabau Latn', 'feedzy-rss-feeds' ),
			'mkd_Cyrl' => __( 'Macedonian', 'feedzy-rss-feeds' ),
			'plt_Latn' => __( 'Plateau Malagasy', 'feedzy-rss-feeds' ),
			'mlt_Latn' => __( 'Maltese', 'feedzy-rss-feeds' ),
			'mni_Beng' => __( 'Meitei', 'feedzy-rss-feeds' ),
			'khk_Cyrl' => __( 'Halh Mongolian', 'feedzy-rss-feeds' ),
			'mos_Latn' => __( 'Mossi', 'feedzy-rss-feeds' ),
			'mri_Latn' => __( 'Maori', 'feedzy-rss-feeds' ),
			'mya_Mymr' => __( 'Burmese', 'feedzy-rss-feeds' ),
			'nld_Latn' => __( 'Dutch', 'feedzy-rss-feeds' ),
			'nno_Latn' => __( 'Norwegian Nynorsk', 'feedzy-rss-feeds' ),
			'nob_Latn' => __( 'Norwegian Bokmål', 'feedzy-rss-feeds' ),
			'npi_Deva' => __( 'Nepali', 'feedzy-rss-feeds' ),
			'nso_Latn' => __( 'Northern Sotho', 'feedzy-rss-feeds' ),
			'nus_Latn' => __( 'Nuer', 'feedzy-rss-feeds' ),
			'nya_Latn' => __( 'Nyanja', 'feedzy-rss-feeds' ),
			'oci_Latn' => __( 'Occitan', 'feedzy-rss-feeds' ),
			'gaz_Latn' => __( 'West Central Oromo', 'feedzy-rss-feeds' ),
			'ory_Orya' => __( 'Odia', 'feedzy-rss-feeds' ),
			'pag_Latn' => __( 'Pangasinan', 'feedzy-rss-feeds' ),
			'pan_Guru' => __( 'Eastern Panjabi', 'feedzy-rss-feeds' ),
			'pap_Latn' => __( 'Papiamento', 'feedzy-rss-feeds' ),
			'pes_Arab' => __( 'Western Persian', 'feedzy-rss-feeds' ),
			'pol_Latn' => __( 'Polish', 'feedzy-rss-feeds' ),
			'por_Latn' => __( 'Portuguese', 'feedzy-rss-feeds' ),
			'prs_Arab' => __( 'Dari', 'feedzy-rss-feeds' ),
			'pbt_Arab' => __( 'Southern Pashto', 'feedzy-rss-feeds' ),
			'quy_Latn' => __( 'Ayacucho Quechua', 'feedzy-rss-feeds' ),
			'ron_Latn' => __( 'Romanian', 'feedzy-rss-feeds' ),
			'run_Latn' => __( 'Rundi', 'feedzy-rss-feeds' ),
			'rus_Cyrl' => __( 'Russian', 'feedzy-rss-feeds' ),
			'sag_Latn' => __( 'Sango', 'feedzy-rss-feeds' ),
			'san_Deva' => __( 'Sanskrit', 'feedzy-rss-feeds' ),
			'sat_Olck' => __( 'Santali', 'feedzy-rss-feeds' ),
			'scn_Latn' => __( 'Sicilian', 'feedzy-rss-feeds' ),
			'shn_Mymr' => __( 'Shan', 'feedzy-rss-feeds' ),
			'sin_Sinh' => __( 'Sinhala', 'feedzy-rss-feeds' ),
			'slk_Latn' => __( 'Slovak', 'feedzy-rss-feeds' ),
			'slv_Latn' => __( 'Slovenian', 'feedzy-rss-feeds' ),
			'smo_Latn' => __( 'Samoan', 'feedzy-rss-feeds' ),
			'sna_Latn' => __( 'Shona', 'feedzy-rss-feeds' ),
			'snd_Arab' => __( 'Sindhi', 'feedzy-rss-feeds' ),
			'som_Latn' => __( 'Somali', 'feedzy-rss-feeds' ),
			'sot_Latn' => __( 'Southern', 'feedzy-rss-feeds' ),
			'spa_Latn' => __( 'Spanish', 'feedzy-rss-feeds' ),
			'als_Latn' => __( 'Tosk Albanian', 'feedzy-rss-feeds' ),
			'srd_Latn' => __( 'Sardinian', 'feedzy-rss-feeds' ),
			'srp_Cyrl' => __( 'Serbian', 'feedzy-rss-feeds' ),
			'ssw_Latn' => __( 'Swati', 'feedzy-rss-feeds' ),
			'sun_Latn' => __( 'Sundanese', 'feedzy-rss-feeds' ),
			'swe_Latn' => __( 'Swedish', 'feedzy-rss-feeds' ),
			'swh_Latn' => __( 'Swahili', 'feedzy-rss-feeds' ),
			'szl_Latn' => __( 'Silesian', 'feedzy-rss-feeds' ),
			'tam_Taml' => __( 'Tamil', 'feedzy-rss-feeds' ),
			'tat_Cyrl' => __( 'Tatar', 'feedzy-rss-feeds' ),
			'tel_Telu' => __( 'Telugu', 'feedzy-rss-feeds' ),
			'tgk_Cyrl' => __( 'Tajik', 'feedzy-rss-feeds' ),
			'tgl_Latn' => __( 'Tagalog', 'feedzy-rss-feeds' ),
			'tha_Thai' => __( 'Thai', 'feedzy-rss-feeds' ),
			'tir_Ethi' => __( 'Tigrinya', 'feedzy-rss-feeds' ),
			'taq_Latn' => __( 'Tamasheq Latn', 'feedzy-rss-feeds' ),
			'taq_Tfng' => __( 'Tamasheq Tfng', 'feedzy-rss-feeds' ),
			'tpi_Latn' => __( 'Tok Pisin', 'feedzy-rss-feeds' ),
			'tsn_Latn' => __( 'Tswana', 'feedzy-rss-feeds' ),
			'tso_Latn' => __( 'Tsonga', 'feedzy-rss-feeds' ),
			'tuk_Latn' => __( 'Turkmen', 'feedzy-rss-feeds' ),
			'tum_Latn' => __( 'Tumbuka', 'feedzy-rss-feeds' ),
			'tur_Latn' => __( 'Turkish', 'feedzy-rss-feeds' ),
			'twi_Latn' => __( 'Twi', 'feedzy-rss-feeds' ),
			'tzm_Tfng' => __( 'Central Atlas Tamazight', 'feedzy-rss-feeds' ),
			'uig_Arab' => __( 'Uyghur', 'feedzy-rss-feeds' ),
			'ukr_Cyrl' => __( 'Ukrainian', 'feedzy-rss-feeds' ),
			'umb_Latn' => __( 'Umbundu', 'feedzy-rss-feeds' ),
			'urd_Arab' => __( 'Urdu', 'feedzy-rss-feeds' ),
			'uzn_Latn' => __( 'Northern Uzbek', 'feedzy-rss-feeds' ),
			'vec_Latn' => __( 'Venetian', 'feedzy-rss-feeds' ),
			'vie_Latn' => __( 'Vietnamese', 'feedzy-rss-feeds' ),
			'war_Latn' => __( 'Waray', 'feedzy-rss-feeds' ),
			'wol_Latn' => __( 'Wolof', 'feedzy-rss-feeds' ),
			'xho_Latn' => __( 'Xhosa', 'feedzy-rss-feeds' ),
			'ydd_Hebr' => __( 'Eastern Yiddish', 'feedzy-rss-feeds' ),
			'yor_Latn' => __( 'Yoruba', 'feedzy-rss-feeds' ),
			'yue_Hant' => __( 'Yue Chinese', 'feedzy-rss-feeds' ),
			'zho_Hans' => __( 'Chinese Simplified', 'feedzy-rss-feeds' ),
			'zho_Hant' => __( 'Chinese Traditional', 'feedzy-rss-feeds' ),
			'zsm_Latn' => __( 'Standard Malay', 'feedzy-rss-feeds' ),
			'zul_Latn' => __( 'Zulu', 'feedzy-rss-feeds' ),
		);
		$target_lang = apply_filters( 'feedzy_available_automatically_translation_language', $target_lang );

		return $target_lang;
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting(
			'feedzy',
			'feedzy_review_notice',
			array(
				'type'         => 'string',
				'default'      => 'no',
				'show_in_rest' => true,
			)
		);
	}

	/**
	 * Mark the page as internal.
	 *
	 * @param string $page_slug Page slug.
	 * @return void
	 */
	private function do_internal_page( $page_slug ) {
		do_action( 'themeisle_internal_page', $this->plugin_name, $page_slug );
	}

	/**
	 * Enable the loading of telemetry script in page.
	 */
	private function enable_telemetry() {
		if ( defined( 'E2E_TESTING' ) ) {
			return;
		}

		add_filter( 'themeisle_sdk_enable_telemetry', '__return_true' );
		wp_enqueue_script( $this->plugin_name . '_telemetry', FEEDZY_ABSURL . 'js/telemetry.js', array(), $this->version, true );
	}

	/**
	 * Set the black friday data.
	 *
	 * @param array $configs The configuration array for the loaded products.
	 * @return array
	 */
	public function add_black_friday_data( $configs ) {
		$config = $configs['default'];

		// translators: %1$s - HTML tag, %2$s - discount, %3$s - HTML tag, %4$s - product name.
		$message_template = __( 'Our biggest sale of the year: %1$sup to %2$s OFF%3$s on %4$s. Don\'t miss this limited-time offer.', 'feedzy-rss-feeds' );
		$product_label    = 'Feedzy';
		$discount         = '70%';

		$is_pro = feedzy_is_pro();

		if ( $is_pro ) {
			// translators: %1$s - HTML tag, %2$s - discount, %3$s - HTML tag, %4$s - product name.
			$message_template = __( 'Get %1$sup to %2$s off%3$s when you upgrade your %4$s plan or renew early.', 'feedzy-rss-feeds' );
			$product_label    = 'Feedzy Pro';
			$discount         = '30%';
		}

		$product_label = sprintf( '<strong>%s</strong>', $product_label );
		$url_params    = array(
			'utm_term' => $is_pro ? 'plan-' . apply_filters( 'product_feedzy_license_plan', 0 ) : 'free',
			'lkey'     => $is_pro ? apply_filters( 'product_feedzy_license_key', false ) : false,
		);

		$config['message']  = sprintf( $message_template, '<strong>', $discount, '</strong>', $product_label );
		$config['sale_url'] = add_query_arg(
			$url_params,
			tsdk_translate_link( tsdk_utmify( 'https://themeisle.link/feedzy-bf', 'bfcm', 'feedzy' ) )
		);

		$configs[ $this->plugin_name ] = $config;

		return $configs;
	}

	/**
	 * Validate the feed URL and check if it's a valid RSS/Atom feed.
	 *
	 * @return void
	 */
	public function validate_feed() {
		try {
			if (
				! isset( $_POST['nonce'] ) ||
				! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), FEEDZY_BASEFILE )
			) {
				wp_send_json_error( array( 'message' => __( 'Security check failed.', 'feedzy-rss-feeds' ) ) );
			}

			$feed_urls = isset( $_POST['feed_url'] ) ? sanitize_text_field( wp_unslash( $_POST['feed_url'] ) ) : '';

			if ( empty( $feed_urls ) ) {
				wp_send_json_error( array( 'message' => __( 'Feed URL cannot be empty.', 'feedzy-rss-feeds' ) ) );
			}

			$urls = array_map( 'trim', explode( ',', $feed_urls ) );
			$urls = array_filter( $urls );

			if ( empty( $urls ) ) {
				wp_send_json_error( array( 'message' => __( 'No valid URLs provided.', 'feedzy-rss-feeds' ) ) );
			}

			$results = array();

			foreach ( $urls as $feed_url ) {
				$feed_url = esc_url_raw( $feed_url );

				if ( ! filter_var( $feed_url, FILTER_VALIDATE_URL ) ) {
					$results[] = array(
						'url'     => $feed_url,
						'status'  => 'error',
						'message' => __( 'Invalid URL format', 'feedzy-rss-feeds' ),
					);
					continue;
				}

				$feed = $this->fetch_feed( array( $feed_url ), '1_mins', array() );

				if ( is_wp_error( $feed ) ) {
					$results[] = array(
						'url'     => $feed_url,
						'status'  => 'error',
						'message' => __( 'Error fetching feed: ', 'feedzy-rss-feeds' ) . $feed->get_error_message(),
					);
					continue;
				}

				if (
					! is_object( $feed ) ||
					! method_exists( $feed, 'get_item_quantity' )
				) {
					$results[] = array(
						'url'     => $feed_url,
						'status'  => 'error',
						'message' => __( 'Invalid feed object returned', 'feedzy-rss-feeds' ),
					);
					continue;
				}

				try {
					$items = $feed->get_item_quantity();
					$title = $feed->get_title();
					$error = $feed->error();

					if ( is_array( $error ) && ! empty( $error ) ) {
						$results[] = array(
							'url'     => $feed_url,
							'status'  => 'error',
							'message' => __( 'Error fetching feed: ', 'feedzy-rss-feeds' ) . implode( ', ', $error ),
						);
						continue;
					}

					if ( 0 === $items ) {
						$results[] = array(
							'url'     => $feed_url,
							'status'  => 'warning',
							'message' => __( 'Feed is empty', 'feedzy-rss-feeds' ),
						);
						continue;
					}

					$results[] = array(
						'url'     => $feed_url,
						'status'  => 'success',
						'message' => $title . sprintf(
						/* translators: %d is the number of items found in the feed */
							_n(
								'%d item found',
								'%d items found',
								$items,
								'feedzy-rss-feeds'
							),
							$items 
						),
						'items'   => $items,
						'title'   => $title,
					);

				} catch ( Throwable $e ) {
					$results[] = array(
						'url'     => $feed_url,
						'status'  => 'error',
						/* translators: %s is the error message */
						'message' => sprintf( __( 'Error reading feed: %s', 'feedzy-rss-feeds' ), $e->getMessage() ),
					);
				}
			}

			wp_send_json_success(
				array(
					'results' => $results,
				)
			);
		} catch ( Throwable $e ) {
			wp_send_json_error(
				array(
					/* translators: %s is the error message */
					'message' => sprintf( __( 'An error occurred: %s', 'feedzy-rss-feeds' ), $e->getMessage() ),
				)
			);
		}
	}

	/**
	 * Append custom schedules to the existing schedules.
	 * 
	 * @since 5.1.0
	 * @param array<string,array{interval:int,display:string}> $schedules Existing schedules.
	 * @return array<string,array{interval:int,display:string}> Modified schedules with custom schedules appended.
	 */
	public function append_custom_cron_schedules( $schedules ) {
		if ( ! feedzy_is_pro() ) {
			return $schedules;
		}

		$saved_settings = apply_filters( 'feedzy_get_settings', array() );
		if ( ! empty( $saved_settings['custom_schedules'] ) && is_array( $saved_settings['custom_schedules'] ) ) {
			$custom_schedules = $saved_settings['custom_schedules'];

			foreach ( $custom_schedules as $key => $value ) {
				if ( ! empty( $value['interval'] ) && ! empty( $value['display'] ) ) {
					$schedules[ $key ] = array(
						'interval' => intval( $value['interval'] ),
						'display'  => sanitize_text_field( $value['display'] ),
					);
				}
			}
		}
		
		return $schedules;
	}

	/**
	 * Add slugs for internal cron schedules.
	 * 
	 * @param string[] $cron_slugs The cron slugs to be modified.
	 * @return string[]
	 */
	public function internal_cron_schedule_slugs( $cron_slugs ) {
		$wp_standard_schedules = array(
			'hourly',
			'twicedaily', 
			'daily',
			'weekly',
		);

		return array_merge( $wp_standard_schedules, $cron_slugs );
	}
}
