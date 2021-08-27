<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://themeisle.com
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

		if ( 'feedzy_page_feedzy-settings' === $screen->base ) {
			if ( ! did_action( 'wp_enqueue_media' ) ) {
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
					),
				)
			);
		}

		if ( in_array( $screen->base, array( 'post' ), true ) ) {
			wp_enqueue_style( $this->plugin_name . '-admin', FEEDZY_ABSURL . 'css/admin.css', array(), $this->version, 'all' );
		}

		$upsell_screens = array( 'feedzy-rss_page_feedzy-settings', 'feedzy-rss_page_feedzy-admin-menu-pro-upsell' );

		if ( ! in_array( $screen->base, $upsell_screens, true ) && strpos( $screen->id, 'feedzy' ) === false ) {
			return;
		}
		wp_enqueue_style( $this->plugin_name . '-settings', FEEDZY_ABSURL . 'css/settings.css', [], $this->version );
		wp_enqueue_style( $this->plugin_name . '-metabox', FEEDZY_ABSURL . 'css/metabox-settings.css', array( $this->plugin_name . '-settings' ), $this->version );
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
			'name'               => __( 'Feed Categories', 'feedzy-rss-feeds' ),
			'singular_name'      => __( 'Feed Category', 'feedzy-rss-feeds' ),
			'add_new'            => __( 'Add Category', 'feedzy-rss-feeds' ),
			'add_new_item'       => __( 'Add Category', 'feedzy-rss-feeds' ),
			'edit_item'          => __( 'Edit Category', 'feedzy-rss-feeds' ),
			'new_item'           => __( 'New Feed Category', 'feedzy-rss-feeds' ),
			'view_item'          => __( 'View Category', 'feedzy-rss-feeds' ),
			'search_items'       => __( 'Search Category', 'feedzy-rss-feeds' ),
			'not_found'          => __( 'No categories found', 'feedzy-rss-feeds' ),
			'not_found_in_trash' => __( 'No categories in the trash', 'feedzy-rss-feeds' ),
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
		);
		$args     = apply_filters( 'feedzy_post_type_args', $args );
		register_post_type( 'feedzy_categories', $args );
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
			__( 'Category Feeds', 'feedzy-rss-feeds' ),
			array(
				$this,
				'feedzy_category_feed',
			),
			'feedzy_categories',
			'normal',
			'high'
		);
		add_meta_box(
			'feedzy_category_feeds_rn',
			__( 'Increase your social media presence', 'feedzy-rss-feeds' ),
			array(
				$this,
				'render_upsell_rn',
			),
			'feedzy_categories',
			'side',
			'low'
		);
	}

	/**
	 * Render RN upsell metabox.
	 */
	public function render_upsell_rn() {
		echo '<p>Learn how you can connect with people by sharing content from RSS feeds on your social media accounts. </p>';
		echo '<a class="button button-primary  " href="https://revive.social/plugins/revive-network/" target="_blank">View more details</a>';

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
			<strong>' . sprintf( __( 'Please be aware that multiple feeds, when mashed together, may sometimes not work as expected as explained %1$shere%2$s.', 'feedzy-rss-feeds' ), '<a href="http://simplepie.org/wiki/faq/typical_multifeed_gotchas" target="_blank">', '</a>' ) . '</strong><br/><br/>'
			. $invalid
			. '<textarea name="feedzy_category_feed" rows="15" class="widefat" placeholder="' . __( 'Place your URL\'s here followed by a comma.', 'feedzy-rss-feeds' ) . '" >' . $feed . '</textarea>
        ';
		echo wp_kses( $output, apply_filters( 'feedzy_wp_kses_allowed_html', array() ) );
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
			$category_meta['feedzy_category_feed'] = wp_strip_all_tags( wp_unslash( $_POST['feedzy_category_feed'] ) );
		}
		if ( $post->post_type === 'revision' ) {
			return true;
		} else {
			foreach ( $category_meta as $key => $value ) {
				$value = implode( ',', (array) $value );
				if ( get_post_meta( $post_id, $key, false ) ) {
					update_post_meta( $post_id, $key, sanitize_text_field( $value ) );
				} else {
					add_post_meta( $post_id, $key, sanitize_text_field( $value ) );
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
		$columns['title'] = __( 'Category Title', 'feedzy-rss-feeds' );
		if ( $new_columns = $this->array_insert_before( 'date', $columns, 'slug', __( 'Slug', 'feedzy-rss-feeds' ) ) ) {
			$columns = $new_columns;
		} else {
			$columns['slug'] = __( 'Slug', 'feedzy-rss-feeds' );
		}

		if ( $new_columns = $this->array_insert_before( 'date', $columns, 'actions', __( 'Actions', 'feedzy-rss-feeds' ) ) ) {
			$columns = $new_columns;
		} else {
			$columns['actions'] = __( 'Actions', 'feedzy-rss-feeds' );
		}

		return $columns;
	}

	/**
	 * Add/remove row actions for each category.
	 *
	 * @since   ?
	 * @access  public
	 */
	public function add_feedzy_category_actions( $actions, $post ) {
		if ( $post->post_type === 'feedzy_categories' ) {
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
				$new_links['more_features'] = '<a href="' . FEEDZY_UPSELL_LINK . '" target="_blank" title="' . __( 'More Features', 'feedzy-rss-feeds' ) . '">' . __( 'Upgrade to Pro', 'feedzy-rss-feeds' ) . '<i style="width: 17px; height: 17px; margin-left: 4px; color: #ffca54; font-size: 17px; vertical-align: -3px;" class="dashicons dashicons-unlock more-features-icon"></i></a>';
			} elseif ( false === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) {
				$new_links['more_features'] = '<a href="' . FEEDZY_UPSELL_LINK . '" target="_blank" title="' . __( 'More Features', 'feedzy-rss-feeds' ) . '">' . __( 'Upgrade your license', 'feedzy-rss-feeds' ) . '<i style="width: 17px; height: 17px; margin-left: 4px; color: #ffca54; font-size: 17px; vertical-align: -3px;" class="dashicons dashicons-unlock more-features-icon"></i></a>';
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
	 */
	public function feedzy_menu_pages() {
		$svg_base64_icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iNzdweCIgaGVpZ2h0PSI3N3B4IiB2aWV3Qm94PSIwIDAgNzcgNzciIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8IS0tIEdlbmVyYXRvcjogU2tldGNoIDUyLjYgKDY3NDkxKSAtIGh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaCAtLT4KICAgIDx0aXRsZT5Db21iaW5lZCBTaGFwZTwvdGl0bGU+CiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4KICAgIDxnIGlkPSJQcm9kdWN0LVBhZ2UiIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPgogICAgICAgIDxnIGlkPSJXb3JkUHJlc3MtcGx1Z2lucyIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTE5Ni4wMDAwMDAsIC05NTcuMDAwMDAwKSIgZmlsbD0iIzQyNjhDRiI+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMzQuNSwxMDM0IEMyMTMuMjM3MDM3LDEwMzQgMTk2LDEwMTYuNzYyOTYgMTk2LDk5NS41IEMxOTYsOTc0LjIzNzAzNyAyMTMuMjM3MDM3LDk1NyAyMzQuNSw5NTcgQzI1NS43NjI5NjMsOTU3IDI3Myw5NzQuMjM3MDM3IDI3Myw5OTUuNSBDMjczLDEwMTYuNzYyOTYgMjU1Ljc2Mjk2MywxMDM0IDIzNC41LDEwMzQgWiBNMjM4LjM4OTA4NywxMDAzLjYxMDkxIEMyMzYuMjQxMjU2LDEwMDEuNDYzMDggMjMyLjc1ODg1MSwxMDAxLjQ2Mjk3IDIzMC42MTA5NDMsMTAwMy42MTA4OCBDMjI4LjQ2MzAzNSwxMDA1Ljc1ODc5IDIyOC40NjMwMjEsMTAwOS4yNDEyIDIzMC42MTA5MTMsMTAxMS4zODkwOSBDMjMyLjc1ODgwNCwxMDEzLjUzNjk4IDIzNi4yNDExNDksMTAxMy41MzcwMyAyMzguMzg5MDU3LDEwMTEuMzg5MTIgQzI0MC41MzY5NjUsMTAwOS4yNDEyMSAyNDAuNTM2OTc5LDEwMDUuNzU4OCAyMzguMzg5MDg3LDEwMDMuNjEwOTEgWiBNMjUxLjE5OTE5Niw5OTYuNTI0MjY5IEMyNDEuNzE2MDEsOTg4LjAxMzQwOSAyMjcuMjk0MTQzLDk4OC4wMDQzMDcgMjE3LjgwMDg1OSw5OTYuNTI0MjE0IEMyMTcuMjQwNDk2LDk5Ny4wMjcwNzkgMjE3LjIyMjEwOCw5OTcuODk5Nzc3IDIxNy43NTQ0OCw5OTguNDMyMTUgTDIyMC41NTE4NzksMTAwMS4yMjk1NSBDMjIxLjA0MTU5NCwxMDAxLjcxOTI2IDIyMS44Mjk5NjcsMTAwMS43NTIyNiAyMjIuMzUwNDA4LDEwMDEuMjk1MzcgQzIyOS4yODI0MDEsOTk1LjIxMTE3IDIzOS43MDI4MSw5OTUuMTk4MjA5IDI0Ni42NDk1NDYsMTAwMS4yOTU0MSBDMjQ3LjE3MDA0NywxMDAxLjc1MjI1IDI0Ny45NTg0MiwxMDAxLjcxOTI1IDI0OC40NDgwNzUsMTAwMS4yMjk1OSBMMjUxLjI0NTQ2NSw5OTguNDMyMjA1IEMyNTEuNzc3OTUyLDk5Ny44OTk4MzQgMjUxLjc1OTU2MSw5OTcuMDI3MTM2IDI1MS4xOTkxOTYsOTk2LjUyNDI2OSBaIE0yNTkuNTE3NDgxLDk4OC4wNjI4MTggQzI0NS43NTQ2NjIsOTc1LjI1MzkxIDIyNC4zMTI1MzEsOTc1LjE5MTM3NCAyMTAuNDgyNDY0LDk4OC4wNjI4NzMgQzIwOS45NTA5Niw5ODguNTU3NTU3IDIwOS45NDA4NDUsOTg5LjM5NjY4OSAyMTAuNDU0MjIyLDk4OS45MTAwNjYgTDIxMy4xODU0ODksOTkyLjY0MTMzMyBDMjEzLjY3NTU2OSw5OTMuMTMxNDEzIDIxNC40NjI4MjQsOTkzLjE0MTkyNCAyMTQuOTcyNjIyLDk5Mi42NzIzNTUgQzIyNi4yODEwMjksOTgyLjI1NDc4NiAyNDMuNzIwODA0LDk4Mi4yNTY0MTUgMjU1LjAyNzQyNyw5OTIuNjcyMzExIEMyNTUuNTM3MTY3LDk5My4xNDE5MzUgMjU2LjMyNDQyMiw5OTMuMTMxNDIzIDI1Ni44MTQ1Niw5OTIuNjQxMjg0IEwyNTkuNTQ1ODMzLDk4OS45MTAwMTEgQzI2MC4wNTkwOTcsOTg5LjM5NjYzMyAyNjAuMDQ4OTg0LDk4OC41NTc1MDEgMjU5LjUxNzQ4MSw5ODguMDYyODE4IFoiIGlkPSJDb21iaW5lZC1TaGFwZSI+PC9wYXRoPgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+';
		add_menu_page( __( 'Feedzy', 'feedzy-rss-feeds' ), __( 'Feedzy', 'feedzy-rss-feeds' ), 'manage_options', 'feedzy-admin-menu', '', $svg_base64_icon, 98.7666 );

		add_submenu_page(
			'feedzy-admin-menu',
			__( 'Settings', 'feedzy-rss-feeds' ),
			__( 'Settings', 'feedzy-rss-feeds' ),
			'manage_options',
			'feedzy-settings',
			array(
				$this,
				'feedzy_settings_page',
			)
		);
		add_submenu_page(
			'feedzy-admin-menu',
			__( 'Support', 'feedzy-rss-feeds' ),
			__( 'Support', 'feedzy-rss-feeds' ) . '<span class="dashicons dashicons-editor-help more-features-icon" style="width: 17px; height: 17px; margin-left: 4px; color: #ffca54; font-size: 17px; vertical-align: -3px;"></span>',
			'manage_options',
			'feedzy-support',
			array(
				$this,
				'render_support',
			)
		);
	}

	/**
	 * Method to register the settings page.
	 *
	 * @access  public
	 */
	public function feedzy_settings_page() {
		if ( isset( $_POST['feedzy-settings-submit'] ) && isset( $_POST['tab'] ) && wp_verify_nonce( filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING ), filter_input( INPUT_POST, 'tab', FILTER_SANITIZE_STRING ) ) ) {
			$this->save_settings();
			$this->notice = __( 'Your settings were saved.', 'feedzy-rss-feeds' );
		}

		$settings = apply_filters( 'feedzy_get_settings', array() );
		include FEEDZY_ABSPATH . '/includes/layouts/settings.php';
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
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING ), filter_input( INPUT_POST, 'tab', FILTER_SANITIZE_STRING ) ) ) {
			return;
		}
		$post_tab = isset( $_POST['tab'] ) ? filter_input( INPUT_POST, 'tab', FILTER_SANITIZE_STRING ) : '';

		$settings = apply_filters( 'feedzy_get_settings', array() );
		switch ( $post_tab ) {
			case 'general':
				$settings['general']['rss-feeds'] = isset( $_POST['rss-feeds'] ) ? (int) filter_input( INPUT_POST, 'rss-feeds', FILTER_SANITIZE_NUMBER_INT ) : '';
				$settings['general']['default-thumbnail-id'] = isset( $_POST['default-thumbnail-id'] ) ? (int) filter_input( INPUT_POST, 'default-thumbnail-id', FILTER_SANITIZE_NUMBER_INT ) : 0;
				break;
			case 'headers':
				$settings['header']['user-agent'] = isset( $_POST['user-agent'] ) ? filter_input( INPUT_POST, 'user-agent', FILTER_SANITIZE_STRING ) : '';
				break;
			case 'proxy':
				$settings['proxy'] = array(
					'host' => isset( $_POST['proxy-host'] ) ? filter_input( INPUT_POST, 'proxy-host', FILTER_SANITIZE_STRING ) : '',
					'port' => isset( $_POST['proxy-port'] ) ? filter_input( INPUT_POST, 'proxy-port', FILTER_SANITIZE_NUMBER_INT ) : '',
					'user' => isset( $_POST['proxy-user'] ) ? filter_input( INPUT_POST, 'proxy-user', FILTER_SANITIZE_STRING ) : '',
					'pass' => isset( $_POST['proxy-pass'] ) ? filter_input( INPUT_POST, 'proxy-pass', FILTER_SANITIZE_STRING ) : '',
				);
				break;
			default:
				$settings = apply_filters( 'feedzy_save_tab_settings', $settings, $post_tab );
		}

		update_option( 'feedzy-settings', $settings );
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
	 * @access  public
	 */
	public function pre_http_setup( $url ) {
		$this->add_proxy( $url );
		add_filter( 'http_headers_useragent', array( $this, 'add_user_agent' ) );
		add_filter( 'http_request_args', array( $this, 'http_request_args' ) );
	}

	/**
	 * Add the proxy settings as specified in the settings.
	 *
	 * @access  private
	 */
	private function add_proxy( $url ) {
		$settings = apply_filters( 'feedzy_get_settings', null );
		if ( $settings && isset( $settings['proxy'] ) && is_array( $settings['proxy'] ) && ! empty( $settings['proxy'] ) ) {
			// if even one constant is defined, escape.
			if ( defined( 'WP_PROXY_HOST' ) || defined( 'WP_PROXY_PORT' ) || defined( 'WP_PROXY_USERNAME' ) || defined( 'WP_PROXY_PASSWORD' ) ) {
				do_action( 'themeisle_log_event', FEEDZY_NAME, 'Some proxy constants already defined; ignoring proxy settings', 'info', __FILE__, __LINE__ );

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
	 * @access  public
	 */
	public function http_request_args( $args ) {
		// allow private IPs.
		$args['reject_unsafe_urls'] = false;
		// allow SSLs to go through without certificate verification.
		$args['sslverify'] = false;

		return $args;
	}

	/**
	 * Add the user agent if specified in the settings.
	 *
	 * @access  public
	 */
	public function add_user_agent( $ua ) {
		$settings = apply_filters( 'feedzy_get_settings', null );
		if ( $settings && isset( $settings['header']['user-agent'] ) && ! empty( $settings['header']['user-agent'] ) ) {
			do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Override user-agent from %s to %s', $ua, $settings['header']['user-agent'] ), 'info', __FILE__, __LINE__ );
			$ua = $settings['header']['user-agent'];
		}

		return $ua;
	}

	/**
	 * Check if the uri should go through the proxy.
	 *
	 * @access  public
	 */
	public function send_through_proxy( $return, $uri, $check, $home ) {
		$proxied = defined( 'FEEZY_URL_THRU_PROXY' ) ? FEEZY_URL_THRU_PROXY : null;
		if ( $proxied && ( ( is_array( $proxied ) && in_array( $uri, $proxied, true ) ) || $uri === $proxied ) ) {
			do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'sending %s through proxy', $uri ), 'info', __FILE__, __LINE__ );

			return true;
		}

		return false;
	}

	/**
	 * Teardown the HTTP parameters/headers.
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

		if ( get_option( 'feedzy-activated' ) ) {
			delete_option( 'feedzy-activated' );
			if ( ! headers_sent() ) {
				wp_safe_redirect(
					add_query_arg(
						array(
							'page' => 'feedzy-support',
							'tab'  => 'help#shortcode',
						),
						admin_url( 'admin.php' )
					)
				);
				exit();
			}
		}
	}

	/**
	 * Validates the URLs and removes the ones that were found to be invalid.
	 *
	 * @access  public
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
	 * @access  public
	 */
	public function check_source_validity( $src, $post_id, $add_pseudo_transient, $return_valid ) {
		$urls_in   = $src;
		$post_type = get_post_type( $post_id );
		if ( 'feedzy_imports' === $post_type && strpos( $src, 'http' ) === false && strpos( $src, 'https' ) === false ) {
			// category
			$category = get_page_by_path( $src, OBJECT, 'feedzy_categories' );
			if ( $category ) {
				$urls_in = get_post_meta( $category->ID, 'feedzy_category_feed', true );
			}
		}

		// this method is fired through ajax when the category title is updated
		// even without clicking the publish button
		// thereby sending empty urls
		if ( empty( $urls_in ) ) {
			return array();
		}

		$urls = $this->normalize_urls( $urls_in );
		if ( ! is_array( $urls ) ) {
			$urls = array( $urls );
		}
		$valid   = $this->get_valid_feed_urls( $urls, '1_mins', false );
		$invalid = array_diff( $urls, $valid );

		if ( $add_pseudo_transient && ( empty( $valid ) || ! empty( $invalid ) ) ) {
			// let's save the invalid urls in a pseudo-transient so that we can show it in the import edit screen.
			switch ( $post_type ) {
				case 'feedzy_categories':
					update_post_meta( $post_id, '__transient_feedzy_category_feed', $invalid );
					break;
				case 'feedzy_imports':
					$invalid_dc_namespace = get_post_meta( $post_id, '__transient_feedzy_invalid_dc_namespace', true );
					if ( empty( $invalid_dc_namespace ) ) {
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
	 * @access  public
	 */
	public function get_source_validity_error( $message = '', $post, $class = '' ) {
		$invalid = $text = null;
		switch ( $post->post_type ) {
			case 'feedzy_categories':
				$text    = __( 'We found the following invalid URLs that we have removed from the list', 'feedzy-rss-feeds' );
				$invalid = get_post_meta( $post->ID, '__transient_feedzy_category_feed', true );
				delete_post_meta( $post->ID, '__transient_feedzy_category_feed' );
				break;
			case 'feedzy_imports':
				$invalid_source = get_post_meta( $post->ID, '__transient_feedzy_invalid_source', true );
				$invalid_dc_namespace = get_post_meta( $post->ID, '__transient_feedzy_invalid_dc_namespace', true );
				if ( $invalid_source ) {
					$text = __( 'This source has invalid URLs. Please correct/remove the following', 'feedzy-rss-feeds' );
					$invalid = $invalid_source;
					delete_post_meta( $post->ID, '__transient_feedzy_invalid_source' );
				} elseif ( $invalid_dc_namespace ) {
					$text = __( 'Please enter a valid feed URL to import the author', 'feedzy-rss-feeds' );
					$invalid = $invalid_dc_namespace;
					delete_post_meta( $post->ID, '__transient_feedzy_invalid_dc_namespace' );
				}
				break;
			default:
				return $message;
		}

		if ( $invalid ) {
			if ( empty( $class ) ) {
				$class = 'notice notice-error notice-alt feedzy-error-critical';
			}
			$message .= '<div class="' . $class . '"><p style="color: inherit"><i class="dashicons dashicons-warning"></i>' . $text . ': <ol style="color: inherit">';
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

		$post_action = isset( $_POST['_action'] ) ? filter_input( INPUT_POST, '_action', FILTER_SANITIZE_STRING ) : '';
		$post_id     = isset( $_POST['id'] ) ? filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) : '';

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
		}
	}
}
