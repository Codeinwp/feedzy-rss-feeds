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
	 * The ID of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

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

		wp_enqueue_style( $this->plugin_name, FEEDZY_ABSURL . 'css/feedzy-rss-feeds.css', array(), $this->version, 'all' );
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

		$upsell_screens = array( 'feedzy-rss_page_feedzy-settings', 'feedzy-rss_page_feedzy-admin-menu-pro-upsell' );

		if ( ! in_array( $screen->base, $upsell_screens ) && strpos( $screen->id, 'feedzy' ) === false ) {
			return;
		}
		wp_enqueue_style( $this->plugin_name . '-upsell', FEEDZY_ABSURL . 'includes/layouts/css/upsell.css' );
		wp_enqueue_style( $this->plugin_name . '-settings', FEEDZY_ABSURL . 'css/metabox-settings.css', array( $this->plugin_name . '-upsell' ) );
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
			'labels'               => $labels,
			'supports'             => $supports,
			'public'               => true,
			'exclude_from_search'  => true,
			'publicly_queryable'   => false,
			'show_in_nav_menus'    => false,
			'capability_type'      => 'post',
			'rewrite'              => array( 'slug' => 'feedzy-category' ),
			'show_in_menu'         => 'feedzy-admin-menu',
			'register_meta_box_cb' => array( $this, 'add_feedzy_post_type_metaboxes' ),
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
			'feedzy_category_feeds', __( 'Category Feeds', 'feedzy-rss-feeds' ), array(
				$this,
				'feedzy_category_feed',
			), 'feedzy_categories', 'normal', 'high'
		);
		add_meta_box(
			'feedzy_category_feeds_rn', __( 'Increase your social media presence', 'feedzy-rss-feeds' ), array(
				$this,
				'render_upsell_rn',
			), 'feedzy_categories', 'side', 'low'
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
		$nonce  = wp_create_nonce( FEEDZY_BASEFILE );
		$feed   = get_post_meta( $post->ID, 'feedzy_category_feed', true );
		$output = '
            <input type="hidden" name="feedzy_category_meta_noncename" id="feedzy_category_meta_noncename" value="' . $nonce . '" />
            <textarea name="feedzy_category_feed" rows="15" class="widefat" placeholder="' . __( 'Place your URL\'s here followed by a comma.', 'feedzy-rss-feeds' ) . '" >' . $feed . '</textarea>
        ';
		echo $output;
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
		if (
			empty( $_POST ) ||
			( isset( $_POST['feedzy_category_meta_noncename'] ) && ! wp_verify_nonce( $_POST['feedzy_category_meta_noncename'], FEEDZY_BASEFILE ) ) ||
			! current_user_can( 'edit_post', $post_id )
		) {
			return $post_id;
		}
		$category_meta['feedzy_category_feed'] = array();
		if ( isset( $_POST['feedzy_category_feed'] ) ) {
			$category_meta['feedzy_category_feed'] = $_POST['feedzy_category_feed'];
		}
		if ( $post->post_type == 'revision' ) {
			return true;
		} else {
			foreach ( $category_meta as $key => $value ) {
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
		$columns['title'] = __( 'Category Title', 'feedzy-rss-feeds' );
		if ( $new_columns = $this->array_insert_before( 'date', $columns, 'slug', __( 'Slug', 'feedzy-rss-feeds' ) ) ) {
			$columns = $new_columns;
		} else {
			$columns['slug'] = __( 'Slug', 'feedzy-rss-feeds' );
		}

		return $columns;
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
					echo __( 'Undefined', 'feedzy-rss-feeds' );
				} else {
					echo '<code>' . $slug . '</code>';
				}
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
			$new_links = array(
				'doc'           => '<a href="http://docs.themeisle.com/article/277-feedzy-rss-feeds-hooks" target="_blank" title="' . __( 'Documentation and examples', 'feedzy-rss-feeds' ) . '">' . __( 'Documentation and examples', 'feedzy-rss-feeds' ) . '</a>',
				'more_features' => '<a href="' . FEEDZY_UPSELL_LINK . '" target="_blank" title="' . __( 'More Plugins', 'feedzy-rss-feeds' ) . '">' . __( 'More Features', 'feedzy-rss-feeds' ) . '<i style="width: 17px; height: 17px; margin-left: 4px; color: #ffca54; font-size: 17px; vertical-align: -3px;" class="dashicons dashicons-unlock more-features-icon"></i></a>',
			);
			$links     = array_merge( $links, $new_links );
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
		$svg_base64_icon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4NTAuMzkiIGhlaWdodD0iODUwLjM5Ij48cGF0aCBmaWxsPSIjREIzOTM5IiBkPSJNNDI1LjIgMkMxOTAuMzYgMiAwIDE5MS45MiAwIDQyNi4yYzAgMjM0LjI3IDE5MC4zNyA0MjQuMiA0MjUuMiA0MjQuMiAyMzQuODIgMCA0MjUuMi0xODkuOTMgNDI1LjItNDI0LjJDODUwLjQgMTkxLjkgNjYwIDIgNDI1LjIgMnptLTQ2LjU1IDY2OC42NmgtOTEuNTh2LTU3LjFMMjM3LjUgNTY0LjFoLTU3LjI2di05MS4yNGg5NS4yNWwxMDMuMTUgMTAyLjh2OTV6bTE1Mi41MiAwSDQzOS42di0xMzMuM0wzMTMuODUgNDExLjk0aC0xMzMuNnYtOTEuMzZIMzUxLjdMNTMxLjE4IDQ5OS42djE3MS4wNnptMTUyLjU1IDBoLTkxLjU4VjQ2MS4yTDM5MC4wNiAyNTkuNzRIMTgwLjI0di05MS4zNmgyNDcuOGwyNTUuNjggMjU1LjA3djI0Ny4yMnoiLz48L3N2Zz4=';
		add_menu_page( __( 'Feedzy RSS Feeds', 'feedzy-rss-feeds' ), __( 'Feedzy RSS', 'feedzy-rss-feeds' ), 'manage_options', 'feedzy-admin-menu', '', $svg_base64_icon, 98.7666 );
		if ( ! class_exists( 'Feedzy_Rss_Feeds_Pro' ) ) {
			add_submenu_page(
				'feedzy-admin-menu', __( 'More Features', 'feedzy-rss-feeds' ), __( 'More Features', 'feedzy-rss-feeds' ) . '<span class="dashicons 
		dashicons-star-filled more-features-icon" style="width: 17px; height: 17px; margin-left: 4px; color: #ffca54; font-size: 17px; vertical-align: -3px;"></span>', 'manage_options', 'feedzy-admin-menu-pro-upsell',
				array(
					$this,
					'render_upsell',
				)
			);
		} else {
			$is_business = apply_filters( 'feedzy_is_business_filter', false );
			if ( $is_business != false ) {
				add_submenu_page( 'feedzy-admin-menu', __( 'Import Posts', 'feedzy-rss-feeds' ), __( 'Import Posts', 'feedzy-rss-feeds' ), 'manage_options', 'edit.php?post_type=feedzy_imports' );
			}
		}
		add_submenu_page(
			'feedzy-admin-menu', __( 'Settings', 'feedzy-rss-feeds' ), __( 'Settings', 'feedzy-rss-feeds' ), 'manage_options', 'feedzy-settings', array(
				$this,
				'feedzy_settings_page',
			)
		);
	}

	/**
	 * Method to register the settings page.
	 *
	 * @access  public
	 */
	public function feedzy_settings_page() {
		if ( isset( $_POST['feedzy-settings-submit'] ) && isset( $_POST['tab'] ) && wp_verify_nonce( $_POST['nonce'], $_POST['tab'] ) ) {
			$this->save_settings();
			$this->notice = __( 'Your settings were saved.', 'feedzy-rss-feeds' );
		}

		$settings = apply_filters( 'feedzy_get_settings', array() );
		include( FEEDZY_ABSPATH . '/includes/layouts/settings.php' );
	}

	/**
	 * Method to save the settings.
	 *
	 * @access  private
	 */
	private function save_settings() {
		$settings = apply_filters( 'feedzy_get_settings', array() );
		switch ( $_POST['tab'] ) {
			case 'headers':
				$settings['header']['user-agent'] = $_POST['user-agent'];
				break;
			case 'proxy':
				$settings['proxy'] = array(
					'host' => $_POST['proxy-host'],
					'port' => $_POST['proxy-port'],
					'user' => $_POST['proxy-user'],
					'pass' => $_POST['proxy-pass'],
				);
				break;
			default:
				$settings = apply_filters( 'feedzy_save_tab_settings', $settings, $_POST['tab'] );
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
		if ( $settings && is_array( $settings['proxy'] ) && ! empty( $settings['proxy'] ) ) {
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
		if ( $proxied && ( ( is_array( $proxied ) && in_array( $uri, $proxied ) ) || $uri === $proxied ) ) {
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


}
