<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://themeisle.com/plugins/feedzy-rss-feed/
 * @since      1.0.0
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
 * @author     Bogdan Preda <bogdan.preda@themeisle.com>
 */

/**
 * Class Feedzy_Rss_Feeds_Import
 */
class Feedzy_Rss_Feeds_Import {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The settings for Feedzy PRO services.
	 *
	 * @since   1.3.2
	 * @access  public
	 * @var     array $settings The settings for Feedzy PRO.
	 */
	private $settings;

	/**
	 * The settings for Feedzy free.
	 *
	 * @access  public
	 * @var     array $settings The settings for Feedzy free.
	 */
	private $free_settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since       1.0.0
	 * @access      public
	 *
	 * @param       string $plugin_name The name of this plugin.
	 * @param       string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->settings = get_option( 'feedzy-rss-feeds-settings', array() );
		$this->free_settings = get_option( 'feedzy-settings', array() );
	}

	/**
	 * Adds the class to the div that shows the upsell.
	 *
	 * @since       ?
	 * @access      public
	 */
	public function upsell_class( $class ) {
		if ( ! feedzy_is_pro() ) {
			$class = 'only-pro';
		}
		return $class;
	}

	/**
	 * Adds the content to the div that shows the upsell.
	 *
	 * @since       ?
	 * @access      public
	 */
	public function upsell_content( $content ) {
		if ( ! feedzy_is_pro() ) {
			$content = '
			<div>
				<div class="only-pro-content">
					<div class="only-pro-container">
						<div class="only-pro-inner">
							<p>' . __( 'This feature is only enabled in the Pro version! To learn more about the benefits of Pro and how you can upgrade', 'feedzy-rss-feeds' ) . '
							<a target="_blank" href="' . FEEDZY_UPSELL_LINK . '" title="' . __( 'Buy Now', 'feedzy-rss-feeds' ) . '">' . __( 'Click here!', 'feedzy-rss-feeds' ) . '</a>
							</p>
						</div>
					</div>
				</div>
			</div>';
		}
		return $content;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since       1.0.0
	 * @access      public
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, FEEDZY_ABSURL . 'css/feedzy-rss-feed-import.css', array(), $this->version, 'all' );
		if ( get_current_screen()->post_type === 'feedzy_imports' ) {
			wp_enqueue_style( $this->plugin_name . '_chosen', FEEDZY_ABSURL . 'includes/views/css/chosen.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name . '_metabox_edit', FEEDZY_ABSURL . 'includes/views/css/import-metabox-edit.css', array( 'wp-jquery-ui-dialog' ), $this->version, 'all' );
			wp_enqueue_script( $this->plugin_name . '_chosen_scipt', FEEDZY_ABSURL . 'includes/views/js/chosen.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script(
				$this->plugin_name . '_metabox_edit_script',
				FEEDZY_ABSURL . 'includes/views/js/import-metabox-edit.js',
				array(
					'jquery',
					'jquery-ui-dialog',
					$this->plugin_name . '_chosen_scipt',
				),
				$this->version,
				true
			);
			wp_localize_script(
				$this->plugin_name . '_metabox_edit_script',
				'feedzy',
				array(
					'ajax' => array(
						'security'  => wp_create_nonce( FEEDZY_BASEFILE ),
					),
					'i10n' => array(
						'importing' => __( 'Importing', 'feedzy-rss-feeds' ) . '...',
						'run_now' => __( 'Run Now', 'feedzy-rss-feeds' ),
						'dry_run_loading' => '<p class="hide-when-loaded">' . __( 'Processing the source and loading the items that will be imported when it runs', 'feedzy-rss-feeds' ) . '...</p>'
										. '<p><b>' . __( 'Please note that if some of these items have already have been imported in previous runs with the same filters, they may be shown here but will not be imported again.', 'feedzy-rss-feeds' ) . '</b></p>'
										. '<p class="loading-img hide-when-loaded"><img src="' . includes_url( 'images/wpspin-2x.gif' ) . '"></p><div></div>',
						'dry_run_title' => __( 'Importable Items', 'feedzy-rss-feeds' ),
					),
				)
			);
		}
	}

	/**
	 * Add attributes to $itemArray.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param   array  $itemArray The item attributes array.
	 * @param   object $item The feed item.
	 * @param   array  $sc The shorcode attributes array.
	 * @param   int    $index The item number.
	 *
	 * @return mixed
	 */
	public function add_data_to_item( $itemArray, $item, $sc = null, $index = null ) {
		$itemArray['item_categories'] = $this->retrieve_categories( null, $item );

		// If set to true, SimplePie will return a unique MD5 hash for the item.
		// If set to false, it will check <guid>, <link>, and <title> before defaulting to the hash.
		$itemArray['item_id']   = $item->get_id( false );

		$itemArray['item']      = $item;
		return $itemArray;
	}

	/**
	 * Retrieve the categories.
	 *
	 * @since   ?
	 * @access  public
	 *
	 * @param   string $dumb The initial categories (only a placeholder argument for the filter).
	 * @param   object $item The feed item.
	 *
	 * @return string
	 */
	public function retrieve_categories( $dumb, $item ) {
		$cats       = array();
		$categories = $item->get_categories();
		if ( $categories ) {
			foreach ( $categories as $category ) {
				$cats[] = $category->get_label();
			}
		}
		return apply_filters( 'feedzy_categories', implode( ', ', $cats ), $cats, $item );
	}

	/**
	 * Register a new post type for feed imports.
	 *
	 * @since   1.2.0
	 * @access  public
	 */
	public function register_import_post_type() {
		$labels   = array(
			'name'               => __( 'Import Posts', 'feedzy-rss-feeds' ),
			'singular_name'      => __( 'Import Post', 'feedzy-rss-feeds' ),
			'add_new'            => __( 'New Import', 'feedzy-rss-feeds' ),
			'add_new_item'       => __( 'New Import', 'feedzy-rss-feeds' ),
			'edit_item'          => __( 'Edit Import', 'feedzy-rss-feeds' ),
			'new_item'           => __( 'New Import Post', 'feedzy-rss-feeds' ),
			'view_item'          => __( 'View Import', 'feedzy-rss-feeds' ),
			'search_items'       => __( 'Search Imports', 'feedzy-rss-feeds' ),
			'not_found'          => __( 'No imports found', 'feedzy-rss-feeds' ),
			'not_found_in_trash' => __( 'No imports in the trash', 'feedzy-rss-feeds' ),
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
			'capability_type'      => 'post',
			'show_in_nav_menus'    => false,
			'rewrite'              => array(
				'slug' => 'feedzy-import',
			),
			'show_in_menu'         => 'feedzy-admin-menu',
			'register_meta_box_cb' => array( $this, 'add_feedzy_import_metaboxes' ),
		);
		$args     = apply_filters( 'feedzy_imports_args', $args );
		register_post_type( 'feedzy_imports', $args );
	}

	/**
	 * Method to add a meta box to `feedzy_imports`
	 * custom post type.
	 *
	 * @since   1.2.0
	 * @access  public
	 */
	public function add_feedzy_import_metaboxes() {
		add_meta_box(
			'feedzy_import_feeds',
			__( 'Feed Import Options', 'feedzy-rss-feeds' ),
			array(
				$this,
				'feedzy_import_feed_options',
			),
			'feedzy_imports',
			'normal',
			'high'
		);
	}

	/**
	 * Method to display metabox for import post type.
	 *
	 * @since   1.2.0
	 * @access  public
	 * @return mixed
	 */
	public function feedzy_import_feed_options() {
		global $post;
		$args                 = array(
			'post_type'      => 'feedzy_categories',
			'posts_per_page' => 100,
		);
		$feed_categories      = get_posts( $args );
		$post_types           = get_post_types( '', 'names' );
		$post_types           = array_diff( $post_types, array( 'feedzy_imports', 'feedzy_categories' ) );
		$published_status     = array( 'publish', 'draft' );
		$import_post_type     = get_post_meta( $post->ID, 'import_post_type', true );
		$import_post_term     = get_post_meta( $post->ID, 'import_post_term', true );
		if ( metadata_exists( $import_post_type, $post->ID, 'import_post_status' ) ) {
			$import_post_status  = get_post_meta( $post->ID, 'import_post_status', true );
		} else {
			add_post_meta( $post->ID, 'import_post_status', 'publish' );
			$import_post_status  = get_post_meta( $post->ID, 'import_post_status', true );
		}
		$source               = get_post_meta( $post->ID, 'source', true );
		$inc_key              = get_post_meta( $post->ID, 'inc_key', true );
		$exc_key              = get_post_meta( $post->ID, 'exc_key', true );
		$import_title         = get_post_meta( $post->ID, 'import_post_title', true );
		$import_date          = get_post_meta( $post->ID, 'import_post_date', true );
		$import_content       = get_post_meta( $post->ID, 'import_post_content', true );
		$import_featured_img  = get_post_meta( $post->ID, 'import_post_featured_img', true );

		// default values so that post is not created empty.
		if ( empty( $import_title ) ) {
			$import_title = '[#item_title]';
		}
		if ( empty( $import_content ) ) {
			$import_content = '[#item_content]';
		}

		$import_link_author_admin         = get_post_meta( $post->ID, 'import_link_author_admin', true );
		$import_link_author_public        = get_post_meta( $post->ID, 'import_link_author_public', true );

		// admin, public
		$import_link_author = array( '', '' );
		if ( $import_link_author_admin === 'yes' ) {
			$import_link_author[0] = 'checked';
		}
		if ( $import_link_author_public === 'yes' ) {
			$import_link_author[1] = 'checked';
		}

		// maybe more options are required from pro?
		$pro_options = apply_filters( 'feedzy_metabox_options', array(), $post->ID );

		$import_custom_fields = get_post_meta( $post->ID, 'imports_custom_fields', true );
		$import_feed_limit    = get_post_meta( $post->ID, 'import_feed_limit', true );
		if ( empty( $import_feed_limit ) ) {
			$import_feed_limit = 10;
		}
		$import_feed_delete_days    = intval( get_post_meta( $post->ID, 'import_feed_delete_days', true ) );
		if ( empty( $import_feed_delete_days ) ) {
			$import_feed_delete_days = 0;
		}
		$post_status          = $post->post_status;
		$nonce                = wp_create_nonce( FEEDZY_BASEFILE );
		$invalid_source_msg   = apply_filters( 'feedzy_get_source_validity_error', '', $post );
		$output               = '
            <input type="hidden" name="feedzy_category_meta_noncename" id="feedzy_category_meta_noncename" value="' . $nonce . '" />
        ';

		add_thickbox();
		include FEEDZY_ABSPATH . '/includes/views/import-metabox-edit.php';
		echo $output;
	}

	/**
	 * Change number of posts imported.
	 */
	public function items_limit( $range, $post ) {
		if ( ! feedzy_is_pro() ) {
			$range = range( 10, 10, 10 );
		}
		return $range;
	}

	/**
	 * Save method for custom post type
	 * import feeds.
	 *
	 * @since   1.2.0
	 * @access  public
	 *
	 * @param   integer $post_id The post ID.
	 * @param   object  $post The post object.
	 *
	 * @return bool
	 */
	public function save_feedzy_import_feed_meta( $post_id, $post ) {
		if (
			empty( $_POST ) ||
			get_post_type( $post_id ) !== 'feedzy_imports' ||
			( ! defined( 'TI_UNIT_TESTING' ) && ! wp_verify_nonce( $_POST['feedzy_category_meta_noncename'], FEEDZY_BASEFILE ) ) ||
			! current_user_can( 'edit_post', $post_id )
		) {
			return $post_id;
		}
		$data_meta            = isset( $_POST['feedzy_meta_data'] ) ? ( is_array( $_POST['feedzy_meta_data'] ) ? $_POST['feedzy_meta_data'] : array() ) : array();
		$custom_fields_keys   = isset( $_POST['custom_vars_key'] ) ? ( is_array( $_POST['custom_vars_key'] ) ? $_POST['custom_vars_key'] : array() ) : array();
		$custom_fields_values = isset( $_POST['custom_vars_value'] ) ? ( is_array( $_POST['custom_vars_value'] ) ? $_POST['custom_vars_value'] : array() ) : array();
		$custom_fields        = array();
		foreach ( $custom_fields_keys as $index => $key_value ) {
			$value = '';
			if ( isset( $custom_fields_values[ $index ] ) ) {
				$value = implode( ',', (array) $custom_fields_values[ $index ] );
			}
			$custom_fields[ $key_value ] = $value;
		}
		if ( $post->post_type !== 'revision' ) {
			// delete these checkbox related fields; if checked, they will be added below.
			delete_post_meta( $post_id, 'import_link_author_admin' );
			delete_post_meta( $post_id, 'import_link_author_public' );

			// we will activate this import only if the source has no invalid URL(s)
			$source_is_valid = false;

			foreach ( $data_meta as $key => $value ) {
				$value = is_array( $value ) ? implode( ',', $value ) : implode( ',', (array) $value );
				if ( 'source' === $key ) {
					// check if the source is valid
					$invalid_urls = apply_filters( 'feedzy_check_source_validity', $value, $post_id, true, false );
					$source_is_valid = empty( $invalid_urls );
				}

				if ( get_post_meta( $post_id, $key, false ) ) {
					update_post_meta( $post_id, $key, wp_kses( $value, wp_kses_allowed_html( 'post' ) ) );
				} else {
					add_post_meta( $post_id, $key, wp_kses( $value, wp_kses_allowed_html( 'post' ) ) );
				}
				if ( ! $value ) {
					delete_post_meta( $post_id, $key );
				}
			}
			// Added this to activate post if publish is clicked and sometimes it does not change status.
			if ( $source_is_valid && isset( $_POST['custom_post_status'] ) && $_POST['custom_post_status'] === 'Publish' ) {
				$activate = array(
					'ID'          => $post_id,
					'post_status' => 'publish',
				);
				remove_action( 'save_post_feedzy_imports', array( $this, 'save_feedzy_import_feed_meta' ), 1, 2 );
				wp_update_post( $activate );
				add_action( 'save_post_feedzy_imports', array( $this, 'save_feedzy_import_feed_meta' ), 1, 2 );
			}

			do_action( 'feedzy_save_fields', $post_id, $post );
		}
		return true;
	}

	/**
	 * Redirect save post to post listing.
	 *
	 * @access  public
	 *
	 * @param   string $location   The url to redirect to.
	 * @param   int    $post_id    The post ID.
	 *
	 * @return string
	 */
	public function redirect_post_location( $location, $post_id ) {
		$post = get_post( $post_id );
		if ( 'feedzy_imports' === $post->post_type ) {
			// if invalid source has been found, redirect back to edit screen
			// where errors can be shown
			$invalid = get_post_meta( $post_id, '__transient_feedzy_invalid_source', true );
			error_log( "redirect_post_location $post_id = " . print_r( $invalid, true ) );
			if ( empty( $invalid ) ) {
				return admin_url( 'edit.php?post_type=feedzy_imports' );
			}
		}
		return $location;
	}

	/**
	 * Method to add header columns to import feeds table.
	 *
	 * @since   1.2.0
	 * @access  public
	 *
	 * @param   array $columns The columns array.
	 *
	 * @return array|bool
	 */
	public function feedzy_import_columns( $columns ) {
		$columns['title'] = __( 'Import Title', 'feedzy-rss-feeds' );
		if ( $new_columns = $this->array_insert_before( 'date', $columns, 'feedzy-source', __( 'Source', 'feedzy-rss-feeds' ) ) ) {
			$columns = $new_columns;
		} else {
			$columns['feedzy-source'] = __( 'Source', 'feedzy-rss-feeds' );
		}

		if ( $new_columns = $this->array_insert_before( 'date', $columns, 'feedzy-status', __( 'Current Status', 'feedzy-rss-feeds' ) ) ) {
			$columns = $new_columns;
		} else {
			$columns['feedzy-status'] = __( 'Current Status', 'feedzy-rss-feeds' );
		}

		if ( $new_columns = $this->array_insert_before( 'date', $columns, 'feedzy-next_run', __( 'Next Run', 'feedzy-rss-feeds' ) ) ) {
			$columns = $new_columns;
		} else {
			$columns['feedzy-next_run'] = __( 'Next Run', 'feedzy-rss-feeds' );
		}

		if ( $new_columns = $this->array_insert_before( 'date', $columns, 'feedzy-last_run', __( 'Last Run Status', 'feedzy-rss-feeds' ) ) ) {
			$columns = $new_columns;
		} else {
			$columns['feedzy-last_run'] = __( 'Last Run Status', 'feedzy-rss-feeds' );
		}

		unset( $columns['date'] );

		return $columns;
	}

	/**
	 * Utility method to insert before specific key
	 * in an associative array.
	 *
	 * @since   1.2.0
	 * @access  public
	 *
	 * @param   string $key The key before to insert.
	 * @param   array  $array The array in which to insert the new key.
	 * @param   string $new_key The new key name.
	 * @param   mixed  $new_value The new key value.
	 *
	 * @return array|bool
	 */
	protected function array_insert_before( $key, &$array, $new_key, $new_value ) {
		if ( array_key_exists( $key, $array ) ) {
			$new = array();
			foreach ( $array as $k => $value ) {
				if ( $k === $key ) {
					$new[ $new_key ] = $new_value;
				}
				$new[ $k ] = $value;
			}

			return $new;
		}

		return false;
	}

	/**
	 * Method to add a columns in the import feeds table.
	 *
	 * @since   1.2.0
	 * @access  public
	 *
	 * @param   string  $column The current column to check.
	 * @param   integer $post_id The post ID.
	 */
	public function manage_feedzy_import_columns( $column, $post_id ) {
		global $post;
		switch ( $column ) {
			case 'feedzy-source':
				$src = get_post_meta( $post_id, 'source', true );
				// if the source is a category, link it.
				if ( strpos( $src, 'http' ) === false && strpos( $src, 'https' ) === false ) {
					$src = sprintf( '%s: %s%s%s', __( 'Feed Category', 'feedzy-rss-feeds' ), '<a href="' . admin_url( 'edit.php?post_type=feedzy_categories' ) . '" target="_blank">', $src, '</a>' );
				} else {
					// else link it to the feed but shorten it if it is too long.
					$too_long = 65;
					$src = sprintf( '%s%s%s', '<a href="' . $src . '" target="_blank" title="' . __( 'Click to view', 'feedzy-rss-feeds' ) . '">', ( strlen( $src ) > $too_long ? substr( $src, 0, $too_long ) . '...' : $src ), '</a>' );
				}
				echo $src;
				break;
			case 'feedzy-status':
				$status = $post->post_status;
				if ( empty( $status ) ) {
					echo __( 'Undefined', 'feedzy-rss-feeds' );
				} else {
					if ( $status === 'publish' ) {
						$checked = 'checked';
					} else {
						$checked = '';
					}
					echo '
                    <div class="switch">
                        <input id="feedzy-toggle_' . $post->ID . '" class="feedzy-toggle feedzy-toggle-round" type="checkbox" value="' . $post->ID . '" ' . $checked . '>
                        <label for="feedzy-toggle_' . $post->ID . '"></label>
						<span class="feedzy-spinner spinner"></span>
                    </div>
                    ';
				}
				break;
			case 'feedzy-last_run':
				$last   = get_post_meta( $post_id, 'last_run', true );
				$msg    = __( 'Never Run', 'feedzy-rss-feeds' );
				if ( $last ) {
					$now  = new DateTime();
					$then = new DateTime();
					$then = $then->setTimestamp( $last );
					$in   = $now->diff( $then );
					$msg  = sprintf( __( 'Ran %1$d hours %2$d minutes ago', 'feedzy-rss-feeds' ), $in->format( '%h' ), $in->format( '%i' ) );
				}

				$msg .= $this->get_last_run_details( $post_id );
				echo $msg;

				if ( 'publish' === $post->post_status ) {
					 echo sprintf( '<p><input type="button" class="button button-primary feedzy-run-now" data-id="%d" value="%s"></p>', $post_id, __( 'Run Now', 'feedzy-rss-feeds' ) );
				}

				break;
			case 'feedzy-next_run':
				$next = wp_next_scheduled( 'feedzy_cron' );
				if ( $next ) {
					$now  = new DateTime();
					$then = new DateTime();
					$then = $then->setTimestamp( $next );
					$in   = $now->diff( $then );
					echo sprintf( __( 'In %1$d hours %2$d minutes', 'feedzy-rss-feeds' ), $in->format( '%h' ), $in->format( '%i' ) );
				}
				break;
			default:
				break;
		}
	}

	/**
	 * Generate the markup that displays the status.
	 *
	 * @since   ?
	 * @access  private
	 *
	 * @param   integer $post_id The post ID.
	 */
	private function get_last_run_details( $post_id ) {
		$msg    = '';
		$last   = get_post_meta( $post_id, 'last_run', true );
		$status = array(
			'total' => '-',
			'items' => '-',
			'duplicates' => '-',
			'cumulative' => '-',
		);
		if ( $last ) {
			$status = array(
				'total' => 0,
				'items' => 0,
				'duplicates' => 0,
				'cumulative' => 0,
			);
			$status = $this->get_complete_import_status( $post_id );
		}

		// link to the posts listing for this job.
		$job_linked_posts   = add_query_arg( array( 'feedzy_job_id' => $post_id, 'post_type' => get_post_meta( $post_id, 'import_post_type', true ) ), admin_url( 'edit.php' ) );

		// link to the posts listing for this job run.
		$job_run_linked_posts    = '';
		$job_run_id   = get_post_meta( $post_id, 'last_run_id', true );
		if ( ! empty( $job_run_id ) ) {
			$job_run_linked_posts    = add_query_arg( array( 'feedzy_job_id' => $post_id, 'feedzy_job_time' => $job_run_id, 'post_type' => get_post_meta( $post_id, 'import_post_type', true ) ), admin_url( 'edit.php' ) );
		}

		// popup for items found.
		if ( is_array( $status['items'] ) ) {
			$msg .= '<div class="feedzy-items-found-' . $post_id . ' feedzy-dialog" title="' . __( 'Items found', 'feedzy-rss-feeds' ) . '"><ol>';
			foreach ( $status['items'] as $url => $title ) {
				$msg .= sprintf( '<li><p><a href="%s" target="_blank">%s</a></p></li>', esc_url( $url ), esc_html( $title ) );
			}
			$msg .= '</ol></div>';
		}

		// popup for duplicates found.
		if ( is_array( $status['duplicates'] ) ) {
			$msg .= '<div class="feedzy-duplicates-found-' . $post_id . ' feedzy-dialog" title="' . __( 'Duplicates found', 'feedzy-rss-feeds' ) . '"><ol>';
			foreach ( $status['duplicates'] as $url => $title ) {
				$msg .= sprintf( '<li><p><a href="%s" target="_blank">%s</a></p></li>', esc_url( $url ), esc_html( $title ) );
			}
			$msg .= '</ol></div>';
		}

		$errors = $this->get_import_errors( $post_id );
		// popup for errors found.
		if ( ! empty( $errors ) ) {
			$msg .= '<div class="feedzy-errors-found-' . $post_id . ' feedzy-dialog" title="' . __( 'Errors', 'feedzy-rss-feeds' ) . '">' . $errors . '</div>';
		}

		// remember, cypress will work off the data-value attributes.
		$msg .= sprintf(
			'<script class="feedzy-last-run-data" type="text/template">
				<tr style="display: none"></tr>
				<tr class="feedzy-import-status-row">
					<td colspan="6" align="right">
						<table>
							<tr>
								<td class="feedzy-items %s" data-value="%d"><a class="feedzy-popup-details feedzy-dialog-open" title="%s" data-dialog="feedzy-items-found-%d">%s</a></td>
								<td class="feedzy-duplicates %s" data-value="%d"><a class="feedzy-popup-details feedzy-dialog-open" title="%s" data-dialog="feedzy-duplicates-found-%d">%s</a></td>
								<td class="feedzy-imported %s" data-value="%d"><a target="%s" href="%s" class="feedzy-popup-details" title="%s">%s</a></td>
								<td class="feedzy-cumulative %s" data-value="%d"><a target="%s" href="%s" class="feedzy-popup-details" title="%s">%s</a></td>
								<td class="feedzy-error-status %s" data-value="%d"><a class="feedzy-popup-details feedzy-dialog-open" data-dialog="feedzy-errors-found-%d" title="%s">%s</a></td>
							</tr>
							<tr>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
							</tr>
						</table>
					</td>
				</tr>
			</script>',
			// first cell
			is_array( $status['items'] ) ? 'feedzy-has-popup' : '',
			is_array( $status['items'] ) ? count( $status['items'] ) : $status['items'],
			__( 'Items that were found in the feed', 'feedzy-rss-feeds' ),
			$post_id,
			is_array( $status['items'] ) ? count( $status['items'] ) : $status['items'],
			// second cells
			is_array( $status['duplicates'] ) ? 'feedzy-has-popup' : '',
			is_array( $status['duplicates'] ) ? count( $status['duplicates'] ) : $status['duplicates'],
			__( 'Items that were discarded as duplicates', 'feedzy-rss-feeds' ),
			$post_id,
			is_array( $status['duplicates'] ) ? count( $status['duplicates'] ) : $status['duplicates'],
			// third cell
			$status['total'] > 0 && ! empty( $job_run_linked_posts ) ? 'feedzy-has-popup' : '',
			$status['total'],
			defined( 'TI_CYPRESS_TESTING' ) ? '' : '_blank',
			$status['total'] > 0 && ! empty( $job_run_linked_posts ) ? $job_run_linked_posts : '',
			__( 'Items that were imported', 'feedzy-rss-feeds' ),
			$status['total'],
			// fourth cell
			$status['cumulative'] > 0 ? 'feedzy-has-popup' : '',
			$status['cumulative'],
			defined( 'TI_CYPRESS_TESTING' ) ? '' : '_blank',
			$status['cumulative'] > 0 ? $job_linked_posts : '',
			__( 'Items that were imported across all runs', 'feedzy-rss-feeds' ),
			$status['cumulative'],
			// fifth cell
			empty( $last ) ? '' : ( ! empty( $errors ) ? 'feedzy-has-popup import-error' : 'import-success' ),
			empty( $last ) ? '-1' : ( ! empty( $errors ) ? 0 : 1 ),
			$post_id,
			__( 'View the errors', 'feedzy-rss-feeds' ),
			empty( $last ) ? '-' : ( ! empty( $errors ) ? '<i class="dashicons dashicons-warning"></i>' : '<i class="dashicons dashicons-yes-alt"></i>' ),
			// second row
			__( 'Found', 'feedzy-rss-feeds' ),
			__( 'Duplicates', 'feedzy-rss-feeds' ),
			__( 'Imported', 'feedzy-rss-feeds' ),
			__( 'Cumulative', 'feedzy-rss-feeds' ),
			__( 'Status', 'feedzy-rss-feeds' )
		);

		return $msg;
	}

	/**
	 * Gets every aspect of the import job that would reflect its status.
	 *
	 * @since   ?
	 * @access  private
	 */
	private function get_complete_import_status( $post_id ) {
		$items_count  = get_post_meta( $post_id, 'imported_items_count', true );
		$items      = get_post_meta( $post_id, 'imported_items_hash', true );
		if ( empty( $items ) ) {
			$items      = get_post_meta( $post_id, 'imported_items', true );
		}
		$count  = $items_count;
		if ( '' === $count && $items ) {
			// backward compatibility where imported_items_count post_meta has not been populated yet
			$count  = count( $items );
		}

		$status = array(
			'total' => $count,
			'items' => 0,
			'duplicates' => 0,
			'cumulative' => 0,
		);

		$import_info = get_post_meta( $post_id, 'import_info', true );
		if ( $import_info ) {
			foreach ( $import_info as $label => $value ) {
				switch ( $label ) {
					case 'total':
						if ( count( $value ) > 0 ) {
							$status['items'] = $value;
						}
						break;
					case 'duplicates':
						if ( count( $value ) > 0 ) {
							$status['duplicates'] = $value;
						}
						break;
				}
			}
		}

		$items      = get_post_meta( $post_id, 'imported_items_hash', true );
		if ( empty( $items ) ) {
			$items      = get_post_meta( $post_id, 'imported_items', true );
		}
		if ( $items ) {
			$status['cumulative'] = count( $items );
		}

		return $status;

	}

	/**
	 * Creates the data by extracting the 'import_errors' from each import.
	 *
	 * @since   ?
	 * @access  private
	 */
	private function get_import_errors( $post_id ) {
		$msg = '';
		$import_errors = get_post_meta( $post_id, 'import_errors', true );
		if ( $import_errors ) {
			$errors = '';
			if ( is_array( $import_errors ) ) {
				foreach ( $import_errors as $err ) {
					$errors .= '<div><i class="dashicons dashicons-warning"></i>' . $err . '</div>';
				}
			} else {
				$errors = '<div><i class="dashicons dashicons-warning"></i>' . $import_errors . '</div>';
			}
			$msg = '<div class="feedzy-error feedzy-api-error">' . $errors . '</div>';
		}

		$pro_msg = apply_filters( 'feedzy_run_status_errors', '', $post_id );

		// the pro messages may not have the dashicons, so let's add them.
		if ( $pro_msg && strpos( $pro_msg, 'dashicons-warning' ) === false ) {
			$errors = '';
			$pro_errors = explode( '<br>', $pro_msg );
			if ( is_array( $pro_errors ) ) {
				foreach ( $pro_errors as $err ) {
					$errors .= '<div><i class="dashicons dashicons-warning"></i>' . $err . '</div>';
				}
			} else {
				$errors = '<div><i class="dashicons dashicons-warning"></i>' . $pro_errors . '</div>';
			}
			$pro_msg = '<div class="feedzy-error feedzy-api-error">' . $errors . '</div>';

		}

		return $msg . $pro_msg;
	}

	/**
	 * AJAX single-entry method.
	 *
	 * @since   3.4.1
	 * @access  public
	 */
	public function ajax() {
		check_ajax_referer( FEEDZY_BASEFILE, 'security' );

		$_POST['feedzy_category_meta_noncename'] = $_POST['security'];

		switch ( $_POST['_action'] ) {
			case 'import_status':
				$this->import_status();
				break;
			case 'get_taxonomies':
				$this->get_taxonomies();
				break;
			case 'run_now':
				$this->run_now();
				break;
			case 'purge':
				$this->purge_data();
				break;
			case 'dry_run':
				$this->dry_run();
				break;
		}
	}

	/**
	 * AJAX called method to update post status.
	 *
	 * @since   1.2.0
	 * @access  private
	 */
	private function import_status() {
		global $wpdb;
		$id      = $_POST['id'];
		$status  = $_POST['status'];
		$publish = 'draft';

		// no activation till source is not valid.
		if ( $status === 'true' ) {
			$invalid_urls = apply_filters( 'feedzy_check_source_validity', get_post_meta( $id, 'source', true ), $id, true, false );
			if ( ! empty( $invalid_urls ) ) {
				$msg = apply_filters( 'feedzy_get_source_validity_error', '', get_post( $id ), '' );
				wp_send_json_error( array( 'msg' => $msg ) );
			}

			$publish = 'publish';
		}

		$new_post_status = array(
			'ID'          => $id,
			'post_status' => $publish,
		);

		remove_action( 'save_post_feedzy_imports', array( $this, 'save_feedzy_import_feed_meta' ), 1, 2 );
		$post_id         = wp_update_post( $new_post_status );
		add_action( 'save_post_feedzy_imports', array( $this, 'save_feedzy_import_feed_meta' ), 1, 2 );

		if ( is_wp_error( $post_id ) ) {
			$errors = $post_id->get_error_messages();
			wp_send_json_error( array( 'msg' => implode( ', ', $errors ) ) );
		}
		wp_send_json_success();
	}

	/**
	 * AJAX method to get taxonomies for a given post_type.
	 *
	 * @since   1.2.0
	 * @access  private
	 */
	private function get_taxonomies() {
		$post_type  = $_POST['post_type'];
		$taxonomies = get_object_taxonomies(
			array(
				'post_type' => $post_type,
			)
		);
		$results    = array();
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$terms                = get_terms(
					array(
						'taxonomy'   => $taxonomy,
						'hide_empty' => false,
					)
				);
				$results[ $taxonomy ] = $terms;
			}
		}
		echo json_encode( $results );
		wp_die();
	}

	/**
	 * Run a specific job.
	 *
	 * @since   1.6.1
	 * @access  private
	 */
	private function run_now() {
		$job    = get_post( $_POST['id'] );
		$count  = $this->run_job( $job, 100 );

		$msg    = $count > 0 ? __( 'Successfully run!', 'feedzy-rss-feeds' ) : __( 'Nothing imported!', 'feedzy-rss-feeds' );
		$msg    .= ' (' . __( 'Refresh this page for the updated status', 'feedzy-rss-feeds' ) . ')';

		wp_send_json_success( array( 'msg' => $msg ) );
	}

	/**
	 * Dry run a specific job so that the user is aware what would be imported.
	 *
	 * @since  ?
	 * @access  private
	 */
	private function dry_run() {
		$fields = urldecode( $_POST['fields'] );
		parse_str( $fields, $data );

		$feedzy_meta_data = $data['feedzy_meta_data'];

		add_filter(
			'feedzy_default_error', function( $errors, $feed, $url ) {
				$errors .=
				sprintf( __( 'For %1$ssingle feeds%2$s, this could be because of the following reasons:', 'feedzy-rss-feeds' ), '<b>', '</b>' )
				. '<ol>'
				. '<li>' . sprintf( __( '%1$sSource invalid%2$s: Check that your source is valid by clicking the validate button adjacent to the source box.', 'feedzy-rss-feeds' ), '<b>', '</b>' ) . '</li>'
				. '<li>' . sprintf( __( '%1$sSource unavailable%2$s: Copy the source and paste it on the browser to check that it is available. It could be an intermittent issue.', 'feedzy-rss-feeds' ), '<b>', '</b>' ) . '</li>'
				. '<li>' . sprintf( __( '%1$sSource inaccessible from server%2$s: Check that your source is accessible from the server (not the browser). It could be an intermittent issue.', 'feedzy-rss-feeds' ), '<b>', '</b>' ) . '</li>'
				. '</ol>'
				. sprintf( __( 'For %1$smultiple feeds%2$s (comma-separated or in a Feedzy Category), this could be because of the following reasons:', 'feedzy-rss-feeds' ), '<b>', '</b>' )
				. '<ol>'
				. '<li>' . sprintf( __( '%1$sSource invalid%2$s: One or more feeds may be misbehaving. Check each feed individually as mentioned above to weed out the problematic feed.', 'feedzy-rss-feeds' ), '<b>', '</b>' ) . '</li>'
				. '</ol>';

				return $errors;
			}, 11, 3
		);

		// we will add tags corresponding to the most potential problems.
		$tags = array();
		if ( $this->feedzy_is_business() && strpos( $feedzy_meta_data['import_post_content'], 'full_content' ) !== false ) {
			$tags[] = 'item_full_content';
		}
		if ( strpos( $feedzy_meta_data['import_post_content'], 'item_image' ) !== false || strpos( $feedzy_meta_data['import_post_featured_img'], 'item_image' ) !== false ) {
			$tags[] = 'item_image';
		}

		$shortcode = sprintf(
			'[feedzy-rss feeds="%s" max="%d" feed_title=no meta=no summary=no thumb=no error_empty="%s" keywords_title="%s" %s="%s" _dry_run_tags_="%s" _dryrun_="yes"]',
			$feedzy_meta_data['source'],
			$feedzy_meta_data['import_feed_limit'],
			'', // should be empty
			$feedzy_meta_data['inc_key'],
			feedzy_is_pro() ? 'keywords_ban' : '',
			feedzy_is_pro() ? $feedzy_meta_data['exc_key'] : '',
			implode( ',', $tags )
		);

		wp_send_json_success( array( 'output' => do_shortcode( $shortcode ) ) );
	}


	/**
	 * The Cron Job.
	 *
	 * @since   1.2.0
	 * @access  public
	 */
	public function run_cron( $max = 100 ) {
		if ( empty( $max ) ) {
			$max = 10;
		}
		global $post;
		$args           = array(
			'post_type'   => 'feedzy_imports',
			'post_status' => 'publish',
			'numberposts' => 300,
		);
		$feedzy_imports = get_posts( $args );
		foreach ( $feedzy_imports as $job ) {
			$this->run_job( $job, $max );
			do_action( 'feedzy_run_cron_extra', $job );
		}
	}

	/**
	 * Runs a specific job.
	 *
	 * @since   1.6.1
	 * @access  private
	 * @return  int
	 */
	private function run_job( $job, $max ) {
		$source               = get_post_meta( $job->ID, 'source', true );
		$inc_key              = get_post_meta( $job->ID, 'inc_key', true );
		$exc_key              = get_post_meta( $job->ID, 'exc_key', true );
		$import_title         = get_post_meta( $job->ID, 'import_post_title', true );
		$import_date          = get_post_meta( $job->ID, 'import_post_date', true );
		$import_content       = get_post_meta( $job->ID, 'import_post_content', true );
		$import_featured_img  = get_post_meta( $job->ID, 'import_post_featured_img', true );
		$import_post_type     = get_post_meta( $job->ID, 'import_post_type', true );
		$import_post_term     = get_post_meta( $job->ID, 'import_post_term', true );
		$import_feed_limit    = get_post_meta( $job->ID, 'import_feed_limit', true );
		$max                  = $import_feed_limit;
		if ( metadata_exists( $import_post_type, $job->ID, 'import_post_status' ) ) {
			$import_post_status  = get_post_meta( $job->ID, 'import_post_status', true );
		} else {
			add_post_meta( $job->ID, 'import_post_status', 'publish' );
			$import_post_status  = get_post_meta( $job->ID, 'import_post_status', true );
		}

		// the array of imported items that uses the old scheme of custom hashing the url and date
		$imported_items = array();
		$imported_items_old       = get_post_meta( $job->ID, 'imported_items', true );
		if ( ! is_array( $imported_items_old ) ) {
			$imported_items_old = array();
		}

		// the array of imported items that uses the new scheme of SimplePie's hash/id
		$imported_items_new       = get_post_meta( $job->ID, 'imported_items_hash', true );
		if ( ! is_array( $imported_items_new ) ) {
			$imported_items_new = array();
		}

		// Note: this implementation will only work if only one of the fields is allowed to provide
		// the date, because if the title can have UTC date and content can have local date then it
		// all goes sideways.
		// also if the user provides multiple date types, local will win.
		$meta               = 'yes';
		if ( strpos( $import_title, '[#item_date_local]' ) !== false ) {
			$meta           = 'author, date, time, tz=local';
		} elseif ( strpos( $import_title, '[#item_date_feed]' ) !== false ) {
			$meta           = 'author, date, time, tz=no';
		}

		$options = apply_filters(
			'feedzy_shortcode_options', array(
				'feeds'          => $source,
				'max'            => $max,
				'feed_title'     => 'no',
				'target'         => '_blank',
				'title'          => '',
				'meta'           => $meta,
				'summary'        => 'yes',
				'summarylength'  => '',
				'thumb'          => 'auto',
				'default'        => '',
				'size'           => '250',
				'keywords_title' => $inc_key,
				'keywords_ban'   => $exc_key,
				'columns'        => 1,
				'offset'         => 0,
				'multiple_meta'  => 'no',
				'refresh'        => '55_mins',
			), $job
		);

		$options['__jobID'] = $job->ID;

		$last_run = time();
		update_post_meta( $job->ID, 'last_run', $last_run );
		// we will use this last_run_id to associate imports with a specific job run.
		update_post_meta( $job->ID, 'last_run_id', $last_run );
		delete_post_meta( $job->ID, 'import_errors' );
		delete_post_meta( $job->ID, 'import_info' );

		// let's increase this time in case spinnerchief/wordai is being used.
		ini_set( 'max_execution_time', apply_filters( 'feedzy_max_execution_time', 500 ) );

		$count = $index = $import_image_errors = $duplicates = 0;

		// the array that captures errors about the import.
		$import_errors = array();

		// the array that captures additional information about the import.
		$import_info = array();

		$results = $this->get_job_feed( $options, $import_content, true );
		if ( is_wp_error( $results ) ) {
			$import_errors[] = $results->get_error_message();
			update_post_meta( $job->ID, 'import_errors', $import_errors );
			update_post_meta( $job->ID, 'imported_items_count', 0 );
			return;
		}

		$result = $results['items'];
		do_action( 'feedzy_run_job_pre', $job, $result );

		// check if we should be using the old scheme of custom hashing the url and date
		// or the new scheme of depending on SimplePie's hash/id
		// basically if the old scheme hasn't be used before, use the new scheme
		// BUT if the old scheme has been used, continue with it.
		$use_new_hash = empty( $imported_items_old );
		$imported_items = $use_new_hash ? $imported_items_new : $imported_items_old;

		$start_import = true;
		// bail if both title and content are empty because the post will not be created.
		if ( empty( $import_title ) && empty( $import_content ) ) {
			$import_errors[] = __( 'Title & Content are both empty.', 'feedzy-rss-feeds' );
			$start_import = false;
		}

		if ( ! $start_import ) {
			update_post_meta( $job->ID, 'import_errors', $import_errors );
			return 0;
		}

		$duplicates = $items_found = array();

		foreach ( $result as $item ) {
			$item_hash = $use_new_hash ? $item['item_id'] : hash( 'sha256', $item['item_url'] . '_' . $item['item_date'] );
			$is_duplicate = $use_new_hash ? in_array( $item_hash, $imported_items_new, true ) : in_array( $item_hash, $imported_items_old, true );
			$items_found[ $item['item_url'] ] = $item['item_title'];

			if ( $is_duplicate ) {
				do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Ignoring %s as it is a duplicate (%s hash).', $item_hash, $use_new_hash ? 'new' : 'old' ), 'warn', __FILE__, __LINE__ );
				$index++;
				$duplicates[ $item['item_url'] ] = $item['item_title'];
				continue;
			}

			$import_image = strpos( $import_content, '[#item_image]' ) !== false || strpos( $import_featured_img, '[#item_image]' ) !== false;
			if ( $import_image && empty( $item['item_img_path'] ) ) {
				do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Unable to find an image for item title %s.', $item['item_title'] ), 'warn', __FILE__, __LINE__ );
				$import_image_errors++;
			}

			$author     = '';
			if ( $item['item_author'] ) {
				if ( is_string( $item['item_author'] ) ) {
					$author = $item['item_author'];
				} elseif ( is_object( $item['item_author'] ) ) {
					$author = $item['item_author']->get_name();
					if ( empty( $author ) ) {
						$author = $item['item_author']->get_email();
					}
				}
			} else {
				do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Author is empty for %s.', $item['item_title'] ), 'warn', __FILE__, __LINE__ );
			}

			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$item_date = date( get_option( 'date_format' ) . ' at ' . get_option( 'time_format' ), $item['item_date'] );
			$item_date = $item['item_date_formatted'];

			$post_title = str_replace(
				array(
					'[#item_title]',
					'[#item_author]',
					'[#item_date]',
					'[#item_date_local]',
					'[#item_date_feed]',
					'[#item_source]',
				),
				array(
					$item['item_title'],
					$author,
					$item_date,
					$item_date,
					$item_date,
					$item['item_source'],
				),
				$import_title
			);

			if ( $this->feedzy_is_business() ) {
				$post_title = apply_filters( 'feedzy_parse_custom_tags', $post_title, $results['feed'], $index );
			}

			$post_title = apply_filters( 'feedzy_invoke_services', $post_title, 'title', $item['item_title'], $job );

			$item_link = '<a href="' . $item['item_url'] . '" target="_blank">' . __( 'Read More', 'feedzy-rss-feeds' ) . '</a>';
			$image_html   = '<img src="' . $item['item_img_path'] . '" title="' . $item['item_title'] . '" />';
			$post_content = str_replace(
				array(
					'[#item_description]',
					'[#item_content]',
					'[#item_image]',
					'[#item_url]',
					'[#item_categories]',
					'[#item_source]',
				),
				array(
					$item['item_description'],
					! empty( $item['item_content'] ) ? $item['item_content'] : $item['item_description'],
					$image_html,
					$item_link,
					$item['item_categories'],
					$item['item_source'],
				),
				$import_content
			);

			if ( $this->feedzy_is_business() ) {
				$full_content = ! empty( $item['item_full_content'] ) ? $item['item_full_content'] : $item['item_content'];
				if ( false !== strpos( $post_content, '[#item_full_content]' ) ) {
					// if full content is empty, log a message
					if ( empty( $full_content ) ) {
						// let's see if there is an error.
						$full_content_error = isset( $item['full_content_error'] ) && ! empty( $item['full_content_error'] ) ? $item['full_content_error'] : '';
						if ( empty( $full_content_error ) ) {
							$full_content_error = __( 'Unknown', 'feedzy-rss-feeds' );
						}
						$import_errors[] = sprintf( __( 'Full content is empty. Error: %s', 'feedzy-rss-feeds' ), $full_content_error );
					}

					$post_content = str_replace(
						array(
							'[#item_full_content]',
						),
						array(
							$full_content,
						),
						$post_content
					);
				}
				$post_content = apply_filters( 'feedzy_invoke_services', $post_content, 'full_content', $full_content, $job );
			}

			if ( $this->feedzy_is_business() ) {
				$post_content = apply_filters( 'feedzy_parse_custom_tags', $post_content, $results['feed'], $index );
			}

			$post_content   = apply_filters( 'feedzy_invoke_services', $post_content, 'content', $item['item_description'], $job );

			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$item_date = date( 'Y-m-d H:i:s', $item['item_date'] );
			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$now       = date( 'Y-m-d H:i:s' );
			if ( trim( $import_date ) === '' ) {
				$post_date = $now;
			}
			$post_date   = str_replace( '[#item_date]', $item_date, $import_date );
			$post_date   = str_replace( '[#post_date]', $now, $post_date );

			$new_post    = apply_filters(
				'feedzy_insert_post_args', array(
					'post_type'    => $import_post_type,
					'post_title'   => $post_title,
					'post_content' => $post_content,
					'post_date'    => $post_date,
					'post_status'  => $import_post_status,
				),
				$item,
				$post_title,
				$post_content,
				$index,
				$job
			);

			// no point creating a post if either the title or the content is null.
			if ( is_null( $post_title ) || is_null( $post_content ) ) {
				do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'NOT creating a new post as title (%s) or content (%s) is null.', $post_title, $post_content ), 'info', __FILE__, __LINE__ );
				$index++;
				$import_errors[] = __( 'Title or Content is empty.', 'feedzy-rss-feeds' );
				continue;
			}

			$new_post_id = wp_insert_post( $new_post, true );
			if ( $new_post_id === 0 || is_wp_error( $new_post_id ) ) {
				$error_reason = 'N/A';
				if ( is_wp_error( $new_post_id ) ) {
					$error_reason = $new_post_id->get_error_message();
					if ( ! empty( $error_reason ) ) {
						$import_errors[] = $error_reason;
					}
				}
				do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Unable to create a new post with params %s. Error: %s', print_r( $new_post, true ), $error_reason ), 'error', __FILE__, __LINE__ );
				$index++;
				continue;
			}
			do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'created new post with ID %d with post_content %s', $new_post_id, $post_content ), 'debug', __FILE__, __LINE__ );

			$imported_items[] = $item_hash;
			$count++;

			if ( $import_post_term !== 'none' && strpos( $import_post_term, '_' ) > 0 ) {
				// let's get the slug of the uncategorized category, even if it renamed.
				$uncategorized = get_category( 1 );
				$terms = explode( ',', $import_post_term );
				foreach ( $terms as $term ) {
					// this handles both x_2, where 2 is the term id and x is the taxonomy AND x_2_3_4 where 4 is the term id and the taxonomy name is "x 2 3 4".
					$array = explode( '_', $term );
					$term_id = array_pop( $array );
					$taxonomy = implode( '_', $array );

					// uncategorized
					// 1. may be the unmodified category ID 1
					// 2. may have been recreated ('uncategorized') and may have a different slug in different languages.
					wp_remove_object_terms( $new_post_id, apply_filters( 'feedzy_uncategorized', array( 1, 'uncategorized', $uncategorized->slug ), $job->ID ), 'category' );

					$result = wp_set_object_terms( $new_post_id, intval( $term_id ), $taxonomy, true );
					do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'After creating post in %s/%d, result = %s', $taxonomy, $term_id, print_r( $result, true ) ), 'debug', __FILE__, __LINE__ );
				}
			}

			do_action( 'feedzy_import_extra', $job, $results, $new_post_id, $index, $import_errors, $import_info );

			$index++;

			if ( trim( $import_featured_img ) !== '' && ! empty( $item['item_img_path'] ) ) {
				$image_url = str_replace( '[#item_image]', $item['item_img_path'], $import_featured_img );
				$img_success = true;
				if ( $image_url !== '' && isset( $item['item_img_path'] ) && $item['item_img_path'] !== '' ) {
					$img_success = $this->generate_featured_image( $image_url, $new_post_id, $item['item_title'], $import_errors, $import_info );
				} else {
					$img_success = $this->generate_featured_image( $import_featured_img, $new_post_id, $item['item_title'], $import_errors, $import_info );
				}
				if ( ! $img_success ) {
					$import_image_errors++;
				}
			}

			// indicate that this post was imported by feedzy.
			update_post_meta( $new_post_id, 'feedzy', 1 );
			update_post_meta( $new_post_id, 'feedzy_item_url', esc_url_raw( $item['item_url'] ) );
			update_post_meta( $new_post_id, 'feedzy_job', $job->ID );
			update_post_meta( $new_post_id, 'feedzy_item_author', sanitize_text_field( $author ) );

			// we can use this to associate the items that were imported in a particular run.
			update_post_meta( $new_post_id, 'feedzy_job_time', $last_run );

			do_action( 'feedzy_after_post_import', $new_post_id, $item, $this->settings );
		}

		if ( $use_new_hash ) {
			update_post_meta( $job->ID, 'imported_items_hash', $imported_items );
		} else {
			update_post_meta( $job->ID, 'imported_items', $imported_items );
		}
		update_post_meta( $job->ID, 'imported_items_count', $count );

		if ( $import_image_errors > 0 ) {
			$import_errors[] = sprintf( __( 'Unable to find an image for %1$d out of %2$d items imported', 'feedzy-rss-feeds' ), $import_image_errors, $count );
		}
		update_post_meta( $job->ID, 'import_errors', $import_errors );

		// the order of these matters in how they are finally shown in the summary.
		$import_info['total'] = $items_found;
		$import_info['duplicates'] = $duplicates;

		update_post_meta( $job->ID, 'import_info', $import_info );

		return $count;
	}

	/**
	 * Method to return feed items to use on cron job.
	 *
	 * @since   1.2.0
	 * @access  public
	 *
	 * @param   array  $options The options for the job.
	 * @param   string $import_content The import content (along with the magic tags).
	 * @param   bool   $raw_feed_also Whether to return the raw SimplePie object as well.
	 *
	 * @return mixed
	 */
	public function get_job_feed( $options, $import_content = null, $raw_feed_also = false ) {
		$admin = Feedzy_Rss_Feeds::instance()->get_admin();
		if ( ! method_exists( $admin, 'normalize_urls' ) ) {
			return array();
		}
		$feedURL = $admin->normalize_urls( $options['feeds'] );

		$feedURL = apply_filters( 'feedzy_import_feed_url', $feedURL, $import_content, $options );
		if ( is_wp_error( $feedURL ) ) {
			return $feedURL;
		}

		$feed    = $admin->fetch_feed( $feedURL, isset( $options['refresh'] ) ? $options['refresh'] : '12_hours', $options );

		if ( is_string( $feed ) ) {
			return array();
		}
		$sizes      = array(
			'width'  => $options['size'],
			'height' => $options['size'],
		);
		$sizes      = apply_filters( 'feedzy_thumb_sizes', $sizes, $feedURL );
		$feed_items = apply_filters( 'feedzy_get_feed_array', array(), $options, $feed, $feedURL, $sizes );

		if ( $raw_feed_also ) {
			return array(
				'items' => $feed_items,
				'feed'  => $feed,
			);
		}
		return $feed_items;
	}

	/**
	 * Downloads and sets a post featured image if possible.
	 *
	 * @since   1.2.0
	 * @access  private
	 *
	 * @param   string  $file The file URL.
	 * @param   integer $post_id The post ID.
	 * @param   string  $desc Description.
	 * @param   array   $import_errors Array of import error messages.
	 * @param   array   $import_info Array of import information messages.
	 *
	 * @return bool
	 */
	private function generate_featured_image( $file, $post_id, $desc, &$import_errors, &$import_info ) {
		do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Trying to generate featured image for %s and postID %d', $file, $post_id ), 'debug', __FILE__, __LINE__ );

		require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
		require_once( ABSPATH . 'wp-admin' . '/includes/file.php' );
		require_once( ABSPATH . 'wp-admin' . '/includes/media.php' );

		$file_array     = array();
		$local_file     = download_url( $file );
		if ( is_wp_error( $local_file ) ) {
			do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Unable to download file = %s and postID %d', print_r( $local_file, true ), $post_id ), 'error', __FILE__, __LINE__ );
			return false;
		}

		$type           = mime_content_type( $local_file );
		// the file is downloaded with a .tmp extension
		// if the URL mentions the extension of the file, the upload succeeds
		// but if the URL is like https://source.unsplash.com/random, then the upload fails
		// so let's determine the file's mime type and then rename the .tmp file with that extension
		if ( in_array( $type, array_values( get_allowed_mime_types() ), true ) ) {
			$new_local_file = str_replace( '.tmp', str_replace( 'image/', '.', $type ), $local_file );
			$renamed        = rename( $local_file, $new_local_file );
			if ( $renamed ) {
				$local_file = $new_local_file;
			} else {
				do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Unable to rename file for postID %d', $post_id ), 'error', __FILE__, __LINE__ );
				return false;
			}
		}

		$file_array['tmp_name'] = $local_file;
		$file_array['name']     = basename( $local_file );

		$id                 = media_handle_sideload( $file_array, $post_id, $desc );
		if ( is_wp_error( $id ) ) {
			do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Unable to attach file for postID %d = %s', $post_id, print_r( $id, true ) ), 'error', __FILE__, __LINE__ );
			unlink( $file_array['tmp_name'] );
			return false;
		}

		$success = set_post_thumbnail( $post_id, $id );
		if ( false === $success ) {
			do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Unable to attach file for postID %d for no apparent reason', $post_id ), 'error', __FILE__, __LINE__ );
		} else {
			do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Attached file as featured image for postID %d', $post_id ), 'info', __FILE__, __LINE__ );
		}
		return $success;
	}

	/**
	 * Registers a cron schedule.
	 *
	 * @since   1.2.0
	 * @access  public
	 */
	public function add_cron() {
		if ( false === wp_next_scheduled( 'feedzy_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'feedzy_cron' );
		}
	}

	/**
	 * Checks if WP Cron is enabled and if not, shows a notice.
	 *
	 * @access  public
	 */
	public function admin_notices() {
		$screen = get_current_screen();
		$allowed    = array( 'edit-feedzy_categories', 'edit-feedzy_imports', 'feedzy-rss_page_feedzy-settings' );
		// only show in the feedzy screens.
		if ( ! in_array( $screen->id, $allowed, true ) ) {
			return;
		}

		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			echo '<div class="notice notice-error feedzy-error-critical is-dismissible"><p>' . __( 'WP Cron is disabled. Your feeds would not get updated. Please contact your hosting provider or system administrator', 'feedzy-rss-feeds' ) . '</p></div>';
		}

		if ( false === wp_next_scheduled( 'feedzy_cron' ) ) {
			echo '<div class="notice notice-error"><p>' . __( 'Unable to register cron job. Your feeds might not get updated', 'feedzy-rss-feeds' ) . '</p></div>';
		}

	}

	/**
	 * Method to return license status.
	 * Used to filter PRO version types.
	 *
	 * @since   1.2.0
	 * @access  public
	 * @return bool
	 */
	public function feedzy_is_business() {
		return $this->feedzy_is_license_of_type( false, 'business' );
	}

	/**
	 * Method to return if licence is agency.
	 *
	 * @since   1.3.2
	 * @access  public
	 * @return bool
	 */
	public function feedzy_is_agency() {
		return $this->feedzy_is_license_of_type( false, 'agency' );
	}

	/**
	 * Method to return the type of licence.
	 *
	 * @access  public
	 * @return bool
	 */
	public function feedzy_is_license_of_type( $default, $type ) {
		// proceed to check the plan only if the license is active.
		if ( ! ( defined( 'TI_UNIT_TESTING' ) || defined( 'TI_CYPRESS_TESTING' ) ) ) {
			$status = apply_filters( 'feedzy_rss_feeds_pro_license_status', false );
			if ( $status !== 'valid' ) {
				return $default;
			}
		}
		$plan = get_option( 'feedzy_rss_feeds_pro_license_plan', 1 );
		$plan = intval( $plan );

		switch ( $type ) {
			case 'agency':
				return ( $plan > 2 );
			case 'business':
				return ( $plan > 1 );
			case 'pro':
				return ( $plan > 0 );
		}
		return $default;
	}


	/**
	 * Method for updating settings page via AJAX.
	 *
	 * @since   1.3.2
	 * @access  public
	 */
	public function update_settings_page() {
		$post_data = $_POST['feedzy_settings'];
		$this->save_settings();
		wp_die();
	}

	/**
	 * Display settings fields for the tab.
	 *
	 * @since   1.3.2
	 * @access  public
	 */
	public function display_tab_settings( $fields, $tab ) {
		$this->free_settings = get_option( 'feedzy-settings', array() );

		$fields[] = array(
			'content'   => $this->render_view( $tab ),
			'ajax'      => false,
		);
		return $fields;
	}

	/**
	 * Method to save settings.
	 *
	 * @since   1.3.2
	 * @access  private
	 */
	private function save_settings() {
		update_option( 'feedzy-rss-feeds-settings', $this->settings );
	}

	/**
	 * Add settings tab.
	 *
	 * @since   1.3.2
	 * @access  public
	 */
	public function settings_tabs( $tabs ) {
		$tabs['misc'] = __( 'Miscellaneous', 'feedzy-rss-feeds' );
		return $tabs;
	}

	/**
	 * Save settings for the tab.
	 *
	 * @access  public
	 */
	public function save_tab_settings( $settings, $tab ) {
		if ( 'misc' === $tab ) {
			$settings['canonical'] = isset( $_POST['canonical'] ) ? $_POST['canonical'] : 0;
		}
		return $settings;
	}

	/**
	 * Render a view page.
	 *
	 * @since   1.3.2
	 * @access  public
	 *
	 * @param   string $name The name of the view.
	 *
	 * @return string
	 */
	private function render_view( $name ) {
		$file = null;
		switch ( $name ) {
			case 'misc':
				$file = FEEDZY_ABSPATH . '/includes/views/' . $name . '-view.php';
				break;
			default:
				$file = apply_filters( 'feedzy_render_view', $file, $name );
				break;
		}

		if ( ! $file ) {
			return;
		}

		ob_start();
		include $file;
		return ob_get_clean();
	}

	/**
	 * Renders the HTML for the tags.
	 *
	 * @since   1.4.2
	 * @access  public
	 */
	public function render_magic_tags( $default, $tags, $type ) {
		if ( $tags ) {
			$disabled = array();
			foreach ( $tags as $tag => $label ) {
				if ( strpos( $tag, ':disabled' ) !== false ) {
					$disabled[ str_replace( ':disabled', '', $tag ) ] = $label;
					continue;
				}
				$default    .= '<a class="dropdown-item" href="#" data-field-name="' . $type . '" data-field-tag="' . $tag . '">' . $label . ' -- <small>[#' . $tag . ']</small></a>';
			}

			if ( $disabled ) {
				foreach ( $disabled as $tag => $label ) {
					$default    .= '<span disabled title="' . __( 'Upgrade your license to use this tag', 'feedzy-rss-feeds' ) . '" class="dropdown-item">' . $label . ' -- <small>[#' . $tag . ']</small></span>';
				}
			}
		}
		return $default;
	}

	/**
	 * Renders the tags for the title.
	 *
	 * @since   1.4.2
	 * @access  public
	 *
	 * @param   array $default The default tags, empty.
	 */
	public function magic_tags_title( $default ) {
		$default['item_title']  = __( 'Item Title', 'feedzy-rss-feeds' );
		$default['item_author'] = __( 'Item Author', 'feedzy-rss-feeds' );
		$default['item_date']   = __( 'Item Date (UTC/GMT)', 'feedzy-rss-feeds' );
		$default['item_date_local']   = __( 'Item Date (local timezone)', 'feedzy-rss-feeds' );
		$default['item_date_feed']   = __( 'Item Date (feed timezone)', 'feedzy-rss-feeds' );
		$default['item_source']        = __( 'Item Source', 'feedzy-rss-feeds' );

		// disabled tags
		if ( ! feedzy_is_pro() ) {
			$default['title_spinnerchief:disabled']    = __( 'Title from SpinnerChief', 'feedzy-rss-feeds' );
			$default['title_wordai:disabled']    = __( 'Title from WordAI', 'feedzy-rss-feeds' );
		}
		return $default;
	}

	/**
	 * Renders the tags for the date.
	 *
	 * @since   1.4.2
	 * @access  public
	 *
	 * @param   array $default The default tags, empty.
	 */
	public function magic_tags_date( $default ) {
		$default['item_date']   = __( 'Item Date', 'feedzy-rss-feeds' );
		$default['post_date']   = __( 'Post Date', 'feedzy-rss-feeds' );
		return $default;
	}

	/**
	 * Renders the tags for the content.
	 *
	 * @since   1.4.2
	 * @access  public
	 *
	 * @param   array $default The default tags, empty.
	 */
	public function magic_tags_content( $default ) {
		$default['item_content']    = __( 'Item Content', 'feedzy-rss-feeds' );
		$default['item_description']    = __( 'Item Description', 'feedzy-rss-feeds' );
		$default['item_image']      = __( 'Item Image', 'feedzy-rss-feeds' );
		$default['item_url']        = __( 'Item URL', 'feedzy-rss-feeds' );
		$default['item_categories']        = __( 'Item Categories', 'feedzy-rss-feeds' );
		$default['item_source']        = __( 'Item Source', 'feedzy-rss-feeds' );

		// disabled tags
		if ( ! feedzy_is_pro() ) {
			$default['item_full_content:disabled']    = __( 'Item Full Content', 'feedzy-rss-feeds' );
			$default['content_spinnerchief:disabled']    = __( 'Content from SpinnerChief', 'feedzy-rss-feeds' );
			$default['full_content_spinnerchief:disabled']    = __( 'Full content from SpinnerChief', 'feedzy-rss-feeds' );
			$default['content_wordai:disabled']    = __( 'Content from WordAI', 'feedzy-rss-feeds' );
			$default['full_content_wordai:disabled']    = __( 'Full content from WordAI', 'feedzy-rss-feeds' );
		}
		return $default;
	}

	/**
	 * Renders the tags for the featured image.
	 *
	 * @since   1.4.2
	 * @access  public
	 *
	 * @param   array $default The default tags, empty.
	 */
	public function magic_tags_image( $default ) {
		$default['item_image']      = __( 'Item Image', 'feedzy-rss-feeds' );
		return $default;
	}

	/**
	 * Register the meta tags.
	 *
	 * @access      public
	 */
	public function wp() {
		global $wp_version;

		$free_settings = get_option( 'feedzy-settings', array() );
		if ( ! isset( $free_settings['canonical'] ) || 1 !== intval( $free_settings['canonical'] ) ) {
			return;
		}

		// Yoast.
		add_filter( 'wpseo_canonical', array( $this, 'get_canonical_url' ) );

		// All In One SEO.
		add_filter( 'aioseop_canonical_url', array( $this, 'get_canonical_url' ) );

		if ( version_compare( $wp_version, '4.6.0', '>=' ) ) {
			// Fallback if none of the above plugins is present.
			add_filter( 'get_canonical_url', array( $this, 'get_canonical_url' ) );
		}
	}

	/**
	 * Return the canonical URL.
	 *
	 * @access      public
	 */
	public function get_canonical_url( $canonical_url ) {
		if ( ! is_singular() ) {
			return $canonical_url;
		}

		global $post;
		if ( ! $post ) {
			return $canonical_url;
		}

		// let's check if the post has been imported by feedzy.
		if ( 1 === intval( get_post_meta( $post->ID, 'feedzy', true ) ) ) {
			$url    = get_post_meta( $post->ID, 'feedzy_item_url', true );
			if ( ! empty( $url ) ) {
				$canonical_url = $url;
			}
		}
		return $canonical_url;
	}

	/**
	 * Add/remove row actions for each import.
	 *
	 * @since   ?
	 * @access  public
	 */
	public function add_import_actions( $actions, $post ) {
		if ( $post->post_type === 'feedzy_imports' ) {
			// don't need quick edit.
			unset( $actions['inline hide-if-no-js'] );

			$actions['feedzy_purge'] = sprintf(
				'<a href="#" class="feedzy-purge" data-id="%d">%2$s</a><span class="feedzy-spinner spinner"></span>',
				$post->ID,
				esc_html( __( 'Purge &amp; Reset', 'feedzy-rss-feeds' ) )
			);
		} elseif ( 1 === intval( get_post_meta( $post->ID, 'feedzy', true ) ) ) {
			// show an unclickable action that mentions that it is imported by us
			// so that users are aware
			$actions['feedzy'] = sprintf( '(%s)', __( 'Imported by Feedzy', 'feedzy-rss-feeds' ) );
		}
		return $actions;
	}

	/**
	 * AJAX called method to purge imported items.
	 *
	 * @since   ?
	 * @access  private
	 */
	private function purge_data() {
		$id     = $_POST['id'];
		$post   = get_post( $id );
		if ( $post->post_type !== 'feedzy_imports' ) {
			wp_die();
		}

		delete_post_meta( $id, 'imported_items_hash' );
		delete_post_meta( $id, 'imported_items' );
		delete_post_meta( $id, 'imported_items_count' );
		delete_post_meta( $id, 'import_errors' );
		delete_post_meta( $id, 'import_info' );
		delete_post_meta( $id, 'last_run' );
		wp_die();
	}

	/**
	 * Load only those posts that are linked to a particular import job.
	 *
	 * @since   ?
	 * @access  public
	 */
	public function pre_get_posts( $query ) {
		if ( is_admin() && $query->is_main_query() && ! empty( $_GET['feedzy_job_id'] ) ) {
			$meta_query = array(
				array(
					'key' => 'feedzy',
					'value' => 1,
				),
				array(
					'key' => 'feedzy_job',
					'value' => $_GET['feedzy_job_id'],
				),
			);

			if ( ! empty( $_GET['feedzy_job_time'] ) ) {
				$meta_query[] = array(
					'key' => 'feedzy_job_time',
					'value' => $_GET['feedzy_job_time'],
				);
			}

			$query->set( 'meta_query', $meta_query );
		}
	}
}
