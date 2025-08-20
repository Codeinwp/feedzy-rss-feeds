<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://themeisle.com/plugins/feedzy-rss-feed/
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
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since       1.0.0
	 * @access      public
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->settings      = get_option( 'feedzy-rss-feeds-settings', array() );
		$this->free_settings = get_option( 'feedzy-settings', array() );
	}

	/**
	 * Adds the class to the div that shows the upsell.
	 *
	 * @param string $css_class The class name.
	 * @access public
	 */
	public function upsell_class( $css_class ) {
		if ( ! feedzy_is_pro() ) {
			$css_class = 'only-pro';
		}

		return $css_class;
	}

	/**
	 * Adds the content to the div that shows the upsell.
	 *
	 * @param string $content The content.
	 * @param string $area The area identifier.
	 * @param string $location The location identifier.
	 * @access public
	 */
	public function upsell_content( $content, $area, $location ) {
		if ( ! feedzy_is_pro() ) {
			$content = '
			<div class="only-pro-content">
				<div class="only-pro-container">
					<div class="only-pro-inner upgrade-alert">
						' . __( 'This feature is available in the Pro version.  Unlock more features, by', 'feedzy-rss-feeds' ) . '
						<a target="_blank" href="' . esc_url( tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, $area, $location ) ) ) . '" title="' . __( 'Buy Now', 'feedzy-rss-feeds' ) . '">' . __( 'upgrading to Feedzy Pro', 'feedzy-rss-feeds' ) . '</a>
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
		$screen = get_current_screen();

		if (
			'feedzy_imports' === $screen->post_type ||
			'feedzy_categories' === $screen->post_type ||
			'feedzy_page_feedzy-integration' === $screen->id ||
			'feedzy_page_feedzy-settings' === $screen->id ||
			'feedzy_page_feedzy-support' === $screen->id ||
			'feedzy_page_feedzy-setup-wizard' === $screen->id
		) {
			wp_enqueue_style( $this->plugin_name, FEEDZY_ABSURL . 'css/feedzy-rss-feed-import.css', array(), $this->version, 'all' );
			wp_register_style( $this->plugin_name . '_chosen', FEEDZY_ABSURL . 'includes/views/css/chosen.css', array(), $this->version, 'all' );
			wp_register_script( $this->plugin_name . '_chosen_script', FEEDZY_ABSURL . 'includes/views/js/chosen.js', array( 'jquery' ), $this->version, true );
		}

		if ( 'feedzy_imports' === $screen->post_type ) {
			if ( ! did_action( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			}
			wp_enqueue_style( $this->plugin_name . '_chosen', FEEDZY_ABSURL . 'includes/views/css/chosen.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name . '_tagify', FEEDZY_ABSURL . 'includes/views/css/tagify.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name . '_metabox_edit', FEEDZY_ABSURL . 'includes/views/css/import-metabox-edit.css', array( 'wp-jquery-ui-dialog' ), $this->version, 'all' );
			wp_enqueue_script( $this->plugin_name . '_chosen_script', FEEDZY_ABSURL . 'includes/views/js/chosen.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( $this->plugin_name . '_tagify_script', FEEDZY_ABSURL . 'includes/views/js/jquery.tagify.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script(
				$this->plugin_name . '_metabox_edit_script',
				FEEDZY_ABSURL . 'includes/views/js/import-metabox-edit.js',
				array(
					'jquery',
					'jquery-ui-dialog',
					$this->plugin_name . '_chosen_script',
				),
				$this->version,
				true
			);
			wp_localize_script(
				$this->plugin_name . '_metabox_edit_script',
				'feedzy',
				array(
					'ajax'  => array(
						'security' => wp_create_nonce( FEEDZY_BASEFILE ),
						'url'      => admin_url( 'admin-ajax.php' ),
					),
					'pages' => array(
						'logs' => add_query_arg(
							array(
								'page' => 'feedzy-settings',
								'tab'  => 'logs',
							),
							admin_url( 'admin.php' )
						),
					),
					'i10n'  => array(
						'importing'           => __( 'Importing', 'feedzy-rss-feeds' ) . '...',
						'run_now'             => __( 'Run Now', 'feedzy-rss-feeds' ),
						'dry_run_loading'     => '<p class="hide-when-loaded">' . __( 'Processing the source and loading the items that will be imported when it runs', 'feedzy-rss-feeds' ) . '...</p>'
							. '<p><b>' . __( 'Please note that if some of these items have already have been imported in previous runs with the same filters, they may be shown here but will not be imported again.', 'feedzy-rss-feeds' ) . '</b></p>'
							. '<p class="loading-img hide-when-loaded"><img src="' . includes_url( 'images/wpspin-2x.gif' ) . '"></p><div></div>',
						'dry_run_title'       => __( 'Importable Items', 'feedzy-rss-feeds' ),
						'delete_post_message' => __( 'Would you also like to delete all the imported posts for this import job?', 'feedzy-rss-feeds' ),
						'media_iframe_title'  => __( 'Select image', 'feedzy-rss-feeds' ),
						'media_iframe_button' => __( 'Set default image', 'feedzy-rss-feeds' ),
						'action_btn_text_1'   => __( 'Choose image', 'feedzy-rss-feeds' ),
						'action_btn_text_2'   => __( 'Replace image', 'feedzy-rss-feeds' ),
						'author_helper'       => __( 'We display up to 100 users. If the desired username isn’t listed, type the exact existing username manually to save it.', 'feedzy-rss-feeds' ),
						'clearLogButton'      => __( 'Clear Log', 'feedzy-rss-feeds' ),
						'goToLogsTab'         => __( 'See more details', 'feedzy-rss-feeds' ),
						'okButton'            => __( 'Ok', 'feedzy-rss-feeds' ),
						'removeErrorLogsMsg'  => __( 'Removed all error logs.', 'feedzy-rss-feeds' ),
						// translators: %d select images count.
						'action_btn_text_3'   => __( '(%d) images selected', 'feedzy-rss-feeds' ),
						'importButton'        => sprintf(
							'<a href="#" class="page-title-action fz-export-import-btn%1$s"><span class="dashicons %2$s"></span>%3$s</a>',
							! feedzy_is_pro() ? ' only-pro' : '',
							feedzy_is_pro() ? 'dashicons-upload' : 'dashicons-lock',
							esc_html__( 'Upload Import', 'feedzy-rss-feeds' )
						),
						'is_pro'              => feedzy_is_pro(),
						'validation_messages' => array(
							'invalid_feed_url'          => __( 'Invalid feed URL.', 'feedzy-rss-feeds' ),
							'error_validating_feed_url' => __( 'Error validating feed URL.', 'feedzy-rss-feeds' ),
						),
					),
				)
			);
		}
	}

	/**
	 * Add attributes to $item_array.
	 *
	 * @param array          $item_array The item attributes array.
	 * @param SimplePie\Item $item The feed item.
	 * @param array          $sc The shortcode attributes array.
	 * @param int            $index The item number.
	 * @param int            $item_index The real index of this item in the feed.
	 * @return mixed
	 * @since 1.0.0
	 * @access public
	 */
	public function add_data_to_item( $item_array, $item, $sc = null, $index = null, $item_index = null ) {
		$item_array['item_categories'] = $this->retrieve_categories( null, $item );

		// If set to true, SimplePie will return a unique MD5 hash for the item.
		// If set to false, it will check <guid>, <link>, and <title> before defaulting to the hash.
		$item_array['item_id'] = $item->get_id( false );

		$item_array['item']       = $item;
		$item_array['item_index'] = $item_index;

		$item_array = $this->handle_youtube_content( $item_array, $item, $sc );

		return $item_array;
	}

	/**
	 * Fetches additional information for each item.
	 *
	 * @param array<string, string > $item_array The item attributes array.
	 * @param SimplePie\Item         $item The feed item.
	 * @param array<string, mixed>   $sc The shortcode attributes array. This will be empty through the block editor.
	 * 
	 * @return array<string, string>
	 */
	private function handle_youtube_content( $item_array, $item, $sc ) {
		$url = '';
		if ( array_key_exists( 'item_url', $item_array ) ) {
			$url = $item_array['item_url'];
		} elseif ( $item ) {
			$url = $item->get_permalink();
		}

		if ( empty( $url ) ) {
			return $item_array;
		}

		$host = wp_parse_url( $url, PHP_URL_HOST );

		// Remove all dots in the hostname so that shortforms such as youtu.be can also be resolved.
		$host = str_replace( array( '.', 'www' ), '', $host );
		
		if ( ! in_array( $host, array( 'youtubecom', 'youtube' ), true ) ) {
			// Not a YouTube link, return the item array as is.
			return $item_array;
		}

		$tags = $item->get_item_tags( \SimplePie\SimplePie::NAMESPACE_MEDIARSS, 'group' );
		$desc = '';
		if ( $tags ) {
			$desc_tag = $tags[0]['child'][ \SimplePie\SimplePie::NAMESPACE_MEDIARSS ]['description'];
			if ( $desc_tag ) {
				$desc = $desc_tag[0]['data'];
			}
		}

		if ( ! empty( $desc ) ) {
			if ( empty( $item_array['item_content'] ) ) {
				$item_array['item_content'] = $desc;
			}

			if (
				( empty( $sc ) || 'yes' === $sc['summary'] ) &&
				empty( $item_array['item_description'] )
			) {
				if (
					is_numeric( $sc['summarylength'] ) &&
					strlen( $desc ) > $sc['summarylength']
				) {
					$desc = preg_replace( '/\s+?(\S+)?$/', '', substr( $desc, 0, $sc['summarylength'] ) ) . ' [&hellip;]';
				}
				$item_array['item_description'] = $desc;
			}
		}

		$embed_video_shortcode      = '[embed]' . $url . '[/embed]';
		$should_overwrite           = str_contains( $item_array['item_content'], 'Post Content' );
		$item_array['item_content'] = ( $should_overwrite ? '' : $item_array['item_content'] ) . $embed_video_shortcode;
		
		return $item_array;
	}

	/**
	 * Retrieve the categories.
	 *
	 * @param string         $dumb The initial categories (only a placeholder argument for the filter).
	 * @param SimplePie\Item $item The feed item.
	 *
	 * @return string
	 * @access  public
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
			'edit_item'          => false,
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
			'labels'              => $labels,
			'supports'            => false,
			'public'              => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'show_in_nav_menus'   => false,
			'rewrite'             => array(
				'slug' => 'feedzy-import',
			),
			'show_in_menu'        => 'feedzy-admin-menu',
			'show_ui'             => feedzy_current_user_can(),
			'show_in_rest'        => true,
		);
		$args     = apply_filters( 'feedzy_imports_args', $args );
		register_post_type( 'feedzy_imports', $args );

		// Register user meta field.
		register_meta(
			'user',
			'feedzy_import_tour',
			array(
				'type'         => 'boolean',
				'description'  => __( 'Show tour for Feedzy.', 'feedzy-rss-feeds' ),
				'show_in_rest' => true,
				'single'       => true,
				'default'      => true,
			)
		);
		register_meta(
			'user',
			'feedzy_hide_action_message',
			array(
				'type'         => 'boolean',
				'description'  => __( 'Show intro message for Feedzy action popup.', 'feedzy-rss-feeds' ),
				'show_in_rest' => true,
				'single'       => true,
				'default'      => false,
			)
		);
	}

	/**
	 * Method to add a meta box to `feedzy_imports`
	 * custom post type.
	 *
	 * @param string   $post_type The post type.
	 * @param \WP_Post $post The post.
	 * 
	 * @return void
	 * 
	 * @since   1.2.0
	 * @access  public
	 */
	public function add_feedzy_import_metaboxes( $post_type, $post ) {
		if ( 'feedzy_imports' !== $post_type || empty( $post ) ) {
			return;
		}

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
	 * @return void
	 * 
	 * @since   1.2.0
	 * @access  public
	 */
	public function feedzy_import_feed_options() {
		global $post, $pagenow;
		$args             = array(
			'post_type'      => 'feedzy_categories',
			'posts_per_page' => 100,
		);
		$feed_categories  = get_posts( $args );
		$post_types       = get_post_types( '', 'names' );
		$post_types       = array_diff( $post_types, array( 'feedzy_imports', 'feedzy_categories' ) );
		$published_status = array( 'publish', 'draft' );

		$authors       = get_users( array( 'number' => 100 ) );
		$authors_array = array();
		foreach ( $authors as $author ) {
			$authors_array[] = $author->user_login;
		}

		$import_post_type = get_post_meta( $post->ID, 'import_post_type', true );
		$import_post_term = get_post_meta( $post->ID, 'import_post_term', true );
		if ( metadata_exists( $import_post_type, $post->ID, 'import_post_status' ) ) {
			$import_post_status = get_post_meta( $post->ID, 'import_post_status', true );
		} else {
			add_post_meta( $post->ID, 'import_post_status', 'publish' );
			$import_post_status = get_post_meta( $post->ID, 'import_post_status', true );
		}
		$source                   = get_post_meta( $post->ID, 'source', true );
		$inc_key                  = get_post_meta( $post->ID, 'inc_key', true );
		$exc_key                  = get_post_meta( $post->ID, 'exc_key', true );
		$inc_on                   = get_post_meta( $post->ID, 'inc_on', true );
		$exc_on                   = get_post_meta( $post->ID, 'exc_on', true );
		$import_title             = get_post_meta( $post->ID, 'import_post_title', true );
		$import_date              = get_post_meta( $post->ID, 'import_post_date', true );
		$post_excerpt             = get_post_meta( $post->ID, 'import_post_excerpt', true );
		$import_content           = get_post_meta( $post->ID, 'import_post_content', true );
		$import_item_img_url      = get_post_meta( $post->ID, 'import_use_external_image', true );
		$import_item_img_url      = 'yes' === $import_item_img_url ? 'checked' : '';
		$import_featured_img      = get_post_meta( $post->ID, 'import_post_featured_img', true );
		$import_remove_duplicates = get_post_meta( $post->ID, 'import_remove_duplicates', true );
		$import_remove_duplicates = 'yes' === $import_remove_duplicates || 'post-new.php' === $pagenow ? 'checked' : '';
		$import_selected_language = get_post_meta( $post->ID, 'language', true );
		$from_datetime            = get_post_meta( $post->ID, 'from_datetime', true );
		$to_datetime              = get_post_meta( $post->ID, 'to_datetime', true );
		$import_auto_translation  = get_post_meta( $post->ID, 'import_auto_translation', true );
		$import_auto_translation  = 'yes' === $import_auto_translation ? 'checked' : '';
		$import_translation_lang  = get_post_meta( $post->ID, 'import_auto_translation_lang', true );
		$mark_duplicate_tag       = get_post_meta( $post->ID, 'mark_duplicate_tag', true );
		$import_post_author       = get_post_meta( $post->ID, 'import_post_author', true );
		$filter_conditions        = get_post_meta( $post->ID, 'filter_conditions', true );
		$import_remove_html       = get_post_meta( $post->ID, 'import_remove_html', true );
		$import_remove_html       = 'yes' === $import_remove_html ? 'checked' : '';
		$import_order             = get_post_meta( $post->ID, 'import_order', true );

		if ( empty( $filter_conditions ) ) {
			$filter_conditions = apply_filters(
				'feedzy_filter_conditions_migration',
				array(
					'keywords_inc'    => $inc_key,
					'keywords_exc'    => $exc_key,
					'keywords_inc_on' => $inc_on,
					'keywords_exc_on' => $exc_on,
					'from_datetime'   => $from_datetime,
					'to_datetime'     => $to_datetime,
				)
			);
		}

		/**
		 * This code snippet retrieves the post author for backward compatibility for existing imports as well as for any new imports.
		 * It checks if the $import_post_author variable is not empty, otherwise it defaults to the current post's author.
		 */
		$import_post_author = ! empty( $import_post_author ) ? $import_post_author : $post->post_author;
		$author             = get_user_by( 'ID', $import_post_author );
		if ( $author ) {
			$import_post_author = $author->user_login;
			if ( ! in_array( $import_post_author, $authors_array, true ) ) {
				$authors_array[] = $import_post_author;
			}
		}

		// default values so that post is not created empty.
		if ( empty( $import_title ) ) {
			$import_title = '[[{"value":"%5B%7B%22id%22%3A%22%22%2C%22tag%22%3A%22item_title%22%2C%22data%22%3A%7B%7D%7D%5D"}]]';
		}
		if ( empty( $import_content ) ) {
			$import_content = '[[{"value":"%5B%7B%22id%22%3A%22%22%2C%22tag%22%3A%22item_content%22%2C%22data%22%3A%7B%7D%7D%5D"}]]';
		}

		if ( feedzy_is_pro() && empty( $import_post_term ) && 'post-new.php' === $pagenow ) {
			$import_post_term = '[#auto_categories]';
		}

		if ( feedzy_is_pro() ) {
			$custom_terms = array(
				'[#item_categories]' => __( 'Item Categories', 'feedzy-rss-feeds' ),
				'[#auto_categories]' => __( 'Auto Categories by keyword', 'feedzy-rss-feeds' ),
			);
		} elseif ( ! feedzy_is_pro() ) {
			$custom_terms = array(
				'[#item_categories]' => __( 'Item Categories', 'feedzy-rss-feeds' ) . sprintf( '<span class="pro-label">%s</span>', __( 'PRO', 'feedzy-rss-feeds' ) ),
				'[#auto_categories]' => __( 'Auto Categories by keyword', 'feedzy-rss-feeds' ) . sprintf( '<span class="pro-label">%s</span>', __( 'PRO', 'feedzy-rss-feeds' ) ),
			);
		}
		$custom_post_term = wp_json_encode( $custom_terms );

		$import_link_author_admin  = get_post_meta( $post->ID, 'import_link_author_admin', true );
		$import_link_author_public = get_post_meta( $post->ID, 'import_link_author_public', true );

		// Admin / Public.
		$import_link_author = array( '', '' );
		if ( 'yes' === $import_link_author_admin ) {
			$import_link_author[0] = 'checked';
		}
		if ( 'yes' === $import_link_author_public ) {
			$import_link_author[1] = 'checked';
		}

		// default values when creating a import.
		if ( 'post-new.php' === $pagenow ) {
			$import_date         = '[#item_date]';
			$import_featured_img = '[[{"value":"%5B%7B%22id%22%3A%22%22%2C%22tag%22%3A%22item_image%22%2C%22data%22%3A%7B%7D%7D%5D"}]]';    
		}

		// maybe more options are required from pro?
		$pro_options = apply_filters( 'feedzy_metabox_options', array(), $post->ID );

		$import_custom_fields  = get_post_meta( $post->ID, 'imports_custom_fields', true );
		$custom_fields_actions = get_post_meta( $post->ID, 'imports_custom_field_actions', true );
		$import_feed_limit     = get_post_meta( $post->ID, 'import_feed_limit', true );
		if ( empty( $import_feed_limit ) ) {
			$import_feed_limit = 10;
		}

		$default_thumbnail_id   = 0;
		$inherited_thumbnail_id = ! empty( $this->free_settings['general']['default-thumbnail-id'] ) ? (int) $this->free_settings['general']['default-thumbnail-id'] : 0;
		$custom_thumbnail_id    = get_post_meta( $post->ID, 'default_thumbnail_id', true );

		if ( is_numeric( $custom_thumbnail_id ) ) {
			$default_thumbnail_id = $custom_thumbnail_id;
		}

		if ( feedzy_is_pro() ) {
			$import_schedule = array(
				'fz_cron_schedule' => ! empty( $this->free_settings['general']['fz_cron_schedule'] ) ? $this->free_settings['general']['fz_cron_schedule'] : '',
			);
		}

		$fz_cron_schedule = get_post_meta( $post->ID, 'fz_cron_schedule', true );
		if ( ! empty( $fz_cron_schedule ) ) {
			$import_schedule['fz_cron_schedule'] = $fz_cron_schedule;
		}

		$post_status        = $post->post_status;
		$nonce              = wp_create_nonce( FEEDZY_BASEFILE );
		$invalid_source_msg = apply_filters( 'feedzy_get_source_validity_error', '', $post );
		$output             = '
            <input type="hidden" name="feedzy_category_meta_noncename" id="feedzy_category_meta_noncename" value="' . $nonce . '" />
        ';

		add_thickbox();
		include FEEDZY_ABSPATH . '/includes/views/import-metabox-edit.php';
		echo wp_kses( $output, apply_filters( 'feedzy_wp_kses_allowed_html', array() ) );
	}

	/**
	 * Change number of posts imported.
	 * 
	 * @param int      $range The desired range.
	 * @param \WP_Post $post The post.
	 * 
	 * @return int The range. Capped if the user is not PRO.
	 */
	public function items_limit( $range, $post ) {
	 // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( ! feedzy_is_pro() ) {
			$range = range( 10, 10, 10 );
		}

		return $range;
	}

	/**
	 * Save method for custom post type
	 * import feeds.
	 *
	 * @param integer $post_id The post ID.
	 * @param object  $post The post object.
	 *
	 * @return bool|integer True if it is a success.
	 * @since   1.2.0
	 * @access  public
	 */
	public function save_feedzy_import_feed_meta( $post_id, $post ) {
		if ( empty( $_POST ) ) {
			return $post_id;
		}
		if ( ! isset( $_POST['feedzy_post_nonce'] ) ) {
			return $post_id;
		}
		if (
			get_post_type( $post_id ) !== 'feedzy_imports' ||
			( ! defined( 'TI_UNIT_TESTING' ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['feedzy_post_nonce'] ) ), 'feedzy_post_nonce' ) ) ||
			! current_user_can( 'edit_post', $post_id )
		) {
			return $post_id;
		}

		$data_meta = array();
		if ( isset( $_POST['feedzy_meta_data'] ) && is_array( $_POST['feedzy_meta_data'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			foreach ( wp_unslash( $_POST['feedzy_meta_data'] ) as $key => $val ) {
				if ( is_array( $val ) ) {
					foreach ( $val as $sub_key => $sub_val ) {
						$data_meta[ sanitize_text_field( $key ) ][ sanitize_text_field( $sub_key ) ] = wp_kses( $sub_val, apply_filters( 'feedzy_wp_kses_allowed_html', array() ) );
					}
				} else {
					if ( 'import_post_content' === $key ) {
						$val = feedzy_custom_tag_escape( $val );
					} elseif ( 'default_thumbnail_id' === $key && ! empty( $val ) ) {
						$val = explode( ',', $val );
						$val = array_map( 'intval', $val );
					} else {
						$val = wp_kses( $val, apply_filters( 'feedzy_wp_kses_allowed_html', array() ) );
					}
					$data_meta[ sanitize_text_field( $key ) ] = $val;
				}
			}
		}

		$global_cron_schedule = ! empty( $this->free_settings['general']['fz_cron_schedule'] ) ? $this->free_settings['general']['fz_cron_schedule'] : '';
		if (
			empty( $data_meta['fz_cron_schedule'] ) || $global_cron_schedule === $data_meta['fz_cron_schedule']
		) {
			// Remove scheduled cron settings if they are equal to the global settings.
			unset( $data_meta['fz_cron_schedule'] );
		}

		$custom_fields_keys = array();
		if ( isset( $_POST['custom_vars_key'] ) && is_array( $_POST['custom_vars_key'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			foreach ( wp_unslash( $_POST['custom_vars_key'] ) as $key => $val ) {
				$custom_fields_keys[ sanitize_text_field( $key ) ] = esc_html( $val );
			}
		}
		$custom_fields_values = array();
		if ( isset( $_POST['custom_vars_value'] ) && is_array( $_POST['custom_vars_value'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			foreach ( wp_unslash( $_POST['custom_vars_value'] ) as $key => $val ) {
				$custom_fields_values[ sanitize_text_field( $key ) ] = esc_html( $val );
			}
		}
		$custom_fields = array();
		foreach ( $custom_fields_keys as $index => $key_value ) {
			$value = '';
			if ( isset( $custom_fields_values[ $index ] ) ) {
				$value = implode( ',', (array) $custom_fields_values[ $index ] );
			}
			$custom_fields[ $key_value ] = $value;
		}
		if ( 'revision' !== $post->post_type ) {
			// delete these checkbox related fields; if checked, they will be added below.
			delete_post_meta( $post_id, 'import_link_author_admin' );
			delete_post_meta( $post_id, 'import_link_author_public' );

			// we will activate this import only if the source has no invalid URL(s).
			$source_is_valid = false;
			// Check feeds remove duplicates checkbox checked OR not.
			$data_meta['import_remove_duplicates'] = isset( $data_meta['import_remove_duplicates'] ) ? $data_meta['import_remove_duplicates'] : 'no';
			// Check feeds automatically translation checkbox checked OR not.
			$data_meta['import_auto_translation'] = isset( $data_meta['import_auto_translation'] ) ? $data_meta['import_auto_translation'] : 'no';
			// Check feeds external image URL checkbox checked OR not.
			$data_meta['import_use_external_image'] = isset( $data_meta['import_use_external_image'] ) ? $data_meta['import_use_external_image'] : 'no';
			// Check feeds remove html checkbox checked OR not.
			$data_meta['import_remove_html'] = isset( $data_meta['import_remove_html'] ) ? $data_meta['import_remove_html'] : 'no';
			$data_meta['import_post_term']   = isset( $data_meta['import_post_term'] ) ? $data_meta['import_post_term'] : '';

			// If it is filter_conditions we want to escape it.
			if ( isset( $data_meta['filter_conditions'] ) ) {
				$data_meta['filter_conditions'] = wp_slash( $data_meta['filter_conditions'] );
			}

			// $data_meta['feedzy_post_author'] should be the author username. We convert it to the author ID.
			if ( ! empty( $data_meta['import_post_author'] ) ) {
				$author = get_user_by( 'login', $data_meta['import_post_author'] );
				if ( $author ) {
					$data_meta['import_post_author'] = $author->ID;
				} else {
					$data_meta['import_post_author'] = '';
				}
			}

			foreach ( $data_meta as $key => $value ) {
				$value = is_array( $value ) ? implode( ',', $value ) : implode( ',', (array) $value );
				if ( 'source' === $key ) {
					// check if the source is valid.
					$invalid_urls    = apply_filters( 'feedzy_check_source_validity', $value, $post_id, true, false );
					$source_is_valid = empty( $invalid_urls );
				}
				if ( 'import_post_content' === $key ) {
					add_filter( 'wp_kses_allowed_html', array( $this, 'feedzy_wp_kses_allowed_html' ) );
					$value = feedzy_custom_tag_escape( $value );
				} else {
					$value = wp_kses( $value, wp_kses_allowed_html( 'post' ) );
				}
				if ( get_post_meta( $post_id, $key, false ) ) {
					update_post_meta( $post_id, $key, $value );
				} else {
					add_post_meta( $post_id, $key, $value );
				}
				if ( ! $value ) {
					if ( 'default_thumbnail_id' === $key && '0' === $value ) { // Mark the feed as having no default fallback image (including the global fallback).
						continue;
					}
					delete_post_meta( $post_id, $key );
				}
			}
			// Added this to activate post if publish is clicked and sometimes it does not change status.
			if (
				$source_is_valid && isset( $_POST['custom_post_status'] ) &&
				'Publish' === sanitize_text_field( $_POST['custom_post_status'] )
			) {
				$activate = array(
					'ID'          => $post_id,
					'post_status' => 'publish',
				);
				remove_action( 'save_post_feedzy_imports', array( $this, 'save_feedzy_import_feed_meta' ), 1, 2 );
				wp_update_post( $activate );
				add_action( 'save_post_feedzy_imports', array( $this, 'save_feedzy_import_feed_meta' ), 1, 2 );
			}
			// Clear the import job cron schedule if it exists.
			Feedzy_Rss_Feeds_Util_Scheduler::clear_scheduled_hook( 'feedzy_cron', array( 100, $post_id ) );
			do_action( 'feedzy_save_fields', $post_id, $post );
		}

		return true;
	}

	/**
	 * Redirect save post to post listing.
	 *
	 * @access  public
	 *
	 * @param string $location The URL to redirect to.
	 * @param int    $post_id The post ID.
	 * 
	 * @return string The URL to redirect to. Fallback to edit.php?post_type=feedzy_imports.
	 */
	public function redirect_post_location( $location, $post_id ) {
		$post = get_post( $post_id );
		if ( 'feedzy_imports' === $post->post_type ) {
			// if invalid source has been found, redirect back to edit screen where errors can be shown.
			$invalid               = get_post_meta( $post_id, '__transient_feedzy_invalid_source', true );
			$invalid_dc_namespace  = get_post_meta( $post_id, '__transient_feedzy_invalid_dc_namespace', true );
			$invalid_source_errors = get_post_meta( $post_id, '__transient_feedzy_invalid_source_errors', true );
			if ( empty( $invalid ) && empty( $invalid_dc_namespace ) && empty( $invalid_source_errors ) ) {
				return admin_url( 'edit.php?post_type=feedzy_imports' );
			}
		}

		return $location;
	}

	/**
	 * Method to add header columns to import feeds table.
	 *
	 * @param array<string, string> $columns The columns array.
	 *
	 * @return array<string, string> The columns.
	 * 
	 * @since   1.2.0
	 * @access  public
	 */
	public function feedzy_import_columns( $columns ) {
		$columns['title'] = __( 'Import Title', 'feedzy-rss-feeds' );
		$new_columns      = $this->array_insert_before( 'date', $columns, 'feedzy-source', __( 'Source', 'feedzy-rss-feeds' ) );

		if ( $new_columns ) {
			$columns = $new_columns;
		} else {
			$columns['feedzy-source'] = __( 'Source', 'feedzy-rss-feeds' );
		}

		$new_columns = $this->array_insert_before( 'date', $columns, 'feedzy-status', __( 'Current Status', 'feedzy-rss-feeds' ) );
		if ( $new_columns ) {
			$columns = $new_columns;
		} else {
			$columns['feedzy-status'] = __( 'Current Status', 'feedzy-rss-feeds' );
		}

		$new_columns = $this->array_insert_before( 'date', $columns, 'feedzy-next_run', __( 'Next Run', 'feedzy-rss-feeds' ) );
		if ( $new_columns ) {
			$columns = $new_columns;
		} else {
			$columns['feedzy-next_run'] = __( 'Next Run', 'feedzy-rss-feeds' );
		}

		$new_columns = $this->array_insert_before( 'date', $columns, 'feedzy-last_run', __( 'Last Run Status', 'feedzy-rss-feeds' ) );
		if ( $new_columns ) {
			$columns = $new_columns;
		} else {
			$columns['feedzy-last_run'] = __( 'Last Run Status', 'feedzy-rss-feeds' );
		}

		unset( $columns['date'] );

		return $columns;
	}

	/**
	 * Utility method to insert before specific key in an associative array.
	 *
	 * @param string $key The key before which to insert.
	 * @param array  $from_array The array in which to insert the new key.
	 * @param string $new_key The new key name.
	 * @param mixed  $new_value The new key value.
	 * 
	 * @return array|bool The modified array. False otherwise.
	 * 
	 * @since 1.2.0
	 * @access public
	 */
	protected function array_insert_before( $key, &$from_array, $new_key, $new_value ) {
		if ( array_key_exists( $key, $from_array ) ) {
			$new = array();
			foreach ( $from_array as $k => $value ) {
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
	 * @param string  $column The current column to check.
	 * @param integer $post_id The post ID.
	 * 
	 * @return void
	 *
	 * @since   1.2.0
	 * @access  public
	 */
	public function manage_feedzy_import_columns( $column, $post_id ) {
		global $post;
		switch ( $column ) {
			case 'feedzy-source':
				$src = get_post_meta( $post_id, 'source', true );
				// if the source is a category, link it.
				if ( strpos( $src, 'http' ) === false && strpos( $src, 'https' ) === false ) {
					if ( function_exists( 'feedzy_amazon_get_locale_hosts' ) ) {
						$amazon_hosts = feedzy_amazon_get_locale_hosts();
						$src_path     = 'webservices.' . wp_parse_url( $src, PHP_URL_PATH );
						if ( in_array( $src_path, $amazon_hosts, true ) ) {
							$src = sprintf( '%s: %s%s%s', __( 'Amazon Product Advertising API', 'feedzy-rss-feeds' ), '<a>', $src, '</a>' );
						} else {
							$src = sprintf( '%s: %s%s%s', __( 'Feed Group', 'feedzy-rss-feeds' ), '<a href="' . admin_url( 'edit.php?post_type=feedzy_categories' ) . '" target="_blank">', $src, '</a>' );
						}
					} elseif ( empty( $src ) ) {
						$src = __( 'No Source Configured', 'feedzy-rss-feeds' );
					} else {
						$src = sprintf( '%s: %s%s%s', __( 'Feed Group', 'feedzy-rss-feeds' ), '<a href="' . admin_url( 'edit.php?post_type=feedzy_categories' ) . '" target="_blank">', $src, '</a>' );
					}
				} else {
					// else link it to the feed but shorten it if it is too long.
					$too_long = 65;
					$src      = sprintf( '%s%s%s', '<a href="' . $src . '" target="_blank" title="' . __( 'Click to view', 'feedzy-rss-feeds' ) . '">', ( strlen( $src ) > $too_long ? substr( $src, 0, $too_long ) . '...' : $src ), '</a>' );
				}
				echo wp_kses_post( $src );
				break;
			case 'feedzy-status':
				$status = $post->post_status;
				if ( empty( $status ) ) {
					esc_html_e( 'Undefined', 'feedzy-rss-feeds' );
				} else {
					if ( 'publish' === $status ) {
						$checked = 'checked';
					} else {
						$checked = '';
					}
					echo wp_kses(
						'<div class="switch">
							<input id="feedzy-toggle_' . $post->ID . '" class="feedzy-toggle feedzy-toggle-round" type="checkbox" value="' . $post->ID . '" ' . $checked . '>
							<label for="feedzy-toggle_' . $post->ID . '"></label>
							<span class="feedzy-spinner spinner"></span>
						</div>',
						apply_filters( 'feedzy_wp_kses_allowed_html', array() )
					);
				}
				break;
			case 'feedzy-last_run':
				$last = get_post_meta( $post_id, 'last_run', true );
				$msg  = __( 'Never Run', 'feedzy-rss-feeds' );
				if ( $last ) {
					$now  = new DateTime();
					$then = new DateTime();
					$then = $then->setTimestamp( $last );
					$in   = $now->diff( $then );
					$msg  = sprintf(
						// translators: %1$d: number of hours, %2$d: number of minutes.
						__( 'Ran %1$d hours %2$d minutes ago', 'feedzy-rss-feeds' ),
						$in->format( '%h' ),
						$in->format( '%i' )
					);
				}

				$msg .= $this->get_last_run_details( $post_id );
				echo ( $msg ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				if ( 'publish' === $post->post_status ) {
					printf( '<p><input type="button" class="button button-primary feedzy-run-now" data-id="%d" value="%s"></p>', esc_attr( $post_id ), esc_attr__( 'Run Now', 'feedzy-rss-feeds' ) );
				}

				break;
			case 'feedzy-next_run':
				$next = Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'feedzy_cron', array( 100, $post_id ) );
				if ( ! $next ) {
					$next = Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'feedzy_cron' );
				}
				if ( is_numeric( $next ) ) {
					echo wp_kses_post( human_time_diff( $next, time() ) );
				} elseif ( $next ) {
					echo esc_html__( 'in-progress', 'feedzy-rss-feeds' );
				}
				break;
			default:
				break;
		}
	}

	/**
	 * Generate the markup that displays the status.
	 *
	 * @param integer $post_id The post ID.
	 *
	 * @return string The HTML markup.
	 * 
	 * @access  private
	 */
	private function get_last_run_details( $post_id ) {
		$msg         = '';
		$import_info = get_post_meta( $post_id, 'import_info', true );
		$status      = array(
			'total'      => '-',
			'items'      => '-',
			'duplicates' => '-',
			'cumulative' => '-',
		);
		if ( $import_info ) {
			$status = array(
				'total'      => 0,
				'items'      => 0,
				'duplicates' => 0,
				'cumulative' => 0,
			);
			$status = $this->get_complete_import_status( $post_id );
		}

		// link to the posts listing for this job.
		$job_linked_posts = add_query_arg(
			array(
				'feedzy_job_id' => $post_id,
				'post_type'     => get_post_meta(
					$post_id,
					'import_post_type',
					true
				),
				'_nonce'        => wp_create_nonce( 'job_run_linked_posts' ),
			),
			admin_url( 'edit.php' )
		);

		// link to the posts listing for this job run.
		$job_run_linked_posts = '';
		$job_run_id           = get_post_meta( $post_id, 'last_run_id', true );
		if ( ! empty( $job_run_id ) ) {
			$job_run_linked_posts = add_query_arg(
				array(
					'feedzy_job_id'   => $post_id,
					'feedzy_job_time' => $job_run_id,
					'_nonce'          => wp_create_nonce( 'job_run_linked_posts' ),
					'post_type'       => get_post_meta(
						$post_id,
						'import_post_type',
						true
					),
				),
				admin_url( 'edit.php' )
			);
		}

		// popup for items found.
		if ( is_array( $status['items'] ) ) {
			$msg .= '<div class="feedzy-items-found-' . $post_id . ' feedzy-dialog"  title="' . __( 'Items found', 'feedzy-rss-feeds' ) . '"><ol>';
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
			$msg .= '<div class="feedzy-errors-found-' . $post_id . ' feedzy-errors-dialog" title="' . __( 'Errors', 'feedzy-rss-feeds' ) . '" data-id="' . $post_id . '">' . $errors . '</div>';
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
			// first cell.
			is_array( $status['items'] ) ? 'feedzy-has-popup' : '',
			is_array( $status['items'] ) ? count( $status['items'] ) : $status['items'],
			__( 'Items that were found in the feed', 'feedzy-rss-feeds' ),
			$post_id,
			is_array( $status['items'] ) ? count( $status['items'] ) : $status['items'],
			// second cells.
			is_array( $status['duplicates'] ) ? 'feedzy-has-popup' : '',
			is_array( $status['duplicates'] ) ? count( $status['duplicates'] ) : $status['duplicates'],
			__( 'Items that were discarded as duplicates', 'feedzy-rss-feeds' ),
			$post_id,
			is_array( $status['duplicates'] ) ? count( $status['duplicates'] ) : $status['duplicates'],
			// third cell.
			$status['total'] > 0 && ! empty( $job_run_linked_posts ) ? 'feedzy-has-popup' : '',
			$status['total'],
			defined( 'TI_CYPRESS_TESTING' ) ? '' : '_blank',
			$status['total'] > 0 && ! empty( $job_run_linked_posts ) ? $job_run_linked_posts : '',
			__( 'Items that were imported', 'feedzy-rss-feeds' ),
			$status['total'],
			// fourth cell.
			$status['cumulative'] > 0 ? 'feedzy-has-popup' : '',
			$status['cumulative'],
			defined( 'TI_CYPRESS_TESTING' ) ? '' : '_blank',
			$status['cumulative'] > 0 ? $job_linked_posts : '',
			__( 'Items that were imported across all runs', 'feedzy-rss-feeds' ),
			$status['cumulative'],
			// fifth cell.
			empty( $import_info ) ? '' : ( ! empty( $errors ) ? 'feedzy-has-popup import-error' : 'import-success' ),
			empty( $import_info ) ? '-1' : ( ! empty( $errors ) ? 0 : 1 ),
			$post_id,
			__( 'View the errors', 'feedzy-rss-feeds' ),
			empty( $import_info ) ? '-' : ( ! empty( $errors ) ? '<i class="dashicons dashicons-warning"></i>' : '<i class="dashicons dashicons-yes-alt"></i>' ),
			// second row.
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
	 * @param int $post_id The post id.
	 * 
	 * @return array<string, mixed> The status.
	 * 
	 * @access  private
	 */
	private function get_complete_import_status( $post_id ) {
		$items_count = get_post_meta( $post_id, 'imported_items_count', true );
		$items       = get_post_meta( $post_id, 'imported_items_hash', true );
		if ( empty( $items ) ) {
			$items = get_post_meta( $post_id, 'imported_items', true );
		}
		$count = $items_count;
		if ( '' === $count && $items ) {
			// backward compatibility where imported_items_count post_meta has not been populated yet.
			$count = count( $items );
		}

		$status = array(
			'total'      => $count,
			'items'      => 0,
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

		$items = get_post_meta( $post_id, 'imported_items_hash', true );
		if ( empty( $items ) ) {
			$items = get_post_meta( $post_id, 'imported_items', true );
		}
		if ( $items ) {
			$status['cumulative'] = count( $items );
		}
		if ( is_array( $status['duplicates'] ) && ! empty( $status['duplicates'] ) ) {
			$status['total'] = absint( $status['total'] - count( $status['duplicates'] ) );
		}

		return $status;
	}

	/**
	 * Creates the data by extracting the 'import_errors' from each import.
	 *
	 * @param int $post_id The post id.
	 * 
	 * @return string The error component.
	 * 
	 * @access  private
	 */
	private function get_import_errors( $post_id ) {
		$msg           = '';
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
		if ( $pro_msg && false === strpos( $pro_msg, 'dashicons-warning' ) ) {
			$errors     = '';
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
	 * @return void
	 *
	 * @since   3.4.1
	 * @access  public
	 */
	public function ajax() {
		check_ajax_referer( FEEDZY_BASEFILE, 'security' );

		$_POST['feedzy_category_meta_noncename'] = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
		$_action                                 = isset( $_POST['_action'] ) ? sanitize_text_field( wp_unslash( $_POST['_action'] ) ) : '';

		switch ( $_action ) {
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
			case 'fetch_custom_fields':
				$this->fetch_custom_fields();
				break;
			case 'wizard_import_feed':
				$this->wizard_import_feed();
				break;
			case 'remove_upsell_notice':
				$this->remove_import_upsell();
				break;
			case 'clear_error_logs':
				$this->clear_error_logs();
				break;
		}
	}

	/**
	 * AJAX called method to remove import upsell notice.
	 *
	 * @since   3.4.1
	 * @access  public
	 */
	public function remove_import_upsell() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'feedzy_import_upsell_notice', 'dismissed' );

		wp_send_json_success();
	}

	/**
	 * AJAX called method to update post status.
	 * 
	 * @return void
	 *
	 * @since   1.2.0
	 * @access  private
	 */
	private function import_status() {

		if ( ! feedzy_current_user_can() ) {
			wp_send_json_error( array( 'msg' => __( 'You do not have permission to do this.', 'feedzy-rss-feeds' ) ) );
		}

		global $wpdb;

		check_ajax_referer( FEEDZY_BASEFILE, 'security' );

		$id      = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		$status  = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		$publish = 'draft';
		// no activation till source is not valid.
		if ( 'true' === $status ) {
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
		$post_id = wp_update_post( $new_post_status );
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
	 * @return void
	 *
	 * @since   1.2.0
	 * @access  private
	 */
	private function get_taxonomies() {
		check_ajax_referer( FEEDZY_BASEFILE, 'security' );

		$post_type  = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';
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
						'fields'     => 'id=>name',
						'number'     => apply_filters( 'feedzy_post_taxonomy_limit', 999, $taxonomy ),
					)
				);
				$results[ $taxonomy ] = $terms;
			}
		}
		echo wp_json_encode( $results );
		wp_die();
	}

	/**
	 * Run a specific job.
	 * 
	 * @return void
	 *
	 * @since   1.6.1
	 * @access  private
	 */
	private function run_now() {
		check_ajax_referer( FEEDZY_BASEFILE, 'security' );
	
		$job_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		$job    = get_post( $job_id );

		Feedzy_Rss_Feeds_Log::info(
			sprintf(
				'Manual run for import: %s',
				isset( $job->post_title ) ? $job->post_title : 'Unknown Job'
			),
			array(
				'job_id' => $job_id,
			)
		);

		$count = $this->run_job( $job, 100 );

		$msg  = 0 < $count ? __( 'Successfully run!', 'feedzy-rss-feeds' ) : __( 'Nothing imported!', 'feedzy-rss-feeds' );
		$msg .= ' (' . __( 'Refresh this page for the updated status', 'feedzy-rss-feeds' ) . ')';

		wp_send_json_success(
			array(
				'msg'            => $msg,
				'import_success' => 0 < $count,
			)
		);
	}

	/**
	 * Dry run a specific job so that the user is aware what would be imported.
	 *
	 * @return void
	 * 
	 * @access  private
	 */
	private function dry_run() {
		check_ajax_referer( FEEDZY_BASEFILE, 'security' );

		$fields = urldecode( isset( $_POST['fields'] ) ? sanitize_url( $_POST['fields'] ) : '' );
		parse_str( $fields, $data );

		$feedzy_meta_data = $data['feedzy_meta_data'];

		add_filter(
			'feedzy_default_error',
			// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			function ( $errors, $feed, $url ) {
				$errors .=
					sprintf(
						// translators: %1$s and %2$s are opening and closing bold tags respectively.
						__( 'For %1$ssingle feeds%2$s, this could be because of the following reasons:', 'feedzy-rss-feeds' ),
						'<b>',
						'</b>'
					)
					. '<ol>'
					. '<li>'
					. sprintf(
						// translators: %1$s and %2$s are opening and closing bold tags respectively.
						__( '%1$sSource invalid%2$s: Check that your source is valid by clicking the validate button adjacent to the source box.', 'feedzy-rss-feeds' ),
						'<b>',
						'</b>'
					)
					. '</li>'
					. '<li>'
					. sprintf(
						// translators: %1$s and %2$s are opening and closing bold tags respectively.
						__( '%1$sSource unavailable%2$s: Copy the source and paste it on the browser to check that it is available. It could be an intermittent issue.', 'feedzy-rss-feeds' ),
						'<b>',
						'</b>'
					)
					. '</li>'
					. '<li>'
					. sprintf(
						// translators: %1$s and %2$s are opening and closing bold tags respectively.
						__( '%1$sSource inaccessible from server%2$s: Check that your source is accessible from the server (not the browser). It could be an intermittent issue.', 'feedzy-rss-feeds' ),
						'<b>',
						'</b>'
					)
					. '</li>'
					. '</ol>'
					. sprintf(
						// translators: %1$s and %2$s are opening and closing bold tags respectively.
						__( 'For %1$smultiple feeds%2$s (comma-separated or in a Feedzy Category), this could be because of the following reasons:', 'feedzy-rss-feeds' ),
						'<b>',
						'</b>'
					)
					. '<ol>'
					. '<li>'
					. sprintf(
						// translators: %1$s and %2$s are opening and closing bold tags respectively.
						__( '%1$sSource invalid%2$s: One or more feeds may be misbehaving. Check each feed individually as mentioned above to weed out the problematic feed.', 'feedzy-rss-feeds' ),
						'<b>',
						'</b>'
					)
					. '</li>'
					. '</ol>';

				return $errors;
			},
			11,
			3
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
			'[feedzy-rss feeds="%s" max="%d" feed_title=no meta=no summary=no thumb=no error_empty="%s" keywords_inc="%s" %s="%s" %s="%s" _dry_run_tags_="%s" _dryrun_="yes"]',
			$feedzy_meta_data['source'],
			$feedzy_meta_data['import_feed_limit'],
			'', // should be empty.
			$feedzy_meta_data['inc_key'],
			feedzy_is_pro() ? 'keywords_exc' : '',
			feedzy_is_pro() ? $feedzy_meta_data['exc_key'] : '',
			feedzy_is_pro() ? 'keywords_ban' : '',
			feedzy_is_pro() ? $feedzy_meta_data['exc_key'] : '',
			implode( ',', $tags )
		);

		Feedzy_Rss_Feeds_Log::debug(
			'Dry run shortcode generated',
			array(
				'shortcode' => $shortcode,
			)
		);

		wp_send_json_success( array( 'output' => do_shortcode( $shortcode ) ) );
	}

	/**
	 * The Cron Job.
	 * 
	 * @param int $max The import feed limit.
	 * @param int $job_id The ID of the custom post type with the job details.
	 *
	 * @since   1.2.0
	 * @access  public
	 */
	public function run_cron( $max = 100, $job_id = 0 ) {
		if ( empty( $max ) ) {
			$max = 10;
		}
		global $post;
		$args = apply_filters(
			'feedzy_run_cron_get_posts_args',
			array(
				'post_type'   => 'feedzy_imports',
				'post_status' => 'publish',
				'numberposts' => 99,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'  => array(
					'relation' => 'AND',
					array(
						'key'     => 'fz_cron_schedule',
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		if ( $job_id ) {
			$args['post__in'] = array( $job_id );
			unset( $args['meta_query'], $args['numberposts'] );
		}

		$feedzy_imports = get_posts( $args );
		foreach ( $feedzy_imports as $job ) {
			try {
				$result = $this->run_job( $job, $max );

				Feedzy_Rss_Feeds_Log::debug(
					'Cron job run for: ' . $job->post_title,
					array(
						'job_id' => $job->ID,
						'result' => $result,
					)
				);

				if ( empty( $result ) ) {
					$this->run_job( $job, $max );

					Feedzy_Rss_Feeds_Log::debug(
						'Previous run did not return any results, running again for job: ' . $job->post_title,
						array(
							'job_id' => $job->ID,
							'result' => $result,
						)
					);
				}
				do_action( 'feedzy_run_cron_extra', $job );
			} catch ( Exception $e ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( '[Feedzy Run Cron][Post title: ' . ( ! empty( $job->post_title ) ? $job->post_title : '' ) . '] Error: ' . $e->getMessage() );
				}

				Feedzy_Rss_Feeds_Log::error(
					// translators: %1$s is the import job title, %2$s is the error message.
					sprintf( __( 'Error when running "%1$s": %2$s', 'feedzy-rss-feeds' ), $job->post_title, $e->getMessage() ),
					array(
						'job_id' => $job->ID,
						'error'  => $e->getMessage(),
					)
				);
			}
		}
	}

	/**
	 * Runs a specific job.
	 * 
	 * @param \WP_Post $job The custom post type with the job options.
	 * @param int      $max The import feed limit.
	 *
	 * @return  int The number of imported items.
	 * 
	 * @since   1.6.1
	 * @access  private
	 */
	private function run_job( $job, $max ) {
		Feedzy_Rss_Feeds_Usage::get_instance()->track_rss_import();
		Feedzy_Rss_Feeds_Log::get_instance()->enable_error_messages_retention();

		global $themeisle_log_event;
		$source                   = get_post_meta( $job->ID, 'source', true );
		$inc_key                  = get_post_meta( $job->ID, 'inc_key', true );
		$exc_key                  = get_post_meta( $job->ID, 'exc_key', true );
		$inc_on                   = get_post_meta( $job->ID, 'inc_on', true );
		$exc_on                   = get_post_meta( $job->ID, 'exc_on', true );
		$import_title             = get_post_meta( $job->ID, 'import_post_title', true );
		$import_title             = $this->feedzy_import_trim_tags( $import_title );
		$import_date              = get_post_meta( $job->ID, 'import_post_date', true );
		$post_excerpt             = get_post_meta( $job->ID, 'import_post_excerpt', true );
		$post_excerpt             = $this->feedzy_import_trim_tags( $post_excerpt );
		$import_content           = get_post_meta( $job->ID, 'import_post_content', true );
		$import_featured_img      = get_post_meta( $job->ID, 'import_post_featured_img', true );
		$import_post_type         = get_post_meta( $job->ID, 'import_post_type', true );
		$import_post_term         = get_post_meta( $job->ID, 'import_post_term', true );
		$import_feed_limit        = get_post_meta( $job->ID, 'import_feed_limit', true );
		$import_item_img_url      = get_post_meta( $job->ID, 'import_use_external_image', true );
		$import_remove_duplicates = get_post_meta( $job->ID, 'import_remove_duplicates', true );
		$import_selected_language = get_post_meta( $job->ID, 'language', true );
		$from_datetime            = get_post_meta( $job->ID, 'from_datetime', true );
		$to_datetime              = get_post_meta( $job->ID, 'to_datetime', true );
		$import_auto_translation  = get_post_meta( $job->ID, 'import_auto_translation', true );
		$import_auto_translation  = $this->feedzy_is_agency() && 'yes' === $import_auto_translation ? true : false;
		$import_translation_lang  = get_post_meta( $job->ID, 'import_auto_translation_lang', true );
		$filter_conditions        = get_post_meta( $job->ID, 'filter_conditions', true );
		$import_post_author       = get_post_meta( $job->ID, 'import_post_author', true );
		$mark_duplicate_tag       = get_post_meta( $job->ID, 'mark_duplicate_tag', true );
		$mark_duplicate_tag       = feedzy_is_pro() && ! empty( $mark_duplicate_tag ) ? preg_replace( '/[\[\]#]/', '', $mark_duplicate_tag ) : '';
		$max                      = $import_feed_limit;
		$import_remove_html       = get_post_meta( $job->ID, 'import_remove_html', true );
		$import_order             = get_post_meta( $job->ID, 'import_order', true );

		Feedzy_Rss_Feeds_Log::info(
			'Running import job: ' . $job->post_title . ' (ID: ' . $job->ID . ')',
			array(
				'job_id'                   => $job->ID,
				'source'                   => $source,
				'max'                      => $max,
				'status'                   => $job->post_status,
				'exc_key'                  => $exc_key,
				'inc_key'                  => $inc_key,
				'inc_on'                   => $inc_on,
				'exc_on'                   => $exc_on,
				'import_title'             => $import_title,
				'import_date'              => $import_date,
				'post_excerpt'             => $post_excerpt,
				'import_content'           => $import_content,
				'import_featured_img'      => $import_featured_img,
				'import_post_type'         => $import_post_type,
				'import_post_term'         => $import_post_term,
				'import_feed_limit'        => $import_feed_limit,
				'import_item_img_url'      => $import_item_img_url,
				'import_remove_duplicates' => $import_remove_duplicates,
				'import_selected_language' => $import_selected_language,
				'from_datetime'            => $from_datetime,
				'mark_duplicate_tag'       => $mark_duplicate_tag,
				'filter_conditions'        => $filter_conditions,
				'import_auto_translation'  => $import_auto_translation,
				'import_translation_lang'  => $import_translation_lang,
			)
		);

		if ( empty( $filter_conditions ) ) {
			$filter_conditions = apply_filters(
				'feedzy_filter_conditions_migration',
				array(
					'keywords_inc'    => $inc_key,
					'keywords_exc'    => $exc_key,
					'keywords_inc_on' => $inc_on,
					'keywords_exc_on' => $exc_on,
					'from_datetime'   => $from_datetime,
					'to_datetime'     => $to_datetime,
				)
			);
		}

		if ( metadata_exists( 'post', $job->ID, 'import_post_status' ) ) {
			$import_post_status = get_post_meta( $job->ID, 'import_post_status', true );
		} else {
			add_post_meta( $job->ID, 'import_post_status', 'publish' );
			$import_post_status = get_post_meta( $job->ID, 'import_post_status', true );
		}

		// the array of imported items that uses the old scheme of custom hashing the url and date.
		$imported_items     = array();
		$imported_items_old = get_post_meta( $job->ID, 'imported_items', true );
		if ( ! is_array( $imported_items_old ) ) {
			$imported_items_old = array();
		}

		// the array of imported items that uses the new scheme of SimplePie's hash/id.
		$imported_items_new = get_post_meta( $job->ID, 'imported_items_hash', true );
		if ( ! is_array( $imported_items_new ) ) {
			$imported_items_new = array();
		}

		// Get default thumbnail ID.
		$global_fallback_thumbnail = ! empty( $this->free_settings['general']['default-thumbnail-id'] ) ? (int) $this->free_settings['general']['default-thumbnail-id'] : 0;
		if ( feedzy_is_pro() ) {
			$default_thumbnail = get_post_meta( $job->ID, 'default_thumbnail_id', true );
			$default_thumbnail = ! empty( $default_thumbnail ) ? explode( ',', (string) $default_thumbnail ) : $global_fallback_thumbnail;
		} else {
			$default_thumbnail = $global_fallback_thumbnail;
		}

		// Note: this implementation will only work if only one of the fields is allowed to provide
		// the date, because if the title can have UTC date and content can have local date then it
		// all goes sideways.
		// also if the user provides multiple date types, local will win.
		$meta = 'yes';
		if ( strpos( $import_title, '[#item_date_local]' ) !== false ) {
			$meta = 'author, date, time, tz=local';
		} elseif ( strpos( $import_title, '[#item_date_feed]' ) !== false ) {
			$meta = 'author, date, time, tz=no';
		}

		$options = apply_filters(
			'feedzy_shortcode_options',
			array(
				'feeds'         => $source,
				'max'           => $max,
				'feed_title'    => 'no',
				'target'        => '_blank',
				'title'         => '',
				'meta'          => $meta,
				'summary'       => 'yes',
				'summarylength' => '',
				'thumb'         => 'auto',
				'default'       => '',
				'size'          => '250',
				'columns'       => 1,
				'offset'        => 0,
				'multiple_meta' => 'no',
				'refresh'       => '55_mins',
				'filters'       => $filter_conditions,
				'sort'          => $import_order,
			),
			$job
		);

		Feedzy_Rss_Feeds_Log::info(
			'Running job options via feedzy_shortcode_options',
			array(
				'job_id'  => $job->ID,
				'options' => $options,
			)
		);

		$admin   = Feedzy_Rss_Feeds::instance()->get_admin();
		$options = $admin->sanitize_attr( $options, $source );

		$options['__jobID'] = $job->ID;

		$last_run = time();
		update_post_meta( $job->ID, 'last_run', $last_run );
		// we will use this last_run_id to associate imports with a specific job run.
		update_post_meta( $job->ID, 'last_run_id', $last_run );
		delete_post_meta( $job->ID, 'import_errors' );
		delete_post_meta( $job->ID, 'import_info' );

		// let's increase this time in case spinnerchief/wordai is being used.
		set_time_limit( apply_filters( 'feedzy_max_execution_time', 500 ) );

		$count               = 0;
		$index               = 0;
		$import_image_errors = 0;
		$duplicates          = 0;

		// the array that captures errors about the import.
		$import_errors = array();

		// the array that captures additional information about the import.
		$import_info   = array();
		$results       = $this->get_job_feed( $options, $import_content, true );
		$language_code = $results['feed']->get_language();
		
		$xml_results = '';
		if ( str_contains( $import_content, '_full_content' ) ) {
			$xml_results = $this->get_job_feed( $options, '[#item_content]', true );
		}
		
		if ( is_wp_error( $results ) ) {
			// BUG: If $results is error, the import run details will not show the results even if the errors are set.
			Feedzy_Rss_Feeds_Log::error(
				sprintf(
					// translators: %s is the error message.
					__( 'Error when fetching the feed items: %s', 'feedzy-rss-feeds' ),
					$results->get_error_message()
				),
				array(
					'job_id'  => $job->ID,
					'errors'  => $results->get_error_messages(),
					'options' => $options,
					'source'  => $source,
				)
			);

			$import_errors = Feedzy_Rss_Feeds_Log::get_instance()->get_error_messages_accumulator();
			Feedzy_Rss_Feeds_Log::get_instance()->disable_error_messages_retention();

			update_post_meta( $job->ID, 'import_errors', $import_errors );
			update_post_meta( $job->ID, 'imported_items_count', 0 );

			return 0;
		}

		$result = $results['items'];
		do_action( 'feedzy_run_job_pre', $job, $result );

		// check if we should be using the old scheme of custom hashing the url and date
		// or the new scheme of depending on SimplePie's hash/id
		// basically if the old scheme hasn't be used before, use the new scheme
		// BUT if the old scheme has been used, continue with it.
		$use_new_hash   = empty( $imported_items_old );
		$imported_items = $use_new_hash ? $imported_items_new : $imported_items_old;

		$start_import = true;
		// bail if both title and content are empty because the post will not be created.
		if ( empty( $import_title ) && empty( $import_content ) ) {
			$import_errors[] = __( 'Title & Content are both empty.', 'feedzy-rss-feeds' );
			$start_import    = false;

			Feedzy_Rss_Feeds_Log::warning(
				'Import job cannot start because both Title and Content mapping are empty.',
				array(
					'job_id' => $job->ID,
				)
			);
		}

		if ( ! $start_import ) {
			update_post_meta( $job->ID, 'import_errors', $import_errors );

			return 0;
		}

		$rewrite_service_endabled = $this->rewrite_content_service_endabled();

		$duplicates       = array();
		$items_found      = array();
		$found_duplicates = array();
		$result_count     = count( $result );

		foreach ( $result as $key => $item ) {
			Feedzy_Rss_Feeds_Log::debug(
				sprintf( 'Processing item %1$s/%2$s', $key, $result_count ),
				array(
					'item_url' => $item['item_url'],
				)
			);
			
			$item_obj = $item;
			// find item index key when import full content.
			if ( ! empty( $xml_results ) ) {
				$item_unique_hash = array_column( $xml_results['items'], 'item_unique_hash' );
				$real_index_key   = array_search( $item['item_unique_hash'], $item_unique_hash, true );
				if ( isset( $xml_results['items'][ $real_index_key ] ) ) {
					$item_obj = $xml_results['items'][ $real_index_key ];
				}
			}
			$item_hash                        = $use_new_hash ? $item['item_id'] : hash( 'sha256', $item['item_url'] . '_' . $item['item_date'] );
			$is_duplicate                     = $use_new_hash ? in_array( $item_hash, $imported_items_new, true ) : in_array( $item_hash, $imported_items_old, true );
			$items_found[ $item['item_url'] ] = $item['item_title'];

			$duplicate_tag_value = array();
			$mark_duplicate_key  = 'item_url';
			if ( 'yes' === $import_remove_duplicates && ! $is_duplicate ) {
				if ( ! empty( $mark_duplicate_tag ) ) {
					$mark_duplicate_tag  = is_string( $mark_duplicate_tag ) ? explode( ',', $mark_duplicate_tag ) : $mark_duplicate_tag;
					$mark_duplicate_tag  = array_map( 'trim', $mark_duplicate_tag );
					$duplicate_tag_value = array_map(
						function ( $tag ) use ( $item_obj, $item ) {
							if ( str_contains( $tag, 'item_custom' ) && $this->feedzy_is_business() ) {
								$tag = apply_filters( 'feedzy_parse_custom_tags', "[#$tag]", $item_obj );
							} elseif ( isset( $item[ $tag ] ) ) {
								$tag = isset( $item[ $tag ] ) ? is_object( $item[ $tag ] ) ? wp_json_encode( $item[ $tag ] ) : $item[ $tag ] : '';
							}
							return $tag;
						},
						$mark_duplicate_tag
					);
				}
				if ( ! empty( $duplicate_tag_value ) ) {
					$duplicate_tag_value = implode( ' ', $duplicate_tag_value );
					$duplicate_tag_value = substr( sanitize_key( wp_strip_all_tags( $duplicate_tag_value ) ), 0, apply_filters( 'feedzy_mark_duplicate_content_limit', 256 ) );
					$mark_duplicate_key  = 'mark_duplicate';
				} else {
					$duplicate_tag_value = esc_url_raw( $item['item_url'] );
				}
				$is_duplicate_post = $this->is_duplicate_post( $import_post_type, 'feedzy_' . $mark_duplicate_key, $duplicate_tag_value );
				if ( ! empty( $is_duplicate_post ) ) {
					foreach ( $is_duplicate_post as $p ) {
						$found_duplicates[ $item_hash ]  = get_post_meta( $p, 'feedzy_' . $mark_duplicate_key, true );
						$duplicates[ $item['item_url'] ] = $item['item_title'];
						wp_delete_post( $p, true );
						Feedzy_Rss_Feeds_Log::debug(
							sprintf( 'Deleted a duplicate post: %1$s', $item['item_title'] ),
							array(
								'item_url' => $item['item_url'],
								'post_id'  => $p,
								'hash'     => $item_hash,
							)
						);
					}
				}
			}
			if ( $is_duplicate ) {
				do_action(
					'feedzy_log_event',
					array(
						'type'   => 'info',
						'output' => sprintf( 'Ignoring URl %1$s with hash %2$s as it is a duplicate (%3$s hash).', $item['item_url'], $item_hash, $use_new_hash ? 'new' : 'old' ),
						'file'   => __FILE__,
						'line'   => __LINE__,
					) 
				);
				Feedzy_Rss_Feeds_Log::debug(
					sprintf( 'Ignoring item %1$s as it is a duplicate (%2$s hash).', $item['item_url'], $use_new_hash ? 'new' : 'old' )
				);
				++$index;
				$duplicates[ $item['item_url'] ] = $item['item_title'];
				continue;
			}

			$author = '';
			if ( $item['item_author'] ) {
				if ( is_string( $item['item_author'] ) ) {
					$author = $item['item_author'];
				} elseif ( is_object( $item['item_author'] ) ) {
					$author = $item['item_author']->get_name();
					if ( empty( $author ) ) {
						$author = $item['item_author']->get_email();
					}
				}

				Feedzy_Rss_Feeds_Log::debug(
					sprintf( 'Found author: %s', $author ),
					array(
						'item_author' => $item['item_author'],
						'item_url'    => $item['item_url'],
					)
				);
			}


			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$item_date = wp_date( get_option( 'date_format' ) . ' at ' . get_option( 'time_format' ), $item['item_date'] );
			$item_date = $item['item_date_formatted'];

			// Get translated item title.
			$translated_title = '';
			if ( $import_auto_translation && ( false !== strpos( $import_title, '[#translated_title]' ) || false !== strpos( $post_excerpt, '[#translated_title]' ) ) ) {
				$translated_title = apply_filters( 'feedzy_invoke_auto_translate_services', $item['item_title'], '[#translated_title]', $import_translation_lang, $job, $language_code, $item );
			}

			$import_title = rawurldecode( $import_title );
			$import_title = str_replace( PHP_EOL, "\r\n", $import_title );
			$import_title = trim( $import_title );

			$post_title = str_replace(
				array(
					'[#item_title]',
					'[#item_author]',
					'[#item_date]',
					'[#item_date_local]',
					'[#item_date_feed]',
					'[#item_source]',
					'[#translated_title]',
				),
				array(
					$item['item_title'],
					$author,
					$item_date,
					$item_date,
					$item_date,
					$item['item_source'],
					$translated_title,
				),
				$import_title
			);

			// Run all the actions stored for the embedded/serialized tags in the title field.
			$title_action = $this->get_actions_runner( $post_title, 'item_title' );
			$post_title   = $title_action->run_action_job( $title_action->get_serialized_actions(), $translated_title, $job, $language_code, $item );
			$title_lang   = $title_action->get_translation_lang();

			if ( $this->feedzy_is_business() ) {
				$post_title = apply_filters( 'feedzy_parse_custom_tags', $post_title, $item_obj );
			}

			$post_title = apply_filters( 'feedzy_invoke_services', $post_title, 'title', $item['item_title'], $job );

			// Get translated item link text.
			$item_link_txt = __( 'Read More', 'feedzy-rss-feeds' );

			// Now that we set language in the action, we use title's language for the link.
			if ( ( $import_auto_translation || $title_lang ) && false !== strpos( $import_content, '[#item_url]' ) ) {
				$item_link_txt = apply_filters( 'feedzy_invoke_auto_translate_services', $item_link_txt, '[#item_url]', $title_lang, $job, $language_code, $item );
			}

			$item_link_data = apply_filters(
				'feedzy_item_link',
				array(
					'text' => $item_link_txt,
					'attr' => array(
						'href'   => $item['item_url'],
						'target' => '_blank',
						'class'  => 'feedzy-rss-link-icon',
					),
				),
				$item,
				$job
			);

			// Remove WordPress default link rel.
			$link_rel = isset( $item_link_data['attr']['rel'] ) ? $item_link_data['attr']['rel'] : '';
			if ( $link_rel ) {
				add_filter(
					'wp_targeted_link_rel',
					function () use ( $link_rel ) {
						return $link_rel;
					}
				);
			}

			$item_link_attr = isset( $item_link_data['attr'] ) ? $item_link_data['attr'] : array();
			$item_link_attr = array_map(
				function ( $attr, $key ) {
					return sprintf( '%1$s="%2$s"', $key, esc_attr( $attr ) );
				},
				$item_link_attr,
				array_keys( $item_link_attr )
			);

			$item_link_txt = isset( $item_link_data['text'] ) ? $item_link_data['text'] : $item_link_txt;
			$item_link     = '<a ' . implode( ' ', $item_link_attr ) . '>' . $item_link_txt . '</a>';

			// Rewriter item title from feedzy API.
			if ( $rewrite_service_endabled && false !== strpos( $post_title, '[#title_feedzy_rewrite]' ) ) {
				$title_feedzy_rewrite = apply_filters( 'feedzy_invoke_content_rewrite_services', $item['item_title'], '[#title_feedzy_rewrite]', $job, $item );
				$post_title           = str_replace( '[#title_feedzy_rewrite]', $title_feedzy_rewrite, $post_title );
				Feedzy_Rss_Feeds_Log::debug(
					sprintf( 'Using Feedzy rewrite service for item title: %1$s', $item['item_title'] ),
					array(
						'job_id'         => $job->ID,
						'service'        => 'feedzy_invoke_content_rewrite_services',
						'service_output' => $title_feedzy_rewrite,
					)
				);
			}

			if ( is_string( $post_title ) ) {
				$post_title = wp_strip_all_tags( $post_title );
			}
			
			$image_html = '';
			if ( ! empty( $item['item_img_path'] ) ) {
				$image_html = '<img src="' . $item['item_img_path'] . '" title="' . $item['item_title'] . '" />';
			}

			// Get translated item description.
			$translated_description = '';
			if ( $import_auto_translation && ( false !== strpos( $import_content, '[#translated_description]' ) || false !== strpos( $post_excerpt, '[#translated_description]' ) ) ) {
				$translated_description = apply_filters( 'feedzy_invoke_auto_translate_services', $item['item_full_description'], '[#translated_description]', $import_translation_lang, $job, $language_code, $item );

				Feedzy_Rss_Feeds_Log::debug(
					sprintf( 'Using auto-translation service for item description: %1$s', $item['item_full_description'] ),
					array(
						'job_id'         => $job->ID,
						'service'        => 'feedzy_invoke_auto_translate_services',
						'service_output' => $translated_description,
						'language_code'  => $language_code,
					)
				);
			}

			// Get translated item content.
			$translated_content = '';
			if ( $import_auto_translation && ( false !== strpos( $import_content, '[#translated_content]' ) || false !== strpos( $post_excerpt, '[#translated_content]' ) ) ) {
				$translated_content = ! empty( $item['item_content'] ) ? $item['item_content'] : $item['item_description'];
				$translated_content = apply_filters( 'feedzy_invoke_auto_translate_services', $translated_content, '[#translated_content]', $import_translation_lang, $job, $language_code, $item );

				Feedzy_Rss_Feeds_Log::debug(
					sprintf( 'Using auto-translation service for item content: %1$s', $item['item_content'] ),
					array(
						'job_id'         => $job->ID,
						'service'        => 'feedzy_invoke_auto_translate_services',
						'service_output' => $translated_description,
					)
				);
			}

			// Used as a new line character in import content.
			$import_content = rawurldecode( $import_content );
			$import_content = str_replace( PHP_EOL, "\r\n", $import_content );
			$import_content = trim( $import_content );

			$post_content = str_replace(
				array(
					'[#item_description]',
					'[#item_content]',
					'[#item_image]',
					'[#item_url]',
					'[#item_categories]',
					'[#item_source]',
					'[#translated_description]',
					'[#translated_content]',
					'[#item_price]',
					'[#item_author]',
				),
				array(
					$item['item_description'],
					! empty( $item['item_content'] ) ? $item['item_content'] : $item['item_description'],
					$image_html,
					$item_link,
					$item['item_categories'],
					$item['item_source'],
					$translated_description,
					$translated_content,
					! empty( $item['item_price'] ) ? $item['item_price'] : '',
					$author,
				),
				$import_content
			);

			if ( $this->feedzy_is_business() ) {
				$full_content = ! empty( $item['item_full_content'] ) ? $item['item_full_content'] : $item['item_content'];
				if ( str_contains( $import_content, '_full_content' ) ) {
					// if full content is empty, log a message.
					if ( empty( $full_content ) ) {
						// let's see if there is an error.
						$full_content_error = isset( $item['full_content_error'] ) && ! empty( $item['full_content_error'] ) ? $item['full_content_error'] : '';
						if ( empty( $full_content_error ) ) {
							$full_content_error = __( 'Unknown', 'feedzy-rss-feeds' );
						}
						$import_errors[] = sprintf(
							// translators: %s: Error message for empty full content.
							__( 'Full content is empty. Error: %s', 'feedzy-rss-feeds' ),
							$full_content_error
						);

						Feedzy_Rss_Feeds_Log::error(
							sprintf(
								// translators: %s: Error message for empty full content.
								__( 'Full content is empty. Error: %s', 'feedzy-rss-feeds' ),
								$full_content_error
							),
							array(
								'job_id'        => $job->ID,
								'import_errors' => $import_errors,
								'item_url'      => $item['item_url'],
								'source'        => $source,
							)
						);
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
			// Item content action.
			$content_action = $this->get_actions_runner( $post_content, 'item_content' );
			$post_content   = $content_action->get_serialized_actions();
			// Item content action process.
			$post_content = $content_action->run_action_job( $post_content, $import_translation_lang, $job, $language_code, $item );
			// Parse custom tags.
			if ( $this->feedzy_is_business() ) {
				$post_content = apply_filters( 'feedzy_parse_custom_tags', $post_content, $item_obj );
			}

			$post_content = apply_filters( 'feedzy_invoke_services', $post_content, 'content', $item['item_description'], $job );

			// Translate full-content.
			if ( $import_auto_translation && false !== strpos( $post_content, '[#translated_full_content]' ) ) {
				$translated_full_content = apply_filters( 'feedzy_invoke_auto_translate_services', $item['item_url'], '[#translated_full_content]', $import_translation_lang, $job, $language_code, $item );
				$post_content            = str_replace( '[#translated_full_content]', rtrim( $translated_full_content, '.' ), $post_content );

				Feedzy_Rss_Feeds_Log::debug(
					sprintf( 'Using auto-translation service for item full content: %1$s', $item['item_url'] ),
					array(
						'job_id'         => $job->ID,
						'service'        => 'feedzy_invoke_auto_translate_services',
						'service_output' => $translated_full_content,
						'language_code'  => $language_code,
					)
				);
			}
			// Rewriter item content from feedzy API.
			if ( $rewrite_service_endabled && false !== strpos( $post_content, '[#content_feedzy_rewrite]' ) ) {
				$item_content           = ! empty( $item['item_content'] ) ? $item['item_content'] : $item['item_description'];
				$content_feedzy_rewrite = apply_filters( 'feedzy_invoke_content_rewrite_services', $item_content, '[#content_feedzy_rewrite]', $job, $item );
				$post_content           = str_replace( '[#content_feedzy_rewrite]', $content_feedzy_rewrite, $post_content );

				Feedzy_Rss_Feeds_Log::debug(
					sprintf( 'Using Feedzy rewrite service for item content: %1$s', $item['item_url'] ),
					array(
						'job_id'         => $job->ID,
						'service'        => 'feedzy_invoke_content_rewrite_services',
						'service_input'  => $item_content,
						'service_output' => $content_feedzy_rewrite,
					)
				);
			}

			// Rewriter item full content from feedzy API.
			if ( $rewrite_service_endabled && false !== strpos( $post_content, '[#full_content_feedzy_rewrite]' ) ) {
				$full_content_feedzy_rewrite = apply_filters( 'feedzy_invoke_content_rewrite_services', $item['item_url'], '[#full_content_feedzy_rewrite]', $job, $item );
				$post_content                = str_replace( '[#full_content_feedzy_rewrite]', $full_content_feedzy_rewrite, $post_content );

				Feedzy_Rss_Feeds_Log::debug(
					sprintf( 'Using Feedzy rewrite service for item full content: %1$s', $item['item_url'] ),
					array(
						'job_id'         => $job->ID,
						'service'        => 'feedzy_invoke_content_rewrite_services',
						'service_output' => $full_content_feedzy_rewrite,
					)
				);
			}

			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$item_date = wp_date( 'Y-m-d H:i:s', $item['item_date'] );
			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$now = wp_date( 'Y-m-d H:i:s' );
			if ( trim( $import_date ) === '' ) {
				$post_date = $now;
			}
			$post_date = str_replace( '[#item_date]', $item_date, $import_date );
			$post_date = str_replace( '[#post_date]', $now, $post_date );

			if ( ! defined( 'FEEDZY_ALLOW_UNSAFE_HTML' ) || ! FEEDZY_ALLOW_UNSAFE_HTML ) {
				$post_content = wp_kses_post( $post_content );

				if ( ! function_exists( 'use_block_editor_for_post_type' ) ) {
					require_once ABSPATH . 'wp-admin/includes/post.php';
				}

				if ( function_exists( 'use_block_editor_for_post_type' ) && use_block_editor_for_post_type( $import_post_type ) ) {
					$post_content = ! empty( $post_content ) ? '<!-- wp:html -->' . trim( force_balance_tags( wpautop( $post_content, 'br' ) ) ) . '<!-- /wp:html -->' : $post_content;
					$post_content = trim( $post_content );
				}
			}

			$item_post_excerpt = str_replace(
				array(
					'[#item_title]',
					'[#item_content]',
					'[#item_description]',
					'[#translated_title]',
					'[#translated_content]',
					'[#translated_description]',
				),
				array(
					$post_title,
					$post_content,
					$item['item_description'],
					$translated_title,
					$translated_content,
					$translated_description,
				),
				$post_excerpt
			);

			if ( $this->feedzy_is_business() ) {
				$item_post_excerpt = apply_filters( 'feedzy_parse_custom_tags', $item_post_excerpt, $item_obj );
			}

			$post_author = ! empty( $import_post_author ) ? $import_post_author : $job->post_author;

			// strip all HTML tag when remove html option is enabled.
			if ( 'yes' === $import_remove_html ) {
				$post_content = wp_strip_all_tags( $post_content );
			}

			$new_post = apply_filters(
				'feedzy_insert_post_args',
				array(
					'post_type'    => $import_post_type,
					'post_title'   => wp_kses( $post_title, apply_filters( 'feedzy_wp_kses_allowed_html', array() ) ),
					'post_content' => $post_content,
					'post_date'    => $post_date,
					'post_status'  => $import_post_status,
					'post_excerpt' => $item_post_excerpt,
					'post_author'  => $post_author,
				),
				$item_obj,
				$post_title,
				$post_content,
				$item_post_excerpt,
				$index,
				$job
			);

			// no point creating a post if either the title or the content is null.
			if ( is_null( $post_title ) || is_null( $post_content ) ) {
				++$index;

				Feedzy_Rss_Feeds_Log::error(
					__( 'Title or Content is empty.', 'feedzy-rss-feeds' ),
					array(
						'job_id'   => $job->ID,
						'new_post' => $new_post,
					)
				);
				continue;
			}

			if ( 'attachment' === $import_post_type ) {
				$image_source_url = '';
				$img_success      = true;
				$new_post_id      = 0;
				$img_title        = $item['item_title'];
				$feed_img_tag     = ! empty( $import_featured_img ) && is_string( $import_featured_img ) ? $import_featured_img : '';

				// image tag.
				if ( strpos( $feed_img_tag, '[#item_image]' ) !== false ) {
					if ( ! empty( $item['item_img_path'] ) ) {
						$image_source_url = str_replace( '[#item_image]', $item['item_img_path'], $feed_img_tag );
					} else {
						$img_success = false;
					}

					Feedzy_Rss_Feeds_Log::debug(
						'Set the image source URL from item image tag for attachment post type.',
						array(
							'job_id'           => $job->ID,
							'feed_img_tag'     => $feed_img_tag,
							'image_source_url' => $image_source_url,
							'item_img_path'    => $item['item_img_path'],
						)
					);
				} elseif ( strpos( $feed_img_tag, '[#item_custom' ) !== false ) {
					$value = '';
					if ( $this->feedzy_is_business() || $this->feedzy_is_personal() ) {
						$value = apply_filters( 'feedzy_parse_custom_tags', $feed_img_tag, $item_obj );
					}

					if ( ! empty( $value ) && strpos( $value, '[#item_custom' ) === false ) {
						$image_source_url = $value;
					} else {
						$img_success = false;
					}

					Feedzy_Rss_Feeds_Log::debug(
						'Set the image source URL from custom tag for attachment post type.',
						array(
							'job_id'           => $job->ID,
							'feed_img_tag'     => $feed_img_tag,
							'image_source_url' => $image_source_url,
							'item_custom'      => $value,
							'is_business'      => $this->feedzy_is_business(),
							'is_personal'      => $this->feedzy_is_personal(),
						)
					);
				} else {
					$image_source_url = $feed_img_tag;
					$img_title        = pathinfo( basename( $image_source_url ), PATHINFO_FILENAME );
				}

				if ( ! empty( $image_source_url ) ) {
					$img_success = $this->try_save_featured_image( $image_source_url, 0, $img_title, $import_info, $new_post );
					$new_post_id = $img_success;

					Feedzy_Rss_Feeds_Log::debug(
						'Try to save featured image for attachment post type.',
						array(
							'job_id'           => $job->ID,
							'feed_img_tag'     => $feed_img_tag,
							'img_title'        => $img_title,
							'post_id'          => $new_post_id,
							'image_source_url' => $image_source_url,
							'is_business'      => $this->feedzy_is_business(),
							'is_personal'      => $this->feedzy_is_personal(),
						)
					);
				}

				if ( ! $img_success ) {
					++$import_image_errors;
				}
			} else {
				$new_post_id = wp_insert_post( $new_post, true );
			}

			// Set post language.
			if ( function_exists( 'pll_set_post_language' ) && ! empty( $import_selected_language ) ) {
				pll_set_post_language( $new_post_id, $import_selected_language );
			} elseif ( function_exists( 'icl_get_languages' ) && ! empty( $import_selected_language ) ) {
				$this->set_wpml_element_language_details( $import_post_type, $new_post_id, $import_selected_language );
			}

			if ( 0 === $new_post_id || is_wp_error( $new_post_id ) ) {
				if ( is_wp_error( $new_post_id ) ) {
					Feedzy_Rss_Feeds_Log::error(
						sprintf(
							// translators: %1$s is the item URL, %2$s is the error message.
							__( 'Could not save the "%1$s" as post: %2$s', 'feedzy-rss-feeds' ),
							esc_url( $item['item_url'] ),
							$new_post_id->get_error_message()
						),
						array(
							'job_id'   => $job->ID,
							'new_post' => $new_post,
						)
					);
				}

				++$index;
				continue;
			}

			Feedzy_Rss_Feeds_Log::info(
				'Created a new post: ' . $new_post['post_title'],
				array(
					'job_id'    => $job->ID,
					'post_id'   => $new_post_id,
					'post_link' => get_permalink( $new_post_id ),
				)
			);

			if ( ! in_array( $item_hash, $found_duplicates, true ) ) {
				$imported_items[] = $item_hash;
				++$count;
			}

			if (
				'none' !== $import_post_term &&
				0 < strpos( $import_post_term, '_' )
			) {
				$terms = explode( ',', $import_post_term );
				$terms = apply_filters( 'feedzy_import_terms', $terms, $item );
				$terms = array_filter(
					$terms,
					function ( $term ) {
						if ( empty( $term ) ) {
							return;
						}
						if ( false !== strpos( $term, '[#item_' ) ) {
							return;
						}
						if ( false !== strpos( $term, '[#auto_categories]' ) ) {
							return;
						}
						return $term;
					}
				);

				$default_category = (int) get_option( 'default_category' );
				$has_default      = false;

				foreach ( $terms as $term ) {
					// this handles both x_2, where 2 is the term id and x is the taxonomy AND x_2_3_4 where 4 is the term id and the taxonomy name is "x 2 3 4".
					$array    = explode( '_', $term );
					$term_id  = array_pop( $array );
					$taxonomy = implode( '_', $array );

					// If the term is not default, flag it.
					if ( $default_category === (int) $term_id ) {
						$has_default = true;
					}

					$result = wp_set_object_terms( $new_post_id, intval( $term_id ), $taxonomy, true );

					Feedzy_Rss_Feeds_Log::info(
						sprintf( 'Set term "%1$s" with ID %2$s for feedzy import ID %3$s', $taxonomy, $term_id, $new_post_id ),
						array(
							'job_id' => $job->ID,
						)
					);
				}

				// If the default category is not used, remove it.
				if ( ! $has_default ) {
					wp_remove_object_terms(
						$new_post_id,
						apply_filters(
							'feedzy_uncategorized',
							array(
								$default_category,
							),
							$job->ID
						),
						'category'
					);
				}
			}

			do_action(
				'feedzy_import_extra',
				$job,
				$item_obj,
				$new_post_id,
				Feedzy_Rss_Feeds_Log::get_instance()->get_error_messages_accumulator(),
				$import_info,
				array(
					'translation_lang' => $import_translation_lang,
					'language_code'    => $language_code,
					'item'             => $item,
				)
			);

			if ( ! empty( $import_featured_img ) && 'attachment' !== $import_post_type ) {
				$image_source_url = '';
				$img_success      = true;
				$img_title        = $item['item_title'];

				$feed_img_tag = false === strpos( $import_featured_img, '[[{"value":' ) ? $import_featured_img : '[#item_image]'; // Use feed default image when we are using chained actions.

				// Set the feed image as default value for the image source.
				if ( strpos( $feed_img_tag, '[#item_image]' ) !== false ) {
					if ( ! empty( $item['item_img_path'] ) ) { // image exists in item.
						$image_source_url = str_replace( '[#item_image]', $item['item_img_path'], $feed_img_tag );
					} else {
						$img_success = false;
					}

					Feedzy_Rss_Feeds_Log::debug(
						'Found an image for [#item_image]',
						array(
							'job_id'           => $job->ID,
							'feed_img_tag'     => $feed_img_tag,
							'image_source_url' => $image_source_url,
							'item_img_path'    => $item['item_img_path'],
						)
					);
				} elseif (
					( $this->feedzy_is_business() || $this->feedzy_is_personal() ) && // PRO feature.
					false !== strpos( $feed_img_tag, '[#item_custom' )
				) {
					$value = apply_filters( 'feedzy_parse_custom_tags', $feed_img_tag, $item_obj ); // custom image tag.
					if ( ! empty( $value ) && strpos( $value, '[#item_custom' ) === false ) {
						$image_source_url = $value;
					} else {
						$img_success = false;
					}

					Feedzy_Rss_Feeds_Log::debug(
						'Set the image source URL from custom tag for created post.',
						array(
							'job_id'           => $job->ID,
							'feed_img_tag'     => $feed_img_tag,
							'image_source_url' => $image_source_url,
							'item_custom'      => $value,
							'is_business'      => $this->feedzy_is_business(),
							'is_personal'      => $this->feedzy_is_personal(),
						)
					);
				} elseif ( wp_http_validate_url( $import_featured_img ) ) {
					$image_source_url = $import_featured_img;
					$img_title        = pathinfo( basename( $image_source_url ), PATHINFO_FILENAME );
				}

				// Fetch image from graby.
				if ( empty( $image_source_url ) && ( wp_doing_cron() && defined( 'FEEDZY_PRO_FETCH_ITEM_IMG_URL' ) ) ) {
					// if license does not exist, use the site url
					// this should obviously never happen unless on dev instances.
					$license = apply_filters( 'product_feedzy_license_key', sprintf( 'n/a - %s', get_site_url() ) );

					$response = wp_remote_post(
						FEEDZY_PRO_FETCH_ITEM_IMG_URL,
						apply_filters(
							'feedzy_fetch_item_image',
							array(
								// phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
								'timeout' => 100,
								'body'    => array_merge(
									array(
										'item_url' => $item['item_url'],
										'license'  => $license,
										'site_url' => get_site_url(),
									)
								),
							)
						)
					);

					Feedzy_Rss_Feeds_Log::debug(
						sprintf( 'Fetching image from Graby for item %1$s with URL %2$s', $item['item_url'], FEEDZY_PRO_FETCH_ITEM_IMG_URL ),
						array(
							'job_id'   => $job->ID,
							'response' => $response,
						)
					);

					if ( ! is_wp_error( $response ) ) {
						$body = wp_remote_retrieve_body( $response );
						if ( ! is_wp_error( $body ) ) {
							$response_data = json_decode( $body, true );
							if ( isset( $response_data['url'] ) ) {
								$image_source_url = $response_data['url'];

								Feedzy_Rss_Feeds_Log::debug(
									sprintf( 'Fetched image from Graby for item %1$s with URL %2$s', $item['item_url'], $image_source_url ),
									array(
										'job_id'           => $job->ID,
										'image_source_url' => $image_source_url,
									)
								);
							}
						}
					} else {
						Feedzy_Rss_Feeds_Log::error(
							// translators: %1$s is the item URL, %2$s is the error message.
							sprintf( __( 'Error fetching image from Graby for item "%1$s": %2$s', 'feedzy-rss-feeds' ), $item['item_url'], $response->get_error_message() ),
							array(
								'job_id'   => $job->ID,
								'response' => $response,
								'item_url' => $item['item_url'],
								'source'   => $source,
							)
						);
					}
				}

				if ( 'yes' === $import_item_img_url || ! $this->try_reuse_existing_featured_image( $img_success, $img_title, $new_post_id ) ) {
					// Run chained actions.
					$import_featured_img = rawurldecode( $import_featured_img );
					$import_featured_img = trim( $import_featured_img );
					$img_action          = $this->get_actions_runner( $import_featured_img, 'item_image' );
					// Item image action process.
					$image_source_url = $img_action->run_action_job( $img_action->get_serialized_actions(), $import_translation_lang, $job, $language_code, $item, $image_source_url );

					if ( ! empty( $image_source_url ) ) {
						if ( 'yes' === $import_item_img_url ) {
							// Set external image URL.
							update_post_meta( $new_post_id, 'feedzy_item_external_url', $image_source_url );

							Feedzy_Rss_Feeds_Log::debug(
								sprintf( 'Replaced image URL with external URL for post ID %1$s: %2$s', $new_post_id, $image_source_url ),
								array(
									'job_id' => $job->ID,
								)
							);
						} else {
							// if import_featured_img is a tag.
							$img_success = $this->try_save_featured_image( $image_source_url, $new_post_id, $img_title, $import_info );

							Feedzy_Rss_Feeds_Log::debug(
								sprintf( 'Saved featured image for post ID %1$s: %2$s', $new_post_id, $image_source_url ),
								array(
									'job_id'           => $job->ID,
									'image_source_url' => $image_source_url,
								)
							);
						}
					}
				}

				// Set default thumbnail image.
				if ( ! $img_success && ! empty( $default_thumbnail ) ) {
					if ( is_array( $default_thumbnail ) ) {
						$random_key           = array_rand( $default_thumbnail );
						$default_thumbnail_id = isset( $default_thumbnail[ $random_key ] ) ? $default_thumbnail[ $random_key ] : 0;
					} else {
						$default_thumbnail_id = $default_thumbnail;
					}
					$img_success = set_post_thumbnail( $new_post_id, $default_thumbnail_id );
					
					Feedzy_Rss_Feeds_Log::debug(
						sprintf( 'Try to set default thumbnail for post ID %1$s with ID %2$s. Success: %3$s', $new_post_id, $default_thumbnail_id, $img_success ? 'yes' : 'no' ),
						array(
							'job_id' => $job->ID,
						)
					);
				}

				if ( ! $img_success ) {
					++$import_image_errors;
				}
			}

			++$index;

			// indicate that this post was imported by feedzy.
			update_post_meta( $new_post_id, 'feedzy', 1 );
			update_post_meta( $new_post_id, 'feedzy_item_url', esc_url_raw( $item['item_url'] ) );
			update_post_meta( $new_post_id, 'feedzy_job', $job->ID );
			update_post_meta( $new_post_id, 'feedzy_item_author', sanitize_text_field( $author ) );

			Feedzy_Rss_Feeds_Log::debug(
				sprintf( 'Update post meta for "%s"', $new_post['post_title'] ),
				array(
					'feedzy_item_url'    => esc_url_raw( $item['item_url'] ),
					'feedzy_item_author' => sanitize_text_field( $author ),
					'feedzy_job'         => $job->ID,
					'post_id'            => $new_post_id,
				)
			);

			// Verify that the `$mark_duplicate_key` does not match `'item_url'` to ensure the condition applies only when a different tag is specified.
			if ( $mark_duplicate_key && 'item_url' !== $mark_duplicate_key ) {
				update_post_meta( $new_post_id, 'feedzy_' . $mark_duplicate_key, $duplicate_tag_value );

				Feedzy_Rss_Feeds_Log::debug(
					sprintf( 'Mark post (%s) as duplicated', $new_post_id ),
					array(
						'feedzy_' . $mark_duplicate_key => $duplicate_tag_value,
					)
				);
			}

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
			Feedzy_Rss_Feeds_Log::error(
				sprintf(
					// translators: %1$d is the number of items without images, %2$d is the total number of items imported.
					__( 'Unable to find an image for %1$d out of %2$d items imported', 'feedzy-rss-feeds' ),
					$import_image_errors,
					$count
				),
				array(
					'job_id' => $job->ID,
					'source' => $source,
				)
			);
		}
		
		// the order of these matters in how they are finally shown in the summary.
		$import_info['total']      = $items_found;
		$import_info['duplicates'] = $duplicates;

		$import_errors = Feedzy_Rss_Feeds_Log::get_instance()->get_error_messages_accumulator();
		Feedzy_Rss_Feeds_Log::get_instance()->disable_error_messages_retention();
		
		update_post_meta( $job->ID, 'import_info', $import_info );
		update_post_meta( $job->ID, 'import_errors', $import_errors );

		Feedzy_Rss_Feeds_Log::info(
			sprintf(
				'Import completed for job ID (%1$s).',
				$job->ID
			),
			array(
				'job_id'        => $job->ID,
				'import_info'   => $import_info,
				'import_errors' => $import_errors,
			)
		);
		
		return $count;
	}

	/**
	 * Method to return feed items to use on cron job.
	 *
	 * @param array  $options The options for the job.
	 * @param string $import_content The import content (along with the magic tags).
	 * @param bool   $raw_feed_also Whether to return the raw SimplePie object as well.
	 *
	 * @return mixed
	 * @since   1.2.0
	 * @access  public
	 */
	public function get_job_feed( $options, $import_content = null, $raw_feed_also = false ) {
		$admin = Feedzy_Rss_Feeds::instance()->get_admin();
		if ( ! method_exists( $admin, 'normalize_urls' ) ) {
			return array();
		}
		$feed_url    = $admin->normalize_urls( $options['feeds'] );
		$source_type = get_post_meta( $options['__jobID'], '__feedzy_source_type', true );

		if ( 'amazon' === $source_type ) {
			$feed = $admin->init_amazon_api(
				$feed_url,
				isset( $options['refresh'] ) ? $options['refresh'] : '12_hours',
				array(
					'number_of_item' => $options['max'],
					'no-cache'       => false,
				)
			);
			if ( ! empty( $feed->get_errors() ) ) {
				return array();
			}
		} else {
			$feed_url = apply_filters( 'feedzy_import_feed_url', $feed_url, $import_content, $options );
			if ( is_wp_error( $feed_url ) ) {
				return $feed_url;
			}
			$feed = $admin->fetch_feed( $feed_url, isset( $options['refresh'] ) ? $options['refresh'] : '12_hours', $options );

			$feed->force_feed( true );
			$feed->enable_order_by_date( isset( $options['sort'] ) && ! empty( $options['sort'] ) );

			if ( is_string( $feed ) ) {
				return array();
			}
		}
		$sizes      = array(
			'width'  => $options['size'],
			'height' => $options['size'],
		);
		$sizes      = apply_filters( 'feedzy_thumb_sizes', $sizes, $feed_url );
		$feed_items = apply_filters( 'feedzy_get_feed_array', array(), $options, $feed, $feed_url, $sizes );
		if ( $raw_feed_also ) {
			return array(
				'items' => $feed_items,
				'feed'  => $feed,
			);
		}

		return $feed_items;
	}

	/**
	 * Reuses an existing featured image if possible.
	 *
	 * @param int|bool $result The result of the operation. It can be a boolean or an attachment ID.
	 * @param string   $title_feed The title of the feed.
	 * @param int      $post_id The post ID.
	 *
	 * @return bool
	 */
	public function try_reuse_existing_featured_image( &$result, $title_feed, $post_id = 0 ) {
		if ( ! function_exists( 'post_exists' ) ) {
			require_once ABSPATH . 'wp-admin/includes/post.php';
		}
		// Find existing attachment by feed title.
		$attachment_id = post_exists( $title_feed, '', '', 'attachment' );

		if ( ! $attachment_id ) {
			return false;
		}

		$result = set_post_thumbnail( $post_id, $attachment_id );
		return true;
	}

	/**
	 * Will retireve the file type of a file by its URL.
	 *
	 * @param string $url The URL of the file.
	 *
	 * @return string
	 */
	private function get_file_type_by_url( $url ) {
		$response = array();
		if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
			$response = vip_safe_wp_remote_get( $url );
		} else {
			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
			$response = wp_remote_get( $url );
		}

		// wp_remote_retrieve_header can return an array if there are multiple headers with the same name.
		$content_type = wp_remote_retrieve_header( $response, 'content-type' );
		if ( is_array( $content_type ) ) {
			$content_type = $content_type[0];
		}

		return $content_type;
	}

	/**
	 * Will escape the provided URL and convert it to ASCII.
	 *
	 * @param string $url The URL to convert.
	 *
	 * @return string
	 */
	private function convert_url_to_ascii( $url ) {
		$parts = wp_parse_url( $url );
		if ( empty( $parts ) ) {
			return esc_url( $url );
		}

		$scheme = '';
		if ( isset( $parts['scheme'] ) ) {
			$scheme = $parts['scheme'] . '://';
		}

		$host = '';
		if ( isset( $parts['scheme'] ) ) {
			$host = idn_to_ascii( $parts['host'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46 );
		}

		$url = $scheme . $host;
		if ( isset( $parts['port'] ) ) {
			$url .= ':' . $parts['port'];
		}
		if ( isset( $parts['path'] ) ) {
			$ascii_path = '';
			$path       = $parts['path'];
			$len        = strlen( $path );
			for ( $i = 0; $i < $len; $i++ ) {
				if ( preg_match( '/^[A-Za-z0-9\/?=+%_.~-]$/', $path[ $i ] ) ) {
					$ascii_path .= $path[ $i ];
				} else {
					$ascii_path .= rawurlencode( $path[ $i ] );
				}
			}
			$url .= $ascii_path;
		}
		if ( isset( $parts['query'] ) ) {
			$url .= '?' . $parts['query'];
		}
		if ( isset( $parts['fragment'] ) ) {
			$url .= '#' . $parts['fragment'];
		}
		return esc_url( $url );
	}

	/**
	 * Downloads and sets a post featured image if possible.
	 *
	 * @param string  $img_source_url The download source URL for the image.
	 * @param integer $post_id The post ID.
	 * @param string  $post_title Post title.
	 * @param array   $import_info Array of import information messages.
	 * @param array   $post_data Additional post data.
	 * 
	 * @return bool|int Return the attachment ID if the image was successfully attached to the post, false otherwise.
	 * 
	 * @since 1.2.0
	 * @access private
	 */
	private function try_save_featured_image( $img_source_url, $post_id, $post_title, &$import_info, $post_data = array() ) {
		if ( ! function_exists( 'post_exists' ) ) {
			require_once ABSPATH . 'wp-admin/includes/post.php';
		}
		// Find existing attachment by item title.
		$id = post_exists( $post_title, '', '', 'attachment' );

		if ( ! $id ) {

			// We escape the URL to ensure that valid URLs are passed by the filter. We also convert the URL parts to ASCII.
			// This is necessary because FILTER_VALIDATE_URL only validates against ASCII URLs.
			$escaped_url = $this->convert_url_to_ascii( $img_source_url );
			if ( filter_var( $escaped_url, FILTER_VALIDATE_URL ) === false ) {
				Feedzy_Rss_Feeds_Log::error(
					// translators: %s is the invalid image URL.
					sprintf( __( 'Invalid image URL: %s', 'feedzy-rss-feeds' ), $img_source_url ),
					array(
						'post_id'    => $post_id,
						'post_title' => $post_title,
					)
				);
				
				return false;
			}

			Feedzy_Rss_Feeds_Log::debug(
				sprintf( 'Save the image as featured image with upload in Media Library for post ID' ),
				array(
					'post_id'        => $post_id,
					'post_title'     => $post_title,
					'img_source_url' => $img_source_url,
					'escaped_url'    => $escaped_url,
					'post_data'      => $post_data,
				)
			);

			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';

			$file_array     = array();
			$img_source_url = trim( $img_source_url, chr( 0xC2 ) . chr( 0xA0 ) );
			$local_file     = download_url( $img_source_url );
			if ( is_wp_error( $local_file ) ) {
				Feedzy_Rss_Feeds_Log::error(
					// translators: %s is the image source URL.
					sprintf( __( 'Unable to download image: %s', 'feedzy-rss-feeds' ), $img_source_url ),
					array(
						'post_id' => $post_id,
						'errors'  => $local_file->get_error_messages(),
					)
				);

				return false;
			}

			$type = '';
			// try first to get the file type using the built-in function if available.
			if ( function_exists( 'mime_content_type' ) ) {
				$type = mime_content_type( $local_file );
			}

			// if the file type is not found, try to get it from the URL.
			if ( empty( $type ) ) {
				$type = $this->get_file_type_by_url( $img_source_url );
			}

			// the file is downloaded with a .tmp extension
			// if the URL mentions the extension of the file, the upload succeeds
			// but if the URL is like https://source.unsplash.com/random, then the upload fails
			// so let's determine the file's mime type and then rename the .tmp file with that extension.
			if ( in_array( $type, array_values( get_allowed_mime_types() ), true ) ) {
				$new_local_file = str_replace( '.tmp', str_replace( 'image/', '.', $type ), $local_file );

				// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_rename
				$renamed = rename( $local_file, $new_local_file );
				if ( $renamed ) {
					$local_file = $new_local_file;
				} else {

					Feedzy_Rss_Feeds_Log::error(
						// translators: %s the name of the post.
						sprintf( __( 'Could not rename temporary file for: %s', 'feedzy-rss-feeds' ), get_the_title( $post_id ) ),
						array(
							'post_id'        => $post_id,
							'local_file'     => $local_file,
							'new_local_file' => $new_local_file,
						)
					);

					return false;
				}
			}

			$file_array['tmp_name'] = $local_file;
			$file_array['name']     = basename( $local_file );

			$id = media_handle_sideload( $file_array, $post_id, $post_title, $post_data );
			if ( is_wp_error( $id ) ) {
				Feedzy_Rss_Feeds_Log::error(
					// translators: %s is the image source URL.
					sprintf( __( 'Cannot upload the image to Media Library: %s', 'feedzy-rss-feeds' ), $img_source_url ),
					array(
						'post_id' => $post_id,
						'errors'  => $id->get_error_messages(),
					)
				);

				// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
				unlink( $file_array['tmp_name'] );

				return false;
			}
		} else {
			Feedzy_Rss_Feeds_Log::debug(
				sprintf( 'Reusing existing attachment image for post ID %d', $post_id ),
				array(
					'post_id'        => $post_id,
					'img_source_url' => $img_source_url,
					'attachment_id'  => $id,
				)
			);
		}

		if ( ! empty( $post_data ) ) {
			return $id;
		}

		$success = set_post_thumbnail( $post_id, $id );
		if ( false === $success ) {
			Feedzy_Rss_Feeds_Log::error(
				// translators: %s is the post title.
				sprintf( __( 'Could not set the thumbnail for: %s', 'feedzy-rss-feeds' ), get_the_title( $post_id ) ),
				array(
					'post_id'          => $post_id,
					'attachment_id'    => $id,
					'image_source_url' => $img_source_url,
				)
			);
		} else {
			Feedzy_Rss_Feeds_Log::debug(
				sprintf( 'Attached image with ID (%d) to post ID (%d)', $id, $post_id ),
				array(
					'post_id'          => $post_id,
					'attachment_id'    => $id,
					'image_source_url' => $img_source_url,
				)
			);
		}

		return $success;
	}

	/**
	 * Registers a cron schedule.
	 * 
	 * @return void
	 *
	 * @since   1.2.0
	 * @access  public
	 */
	public function add_cron() {
		$schedule = ! empty( $this->free_settings['general']['fz_cron_schedule'] ) ? $this->free_settings['general']['fz_cron_schedule'] : ( feedzy_is_legacyv5() ? 'hourly' : 'daily' );

		if (
			( isset( $_POST['nonce'] ) && isset( $_POST['tab'] ) ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), sanitize_text_field( $_POST['tab'] ) )
		) {
			if ( ! empty( $_POST['fz_cron_schedule'] ) ) {
				$schedule = sanitize_text_field( wp_unslash( $_POST['fz_cron_schedule'] ) );
				Feedzy_Rss_Feeds_Util_Scheduler::clear_scheduled_hook( 'feedzy_cron' );
			}
		}

		if ( false === Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'feedzy_cron' ) ) {
			Feedzy_Rss_Feeds_Util_Scheduler::schedule_event( time() + 10, $schedule, 'feedzy_cron' );
		}

		// Register import jobs based cron jobs.
		$import_job_crons = get_posts(
			array(
				'post_type'   => 'feedzy_imports',
				'post_status' => 'publish',
				'numberposts' => 99,
				'fields'      => 'ids',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'  => array(
					'relation' => 'AND',
					array(
						'key'     => 'fz_cron_schedule',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		
		if ( ! empty( $import_job_crons ) ) {
			Feedzy_Rss_Feeds_Log::debug(
				sprintf( 'Registering cron job with schedule: %s', $schedule ),
				array(
					'job_id'           => 0,
					'schedule'         => $schedule,
					'import_job_crons' => $import_job_crons,
				)
			);

			foreach ( $import_job_crons as $job_id ) {
				$fz_cron_schedule = get_post_meta( $job_id, 'fz_cron_schedule', true );
				if ( false === Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'feedzy_cron', array( 100, $job_id ) ) ) {
					Feedzy_Rss_Feeds_Util_Scheduler::schedule_event( time() + 10, $fz_cron_schedule, 'feedzy_cron', array( 100, $job_id ) );
				}
			}
		}
	}


	/**
	 * Checks if WP Cron is enabled and if not, shows a notice.
	 *
	 * @return void
	 *
	 * @access  public
	 */
	public function admin_notices() {
		$screen  = get_current_screen();
		$allowed = array( 'edit-feedzy_categories', 'edit-feedzy_imports', 'feedzy-rss_page_feedzy-settings', 'feedzy-rss_page_feedzy-integration' );
		// only show in the feedzy screens.
		if ( ! in_array( $screen->id, $allowed, true ) ) {
			return;
		}

		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			echo wp_kses_post( '<div class="notice notice-error fz-notice feedzy-error-critical is-dismissible"><p>' . __( 'WP Cron is disabled. Your feeds would not get updated. Please contact your hosting provider or system administrator', 'feedzy-rss-feeds' ) . '</p></div>' );
		}

		if ( false === Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'feedzy_cron' ) ) {
			echo wp_kses_post( '<div class="notice notice-error fz-notice"><p>' . __( 'Unable to register cron job. Your feeds might not get updated', 'feedzy-rss-feeds' ) . '</p></div>' );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['imported'] ) ) {
			echo wp_kses_post( '<div class="notice notice-success fz-notice is-dismissible"><p>' . __( 'Successfully imported.', 'feedzy-rss-feeds' ) . '</p></div>' );
		}
	}

	/**
	 * Method to return license status.
	 * Used to filter PRO version types.
	 *
	 * @return bool
	 * 
	 * @since   1.2.0
	 * @access  public
	 */
	public function feedzy_is_business() {
		return $this->feedzy_is_license_of_type( false, 'business' );
	}

	/**
	 * Method to return if licence is agency.
	 *
	 * @return bool
	 * 
	 * @since   1.3.2
	 * @access  public
	 */
	public function feedzy_is_agency() {
		return $this->feedzy_is_license_of_type( false, 'agency' );
	}

	/**
	 * Method to return if licence is personal.
	 *
	 * @return bool
	 * 
	 * @since   1.8.2
	 * @access  public
	 */
	public function feedzy_is_personal() {
		return $this->feedzy_is_license_of_type( false, 'pro' );
	}

	/**
	 * Method to return the type of licence.
	 * 
	 * @param boolean $is_of_type The default value for checking.
	 * @param string  $type The type of the license.
	 *
	 * @return bool
	 * 
	 * @access  public
	 */
	public function feedzy_is_license_of_type( $is_of_type, $type ) {
		// proceed to check the plan only if the license is active.
		if ( ! ( defined( 'TI_UNIT_TESTING' ) || defined( 'TI_CYPRESS_TESTING' ) ) ) {
			$status = apply_filters( 'feedzy_rss_feeds_pro_license_status', false );
			if ( 'valid' !== $status ) {
				return $is_of_type;
			}
		}
		$plan = apply_filters( 'product_feedzy_license_plan', 0 );
		$plan = intval( $plan );
		switch ( $type ) {
			case 'agency':
				return in_array( $plan, array( 3, 6, 7 ), true );
			case 'business':
				return in_array( $plan, array( 2, 3, 5, 6, 7, 8 ), true );
			case 'pro':
				return ( $plan > 0 );
		}

		return $is_of_type;
	}

	/**
	 * Method for updating settings page via AJAX.
	 * 
	 * @return void
	 *
	 * @since   1.3.2
	 * @access  public
	 */
	public function update_settings_page() {
		$this->save_settings();
		wp_die();
	}

	/**
	 * Display settings fields for the tab.
	 * 
	 * @param array<mixed> $fields The fields.
	 * @param string       $tab the Tab to render the settings.
	 * 
	 * @return array<mixed> The fields.
	 *
	 * @since   1.3.2
	 * @access  public
	 */
	public function display_tab_settings( $fields, $tab ) {
		$this->free_settings = get_option( 'feedzy-settings', array() );

		$fields[] = array(
			'content' => $this->render_view( $tab ),
			'ajax'    => false,
		);

		return $fields;
	}

	/**
	 * Method to save settings.
	 * 
	 * @return void
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
	 * @param array<string, string> $tabs The tabs.
	 * 
	 * @return array<string, string> The tabs with `misc` tab.
	 *
	 * @since   1.3.2
	 * @access  public
	 */
	public function settings_tabs( $tabs ) {
		$tabs['misc'] = __( 'Miscellaneous', 'feedzy-rss-feeds' );

		return $tabs;
	}

	/**
	 * Add integration tab.
	 * 
	 * @param array<string, string> $tabs The integration tabs.
	 * 
	 * @return array<string, string> The tabs with the Pro features if any,
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function integration_tabs( $tabs ) {
		if ( $this->feedzy_is_business() || $this->feedzy_is_agency() ) {
			$tabs['openai']     = __( 'OpenAI', 'feedzy-rss-feeds' );
			$tabs['openrouter'] = __( 'OpenRouter', 'feedzy-rss-feeds' );
		}
		if ( ! feedzy_is_pro() || ! apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ) {
			$tabs['openai'] = sprintf( '%s <span class="pro-label">PRO</span>', __( 'OpenAI', 'feedzy-rss-feeds' ) );
			if ( ! isset( $tabs['openrouter'] ) ) {
				$tabs['openrouter'] = sprintf( '%s <span class="pro-label">PRO</span>', __( 'OpenRouter', 'feedzy-rss-feeds' ) );
			}
			$tabs['spinnerchief']               = sprintf( '%s <span class="pro-label">PRO</span>', __( 'SpinnerChief', 'feedzy-rss-feeds' ) );
			$tabs['amazon-product-advertising'] = sprintf( '%s <span class="pro-label">PRO</span>', __( 'Amazon Product Advertising', 'feedzy-rss-feeds' ) );
			$tabs['wordai']                     = sprintf( '%s <span class="pro-label">PRO</span>', __( 'WordAi', 'feedzy-rss-feeds' ) );
		}

		return $tabs;
	}

	/**
	 * Save settings for the tab.
	 * 
	 * @param array<string, mixed> $settings The tab settings.
	 * @param string               $tab The tab.
	 * 
	 * @return array<string, mixed> The saved settings.
	 *
	 * @access  public
	 */
	public function save_tab_settings( $settings, $tab ) {
		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), $tab )
		) {
			return array();
		}

		if ( 'misc' === $tab ) {
			$settings['canonical']                      = isset( $_POST['canonical'] ) ? absint( $_POST['canonical'] ) : 0;
			$settings['general']['rss-feeds']           = isset( $_POST['rss-feeds'] ) ? absint( $_POST['rss-feeds'] ) : '';
			$settings['header']['user-agent']           = isset( $_POST['user-agent'] ) ? sanitize_text_field( wp_unslash( $_POST['user-agent'] ) ) : '';
			$settings['general']['feedzy-delete-days']  = isset( $_POST['feedzy-delete-days'] ) ? absint( wp_unslash( $_POST['feedzy-delete-days'] ) ) : '';
			$settings['general']['feedzy-delete-media'] = isset( $_POST['feedzy-delete-media'] ) ? absint( wp_unslash( $_POST['feedzy-delete-media'] ) ) : '';
		}

		return $settings;
	}

	/**
	 * Render a view page.
	 *
	 * @param string $name The name of the view.
	 *
	 * @return string The HTML output.
	 * 
	 * @since   1.3.2
	 * @access  public
	 */
	private function render_view( $name ) {
		$file = null;
		switch ( $name ) {
			case 'misc':
				$file = FEEDZY_ABSPATH . '/includes/views/' . $name . '-view.php';
				break;
			case 'wordai':
			case 'spinnerchief':
			case 'amazon-product-advertising':
			case 'openai':
			case 'openrouter':
				if ( ! feedzy_is_pro() || ! apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ) {
					$file = FEEDZY_ABSPATH . '/includes/views/' . $name . '-view.php';
				} else {
					$file = apply_filters( 'feedzy_render_view', $file, $name );
				}
				break;
			default:
				$file = apply_filters( 'feedzy_render_view', $file, $name );
				break;
		}

		if ( ! $file ) {
			return '';
		}

		ob_start();
		include $file;

		return ob_get_clean();
	}

	/**
	 * Renders the HTML for the tags.
	 * 
	 * @param string               $default_tags The default tags HTML rendering.
	 * @param array<string, mixed> $tags The tags.
	 * @param string               $type The type.
	 * 
	 * @return string The tags component rendering.
	 *
	 * @since   1.4.2
	 * @access  public
	 */
	public function render_magic_tags( $default_tags, $tags, $type ) {
		if ( empty( $tags ) ) {
			return $default_tags;
		}

		$disabled = array();
		foreach ( $tags as $tag => $label ) {
			if ( strpos( $tag, ':disabled' ) !== false ) {
				$disabled[ str_replace( ':disabled', '', $tag ) ] = $label;
				continue;
			}
			if ( in_array( $type, array( 'import_post_content', 'import_post_featured_img', 'import_post_title' ), true ) ) {
				if ( in_array( $tag, array( 'item_content', 'item_description', 'item_full_content', 'item_categories', 'item_image', 'item_title' ), true ) ) {
					$default_tags .= '<a class="dropdown-item" href="#" data-field-name="' . $type . '" data-field-tag="' . $tag . '" data-action_popup="' . $tag . '">' . $label . ' <small>[#' . $tag . ']</small></a>';
					continue;
				}
				$default_tags .= '<a class="dropdown-item" href="#" data-field-name="' . $type . '" data-field-tag="' . $tag . '">' . $label . ' <small>[#' . $tag . ']</small></a>';
				continue;
			}
			$default_tags .= '<a class="dropdown-item" href="#" data-field-name="' . $type . '" data-field-tag="' . $tag . '">' . $label . ' -- <small>[#' . $tag . ']</small></a>';
		}

		if ( $disabled ) {
			foreach ( $disabled as $tag => $label ) {
				$default_tags .= '<span disabled title="' . __( 'Upgrade your license to use this tag', 'feedzy-rss-feeds' ) . '" class="dropdown-item">' . $label . ' -- <small>[#' . $tag . ']</small></span>';
			}
		}

		return $default_tags;
	}

	/**
	 * Renders the tags for the title.
	 *
	 * @param array<string, mixed> $default_value The default tags, empty.
	 * 
	 * @return array<string, mixed> The default tags.
	 * 
	 * @since   1.4.2
	 * @access  public
	 */
	public function magic_tags_title( $default_value ) {
		$default_value['item_title']      = __( 'Item Title', 'feedzy-rss-feeds' );
		$default_value['item_author']     = __( 'Item Author', 'feedzy-rss-feeds' );
		$default_value['item_date']       = __( 'Item Date (UTC/GMT)', 'feedzy-rss-feeds' );
		$default_value['item_date_local'] = __( 'Item Date (local timezone)', 'feedzy-rss-feeds' );
		$default_value['item_date_feed']  = __( 'Item Date (feed timezone)', 'feedzy-rss-feeds' );
		$default_value['item_source']     = __( 'Item Source', 'feedzy-rss-feeds' );

		return $default_value;
	}

	/**
	 * Renders the tags for the date.
	 *
	 * @param array<string, mixed> $default_value The default tags, empty.
	 * 
	 * @return array<string, mixed> The default value for the tags.
	 *
	 * @since   1.4.2
	 * @access  public
	 */
	public function magic_tags_date( $default_value ) {
		$default_value['item_date'] = __( 'Item Date', 'feedzy-rss-feeds' );
		$default_value['post_date'] = __( 'Post Date', 'feedzy-rss-feeds' );

		return $default_value;
	}

	/**
	 * Renders the tags for the content.
	 *
	 * @param array<string, mixed> $default_value The default tags, empty.
	 * 
	 * @return array<string, mixed> The default value for the tags.
	 *
	 * @since   1.4.2
	 * @access  public
	 */
	public function magic_tags_content( $default_value ) {
		$default_value['item_content']     = __( 'Item Content', 'feedzy-rss-feeds' );
		$default_value['item_description'] = __( 'Item Description', 'feedzy-rss-feeds' );
		$default_value['item_image']       = __( 'Item Image', 'feedzy-rss-feeds' );
		$default_value['item_url']         = __( 'Item URL', 'feedzy-rss-feeds' );
		$default_value['item_categories']  = __( 'Item Categories', 'feedzy-rss-feeds' );
		$default_value['item_source']      = __( 'Item Source', 'feedzy-rss-feeds' );

		// disabled tags.
		if ( ! feedzy_is_pro() ) {
			$default_value['item_full_content:disabled'] = __( '🚫 Item Full Content', 'feedzy-rss-feeds' );
		}

		return $default_value;
	}

	/**
	 * Renders the tags for the featured image.
	 *
	 * @param array $default_value The default tags, empty.
	 *
	 * @since   1.4.2
	 * @access  public
	 */
	public function magic_tags_image( $default_value ) {
		$default_value['item_image'] = __( 'Item Image', 'feedzy-rss-feeds' );

		return $default_value;
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
	 * @param string $canonical_url The canonical URL.
	 * 
	 * @return string The canonical URL.
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
			$url = get_post_meta( $post->ID, 'feedzy_item_url', true );
			if ( ! empty( $url ) ) {
				$canonical_url = $url;
			}
		}

		return $canonical_url;
	}

	/**
	 * Add/remove row actions for each import.
	 *
	 * @param array<string, mixed> $actions The actions.
	 * @param \WP_Post             $post The post.
	 * 
	 * @return array<string, mixed> The modified actions.
	 * 
	 * @access  public
	 */
	public function add_import_actions( $actions, $post ) {
		if ( 'feedzy_imports' === $post->post_type ) {
			// don't need quick edit.
			unset( $actions['inline hide-if-no-js'] );

			$actions['feedzy_purge'] = sprintf(
				'<a href="#" class="feedzy-purge" data-id="%d">%2$s</a>',
				$post->ID,
				esc_html( __( 'Purge &amp; Reset', 'feedzy-rss-feeds' ) )
			);

			if ( feedzy_is_pro() ) {
				$actions['feedzy_clone'] =
					sprintf(
						'<a href="%1$s" class="feedzy-clone" >%2$s</a>',
						wp_nonce_url(
							add_query_arg(
								array(
									'action'        => 'feedzy_clone_import_job',
									'feedzy_job_id' => $post->ID,
								),
								'admin.php'
							),
							FEEDZY_BASENAME,
							'clone_import'
						),
						esc_html( __( 'Clone', 'feedzy-rss-feeds' ) )
					);
			} else {
				$actions['feedzy_clone_no'] =
					sprintf(
						'<a href="#" class="feedzy-quick-link feedzy-clone-disabled" >%1$s<span class="dashicons dashicons-lock"></span></a>',
						esc_html( __( 'Clone', 'feedzy-rss-feeds' ) )
					);
				if ( ! feedzy_is_legacyv5() || feedzy_is_pro( false ) ) {
					static $is_not_first = false;
					if ( $is_not_first ) {
						$actions['edit'] = sprintf(
							'<a href="#" class="feedzy-quick-link  feedzy-edit-disabled" >%1$s<span class="dashicons dashicons-lock"></span></a>',
							esc_html( __( 'Edit', 'feedzy-rss-feeds' ) )
						);
					}
					$is_not_first = true;
				}
			}
			// Export action.
			$export_action = sprintf(
				'<a href="%s" class="%s"style="%s">%s</a>',
				feedzy_is_pro() ? esc_url(
					add_query_arg(
						array(
							'action'   => 'fz_export_job',
							'_wpnonce' => wp_create_nonce( 'fz_export_job' ),
							'id'       => $post->ID,
						),
						admin_url( 'admin.php' )
					)
				) : '#',
				feedzy_is_pro() ? 'fz-export-btn' : 'fz-export-btn-pro',
				! feedzy_is_pro() ? 'opacity:0.5;' : '',
				esc_html__( 'Export', 'feedzy-rss-feeds' ) . ( ! feedzy_is_pro() ? '<span style="font-size: 13px;line-height: 1.5em;width: 13px;height: 13px;" class="dashicons dashicons-lock"></span>' : '' )
			);

			$actions['export'] = $export_action;
		} elseif ( 1 === intval( get_post_meta( $post->ID, 'feedzy', true ) ) ) {
			// show an unclickable action that mentions that it is imported by us so that users are aware.
			$feedzy_job_id     = get_post_meta( $post->ID, 'feedzy_job', true );
			$actions['feedzy'] = sprintf( '(%s %s)', __( 'Imported by Feedzy from', 'feedzy-rss-feeds' ), get_the_title( $feedzy_job_id ) );
		}

		return $actions;
	}

	/**
	 * AJAX called method to purge imported items.
	 *
	 * @since ?
	 * @access private
	 */
	private function purge_data() {
		check_ajax_referer( FEEDZY_BASEFILE, 'security' );

		$id                 = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		$del_imported_posts = filter_input( INPUT_POST, 'del_imported_posts', FILTER_VALIDATE_BOOLEAN );
		$post               = get_post( $id );
		if ( 'feedzy_imports' !== $post->post_type ) {
			wp_die();
		}

		// Delete imported posts.
		if ( $del_imported_posts ) {
			$post_types    = get_post_meta( $id, 'import_post_type', true );
			$imported_post = get_posts(
				array(
					'post_type'      => $post_types,
					'post_status'    => 'any',
					'fields'         => 'ids',
					'meta_key'       => 'feedzy_job', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'     => $id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					'meta_compare'   => '=',
					'posts_per_page' => 999, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				)
			);
			if ( ! empty( $imported_post ) ) {
				foreach ( $imported_post as $post_id ) {
					wp_delete_post( $post_id, true );
				}
			}
			delete_post_meta( $id, 'import_errors' );
			delete_post_meta( $id, 'import_info' );
			delete_post_meta( $id, 'imported_items' );
			delete_post_meta( $id, 'imported_items_count' );
		}

		delete_post_meta( $id, 'imported_items_hash' );
		delete_post_meta( $id, 'last_run' );
		wp_die();
	}

	/**
	 * Load only those posts that are linked to a particular import job.
	 *
	 * @param \WP_Query $query The query.
	 * 
	 * @return void
	 * 
	 * @access  public
	 */
	public function pre_get_posts( $query ) {

		if (
			! function_exists( 'wp_verify_nonce' ) || ! isset( $_GET['_nonce'] ) ||
			( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_nonce'] ) ), 'job_run_linked_posts' ) )
		) {
			return;
		}

		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$feedzy_job_id   = isset( $_GET['feedzy_job_id'] ) ? absint( $_GET['feedzy_job_id'] ) : '';
		$feedzy_job_time = isset( $_GET['feedzy_job_time'] ) ? absint( $_GET['feedzy_job_time'] ) : '';

		if ( empty( $feedzy_job_id ) ) {
			return;
		}

		$meta_query = array(
			array(
				'key'   => 'feedzy',
				'value' => 1,
			),
			array(
				'key'   => 'feedzy_job',
				'value' => $feedzy_job_id,
			),
		);

		if ( ! empty( $feedzy_job_time ) ) {
			$meta_query[] = array(
				'key'   => 'feedzy_job_time',
				'value' => $feedzy_job_time,
			);
		}

		$query->set( 'meta_query', $meta_query );
	}

	/**
	 * Add iframe to allowed wp_kses_post tags.
	 *
	 * @param array|string $context Allowed tags, attributes, and/or entities.
	 *
	 * @return array
	 */
	public function feedzy_wp_kses_allowed_html( $context ) {
		if ( ! isset( $context['iframe'] ) ) {
			$context['iframe'] = array(
				'src'             => true,
				'height'          => true,
				'width'           => true,
				'frameborder'     => true,
				'allowfullscreen' => true,
				'data-*'          => true,
			);
		}
		if ( isset( $context['span'] ) ) {
			$context['span']['disabled'] = true;
		}

		return $context;
	}

	/**
	 * Get post data using meta key and value.
	 *
	 * @param string $post_type Post type Default post.
	 * @param string $key Meta Key.
	 * @param string $value Meta value.
	 * @param string $compare Compare operator.
	 *
	 * @return mixed
	 */
	public function is_duplicate_post( $post_type = 'post', $key = '', $value = '', $compare = '=' ) {
		if ( empty( $key ) || empty( $value ) ) {
			return false;
		}
		// Check post exists OR Not.
		$data = get_posts(
			array(
				'posts_per_page' => 80,
				'post_type'      => $post_type,
				'meta_key'       => $key, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $value, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'meta_compare'   => $compare,
				'fields'         => 'ids',
			)
		);

		return $data;
	}

	/**
	 * Get post data using meta key and value.
	 *
	 * @param string $post_type Post type Default post.
	 * @param int    $post_id Post ID.
	 * @param string $language_code Selected language code.
	 */
	public function set_wpml_element_language_details( $post_type = 'post', $post_id = 0, $language_code = '' ) {
		global $sitepress;
		if ( $post_id && ! empty( $language_code ) ) {
			// Get the translation id.
			$trid = $sitepress->get_element_trid( $post_id, 'post_' . $post_type );

			// Update the post language info.
			$language_args = array(
				'element_id'           => $post_id,
				'element_type'         => 'post_' . $post_type,
				'trid'                 => $trid,
				'language_code'        => $language_code,
				'source_language_code' => null,
			);

			do_action( 'wpml_set_element_language_details', $language_args );
		}
	}

	/**
	 * Fetch custom field by selected post type.
	 */
	public function fetch_custom_fields() {
		check_ajax_referer( FEEDZY_BASEFILE, 'security' );
		global $wpdb;

		$post_type  = isset( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '';
		$search_key = isset( $_POST['search_key'] ) ? sanitize_text_field( wp_unslash( $_POST['search_key'] ) ) : '';

		$like = '';
		if ( ! empty( $search_key ) ) {
			$like = $wpdb->prepare( " AND $wpdb->postmeta.meta_key LIKE %s", '%' . $search_key . '%' );
		}

		// phpcs:ignore
		$query_result = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT($wpdb->postmeta.meta_key) FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE $wpdb->posts.post_type = '%s' AND $wpdb->postmeta.meta_key != '' AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)' AND $wpdb->postmeta.meta_key NOT RegExp '(^[_feedzy].+$)' AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'$like LIMIT 0, 9999", $post_type), ARRAY_A);

		$acf_fields = array();
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$groups = acf_get_field_groups( array( 'post_type' => $post_type ) );
			if ( ! empty( $groups ) ) {
				foreach ( $groups as $group ) {
					$fields = acf_get_fields( $group['key'] );
					if ( ! empty( $fields ) ) {
						foreach ( $fields as $field ) {
							if ( ! empty( $search_key ) ) {
								if ( stripos( $field['name'], $search_key ) !== false ) {
									$acf_fields[] = $field['name'];
								}
							} else {
								$acf_fields[] = $field['name'];
							}
						}
					}
				}
			}
		}
		$query_result = is_array( $query_result ) ? $query_result : array();
		$query_result = array_column( $query_result, 'meta_key' );
		$query_result = array_merge( $acf_fields, $query_result );
		$query_result = array_unique( $query_result );

		if ( ! empty( $query_result ) ) {
			wp_send_json_success( $query_result );
		} else {
			wp_send_json_error(
				array(
					'not_found_msg' => __( 'No matches found', 'feedzy-rss-feeds' ),
				)
			);
		}
		wp_die();
	}

	/**
	 * Renders the tags for the post excerpt.
	 *
	 * @access  public
	 *
	 * @param array $default_value The default tags, empty.
	 */
	public function magic_tags_post_excerpt( $default_value ) {
		$default_value['item_title']       = __( 'Item Title', 'feedzy-rss-feeds' );
		$default_value['item_content']     = __( 'Item Content', 'feedzy-rss-feeds' );
		$default_value['item_description'] = __( 'Item Description', 'feedzy-rss-feeds' );
		// disabled tags.
		if ( ! feedzy_is_pro() ) {
			$default_value['translated_title:disabled']       = __( '🚫 Translated Title', 'feedzy-rss-feeds' );
			$default_value['translated_content:disabled']     = __( '🚫 Translated Content', 'feedzy-rss-feeds' );
			$default_value['translated_description:disabled'] = __( '🚫 Translated Description', 'feedzy-rss-feeds' );
		}

		return $default_value;
	}

	/**
	 * Check feedzy rewrite content tool enabled or not.
	 *
	 * @return bool
	 */
	private function rewrite_content_service_endabled() {
		// Check license type.
		if ( $this->feedzy_is_business() || $this->feedzy_is_agency() ) {
			return true;
		}

		return false;
	}

	/**
	 * Clone import job.
	 */
	public function feedzy_clone_import_job() {
		// Check if import job ID has been provided and action.
		if ( empty( $_GET['feedzy_job_id'] ) ) {
			wp_die( esc_html__( 'No post to duplicate has been provided!', 'feedzy-rss-feeds' ) );
		}

		// Nonce verification.
		if ( ! isset( $_GET['clone_import'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['clone_import'] ) ), FEEDZY_BASENAME ) ) {
			return;
		}

		// Get the original import job ID.
		$feedzy_job_id = absint( $_GET['feedzy_job_id'] );
		// Get the original import job.
		$feedzy_job = get_post( $feedzy_job_id );

		if ( $feedzy_job ) {
			$current_user    = wp_get_current_user();
			$new_post_author = $current_user->ID;

			$args = array(
				'post_author' => $new_post_author,
				'post_status' => 'draft',
				'post_title'  => $feedzy_job->post_title,
				'post_type'   => $feedzy_job->post_type,
			);

			// insert the new import job by wp_insert_post() function.
			$new_post_id = wp_insert_post( $args );
			// Get all post meta.
			$post_meta = get_post_meta( $feedzy_job_id );
			// Exclude metakey.
			$blacklist_metakey = array(
				'import_errors',
				'import_info',
				'_edit_lock',
				'last_run_id',
				'last_run',
				'imported_items_hash',
				'imported_items_count',
			);

			if ( $post_meta ) {
				foreach ( $post_meta as $meta_key => $meta_values ) {
					if ( in_array( $meta_key, $blacklist_metakey, true ) ) {
						continue;
					}
					foreach ( $meta_values as $meta_value ) {
						add_post_meta( $new_post_id, $meta_key, $meta_value );
					}
				}
			}

			wp_safe_redirect(
				add_query_arg(
					array(
						'post_type' => ( 'post' !== get_post_type( $feedzy_job ) ? get_post_type( $feedzy_job ) : false ),
						'saved'     => 'fz_duplicate_import_job_created',
					),
					admin_url( 'edit.php' )
				)
			);
			exit;
		} else {
			wp_die( esc_html__( 'Post creation failed, could not find original post.', 'feedzy-rss-feeds' ) );
		}
	}

	/**
	 * Display import job clone notice.
	 */
	public function feedzy_import_clone_success_notice() {
		// Get the current screen.
		$screen = get_current_screen();

		if ( 'edit' !== $screen->base ) {
			return;
		}

		// Display success notice if clone succeed.
		if ( isset( $_GET['saved'] ) && 'fz_duplicate_import_job_created' === $_GET['saved'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			?>
			<div class="notice notice-success fz-notice is-dismissible">
				<p><?php esc_html_e( 'Duplicate import job created', 'feedzy-rss-feeds' ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Trim tags.
	 *
	 * @param string $content Field value.
	 *
	 * @return string
	 */
	public function feedzy_import_trim_tags( $content = '' ) {
		if ( ! empty( $content ) && is_string( $content ) ) {
			$content = explode( ',', $content );
			$content = array_map( 'trim', $content );
			$content = implode( ' ', $content );
		}

		return $content;
	}

	/**
	 * Run a wizard import feed.
	 */
	private function wizard_import_feed() {
		check_ajax_referer( FEEDZY_BASEFILE, 'security' );

		$post_type                = ! empty( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '';
		$post_status              = ! empty( $_POST['post_status'] ) ? sanitize_text_field( wp_unslash( $_POST['post_status'] ) ) : '';
		$fallback_image           = ! empty( $_POST['fallback_image'] ) ? sanitize_text_field( wp_unslash( $_POST['fallback_image'] ) ) : '';
		$excluded_post_title      = ! empty( $_POST['excluded_post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['excluded_post_title'] ) ) : ''; 
		$wizard_data              = get_option( 'feedzy_wizard_data', array() );
		$wizard_data              = ! empty( $wizard_data ) ? $wizard_data : array();
		$wizard_data['post_type'] = $post_type;

		$post_title = __( 'Setup Wizard', 'feedzy-rss-feeds' );
		$job_id     = post_exists( $post_title, '', '', 'feedzy_imports' );

		$response = array(
			'status' => 0,
		);

		// Delete previous meta data.
		if ( $job_id ) {
			$meta = get_post_meta( $job_id );
			foreach ( $meta as $key => $value ) {
				delete_post_meta( $job_id, $key );
			}
		}

		// Create new import job.
		if ( ! $job_id ) {
			$job_id = wp_insert_post(
				array(
					'post_title'  => $post_title,
					'post_type'   => 'feedzy_imports',
					'post_status' => 'draft',
				)
			);
			Feedzy_Rss_Feeds_Usage::get_instance()->track_import_creation();
		}

		if ( ! is_wp_error( $job_id ) ) {
			update_post_meta( $job_id, 'source', $wizard_data['feed'] );
			update_post_meta( $job_id, 'import_post_title', '[[{"value":"%5B%7B%22id%22%3A%22%22%2C%22tag%22%3A%22item_title%22%2C%22data%22%3A%7B%7D%7D%5D"}]]' );
			update_post_meta( $job_id, 'import_post_date', '[#item_date]' );
			update_post_meta( $job_id, 'import_post_content', '[[{"value":"%5B%7B%22id%22%3A%22%22%2C%22tag%22%3A%22item_content%22%2C%22data%22%3A%7B%7D%7D%5D"}]]' );
			update_post_meta( $job_id, 'import_post_type', $post_type );
			update_post_meta( $job_id, 'import_post_status', $post_status );
			update_post_meta( $job_id, 'import_post_featured_img', '[#item_image]' );

			// Update wizard data.
			$wizard_data['job_id'] = $job_id;
			update_option( 'feedzy_wizard_data', $wizard_data );

			$filter_conditions = array(
				'match'      => 'all',
				'conditions' => array(),
			);

			if ( ! empty( $excluded_post_title ) ) {
				$filter_conditions['conditions'] = array(
					array(
						'field'    => 'title',
						'operator' => 'not_contains',
						'value'    => $excluded_post_title,
					),
				);
			}

			update_post_meta( $job_id, 'filter_conditions', wp_json_encode( $filter_conditions ) );
			update_post_meta( $job_id, 'default_thumbnail_id', $fallback_image );

			$response = array(
				'status' => true,
			);
		}
		wp_send_json( $response );
	}

	/**
	 * Get the content action runner used for processing the chained actions from the tags.
	 *
	 * @param string $actions Item content actions.
	 * @param string $type Action type.
	 * @return Feedzy_Rss_Feeds_Actions Instance of Feedzy_Rss_Feeds_Actions.
	 */
	public function get_actions_runner( $actions = '', $type = '' ) {
		$action_instance       = Feedzy_Rss_Feeds_Actions::instance();
		$action_instance->type = $type;
		$action_instance->set_raw_serialized_actions( $actions );
		$action_instance->set_settings( $this->settings );
		return $action_instance;
	}

	/**
	 * Clear error logs.
	 */
	private function clear_error_logs() {
		check_ajax_referer( FEEDZY_BASEFILE, 'security' );
		$id = ! empty( $_POST['id'] ) ? (int) $_POST['id'] : 0;
		if ( $id ) {
			delete_post_meta( $id, 'import_errors' );
		}
		wp_send_json(
			array(
				'status' => 1,
			)
		);
	}

	/**
	 * Load edit screen.
	 */
	public function load_edit_screen() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['post_type'] ) || 'feedzy_imports' !== $_GET['post_type'] ) {
			return;
		}
		add_action( 'admin_footer', array( $this, 'add_import_export_section' ) );
	}

	/**
	 * Add import export section.
	 */
	public function add_import_export_section() {
		?>
		<script type="template/text" id="fz_import_field_section">
			<div class="fz-import-field hidden">
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( add_query_arg( array( 'action' => 'fz_import_job' ), admin_url( 'admin.php' ) ) ); ?>">
					<h4> <?php esc_html_e( 'Choose the import job .json file to import.', 'feedzy-rss-feeds' ); ?></h4>
					<?php wp_nonce_field( 'fz_import_job' ); ?>
					<input type="file" accept=".json" name="fz_import" required>
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Import', 'feedzy-rss-feeds' ); ?></button>
				</form>
			</div>
		</script>
		<?php
	}
}
