<?php
/**
 * The Abstract class with reusable functionality of the plugin.
 *
 * @link       https://themeisle.com
 * @since      3.0.0
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abstract
 */

/**
 * The Feedzy RSS functions of the plugin.
 *
 * Abstract class containing functions for the Feedzy Admin Class
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abstract
 * @author     Themeisle <friends@themeisle.com>
 * @abstract
 */
abstract class Feedzy_Rss_Feeds_Admin_Abstract {
	/**
	 * The ID of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * Number of shortcode count.
	 *
	 * @access   protected
	 * @var      int $shortcode_count
	 */
	protected $shortcode_count = 1;

	/**
	 * Defines the default image to use on RSS Feeds
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $default_img The image source, currently not used.
	 *
	 * @return  string
	 */
	public function feedzy_define_default_image( $default_img ) {
		$doing_import_job = false;

		if ( wp_doing_ajax() && ! empty( $_POST['_action'] ) && 'run_now' === $_POST['_action'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			$doing_import_job = true;
		}
		if ( wp_doing_cron() ) {
			$doing_import_job = true;
		}
		if ( ! $doing_import_job && empty( $default_img ) ) {
			$settings = apply_filters( 'feedzy_get_settings', array() );
			if ( $settings && ! empty( $settings['general']['default-thumbnail-id'] ) ) {
				$default_img = wp_get_attachment_image_src( $settings['general']['default-thumbnail-id'], 'full' );
				$default_img = ! empty( $default_img ) ? reset( $default_img ) : '';
			} else {
				$default_img = FEEDZY_ABSURL . 'img/feedzy.svg';
			}
		}

		return apply_filters( 'feedzy_define_default_image_filter', $default_img );
	}

	/**
	 * Fetches the SDK logger data.
	 *
	 * @param array<string, mixed> $data The default data that needs to be sent.
	 * 
	 * @return array<string, mixed> The usage data.
	 *
	 * @access public
	 */
	public function get_usage_data( $data ) {
		// how many categories created.
		$categories = 0;
		$terms      = get_terms( array( 'taxonomy' => 'feedzy_categories' ) );
		if ( is_array( $terms ) ) {
			$categories = count( $terms );
		}
		// imports.
		$license = 'free';

		$imports = array(
			// how many active imports are created.
			'publish'  => count(
				get_posts(
					array(
						'post_type'   => 'feedzy_imports',
						'post_status' => 'publish',
						'numberposts' => 99,
						'fields'      => 'ids',
					)
				)
			),
			// how many draft imports are created.
			'draft'    => count(
				get_posts(
					array(
						'post_type'   => 'feedzy_imports',
						'post_status' => 'draft',
						'numberposts' => 99,
						'fields'      => 'ids',
					)
				)
			),
			// how many posts were imported by the imports.
			'imported' => count(
				get_posts(
					array(
						'post_type'   => 'post',
						'post_status' => array( 'publish', 'private', 'draft', 'trash' ),
						'numberposts' => 2999, //phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
						'fields'      => 'ids',
						'meta_key'    => 'feedzy', //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_value'  => 1, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					)
				)
			),
		);

		if ( feedzy_is_pro() ) {
			$license = 'pro';
			if ( apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) {
				$license = 'agency';
			} elseif ( apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ) {
				$license = 'business';
			}

			$integrations = array();
			$settings     = get_option( 'feedzy-rss-feeds-settings' );
			if ( 'agency' === $license && $settings ) {
				if ( isset( $settings['spinnerchief_licence'] ) && 'yes' === $settings['spinnerchief_licence'] ) {
					$integrations[] = 'spinnerchief';
				}
				if ( isset( $settings['wordai_licence'] ) && 'yes' === $settings['wordai_licence'] ) {
					$integrations[] = 'wordai';
				}
			}

			$imports['integrations'] = $integrations;
		}

		$imports = wp_parse_args( Feedzy_Rss_Feeds_Usage::get_instance()->get_usage_stats(), $imports );
		$imports = apply_filters( 'feedzy_usage_data', $imports );

		// how many posts contain the shortcode.
		global $wpdb;
		$shortcodes = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}posts WHERE post_status IN ('publish', 'private') AND post_content LIKE '%[feedzy-rss %'" ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		$data = array(
			'categories' => $categories,
			'imports'    => $imports,
			'shortcodes' => $shortcodes,
			'license'    => $license,
		);

		$settings = apply_filters( 'feedzy_get_settings', null );

		if ( ! is_array( $settings ) || empty( $settings ) ) {
			return $data;
		}
		$general_settings = array();
		$config           = array();

		$proxy = isset( $settings['proxy'] ) && is_array( $settings['proxy'] ) && ! empty( $settings['proxy'] ) ? array_filter( $settings['proxy'] ) : array();
		if ( ! empty( $proxy ) ) {
			$config[] = 'proxy';
		}

		if ( isset( $settings['header'], $settings['header']['user-agent'] ) && ! empty( $settings['header']['user-agent'] ) ) {
			$config[] = 'custom-user-agent';
		}

		if ( ! empty( $config ) ) {
			$data['config'] = $config;
		}

		if ( is_array( $settings['general'] ) && ! empty( $settings['general'] ) ) {
			foreach ( $settings['general'] as $key => $value ) {
				if ( ! empty( $value ) ) {
					$general_settings[ $key ] = $value;
				}
			}

			if ( ! empty( $general_settings ) ) {
				$data['general'] = $general_settings;
			}
		}

		if ( isset( $settings['custom_schedules'] ) && is_array( $settings['custom_schedules'] ) ) {
			$data['custom_schedules_count'] = count( $settings['custom_schedules'] );
		}

		$logger         = Feedzy_Rss_Feeds_Log::get_instance();
		$data['logger'] = array(
			'can_send_email' => $logger->can_send_email(),
			'has_email'      => ! empty( $logger->get_email_address() ),
			'file_size'      => $logger->get_log_file_size(),
		);

		return $data;
	}

	/**
	 * Defines the default error notice
	 *
	 * Logs error to the log file
	 * Returns the error message
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   object    $errors The error object.
	 * @param   SimplePie $feed The SimplePie object.
	 * @param   string    $feed_url The feed URL.
	 *
	 * @return  string
	 */
	public function feedzy_default_error_notice( $errors, $feed, $feed_url ) {
		global $post;
		// Show the error message only if the user who has created this post (which contains the feed) is logged in and the user has admin privileges.
		// Or if this is in the dry run window.
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		$show_error = ( is_admin() || ( is_user_logged_in() && $post && get_current_user_id() == $post->post_author ) ) && current_user_can( 'manage_options' );
		$error_msg  = '';

		if ( is_array( $errors ) ) {
			foreach ( $errors as $i => $error ) {
				$error_msg .= sprintf( "%s : %s\n", $feed->multifeed_url[ $i ], $error );
			}
		} elseif ( is_string( $errors ) ) {
			$error_msg = $errors;
		}

		$final_msg = '';

		if ( $show_error ) {
			$final_msg = '<div id="message" class="error"><p>' . sprintf(
				// translators: %s: Detailed error message.
				__( 'Sorry, some part of this feed is currently unavailable or does not exist anymore. The detailed error is %s', 'feedzy-rss-feeds' ),
				'<p style="font-weight: bold">' . wp_strip_all_tags( $error_msg ) . '</p>'
			);
			if ( ! is_admin() ) {
				$final_msg .= '<small>(' . __( 'Only you are seeing this detailed error because you are the creator of this post. Other users will see the error message as below.', 'feedzy-rss-feeds' ) . ')</small>';
			}
			$final_msg .= '</p></div>';
		} else {
			error_log( 'Feedzy RSS Feeds - related feed: ' . print_r( $feed_url, true ) . ' - Error message: ' . wp_strip_all_tags( $error_msg ) );
		}
		return wp_kses_post( $final_msg );
	}

	/**
	 * Converts an object to string
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   object $error The error Object.
	 *
	 * @return  string
	 */
	public function feedzy_array_obj_string( $error ) {
		if ( is_array( $error ) || is_object( $error ) ) {
			return print_r( $error, true );
		} else {
			return $error;
		}
	}

	/**
	 * Padding ratio based on image size
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $item_attr The item attribute.
	 * @param   array  $sizes An array with the current sizes.
	 *
	 * @return  string
	 */
	public function feedzy_add_item_padding( $item_attr, $sizes ) {
		$padding_top    = number_format( ( 15 / 150 ) * $sizes['height'], 0 );
		$padding_bottom = number_format( ( 25 / 150 ) * $sizes['height'], 0 );
		$style_padding  = ' style="padding: ' . $padding_top . 'px 0 ' . $padding_bottom . 'px"';

		return $item_attr . $style_padding;
	}

	/**
	 * Appends classes to the feed item
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $item_attr The item attribute.
	 * @param   string $sizes The item sizes.
	 * @param   string $item The feed item.
	 * @param   string $feed_url The feed URL.
	 * @param   string $sc The short code attributes.
	 *
	 * @return  string
	 */
	public function feedzy_classes_item( $item_attr = '', $sizes = '', $item = '', $feed_url = '', $sc = '' ) {
		$classes = array( 'rss_item' );
		$classes = apply_filters( 'feedzy_add_classes_item', $classes, $sc );
		$classes = ' class="' . implode( ' ', $classes ) . '"';

		return $item_attr . $classes;
	}

	/**
	 * Filter feed description input
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $description The feed description.
	 * @param   string $content The feed description.
	 * @param   string $feed_url The feed URL.
	 *
	 * @return  string
	 */
	public function feedzy_summary_input_filter(
		$description,
		// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$content,
		// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$feed_url
	) {
		$description = trim( wp_strip_all_tags( $description ) );
		$description = trim( str_replace( array( '[â€¦]', '[…]', '[&hellip;]' ), '', $description ) );

		return $description;
	}

	/**
	 * Include cover picture (medium) to rss feed enclosure
	 * and media:content
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function feedzy_include_thumbnail_rss() {
		global $post;
		if ( has_post_thumbnail( $post->ID ) ) {
			$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
			$attachment_meta   = wp_get_attachment_metadata( $post_thumbnail_id );
			$image_url         = wp_get_attachment_image_src( $post_thumbnail_id, 'medium' );
			echo '<enclosure url="' . esc_url( $image_url[0] ) . '" length="' . esc_attr( filesize( get_attached_file( $post_thumbnail_id ) ) ) . '" type="image/jpg" />';
			echo '<media:content url="' . esc_url( $image_url[0] ) . '" width="' . esc_attr( $attachment_meta['sizes']['medium']['width'] ) . '" height="' . esc_attr( $attachment_meta['sizes']['medium']['height'] ) . '" medium="image" type="' . esc_attr( $attachment_meta['sizes']['medium']['mime-type'] ) . '" />';

		}
	}

	/**
	 * Utility method to check if source is a URL's string
	 * or if is a post type slug.
	 *
	 * @since   3.0.12
	 * @access  public
	 *
	 * @param   string $src The feeds source string.
	 *
	 * @return bool|string
	 */
	public function process_feed_source( $src ) {
		$regex  = '((https?|ftp)\:\/\/)?';                                      // Contains Protocol.
		$regex .= '([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?';  // Uses User and Pass.
		$regex .= '([a-z0-9-.]*)\.([a-z]{2,3})';                                // Has Host or IP.
		$regex .= '(\:[0-9]{2,5})?';                                            // Uses Port.
		$regex .= '(\/([a-z0-9+\$_-]\.?)+)*\/?';                                // Has Path.
		$regex .= '(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?';                   // Has GET Query.
		$regex .= '(#[a-z_.-][a-z0-9+\$_.-]*)?';                                // Uses Anchor.
		if ( preg_match( "/^$regex$/", $src ) ) {
			// If it matches Regex ( it's not a slug ) so return the sources.
			return $src;
		} else {
			$src  = trim( $src );
			$post = get_page_by_path( $src, OBJECT, 'feedzy_categories' );
			if ( $post ) {
				return trim( preg_replace( '/\s+/', ' ', get_post_meta( $post->ID, 'feedzy_category_feed', true ) ) );
			} else {
				return $src;
			}
		}
	}

	/**
	 * Main shortcode function
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   array  $atts Shortcode attributes.
	 * @param   string $content The item feed content.
	 *
	 * @return  mixed
	 */
	public function feedzy_rss( $atts, $content = '' ) {
		$sc                   = $this->get_short_code_attributes( $atts );
		$remove_default_style = isset( $sc['disable_default_style'] ) && in_array( (string) $sc['disable_default_style'], array( '1', 'y', 'yes' ), true );
		if ( ! $remove_default_style ) {
			$settings = apply_filters( 'feedzy_get_settings', array() );
			if ( ! empty( $settings['general']['disable-default-style'] ) ) {
				$remove_default_style = true;
			}
		}
		// Do not enqueue style if the default style settings are enabled.
		if ( ! $remove_default_style ) {
			wp_print_styles( $this->plugin_name );
			// Enqueue style using `wp_enqueue_style` in case `wp_print_styles` not working.
			if ( ! wp_style_is( $this->plugin_name, 'done' ) ) {
				wp_enqueue_style( $this->plugin_name );
			}
			$sc['disable_default_style'] = 'no';
		} else {
			$sc['disable_default_style'] = 'yes';
		}

		$feed_url = $this->normalize_urls( $sc['feeds'] );
		if ( empty( $feed_url ) ) {
			return $content;
		}
		$cache = $sc['refresh'];

		// Disregard the pseudo-shortcode coming from Gutenberg as a lazy one.
		if ( ( true === $sc['lazy'] || 'yes' === $sc['lazy'] ) && ! isset( $sc['gutenberg'] ) ) {
			$attributes = '';
			foreach ( $sc as $key => $val ) {
				// ignore the feedData, its not required.
				if ( 'feedData' === $key ) {
					continue;
				}
				if ( is_array( $val ) ) {
					$val = implode( ',', $val );
				}
				$attributes .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $val ) . '"';
			}
			$lazyload_cache_key = md5( sprintf( 'feedzy-lazy-%s-%d-%d', ( is_array( $feed_url ) ? implode( ',', $feed_url ) : $feed_url ), ( ! empty( $sc['max'] ) ? $sc['max'] : 1 ), ( ! empty( $sc['offset'] ) ? $sc['offset'] : 0 ) ) );
			$content            = get_transient( $lazyload_cache_key );

			// the first time the shortcode is being called it will not have any content.
			if ( empty( $content ) ) {
				$content = apply_filters( 'feedzy_lazyload_loading_msg', __( 'Loading', 'feedzy-rss-feeds' ) . '...', $feed_url );
			} else {
				$attributes .= 'data-has_valid_cache="true"';
			}
			$class = array_filter( apply_filters( 'feedzy_add_classes_block', array( $sc['classname'], 'feedzy-' . md5( is_array( $feed_url ) ? implode( ',', $feed_url ) : $feed_url ) ), $sc, null, $feed_url ) );
			$html  = "<div class='feedzy-lazy' $attributes>";
			$html .= "$content</div>";

			wp_register_script( $this->plugin_name . '-lazy', FEEDZY_ABSURL . 'js/feedzy-lazy.js', array( 'jquery' ), $this->version, 'all' );
			wp_enqueue_script( $this->plugin_name . '-lazy' );
			wp_localize_script(
				$this->plugin_name . '-lazy',
				'feedzy',
				array(
					'url'        => get_rest_url( null, 'feedzy/v' . FEEDZY_REST_VERSION . '/lazy/' ),
					'rest_nonce' => wp_create_nonce( 'wp_rest' ),
					'nonce'      => wp_create_nonce( 'feedzy' ),
				)
			);
			return $html;
		}

		if ( isset( $sc['_dry_run_tags_'] ) ) {
			if ( strpos( $sc['_dry_run_tags_'], 'item_full_content' ) !== false ) {
				$sc_clone            = $sc;
				$sc_clone['__jobID'] = ''; // pro expects this but keep it empty.
				$tmp_feed_url        = apply_filters( 'feedzy_import_feed_url', $feed_url, '[#item_full_content]', $sc_clone );
				if ( ! is_wp_error( $tmp_feed_url ) ) {
					$feed_url = $tmp_feed_url;
				}
			}
		}

		// If the user is explicitly using filters attribute, then use it and ignore old filters.
		if ( isset( $sc['filters'] ) && ! empty( $sc['filters'] ) && feedzy_is_pro() ) {
			$sc['filters'] = apply_filters( 'feedzy_filter_conditions_attribute', $sc['filters'] );
		} else {
			$sc['filters'] = apply_filters( 'feedzy_filter_conditions_migration', $sc );
		}

		$sc = array_diff_key(
			$sc,
			array(
				'keywords_title'  => '',
				'keywords_inc'    => '',
				'keywords_inc_on' => '',
				'keywords_exc'    => '',
				'keywords_exc_on' => '',
				'keywords_ban'    => '',
				'from_datetime'   => '',
				'to_datetime'     => '',
			)
		);

		$feed = $this->fetch_feed( $feed_url, $cache, $sc );
		if ( is_string( $feed ) ) {
			return $feed;
		}
		$sc      = $this->sanitize_attr( $sc, $feed_url );
		$content = $this->render_content( $sc, $feed, $feed_url, $content );

		return $content;
	}


	/**
	 * Register Rest Route for Feedzy lazy loader.
	 */
	public function rest_route() {
		register_rest_route(
			'feedzy/v' . FEEDZY_REST_VERSION,
			'/lazy/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'feedzy_lazy_load' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'nonce' => array(
						'validate_callback' => function ( $value ) {
							return wp_verify_nonce( $value, 'feedzy' );
						},
						'required'          => true,
					),
				),
			)
		);

		Feedzy_Rss_Feeds_Log::get_instance()->register_endpoints();
	}

	/**
	 * Parse the feed and send it to the front-end to display.
	 *
	 * @since   ?
	 * @access  public
	 *
	 * @param   array $data The attributes passed by the ajax call.
	 */
	public function feedzy_lazy_load( $data ) {
		$atts     = $data['args'];
		$sc       = $this->get_short_code_attributes( $atts );
		$feed_url = $this->normalize_urls( $sc['feeds'] );

		if ( isset( $sc['filters'] ) && ! empty( $sc['filters'] ) && feedzy_is_pro() ) {
			$sc['filters'] = apply_filters( 'feedzy_filter_conditions_attribute', $sc['filters'] );
		} else {
			$sc['filters'] = apply_filters( 'feedzy_filter_conditions_migration', $sc );
		}

		$sc = array_diff_key(
			$sc,
			array(
				'keywords_title'  => '',
				'keywords_inc'    => '',
				'keywords_inc_on' => '',
				'keywords_exc'    => '',
				'keywords_exc_on' => '',
				'keywords_ban'    => '',
				'from_datetime'   => '',
				'to_datetime'     => '',
			)
		);

		$feed = $this->fetch_feed( $feed_url, $sc['refresh'], $sc );
		if ( is_string( $feed ) ) {
			return $feed;
		}
		$sc      = $this->sanitize_attr( $sc, $feed_url );
		$content = $this->render_content( $sc, $feed, $feed_url, '' );

		// save the content as a transient so that whenever the feed is refreshed next, this stale content is displayed first.
		$lazyload_cache_key = md5( sprintf( 'feedzy-lazy-%s-%d-%d', ( is_array( $feed_url ) ? implode( ',', $feed_url ) : $feed_url ), ( ! empty( $sc['max'] ) ? $sc['max'] : 1 ), ( ! empty( $sc['offset'] ) ? $sc['offset'] : 0 ) ) );
		set_transient( $lazyload_cache_key, $content, apply_filters( 'feedzy_lazyload_cache_time', DAY_IN_SECONDS, $feed_url ) );

		wp_send_json_success( array( 'content' => $content ) );
	}

	/**
	 * Returns the attributes of the shortcode
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   array $atts The attributes passed by WordPress.
	 *
	 * @return  array
	 */
	public function get_short_code_attributes( $atts ) {
		// Retrieve & extract shortcode parameters.
		$sc = shortcode_atts(
			array(
				// comma separated feeds url.
				'feeds'                 => '',
				// number of feeds items (0 for unlimited).
				'max'                   => '5',
				// display feed title yes/no.
				'feed_title'            => 'yes',
				// _blank, _self
				'target'                => '_blank',
				// empty or no for nofollow.
				'follow'                => '',
				// strip title after X char. X can be 0 too, which will remove the title.
				'title'                 => '',
				// yes (author, date, time), no (NEITHER), author, date, time, categories
				// tz=local (for date/time in blog time)
				// tz=gmt (for date/time in UTC time, this is the default)
				// tz=no (for date/time in the feed, without conversion).
				'meta'                  => 'yes',
				// yes (all), no (NEITHER)
				// source: show feed title.
				'multiple_meta'         => 'no',
				// strip title.
				'summary'               => 'yes',
				// strip summary after X char.
				'summarylength'         => '',
				// yes, no, auto.
				'thumb'                 => 'auto',
				// default thumb URL if no image found (only if thumb is set to yes or auto).
				'default'               => '',
				// thumbs pixel size.
				'size'                  => '',
				// default aspect ratio for the image.
				'aspectRatio'           => '1',
				// only display item if title contains specific keywords (Use comma(,) and plus(+) keyword).
				'keywords_title'        => '',
				// only display item if title OR content contains specific keywords (Use comma(,) and plus(+) keyword).
				'keywords_inc'          => '',
				// Keyword filter include in specific field( title, description, author ).
				'keywords_inc_on'       => '',
				// Keyword filter exclude in specific field( title, description, author ).
				'keywords_exc_on'       => '',
				// cache refresh.
				'refresh'               => '12_hours',
				// sorting.
				'sort'                  => '',
				// https = force https
				// default = fall back to default image
				// auto = continue as it is.
				'http'                  => 'auto',
				// message to show when feed is empty.
				'error_empty'           => __( 'Feed has no items.', 'feedzy-rss-feeds' ),
				// to disable amp support, use 'no'. This is currently not available as part of the shortcode tinymce form.
				'amp'                   => 'yes',
				// paginate.
				'offset'                => 0,
				// class name of this block.
				'className'             => '',
				// lazy loading of feeds?
				'lazy'                  => 'no',
				// these are only for internal purposes.
				'_dryrun_'              => 'no',
				'_dry_run_tags_'        => '',
				// From datetime.
				'from_datetime'         => '',
				// To datetime.
				'to_datetime'           => '',
				// Disable default style.
				'disable_default_style' => 'no',
				'filters'               => '',
			),
			$atts,
			'feedzy_default'
		);
		if ( ! isset( $sc['classname'] ) ) {
			$sc['classname'] = $sc['className'];
			unset( $sc['className'] );
		}
		$sc = array_merge( $sc, apply_filters( 'feedzy_get_short_code_attributes_filter', $atts ) );

		return $sc;
	}

	/**
	 * Validate feeds attribute.
	 *
	 * @since   3.0.0
	 * @updated 3.1.7   Take into account $feed_url as array from PRO version.
	 *
	 * @param   string $raw Url or list of urls.
	 *
	 * @return string|array<string> Urls of the feeds.
	 */
	public function normalize_urls( $raw ) {
		$feeds    = apply_filters( 'feedzy_process_feed_source', $raw );
		$feed_url = apply_filters( 'feedzy_get_feed_url', $feeds );
		if ( is_array( $feed_url ) ) {
			foreach ( $feed_url as $index => $url ) {
				$feed_url[ $index ] = trim( $this->smart_convert( $url ) );
			}
		} else {
			$feed_url = trim( $this->smart_convert( $feed_url ) );
		}

		return $feed_url;
	}

	/**
	 * Converts the feed URL.
	 *
	 * @param   string $url The feed url.
	 */
	private function smart_convert( $url ) {

		$url = htmlspecialchars_decode( $url );

		// Automatically fix deprecated google news feeds.
		if ( false !== strpos( $url, 'news.google.' ) ) {

			$parts = wp_parse_url( $url );
			parse_str( $parts['query'], $query );

			if ( isset( $query['q'] ) ) {
				$search_query = $query['q'];
				unset( $query['q'] );
				$url = sprintf( 'https://news.google.com/news/rss/search/section/q/%s/%s?%s', $search_query, $search_query, http_build_query( $query ) );

			}
		}

		return apply_filters( 'feedzy_alter_feed_url', $url );
	}

	/**
	 * Fetch the content feed from a group of urls.
	 *
	 * @since   3.0.0
	 * @access  public
	 * @updated 3.2.0
	 *
	 * @param   array  $feed_url The feeds urls to fetch content from.
	 * @param   string $cache The cache string (eg. 1_hour, 30_min etc.).
	 * @param   array  $sc The shortcode attributes.
	 *
	 * @return SimplePie|string|void|WP_Error The feed resource.
	 */
	public function fetch_feed( $feed_url, $cache = '12_hours', $sc = '' ) {
		// Load SimplePie if not already.
		do_action( 'feedzy_pre_http_setup', $feed_url );
		if ( function_exists( 'feedzy_amazon_get_locale_hosts' ) ) {
			$amazon_hosts     = feedzy_amazon_get_locale_hosts();
			$is_amazon_source = false;
			if ( is_array( $feed_url ) ) {
				$url_host         = array_map(
					function ( $url ) {
						return 'webservices.' . wp_parse_url( $url, PHP_URL_HOST );
					},
					$feed_url
				);
				$url_host         = array_diff( $url_host, $amazon_hosts );
				$is_amazon_source = ! empty( $amazon_hosts ) && empty( $url_host );
			} else {
				$url_host         = 'webservices.' . wp_parse_url( $feed_url, PHP_URL_HOST );
				$is_amazon_source = ! empty( $amazon_hosts ) && in_array( $url_host, $amazon_hosts, true );
			}
			if ( $is_amazon_source ) {
				$feed = $this->init_amazon_api(
					$feed_url,
					isset( $sc['refresh'] ) ? $sc['refresh'] : '12_hours',
					array(
						'number_of_item' => isset( $sc['max'] ) ? $sc['max'] : 5,
						'no-cache'       => false,
					)
				);
				return $feed;
			}
		}
		// Load SimplePie Instance.
		$feed = $this->init_feed( $feed_url, $cache, $sc ); // Not used as log as #41304 is Opened.

		// Report error when is an error loading the feed.
		if ( is_wp_error( $feed ) ) {
			// Fallback for different edge cases.
			if ( is_array( $feed_url ) ) {
				$feed_url = array_map( 'html_entity_decode', $feed_url );
			} else {
				$feed_url = html_entity_decode( $feed_url );
			}

			$feed_url = $this->get_valid_source_urls( $feed_url, $cache );

			$feed = $this->init_feed( $feed_url, $cache, $sc ); // Not used as log as #41304 is Opened.

		}

		do_action( 'feedzy_post_http_teardown', $feed_url );

		return $feed;
	}

	/**
	 * Method to avoid using core implementation in order
	 * order to fix issues reported here: https://core.trac.wordpress.org/ticket/41304
	 * Bug: #41304 with WP wp_kses sanitizer used by WP SimplePie implementation.
	 *
	 * NOTE: This is temporary should be removed as soon as #41304 is patched.
	 *
	 * @since   3.1.7
	 * @access  private
	 *
	 * @param   string $feed_url The feed URL.
	 * @param   string $cache The cache string (eg. 1_hour, 30_min etc.).
	 * @param   array  $sc The shortcode attributes.
	 * @param   bool   $allow_https Defaults to constant FEEDZY_ALLOW_HTTPS.
	 *
	 * @return SimplePie
	 */
	private function init_feed( $feed_url, $cache, $sc, $allow_https = FEEDZY_ALLOW_HTTPS ) {
		$unit_defaults = array(
			'mins'  => MINUTE_IN_SECONDS,
			'hours' => HOUR_IN_SECONDS,
			'days'  => DAY_IN_SECONDS,
		);
		$cache_time    = 12 * HOUR_IN_SECONDS;
		$cache         = trim( $cache );
		if ( isset( $cache ) && '' !== $cache ) {
			list( $value, $unit ) = explode( '_', $cache );
			if ( isset( $value ) && is_numeric( $value ) && $value >= 1 && $value <= 100 ) {
				if ( isset( $unit ) && in_array( strtolower( $unit ), array( 'mins', 'hours', 'days' ), true ) ) {
					$cache_time = $value * $unit_defaults[ $unit ];
				}
			}
		}

		$feed = new Feedzy_Rss_Feeds_Util_SimplePie( $sc );
		if ( ! $allow_https && method_exists( $feed, 'set_curl_options' ) ) {
			$feed->set_curl_options(
				array(
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_SSL_VERIFYPEER => false,
				)
			);
		}
		require_once ABSPATH . WPINC . '/class-wp-feed-cache-transient.php';
		require_once ABSPATH . WPINC . '/class-wp-simplepie-file.php';

		$feed->set_file_class( 'WP_SimplePie_File' );
		$default_agent = $this->get_default_user_agent( $feed_url );
		$feed->set_useragent( apply_filters( 'http_headers_useragent', $default_agent ) );
		if ( false === apply_filters( 'feedzy_disable_db_cache', false, $feed_url ) ) {
			SimplePie_Cache::register( 'wp_transient', 'WP_Feed_Cache_Transient' );
			$feed->set_cache_location( 'wp_transient' );
			if ( ! has_filter( 'wp_feed_cache_transient_lifetime' ) ) {
				add_filter(
					'wp_feed_cache_transient_lifetime',
					function ( $time ) use ( $cache_time ) {
						$time = $cache_time;
						return $time;
					},
					10,
					1
				);
			}
			$feed->set_cache_duration( apply_filters( 'wp_feed_cache_transient_lifetime', $cache_time, $feed_url ) );
		} else {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
			global $wp_filesystem;

			$dir = $wp_filesystem->wp_content_dir() . 'uploads/simplepie';
			if ( ! $wp_filesystem->exists( $dir ) ) {
				$done = $wp_filesystem->mkdir( $dir );
				if ( false === $done ) {
					Feedzy_Rss_Feeds_Log::warning(
						sprintf( 'Unable to create SimplePie cache directory: %s', $dir ),
						array(
							'feed_url' => $feed_url,
							'cache'    => $cache,
							'sc'       => $sc,
						)
					);
				}
			}
			$feed->set_cache_location( $dir );
		}

		// Do not use force_feed for multiple URLs.
		$feed->force_feed( apply_filters( 'feedzy_force_feed', ( is_string( $feed_url ) || ( is_array( $feed_url ) && 1 === count( $feed_url ) ) ) ) );

		do_action( 'feedzy_modify_feed_config', $feed );

		$cloned_feed = clone $feed;

		// set the url as the last step, because we need to be able to clone this feed without the url being set
		// so that we can fall back to raw data in case of an error.
		$feed->set_feed_url( $feed_url );

		// Allow unsafe html.
		if ( defined( 'FEEDZY_ALLOW_UNSAFE_HTML' ) && FEEDZY_ALLOW_UNSAFE_HTML ) {
			$feed->strip_htmltags( false );
		}

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			// phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___SERVER__HTTP_USER_AGENT__
			$set_server_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) . SIMPLEPIE_USERAGENT );
			$feed->set_useragent( apply_filters( 'http_headers_useragent', $set_server_agent ) );
		}

		$feed->init();

		if ( ! $feed->get_type() ) {
			return $feed;
		}

		$error = $feed->error();
		// error could be an array, so let's join the different errors.
		if ( is_array( $error ) ) {
			$error = implode( '|', $error );
		}

		if ( ! empty( $error ) ) {
			Feedzy_Rss_Feeds_Log::error(
				// translators: %1$s is the feed URL, %2$s is the error message.
				sprintf( __( 'Error while parsing feed URL "%1$s": %2$s', 'feedzy-rss-feeds' ), $feed_url, $error ),
				array(
					'feed_url' => $feed_url,
					'cache'    => $cache,
					'sc'       => $sc,
				)
			);

			// curl: (60) SSL certificate problem: unable to get local issuer certificate.
			if ( strpos( $error, 'SSL certificate' ) !== false ) {
				Feedzy_Rss_Feeds_Log::warning(
					sprintf( 'Got an SSL Error (%s), retrying by ignoring SSL', $error ),
					array(
						'feed_url' => $feed_url,
						'cache'    => $cache,
						'sc'       => $sc,
					)
				);
				$feed = $this->init_feed( $feed_url, $cache, $sc, false );
			} elseif ( is_string( $feed_url ) || ( is_array( $feed_url ) && 1 === count( $feed_url ) ) ) {
				Feedzy_Rss_Feeds_Log::debug(
					sprintf( 'Using raw data for feed: %s', $feed_url ),
					array(
						'cache' => $cache,
						'sc'    => $sc,
					)
				);

				$data = wp_remote_retrieve_body( wp_safe_remote_get( $feed_url, array( 'user-agent' => $default_agent ) ) );
				$cloned_feed->set_raw_data( $data );
				$cloned_feed->init();
				$error_raw = $cloned_feed->error();
				if ( empty( $error_raw ) ) {
					// only if using the raw url produces no errors, will we consider the new feed as good to go.
					// otherwise we will use the old feed.
					$feed = $cloned_feed;
				}
			} else {
				Feedzy_Rss_Feeds_Log::debug(
					'Cannot use raw data as this is a multi-feed URL',
					array(
						'feed_url' => $feed_url,
						'cache'    => $cache,
						'sc'       => $sc,
					)
				);
			}
		}
		return $feed;
	}

	/**
	 * Change the default user agent based on the feed url.
	 *
	 * @param string|array $urls Feed urls.
	 *
	 * @return string Optimal User Agent
	 */
	private function get_default_user_agent( $urls ) {

		$set = array();
		if ( ! is_array( $urls ) ) {
			$set[] = $urls;
		}
		foreach ( $set as $url ) {
			if ( strpos( $url, 'medium.com' ) !== false ) {
				return FEEDZY_USER_AGENT;
			}
		}

		return SIMPLEPIE_USERAGENT;
	}

	/**
	 * Returns only valid URLs for fetching.
	 *
	 * @since   3.2.0
	 * @access  protected
	 *
	 * @param   array|string $feed_url The feeds URL/s.
	 * @param   string       $cache The cache string (eg. 1_hour, 30_min etc.).
	 * @param   bool         $echo_result Echo the results.
	 *
	 * @return array
	 */
	protected function get_valid_source_urls( $feed_url, $cache, $echo_result = true ) {
		$valid_feed_url = array();
		if ( is_array( $feed_url ) ) {
			foreach ( $feed_url as $url ) {
				$source_type = 'xml';
				if ( function_exists( 'feedzy_amazon_get_locale_hosts' ) ) {
					$amazon_hosts  = feedzy_amazon_get_locale_hosts();
					$url_host      = 'webservices.' . wp_parse_url( $url, PHP_URL_HOST );
					$is_amazon_url = ! empty( $amazon_hosts ) && in_array( $url_host, $amazon_hosts, true ) ? true : false;
					$source_type   = $is_amazon_url ? 'amazon' : $source_type;
				}
				if ( $this->check_valid_source( $url, $cache, $source_type ) ) {
					$valid_feed_url[] = $url;
				} elseif ( $echo_result ) {
						echo wp_kses_post(
							sprintf(
							// translators: %s: Feed URL.
								__( 'Feed URL: %s not valid and removed from fetch.', 'feedzy-rss-feeds' ),
								'<b>' . esc_url( $url ) . '</b>'
							)
						);
				}
			}
		} else {
			$source_type = 'xml';
			if ( function_exists( 'feedzy_amazon_get_locale_hosts' ) ) {
				$url_host      = 'webservices.' . wp_parse_url( $feed_url, PHP_URL_HOST );
				$amazon_hosts  = feedzy_amazon_get_locale_hosts();
				$is_amazon_url = ! empty( $amazon_hosts ) && in_array( $url_host, $amazon_hosts, true ) ? true : false;
				$source_type   = $is_amazon_url ? 'amazon' : $source_type;
			}
			if ( $this->check_valid_source( $feed_url, $cache, $source_type ) ) {
				$valid_feed_url[] = $feed_url;
			} elseif ( $echo_result ) {
					echo wp_kses_post(
						sprintf(
							// translators: %s: Feed URL.
							__( 'Feed URL: %s not valid and removed from fetch.', 'feedzy-rss-feeds' ),
							'<b>' . esc_url( $feed_url ) . '</b>'
						)
					);
			}
		}

		return $valid_feed_url;
	}

	/**
	 * Checks if a url is a valid feed.
	 *
	 * @since   3.2.0
	 * @access  protected
	 *
	 * @param   string $url The URL to validate.
	 * @param   string $cache The cache string (eg. 1_hour, 30_min etc.).
	 * @param   string $source_type Source Type.
	 *
	 * @return bool
	 */
	protected function check_valid_source( $url, $cache, $source_type = 'xml' ) {
		global $post;

		// phpcs:disable WordPress.Security.NonceVerification
		if ( null === $post && ! empty( $_POST['id'] ) ) {
			$post_id = (int) $_POST['id'];
		} else {
			$post_id = $post->ID;
		}
		$is_valid = true;
		if ( 'amazon' === $source_type ) {
			$amazon_api_errors = array();
			$amazon_products   = $this->init_amazon_api(
				$url,
				$cache,
				array(
					'number_of_item' => 1,
					'no-cache'       => true,
				)
			);
			if ( ! empty( $amazon_products->get_errors() ) ) {
				$amazon_api_errors['source_type'] = '[' . __( 'Amazon Product Advertising API', 'feedzy-rss-feeds' ) . ']';
				$amazon_api_errors['source']      = array( $url );
				$amazon_api_errors['errors']      = $amazon_products->get_errors();
				update_post_meta( $post_id, '__transient_feedzy_invalid_source_errors', $amazon_api_errors );
				$is_valid = false;
			}
		} else {
			$feed = $this->init_feed( $url, $cache, array() );
			if ( $feed->error() ) {
				$is_valid = false;
			}
			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_POST['feedzy_meta_data']['import_link_author_admin'] ) && 'yes' === $_POST['feedzy_meta_data']['import_link_author_admin'] ) {
				if ( $feed->get_items() ) {
					$author = $feed->get_items()[0]->get_author();
					if ( empty( $author ) ) {
						update_post_meta( $post_id, '__transient_feedzy_invalid_dc_namespace', array( $url ) );
						$is_valid = false;
					}
				}
			}
		}
		// Update source type.
		update_post_meta( $post_id, '__feedzy_source_type', $source_type );

		return $is_valid;
	}

	/**
	 * Sanitizes the shortcode array and sets the defaults
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   array  $sc The shorcode attributes array.
	 * @param   string $feed_url The feed url.
	 *
	 * @return  mixed
	 */
	public function sanitize_attr( $sc, $feed_url ) {
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( '0' == $sc['max'] ) {
			$sc['max'] = '999';
		} elseif ( empty( $sc['max'] ) || ! is_numeric( $sc['max'] ) ) {
			$sc['max'] = '5';
		}

		if ( empty( $sc['offset'] ) || ! is_numeric( $sc['offset'] ) ) {
			$sc['offset'] = '0';
		}

		if ( empty( $sc['size'] ) || ! ctype_digit( (string) $sc['size'] ) ) {
			$sc['size'] = '150';
		}
		if ( empty( $sc['summarylength'] ) || ! is_numeric( $sc['summarylength'] ) ) {
			$sc['summarylength'] = '';
		}
		if ( empty( $sc['default'] ) ) {
			$sc['default'] = apply_filters( 'feedzy_default_image', $sc['default'], $feed_url );
		}

		return $sc;
	}

	/**
	 * Render the content to be displayed
	 *
	 * @since   3.0.0
	 * @access  private
	 *
	 * @param   array  $sc The shortcode attributes array.
	 * @param   object $feed The feed object.
	 * @param   string $feed_url The feed url.
	 * @param   string $content The original content.
	 *
	 * @return  string
	 */
	private function render_content( $sc, $feed, $feed_url, $content = '' ) {
		$count                   = 0;
		$sizes                   = array(
			'width'  => $sc['size'],
			'height' => $sc['size'],
		);
		$sizes                   = apply_filters( 'feedzy_thumb_sizes', $sizes, $feed_url );
		$feed_title              = $this->get_feed_title_filter( $feed, $sc, $feed_url );
		$feed_title['use_title'] = false;
		if ( 'yes' === $sc['feed_title'] ) {
			$feed_title['use_title'] = true;
		}
		// Display the error message and quit (before showing the template for pro).
		if ( $feed->error() ) {
			$content .= apply_filters( 'feedzy_default_error', $feed->error(), $feed, $feed_url );
		}

		$feed_items = apply_filters( 'feedzy_get_feed_array', array(), $sc, $feed, $feed_url, $sizes );
		$class      = array_filter( apply_filters( 'feedzy_add_classes_block', array( $sc['classname'], 'feedzy-' . md5( is_array( $feed_url ) ? implode( ',', $feed_url ) : $feed_url ) ), $sc, $feed, $feed_url ) );

		$main_class = 'feedzy-rss';
		if ( isset( $sc['disable_default_style'] ) && 'yes' === $sc['disable_default_style'] ) {
			$main_class = 'feedzy-rss-' . $this->shortcode_count;
			if ( isset( $feed_title['rss_classes'] ) ) {
				$feed_title['rss_classes'][]         = $main_class;
				$feed_title['disable_default_style'] = true;
			}
			++$this->shortcode_count;
		}
		$class[]  = $main_class;
		$content .= '<div class="' . esc_attr( implode( ' ', $class ) ) . '">';
		if ( $feed_title['use_title'] ) {
			$item_title       = ! empty( $feed->get_title() ) ? $feed->get_title() : '';
			$item_description = ! empty( $feed->get_description() ) ? $feed->get_description() : '';
			$item_permalink   = ! empty( $feed->get_permalink() ) ? $feed->get_permalink() : '';

			$content .= '<div class="rss_header">';
			$content .= '<h2><a href="' . esc_url( $item_permalink ) . '" class="rss_title" rel="noopener">' . wp_kses_post( html_entity_decode( $item_title ) ) . '</a> <span class="rss_description"> ' . wp_kses_post( $item_description ) . '</span></h2>';
			$content .= '</div>';
		}
		$content .= '<ul>';

		// Display the error message and quit (before showing the template for pro).
		if ( empty( $feed_items ) ) {
			$content .= esc_html( $sc['error_empty'] );
			$content .= '</ul> </div>';
			return $content;
		}

		$anchor1      = '<a href="%s" target="%s" rel="%s noopener" title="%s" style="%s">%s</a>';
		$anchor2      = '<a href="%s" target="%s" rel="%s noopener">%s</a>';
		$line_item    = '<li %s>%s<span class="title">%s</span><div class="%s" style="%s">%s%s</div></li>';
		$dry_run_item = '<li %s><span class="title">%s</span><div class="dry_run">%s</div></li>';
		$is_dry_run   = isset( $sc['_dryrun_'] ) && 'yes' === $sc['_dryrun_'];
		foreach ( $feed_items as $item ) {
			if ( $is_dry_run ) {
				$details  = $this->get_dry_run_results( $sc, $item );
				$content .= sprintf(
					$dry_run_item,
					wp_kses_post( $item['itemAttr'] ),
					sprintf( $anchor2, esc_url( $item['item_url'] ), esc_attr( $item['item_url_target'] ), esc_attr( $item['item_url_follow'] ), wp_kses_post( $item['item_title'] ) ),
					$details
				);
			} else { 
				$content .= sprintf(
					$line_item,
					wp_kses_post( $item['itemAttr'] ),
					! empty( $item['item_img'] ) && 'no' !== $sc['thumb'] ? sprintf( '<div class="%s" style="%s">%s</div>', $item['item_img_class'], $item['item_img_style'], sprintf( $anchor1, esc_url( $item['item_url'] ), esc_attr( $item['item_url_target'] ), esc_attr( $item['item_url_follow'] ), $item['item_url_title'], $item['item_img_style'], $item['item_img'] ) ) : '',
					sprintf( $anchor2, esc_url( $item['item_url'] ), esc_attr( $item['item_url_target'] ), esc_attr( $item['item_url_follow'] ), wp_kses_post( $item['item_title'] ) ),
					esc_attr( $item['item_content_class'] ),
					esc_attr( $item['item_content_style'] ),
					empty( $item['item_meta'] ) ? '' : sprintf( '<small>%s</small>', wp_kses_post( $item['item_meta'] ) ),
					empty( $item['item_description'] ) ? '' : sprintf( '<p>%s</p>', wp_kses_post( $item['item_description'] ) )
				);
			}
		}
		$content .= '</ul> </div>';
		if ( ! $is_dry_run ) {
			$content  = apply_filters( 'feedzy_global_output', $content, $sc, $feed_title, $feed_items );
			$content .= '<style type="text/css" media="all">' . feedzy_default_css( $main_class ) . '</style>';
		}
		return $content;
	}

	/**
	 * Gets the results of the dry run.
	 *
	 * @since   ?
	 * @access  private
	 *
	 * @param   array  $sc The shorcode attributes array.
	 * @param   object $item The feed item array.
	 *
	 * @return  string
	 */
	private function get_dry_run_results( $sc, $item ) {
		$statuses = array();
		$details  = '';
		if ( true === apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ) {
			if ( ! empty( $item['full_content_error'] ) ) {
				$statuses[] = array(
					'success' => false,
					'msg'     => sprintf(
						// translators: %s: Error message for full content extraction.
						__( 'Full content: %s', 'feedzy-rss-feeds' ),
						$item['full_content_error']
					),
				);
			} elseif ( isset( $item['item_full_content'] ) ) {
				if ( ! empty( $item['item_full_content'] ) ) {
					$statuses[] = array(
						'success' => true,
						'msg'     => __( 'Full content extracted', 'feedzy-rss-feeds' ),
					);
				} else {
					$statuses[] = array(
						'success' => true,
						'msg'     => __( 'Full content extracted (is empty)', 'feedzy-rss-feeds' ),
					);
				}
			}
		}
		if ( strpos( $sc['_dry_run_tags_'], 'item_image' ) !== false ) {
			if ( ! empty( $item['item_img_path'] ) ) {
				$statuses[] = array(
					'success' => true,
					'msg'     => __( 'Image', 'feedzy-rss-feeds' ),
				);
			} else {
				$statuses[] = array(
					'success' => false,
					'msg'     => __( 'Unable to find image', 'feedzy-rss-feeds' ),
				);
			}
		}

		if ( $statuses ) {
			foreach ( $statuses as $status ) {
				$details .= sprintf(
					'<span><i class="dashicons dashicons-%s %s"></i>%s',
					$status['success'] ? 'yes' : 'no-alt',
					$status['success'] ? 'pass' : 'fail',
					$status['msg']
				);
			}
		}
		return $details;
	}

	/**
	 * Retrive the filter rss title array
	 *
	 * @since   3.0.0
	 * @access  private
	 *
	 * @param   object $feed The feed object.
	 * @param   array  $sc The shorcode attributes array.
	 * @param   string $feed_url The feed url.
	 *
	 * @return array
	 */
	private function get_feed_title_filter( $feed, $sc, $feed_url ) {
		return array(
			'rss_url'               => $feed->get_permalink(),
			'rss_title_class'       => 'rss_title',
			'rss_title'             => html_entity_decode( ! empty( $feed->get_title() ) ? $feed->get_title() : '' ),
			'rss_description_class' => 'rss_description',
			'rss_description'       => $feed->get_description(),
			'rss_classes'           => array( $sc['classname'], 'feedzy-' . md5( is_array( $feed_url ) ? implode( ', ', $feed_url ) : $feed_url ) ),
		);
	}

	/**
	 * Get the feed url based on the feeds passed from the shortcode attribute.
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $feeds The feeds from the shortcode attribute.
	 *
	 * @return  array|mixed
	 */
	public function get_feed_url( $feeds ) {
		$feed_url = '';
		if ( ! empty( $feeds ) ) {
			$feeds    = rtrim( $feeds, ',' );
			$feeds    = explode( ',', $feeds );
			$feed_url = array();
			// Remove SSL from HTTP request to prevent fetching errors.
			foreach ( $feeds as $feed ) {
				$feed = trim( $feed );
				// scheme-less URLs.
				if ( strpos( $feed, 'http' ) !== 0 ) {
					$is_feedzy_category = get_page_by_path( $feed, OBJECT, 'feedzy_categories' );
					if ( $is_feedzy_category ) {
						$category_feed = get_post_meta( $is_feedzy_category->ID, 'feedzy_category_feed', true );
						if ( ! empty( $category_feed ) ) {
							$feed = $this->get_feed_url( $category_feed );
						}
					} else {
						$feed = 'http://' . $feed;
					}
				}

				if ( is_array( $feed ) ) {
					foreach ( $feed as $f ) {
						if ( FEEDZY_ALLOW_HTTPS ) {
							$feed_url[] = $f;
						} else {
							$feed_url[] = preg_replace( '/^https:/i', 'http:', $f );
						}
					}
				} elseif ( FEEDZY_ALLOW_HTTPS ) {
						$feed_url[] = $feed;
				} else {
					$feed_url[] = preg_replace( '/^https:/i', 'http:', $feed );
				}
			}
			if ( count( $feed_url ) === 1 ) {
				$feed_url = $feed_url[0];
			}
		}

		return $feed_url;
	}

	/**
	 * Utility method to return feed in array format
	 * before content render.
	 *
	 * @since   3.0.12
	 * @access  public
	 *
	 * @param   array  $feed_items The feed items array.
	 * @param   array  $sc The short code attributes.
	 * @param   object $feed The feed object.
	 * @param   string $feed_url The feed URL source/s.
	 * @param   array  $sizes Sizes array.
	 *
	 * @return array
	 */
	public function get_feed_array( $feed_items, $sc, $feed, $feed_url, $sizes ) {
		$count = 0;
		$items = apply_filters( 'feedzy_feed_items', $feed->get_items( $sc['offset'] ), $feed_url );
		$index = 0;
		foreach ( (array) $items as $item ) {
			$continue = apply_filters( 'feedzy_item_keyword', true, $sc, $item, $feed_url, $index );
			if ( true === $continue ) {
				// Count items. This should be > and not >= because max, when not defined and empty, becomes 0.
				if ( $count >= $sc['max'] ) {
					break;
				}
				$item_attr            = apply_filters( 'feedzy_item_attributes', $item_attr = '', $sizes, $item, $feed_url, $sc, $index );
				$feed_items[ $count ] = $this->get_feed_item_filter( $sc, $sizes, $item, $feed_url, $count, $index );
				if ( isset( $sc['disable_default_style'] ) && 'yes' === $sc['disable_default_style'] ) {
					$item_attr = preg_replace( '/ style=\\"[^\\"]*\\"/', '', $item_attr );
				}
				$feed_items[ $count ]['itemAttr'] = $item_attr;
				++$count;
			}
			++$index;
		}
		return $feed_items;
	}

	/**
	 * Retrive the filter item array
	 *
	 * @since   3.0.0
	 * @access  private
	 *
	 * @param   array           $sc The shorcode attributes array.
	 * @param   array           $sizes The sizes array.
	 * @param   \SimplePie_Item $item The feed item object.
	 * @param   string          $feed_url The feed url.
	 * @param   int             $index The item number (may not be the same as the item_index).
	 * @param   int             $item_index The real index of this items in the feed (maybe be different from $index if filters are used).
	 *
	 * @return array
	 */
	private function get_feed_item_filter( $sc, $sizes, $item, $feed_url, $index, $item_index ) {
		$item_link = $item->get_permalink();
		// if the item has no link (possible in some cases), use the feed link.
		if ( empty( $item_link ) ) {
			$item_link = $item->get_id();
			if ( empty( $item_link ) ) {
				$item_link = $item->get_feed()->get_permalink();
			}
		}
		$new_link      = apply_filters( 'feedzy_item_url_filter', $item_link, $sc, $item );
		$amp_running   = function_exists( 'amp_is_request' ) && amp_is_request();
		$content_thumb = '';

		$thumbnail_to_use = '';
		if ( 'yes' === $sc['thumb'] || 'auto' === $sc['thumb'] ) {
			// Fetch image thumbnail.
			$thumbnail_to_use = $this->feedzy_retrieve_image( $item, $sc );
			$thumbnail_to_use = $this->feedzy_image_encode( $thumbnail_to_use );

			if ( empty( $thumbnail_to_use ) && 'yes' === $sc['thumb'] ) {
				$thumbnail_to_use = $sc['default'];
			}
		} else {
			$thumbnail_to_use = $sc['default'];
		}
		
		if ( ! empty( $thumbnail_to_use ) && is_string( $thumbnail_to_use ) ) {
			$img_style = '';

			if ( isset( $sizes['height'] ) && is_numeric( $sizes['height'] ) ) {
				$img_style .= 'height:' . $sizes['height'] . 'px;';
			}

			if ( isset( $sc['aspectRatio'] ) && '1' !== $sc['aspectRatio'] ) {
				$img_style .= 'aspect-ratio:' . $sc['aspectRatio'] . '; object-fit: fill;';
			}
			
			if (
				isset( $sizes['width'] ) && is_numeric( $sizes['width'] ) && 
				(
					$sizes['width'] !== $sizes['height'] || // Note: Custom modification via filters.
					(
						isset( $sc['aspectRatio'] ) &&
						(
							( 'auto' === $sc['aspectRatio'] && $amp_running ) || // Note: AMP compatibility. Auto without `height` breaks the layout.
							'1' === $sc['aspectRatio'] // Note: Backward compatiblity.
						)
					)
				)
			) {
				$img_style .= 'width:' . $sizes['width'] . 'px;';
			}

			$content_thumb .= '<img decoding="async" src="' . $thumbnail_to_use . '" title="' . esc_attr( $item->get_title() ) . '" style="' . $img_style . '">';
			$content_thumb  = apply_filters( 'feedzy_thumb_output', $content_thumb, $feed_url, $sizes, $item );
		}

		$content_title = html_entity_decode( $item->get_title(), ENT_QUOTES, 'UTF-8' );
		if ( is_numeric( $sc['title'] ) ) {
			$length = intval( $sc['title'] );
			if ( 0 === $length ) {
				$content_title = '';
			}
			if ( $length > 0 && strlen( $content_title ) > $length ) {
				$content_title = preg_replace( '/\s+?(\S+)?$/', '', substr( $content_title, 0, $length ) ) . '...';
			}
		}
		if ( ! is_numeric( $sc['title'] ) && empty( $content_title ) ) {
			$content_title = esc_html__( 'Post Title', 'feedzy-rss-feeds' );
		}
		$content_title = apply_filters( 'feedzy_title_output', $content_title, $feed_url, $item );

		// meta=yes is for backward compatibility, otherwise its always better to provide the fields with granularity.
		// if meta=yes, then meta will be placed in default order. Otherwise in the order stated by the user.
		$meta_args = array(
			'author'      => 'yes' === $sc['meta'] || strpos( $sc['meta'], 'author' ) !== false,
			'date'        => 'yes' === $sc['meta'] || strpos( $sc['meta'], 'date' ) !== false,
			'time'        => 'yes' === $sc['meta'] || strpos( $sc['meta'], 'time' ) !== false,
			'source'      => 'yes' === $sc['multiple_meta'] || strpos( $sc['multiple_meta'], 'source' ) !== false,
			'categories'  => strpos( $sc['meta'], 'categories' ) !== false,
			'tz'          => 'gmt',
			'date_format' => get_option( 'date_format' ),
			'time_format' => get_option( 'time_format' ),
		);

		// parse the x=y type setting e.g. tz=local or tz=gmt.
		if ( strpos( $sc['meta'], '=' ) !== false ) {
			$components = array_map( 'trim', explode( ',', $sc['meta'] ) );
			foreach ( $components as $configs ) {
				if ( strpos( $configs, '=' ) === false ) {
					continue;
				}
				$config                  = explode( '=', $configs );
				$meta_args[ $config[0] ] = $config[1];
			}
		}

		// Filter: feedzy_meta_args.
		$meta_args = apply_filters( 'feedzy_meta_args', $meta_args, $feed_url, $item );

		// order of the meta tags.
		$meta_order = array( 'author', 'date', 'time', 'categories' );
		if ( 'yes' !== $sc['meta'] ) {
			$meta_order = array_map( 'trim', explode( ',', $sc['meta'] ) );
		}

		$content_meta_values = array();

		// multiple sources?
		$is_multiple = is_array( $feed_url );

		$feed_source      = '';
		$feed_source_tags = $item->get_item_tags( FEEDZY_FEED_CUSTOM_TAG_NAMESPACE, 'parent-source' );
		if ( ! empty( $feed_source_tags ) && ! empty( $feed_source_tags[0]['data'] ) ) {
			$feed_source = $feed_source_tags[0]['data'];
		} else {
			$feed_source = $item->get_feed()->get_title();
		}
		// author.
		if ( $item->get_author() && $meta_args['author'] ) {
			$author      = $item->get_author();
			$author_name = $author->get_name();
			if ( ! $author_name ) {
				$author_name = $author->get_email();
			}

			$author_name = apply_filters( 'feedzy_author_name', $author_name, $feed_url, $item );

			if ( $is_multiple && $meta_args['source'] && ! empty( $feed_source ) ) {
				$author_name .= sprintf( ' (%s)', $feed_source );
			}

			if ( $author_name ) {
				$domain                        = wp_parse_url( $new_link );
				$author_url                    = isset( $domain['host'] ) ? '//' . $domain['host'] : '';
				$author_url                    = apply_filters( 'feedzy_author_url', $author_url, $author_name, $feed_url, $item );
				$content_meta_values['author'] = apply_filters( 'feedzy_meta_author', __( 'by', 'feedzy-rss-feeds' ) . ' <a href="' . $author_url . '" target="' . $sc['target'] . '" title="' . $domain['host'] . '" >' . $author_name . '</a> ', $author_name, $author_url, $feed_source, $feed_url, $item );
			}
		} elseif ( $is_multiple && $meta_args['source'] && ! empty( $feed_source ) ) {
			$domain                        = wp_parse_url( $new_link );
			$author_url                    = isset( $domain['host'] ) ? '//' . $domain['host'] : '';
			$author_url                    = apply_filters( 'feedzy_author_url', $author_url, $feed_source, $feed_url, $item );
			$content_meta_values['author'] = apply_filters( 'feedzy_meta_author', __( 'by', 'feedzy-rss-feeds' ) . ' <a href="' . $author_url . '" target="' . $sc['target'] . '" title="' . $domain['host'] . '" >' . $feed_source . '</a> ', $feed_source, $author_url, $feed_source, $feed_url, $item );
		}

		// date/time.
		$date_time = $item->get_date( 'U' );
		if ( 'local' === $meta_args['tz'] ) {
			$date_time = get_date_from_gmt( $item->get_date( 'Y-m-d H:i:s' ), 'U' );
			// strings such as Asia/Kolkata need special handling.
			$tz = get_option( 'timezone_string' );
			if ( $tz ) {
				$date_time = gmdate( 'U', $date_time + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
			}
		} elseif ( 'no' === $meta_args['tz'] ) {
			// change the tz component of the date to UTC.
			$raw_date  = preg_replace( '/\++(\d\d\d\d)/', '+0000', $item->get_date( '' ) );
			$date      = DateTime::createFromFormat( DATE_RFC2822, $raw_date );
			$date_time = $date->format( 'U' );
		}

		$date_time = apply_filters( 'feedzy_feed_timestamp', $date_time, $feed_url, $item );
		if ( $meta_args['date'] && ! empty( $meta_args['date_format'] ) ) {
			// translators: %s: the date of the imported content.
			$content_meta_values['date'] = apply_filters( 'feedzy_meta_date', sprintf( __( 'on %s', 'feedzy-rss-feeds' ), date_i18n( $meta_args['date_format'], $date_time ) ) . ' ', $date_time, $feed_url, $item );
		}

		if ( $meta_args['time'] && ! empty( $meta_args['time_format'] ) ) {
			// translators: %s: the time of the imported content.
			$content_meta_values['time'] = apply_filters( 'feedzy_meta_time', sprintf( __( 'at %s', 'feedzy-rss-feeds' ), date_i18n( $meta_args['time_format'], $date_time ) ) . ' ', $date_time, $feed_url, $item );
		}

		// categories.
		if ( $meta_args['categories'] && has_filter( 'feedzy_retrieve_categories' ) ) {
			$categories = apply_filters( 'feedzy_retrieve_categories', null, $item );
			if ( ! empty( $categories ) ) {
				// translators: %s: the category of the imported content.
				$content_meta_values['categories'] = apply_filters( 'feedzy_meta_categories', sprintf( __( 'in %s', 'feedzy-rss-feeds' ), $categories ) . ' ', $categories, $feed_url, $item );
			}
		}

		$content_meta      = '';
		$content_meta_date = '';
		foreach ( $meta_order as $meta ) {
			if ( isset( $content_meta_values[ $meta ] ) ) {
				// collect date/time values separately too.
				if ( in_array( $meta, array( 'date', 'time' ), true ) ) {
					$content_meta_date .= $content_meta_values[ $meta ];
				}
				$content_meta .= $content_meta_values[ $meta ];
			}
		}

		$content_meta    = apply_filters( 'feedzy_meta_output', $content_meta, $feed_url, $item, $content_meta_values, $meta_order );
		$content_summary = '';
		if ( 'yes' === $sc['summary'] ) {
			$description     = $item->get_description();
			$description     = apply_filters( 'feedzy_summary_input', $description, $item->get_content(), $feed_url, $item );
			$content_summary = $description;
			if ( is_numeric( $sc['summarylength'] ) && strlen( $description ) > $sc['summarylength'] ) {
				$content_summary = preg_replace( '/\s+?(\S+)?$/', '', substr( $description, 0, $sc['summarylength'] ) ) . ' [&hellip;]';
			}
			$content_summary = apply_filters( 'feedzy_summary_output', $content_summary, $new_link, $feed_url, $item );
		}
		$item_content = $item->get_content( false );
		if ( empty( $item_content ) ) {
			$item_content = esc_html__( 'Post Content', 'feedzy-rss-feeds' );
		}

		$img_style = '';
		if ( isset( $sizes['height'] ) ) {
			$img_style = 'height:' . $sizes['height'] . 'px;';
			if ( isset( $sc['aspectRatio'] ) && '1' !== $sc['aspectRatio'] ) {  
				$img_style .= 'aspect-ratio:' . $sc['aspectRatio'] . ';';
			} elseif ( isset( $sizes['width'] ) ) {
				$img_style .= 'width:' . $sizes['width'] . 'px;';
			}
		}

		$item_array = array(
			'feed_url'              => $item->get_feed()->subscribe_url(),
			'item_unique_hash'      => wp_hash( $item->get_permalink() ),
			'item_img_class'        => 'rss_image',
			'item_img_style'        => $img_style,
			'item_url'              => $new_link,
			'item_url_target'       => $sc['target'],
			'item_url_follow'       => isset( $sc['follow'] ) && 'yes' === $sc['follow'] ? 'nofollow' : '',
			'item_url_title'        => $item->get_title(),
			'item_img'              => $content_thumb,
			'item_img_path'         => isset( $sc['thumb'] ) && ( 'yes' === $sc['thumb'] || 'auto' === $sc['thumb'] ) ? $this->feedzy_retrieve_image( $item, $sc ) : '',
			'item_title'            => $content_title,
			'item_content_class'    => 'rss_content',
			'item_content_style'    => '',
			'item_meta'             => $content_meta,
			'item_date'             => $item->get_date( 'U' ),
			'item_date_formatted'   => $content_meta_date,
			'item_author'           => $item->get_author(),
			'item_description'      => $content_summary,
			'item_content'          => apply_filters( 'feedzy_content', $item_content, $item ),
			'item_source'           => $feed_source,
			'item_full_description' => $item->get_description(),
		);
		$item_array = apply_filters( 'feedzy_item_filter', $item_array, $item, $sc, $index, $item_index );

		return $item_array;
	}

	/**
	 * Check if the URL is an image URL based on its extension.
	 * 
	 * @param string|null $url The URL to check.
	 * @return bool
	 */
	public function is_image_url( $url ) {
		if ( empty( $url ) || ! is_string( $url ) ) {
			return false;
		}

		$image_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff', 'tif', 'avif' );
		$url_parts        = wp_parse_url( $url );
		if ( ! isset( $url_parts['path'] ) ) {
			return false;
		}

		$path_info = pathinfo( $url_parts['path'] );
		return (
			isset( $path_info['extension'] ) &&
			in_array( strtolower( $path_info['extension'] ), $image_extensions )
		);
	}

	/**
	 * Retrieve image from the item object
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   SimplePie\Item            $item The item object.
	 * @param   array<string, mixed>|null $sc The shortcode attributes array.
	 *
	 * @return  string
	 */
	public function feedzy_retrieve_image( $item, $sc = null ) {
		$image_mime_types = array();
		foreach ( wp_get_mime_types() as $extn => $mime ) {
			if ( strpos( $mime, 'image/' ) !== false ) {
				$image_mime_types[] = $mime;
			}
		}

		$image_mime_types = apply_filters( 'feedzy_image_mime_types', $image_mime_types );

		$the_thumbnail = '';
		$enclosures    = $item->get_enclosures();
		if ( $enclosures ) {
			foreach ( $enclosures as $enclosure ) {
				$image_url = $this->extract_image_from_enclosure( $enclosure );
				if ( $this->is_image_url( $image_url ) ) {
					$the_thumbnail = $image_url;
					break;
				}
			}
		}
		// xmlns:itunes podcast.
		if ( empty( $the_thumbnail ) ) {
			$data = $item->get_item_tags( 'http://www.itunes.com/dtds/podcast-1.0.dtd', 'image' );
			if ( isset( $data['0']['attribs']['']['href'] ) && ! empty( $data['0']['attribs']['']['href'] ) ) {
				$the_thumbnail = $data['0']['attribs']['']['href'];
			}
		}
		// Content image.
		if ( empty( $the_thumbnail ) ) {
			$feed_description  = $item->get_content();
			$description_image = $this->feedzy_return_image( $feed_description );

			if ( $this->is_image_url( $description_image ) ) {
				$the_thumbnail = $description_image;
			}
		}
		// Description image.
		if ( empty( $the_thumbnail ) ) {
			$feed_description  = $item->get_description();
			$description_image = $this->feedzy_return_image( $feed_description );

			if ( $this->is_image_url( $description_image ) ) {
				$the_thumbnail = $description_image;
			}
		}

		// handle HTTP images.
		if (
			is_string( $the_thumbnail ) && ! empty( $the_thumbnail ) &&
			$sc && isset( $sc['http'] ) && 0 === strpos( $the_thumbnail, 'http://' )
		) {
			switch ( $sc['http'] ) {
				case 'https':
					// fall-through.
				case 'force':
					$the_thumbnail = str_replace( 'http://', 'https://', $the_thumbnail );
					break;
				case 'default':
					$the_thumbnail = $sc['default'];
					break;
			}
		}

		$the_thumbnail = html_entity_decode( $the_thumbnail, ENT_QUOTES, 'UTF-8' );

		if ( is_array( $sc ) && ! empty( $sc ) ) {
			if ( isset( $sc['_dryrun_'] ) && 'yes' === $sc['_dryrun_'] ) {
				return $the_thumbnail;
			}
	
			if ( ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) && ! empty( $sc['feeds'] ) ) {
				$feed_url      = $this->normalize_urls( $sc['feeds'] );
				$the_thumbnail = ! empty( $the_thumbnail ) ? $the_thumbnail : apply_filters( 'feedzy_default_image', $sc['default'], $feed_url );
			}
		}

		$the_thumbnail = apply_filters( 'feedzy_retrieve_image', $the_thumbnail, $item );
		return $the_thumbnail;
	}

	/**
	 * Try to extract an image from the enclosure object.
	 * 
	 * @param \SimplePie\Enclosure $enclosure The enclosure object.
	 * @return string|null
	 * 
	 * @since 5.0.9
	 */
	public function extract_image_from_enclosure( $enclosure ) {
		$image_url = null;
		$medium    = $enclosure->get_medium();
		
		if ( in_array( $medium, array( 'video' ), true ) ) {
			return $image_url;
		}
		
		$single_thumbnail = $enclosure->get_thumbnail();
		if ( $single_thumbnail && $this->is_image_url( $single_thumbnail ) ) {
			$image_url = $single_thumbnail;
		}

		$thumbnails = $enclosure->get_thumbnails();
		if ( ! empty( $thumbnails ) ) {
			foreach ( $thumbnails as $enclosure_thumbnail ) {
				if ( ! $this->is_image_url( $enclosure_thumbnail ) ) {
					continue;
				}
				$image_url = $enclosure_thumbnail;
			}
		}

		if ( ! empty( $enclosure->get_real_type() ) ) {
			$embedded_thumbnail = $enclosure->embed();
			if ( $embedded_thumbnail ) {
				$pattern = '/https?:\/\/.*\.(?:jpg|JPG|jpeg|JPEG|jpe|JPE|gif|GIF|png|PNG)/i';
				if ( preg_match( $pattern, $embedded_thumbnail, $matches ) ) {
					$image_url = $matches[0];
				}
			}
		}

		$enclosure_link = $enclosure->get_link();
		if ( $this->is_image_url( $enclosure_link ) ) {
			$image_url = $enclosure_link;
		}

		return $image_url;
	}

	/**
	 * Get an image from a string
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string|null $img_html A string with an <img/> tag.
	 *
	 * @return  string
	 */
	public function feedzy_return_image( $img_html ) {
		$img     = html_entity_decode( $img_html, ENT_QUOTES, 'UTF-8' );
		$pattern = '/<img[^>]+\>/i';
		preg_match_all( $pattern, $img, $matches );

		$image = null;
		if ( isset( $matches[0] ) ) {
			foreach ( $matches[0] as $match ) {
				$link         = $this->feedzy_scrape_image( $match );
				$blacklist    = $this->feedzy_blacklist_images();
				$is_blacklist = false;
				foreach ( $blacklist as $img_html ) {
					if ( strpos( (string) $link, $img_html ) !== false ) {
						$is_blacklist = true;
						break;
					}
				}
				if ( ! $is_blacklist ) {
					$image = $link;
					break;
				}
			}
		}

		return $image;
	}

	/**
	 * Scrape an image for link from a string with an <img/>
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $img_html A string with an <img/> tag.
	 * @param   string $link The link to search for.
	 *
	 * @return  string
	 */
	public function feedzy_scrape_image( $img_html, $link = '' ) {
		$pattern = '/< *img[^>]*src *= *["\']?([^"\'>]+)/';
		$match   = $link;
		preg_match( $pattern, $img_html, $link );
		if ( ! empty( $link ) && isset( $link[1] ) ) {
			$match = $link[1];
		}

		return $match;
	}

	/**
	 * List blacklisted images to prevent fetching emoticons
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @return  array
	 */
	public function feedzy_blacklist_images() {
		$blacklist = array(
			'frownie.png',
			'icon_arrow.gif',
			'icon_biggrin.gif',
			'icon_confused.gif',
			'icon_cool.gif',
			'icon_cry.gif',
			'icon_eek.gif',
			'icon_evil.gif',
			'icon_exclaim.gif',
			'icon_idea.gif',
			'icon_lol.gif',
			'icon_mad.gif',
			'icon_mrgreen.gif',
			'icon_neutral.gif',
			'icon_question.gif',
			'icon_razz.gif',
			'icon_redface.gif',
			'icon_rolleyes.gif',
			'icon_sad.gif',
			'icon_smile.gif',
			'icon_surprised.gif',
			'icon_twisted.gif',
			'icon_wink.gif',
			'mrgreen.png',
			'rolleyes.png',
			'simple-smile.png',
			'//s.w.org/images/core/emoji/',
		);

		return apply_filters( 'feedzy_feed_blacklist_images', $blacklist );
	}

	/**
	 * Extracts image URLs from query parameters and sanitizes them.
	 * 
	 * Processes URLs that contain image URLs as query parameters (e.g., proxy or CDN URLs).
	 * Supports GIF, JPG, JPEG, PNG, WebP, and AVIF formats.
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $img_url URL containing either a direct image URL or one embedded in query parameters.
	 *
	 * @return  string Extracted and sanitized image URL, or the original URL if no image found.
	 */
	public function feedzy_image_encode( $img_url ) {
		// Check if img url is set as an URL parameter.
		$parsed_url = wp_parse_url( $img_url );
		if ( isset( $parsed_url['query'] ) ) {
			preg_match_all( '/(http|https):\/\/[^ ]+(\.(gif|jpg|jpeg|png|webp|avif))/i', $parsed_url['query'], $matches );
			if ( isset( $matches[0][0] ) && $this->is_image_url( $matches[0][0] ) ) {
				$img_url = $matches[0][0];
			}
		}

		$filtered_url = apply_filters( 'feedzy_image_encode', esc_url( $img_url ), $img_url );

		Feedzy_Rss_Feeds_Log::debug(
			'Change featured image via feedzy_image_encode',
			array(
				'old_url' => $img_url,
				'new_url' => $filtered_url,
			)
		);

		return $filtered_url;
	}

	/**
	 * Render the form template for tinyMCE popup.
	 * Called via ajax.
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function get_tinymce_form() {
		include FEEDZY_ABSPATH . '/form/form.php';
		die();
	}

	/**
	 * Method used to render upsell page.
	 *
	 * @since   3.0.12
	 * @access  public
	 */
	public function render_support() {
		$this->load_layout( 'feedzy-support' );
	}

	/**
	 * Method used to render pages
	 *
	 * @since   3.0.12
	 * @access  public
	 *
	 * @param   string $layout_name The name of the layout.
	 *
	 * @return mixed
	 */
	public function load_layout( $layout_name ) {
		include FEEDZY_ABSPATH . '/includes/layouts/' . $layout_name . '.php';
	}

	/**
	 * Utility method to insert before specific key
	 * in an associative array.
	 *
	 * @since   3.0.12
	 * @access  public
	 *
	 * @param   string $key The key before to insert.
	 * @param   array  $from_array The array in which to insert the new key.
	 * @param   string $new_key The new key name.
	 * @param   mixed  $new_value The new key value.
	 *
	 * @return array|bool
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
	 * Init amazon API.
	 *
	 * @param string[] $urls Source URL.
	 * @param string   $cache Cache time.
	 * @param array    $additional Additional settings.
	 *
	 * @return mixed
	 */
	public function init_amazon_api( $urls, $cache, $additional = array() ) {
		$additional['refresh'] = $cache;
		$urls                  = is_array( $urls ) ? $urls : array( $urls );
		$amazon_product        = new Feedzy_Rss_Feeds_Pro_Amazon_Product_Advertising();
		$settings              = get_option( 'feedzy-rss-feeds-settings', array() );
		foreach ( $urls as $url ) {
			$url = str_replace( array( 'http://', 'https://' ), '', $url );
			$amazon_product->set_config_option( $url, $settings );
			$amazon_product->call_api( $amazon_product->get_api_option( 'access_key' ), $amazon_product->get_api_option( 'secret_key' ), $amazon_product->get_api_option( 'partner_tag' ), $additional );
		}
		return $amazon_product;
	}
}
