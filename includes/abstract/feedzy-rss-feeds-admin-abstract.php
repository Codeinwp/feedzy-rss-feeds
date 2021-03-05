<?php
/**
 * The Abstract class with reusable functionality of the plugin.
 *
 * @link       http://themeisle.com
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
	 * Defines the default image to use on RSS Feeds
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $image_src The image source, currently not used.
	 *
	 * @return  string
	 */
	public function feedzy_define_default_image( $image_src ) {
		$default_img = FEEDZY_ABSURL . '/img/feedzy.svg';

		return apply_filters( 'feedzy_define_default_image_filter', $default_img );
	}

	/**
	 * Fetches the SDK logger data.
	 *
	 * @param array $data The default data that needs to be sent.
	 *
	 * @access public
	 */
	public function get_usage_data( $data ) {
		global $wpdb;

		// how many categories created.
		$categories = 0;
		$terms      = get_terms( array( 'taxonomy' => 'feedzy_categories' ) );
		if ( is_array( $terms ) ) {
			$categories = count( $terms );
		}
		// imports.
		$imports = array();
		$license = 'free';
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

			$imports = apply_filters(
				'feedzy_usage_data',
				array(
					// how many active imports are created.
					'publish'      => count(
						get_posts(
							array(
								'post_type'   => 'feedzy_imports',
								'post_status' => 'publish',
								'numberposts' => 299,
								'fields'      => 'ids',
							)
						)
					),
					// how many draft imports are created
					'draft'        => count(
						get_posts(
							array(
								'post_type'   => 'feedzy_imports',
								'post_status' => 'draft',
								'numberposts' => 299,
								'fields'      => 'ids',
							)
						)
					),
					// how many posts were imported by the imports
					'imported'     => count(
						get_posts(
							array(
								'post_type'   => 'post',
								'post_status' => array( 'publish', 'private', 'draft', 'trash' ),
								'numberposts' => 2999,
								'fields'      => 'ids',
								'meta_key'    => 'feedzy',
								'meta_value'  => 1,
							)
						)
					),
					// integrations
					'integrations' => $integrations,
				)
			);
		}

		$settings = apply_filters( 'feedzy_get_settings', null );
		$config   = array();
		if ( $settings ) {
			$proxy = isset( $settings['proxy'] ) && is_array( $settings['proxy'] ) && ! empty( $settings['proxy'] ) ? array_filter( $settings['proxy'] ) : array();
			if ( ! empty( $proxy ) ) {
				$config[] = 'proxy';
			}
		}

		// how many posts contain the shortcode
		global $wpdb;
		$shortcodes = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}posts WHERE post_status IN ('publish', 'private') AND post_content LIKE '%[feedzy-rss %'" );
		$data       = array(
			'categories' => $categories,
			'imports'    => $imports,
			'shortcodes' => $shortcodes,
			'license'    => $license,
			'config'     => $config,
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
		// Show the error message only if the user who has created this post (which contains the feed) is logged in.
		// Or if this is in the dry run window.
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		$show_error = is_admin() || ( is_user_logged_in() && $post && get_current_user_id() == $post->post_author );
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
			$final_msg = '<div id="message" class="error"><p>' . sprintf( __( 'Sorry, some part of this feed is currently unavailable or does not exist anymore. The detailed error is %s', 'feedzy-rss-feeds' ), '<p style="font-weight: bold">' . $error_msg . '</p>' );
			if ( ! is_admin() ) {
				$final_msg .= sprintf( __( '%1$s(Only you are seeing this detailed error because you are the creator of this post. Other users will see the error message as below.)%2$s', 'feedzy-rss-feeds' ), '<small>', '</small>' );
			}
			$final_msg .= '</p></div>';
		} else {
			error_log( 'Feedzy RSS Feeds - related feed: ' . print_r( $feed_url, true ) . ' - Error message: ' . $error_msg );
		}
		return $final_msg;
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
	public function feedzy_summary_input_filter( $description, $content, $feed_url ) {
		$description = trim( wp_strip_all_tags( $description ) );
		$description = trim( str_replace( array( '[â€¦]', '[…]', '[&hellip;]' ), '', $description ) );

		return $description;
	}

	/**
	 * Check title for keywords
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   boolean $continue A boolean to stop the script.
	 * @param   array   $sc The shortcode attributes.
	 * @param   object  $item The feed item.
	 * @param   string  $feed_url The feed URL.
	 *
	 * @return  boolean
	 */
	public function feedzy_feed_item_keywords_title( $continue, $sc, $item, $feed_url ) {
		if ( feedzy_is_new() && ! feedzy_is_pro() ) {
			return true;
		}

		if ( isset( $sc['keywords_inc'] ) && ! empty( $sc['keywords_inc'] ) ) {
			$keywords = $sc['keywords_inc'];
			if ( ! empty( $keywords ) ) {
				$continue = false;
				foreach ( $keywords as $keyword ) {
					if ( strpos( $item->get_title(), $keyword ) !== false || strpos( $item->get_description(), $keyword ) !== false ) {
						$continue = true;
					}
				}
			}
		} elseif ( isset( $sc['keywords_title'] ) && ! empty( $sc['keywords_title'] ) ) {
			$keywords = $sc['keywords_title'];
			if ( ! empty( $keywords ) ) {
				$continue = false;
				foreach ( $keywords as $keyword ) {
					if ( strpos( $item->get_title(), $keyword ) !== false ) {
						$continue = true;
					}
				}
			}
		}

		if ( isset( $sc['keywords_exc'] ) && ! empty( $sc['keywords_exc'] ) ) {
			$keywords = $sc['keywords_exc'];
			if ( ! empty( $keywords ) ) {
				foreach ( $keywords as $keyword ) {
					if ( strpos( $item->get_title(), $keyword ) !== false || strpos( $item->get_description(), $keyword ) !== false ) {
						return false;
					}
				}
			}
		} elseif ( isset( $sc['keywords_ban'] ) && ! empty( $sc['keywords_ban'] ) ) {
			$keywords = $sc['keywords_ban'];
			if ( ! empty( $keywords ) ) {
				foreach ( $keywords as $keyword ) {
					if ( strpos( $item->get_title(), $keyword ) !== false ) {
						return false;
					}
				}
			}
		}

		return $continue;
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
		wp_enqueue_style( $this->plugin_name );
		$sc       = $this->get_short_code_attributes( $atts );
		$feed_url = $this->normalize_urls( $sc['feeds'] );
		if ( empty( $feed_url ) ) {
			return $content;
		}
		$cache = $sc['refresh'];

		// Disregard the pseudo-shortcode coming from Gutenberg as a lazy one.
		if ( 'yes' === $sc['lazy'] && ! isset( $sc['gutenberg'] ) ) {
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
			$content = get_transient( sprintf( 'feedzy-lazy-%s', is_array( $feed_url ) ? implode( ',', $feed_url ) : $feed_url ) );

			$class = array_filter( apply_filters( 'feedzy_add_classes_block', array( $sc['className'], 'feedzy-' . md5( is_array( $feed_url ) ? implode( ',', $feed_url ) : $feed_url ) ), $sc, null, $feed_url ) );
			$html  = "<div class='feedzy-lazy' $attributes>";
			// the first time the shortcode is being called it will not have any content.
			if ( empty( $content ) ) {
				$content = apply_filters( 'feedzy_lazyload_loading_msg', __( 'Loading', 'feedzy-rss-feeds' ) . '...', $feed_url );
			}
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

		$feed = $this->fetch_feed( $feed_url, $cache, $sc );
		if ( is_string( $feed ) ) {
			return $feed;
		}
		$sc      = $this->sanitize_attr( $sc, $feed_url );
		$content = $this->render_content( $sc, $feed, $content, $feed_url );

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
						'validate_callback' => function( $value ) {
							return wp_verify_nonce( $value, 'feedzy' );
						},
						'required'          => true,
					),
				),
			)
		);
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
		$feed     = $this->fetch_feed( $feed_url, $sc['refresh'], $sc );
		if ( is_string( $feed ) ) {
			return $feed;
		}
		$sc      = $this->sanitize_attr( $sc, $feed_url );
		$content = $this->render_content( $sc, $feed, '', $feed_url );

		// save the content as a transient so that whenever the feed is refreshed next, this stale content is displayed first.
		set_transient( sprintf( 'feedzy-lazy-%s', ( is_array( $feed_url ) ? implode( ',', $feed_url ) : $feed_url ) ), $content, apply_filters( 'feedzy_lazyload_cache_time', DAY_IN_SECONDS, $feed_url ) );

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
				'feeds'          => '',
				// number of feeds items (0 for unlimited).
				'max'            => '5',
				// display feed title yes/no.
				'feed_title'     => 'yes',
				// _blank, _self
				'target'         => '_blank',
				// empty or no for nofollow.
				'follow'         => '',
				// strip title after X char. X can be 0 too, which will remove the title.
				'title'          => '',
				// yes (author, date, time), no (NEITHER), author, date, time, categories
				// tz=local (for date/time in blog time)
				// tz=gmt (for date/time in UTC time, this is the default)
				// tz=no (for date/time in the feed, without conversion).
				'meta'           => 'yes',
				// yes (all), no (NEITHER)
				// source: show feed title.
				'multiple_meta'  => 'no',
				// strip title.
				'summary'        => 'yes',
				// strip summary after X char.
				'summarylength'  => '',
				// yes, no, auto.
				'thumb'          => 'auto',
				// default thumb URL if no image found (only if thumb is set to yes or auto).
				'default'        => '',
				// thumbs pixel size.
				'size'           => '',
				// only display item if title contains specific keywords (comma-separated list/case sensitive).
				'keywords_title' => '',
				// only display item if title OR content contains specific keywords (comma-separated list/case sensitive).
				'keywords_inc'   => '',
				// cache refresh.
				'refresh'        => '12_hours',
				// sorting.
				'sort'           => '',
				// https = force https
				// default = fall back to default image
				// auto = continue as it is.
				'http'           => 'auto',
				// message to show when feed is empty.
				'error_empty'    => 'Feed has no items.',
				// to disable amp support, use 'no'. This is currently not available as part of the shortcode tinymce form.
				'amp'            => 'yes',
				// paginate.
				'offset'         => 0,
				// class name of this block.
				'className'      => '',
				// lazy loading of feeds?
				'lazy'           => 'no',
				// these are only for internal purposes.
				'_dryrun_'       => 'no',
				'_dry_run_tags_' => '',
			),
			$atts,
			'feedzy_default'
		);
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
	 * @return mixed|void Urls of the feeds.
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
	public function fetch_feed( $feed_url, $cache = '12_hours', $sc ) {
		// Load SimplePie if not already.
		do_action( 'feedzy_pre_http_setup', $feed_url );

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

			$feed_url = $this->get_valid_feed_urls( $feed_url, $cache );

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
			add_filter(
				'wp_feed_cache_transient_lifetime',
				function( $time ) use ( $cache_time ) {
					return $cache_time;
				},
				10,
				1
			);
		} else {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
			global $wp_filesystem;

			$dir = $wp_filesystem->wp_content_dir() . 'uploads/simplepie';
			if ( ! $wp_filesystem->exists( $dir ) ) {
				$done = $wp_filesystem->mkdir( $dir );
				if ( false === $done ) {
					do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Unable to create directory %s', $dir ), 'error', __FILE__, __LINE__ );
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

		global $feedzy_current_error_reporting;
		$feedzy_current_error_reporting = error_reporting();

		// to avoid the Warning! Non-numeric value encountered. This can be removed once SimplePie in core is fixed.
		if ( version_compare( phpversion(), '7.1', '>=' ) ) {
			error_reporting( E_ALL ^ E_WARNING );
			// reset the error_reporting back to its original value.
			add_action(
				'shutdown',
				function() {
					global $feedzy_current_error_reporting;
					error_reporting( $feedzy_current_error_reporting );
				}
			);
		}

		$feed->init();

		$error = $feed->error();
		// error could be an array, so let's join the different errors.
		if ( is_array( $error ) ) {
			$error = implode( '|', $error );
		}

		if ( ! empty( $error ) ) {
			do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Error while parsing feed: %s', $error ), 'error', __FILE__, __LINE__ );

			// curl: (60) SSL certificate problem: unable to get local issuer certificate
			if ( strpos( $error, 'SSL certificate' ) !== false ) {
				do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Got an SSL Error (%s), retrying by ignoring SSL', $error ), 'debug', __FILE__, __LINE__ );
				$feed = $this->init_feed( $feed_url, $cache, $sc, false );
			} elseif ( is_string( $feed_url ) || ( is_array( $feed_url ) && 1 === count( $feed_url ) ) ) {
				do_action( 'themeisle_log_event', FEEDZY_NAME, 'Trying to use raw data', 'debug', __FILE__, __LINE__ );
				$data = wp_remote_retrieve_body( wp_remote_get( $feed_url, array( 'user-agent' => $default_agent ) ) );
				$cloned_feed->set_raw_data( $data );
				$cloned_feed->init();
				$error_raw = $cloned_feed->error();
				if ( empty( $error_raw ) ) {
					// only if using the raw url produces no errors, will we consider the new feed as good to go.
					// otherwise we will use the old feed.
					$feed = $cloned_feed;
				}
			} else {
				do_action( 'themeisle_log_event', FEEDZY_NAME, 'Cannot use raw data as this is a multifeed URL', 'debug', __FILE__, __LINE__ );
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
	 * @param   bool         $echo Echo the results.
	 *
	 * @return array
	 */
	protected function get_valid_feed_urls( $feed_url, $cache, $echo = true ) {
		$valid_feed_url = array();
		if ( is_array( $feed_url ) ) {
			foreach ( $feed_url as $url ) {
				if ( $this->check_valid_xml( $url, $cache ) ) {
					$valid_feed_url[] = $url;
				} else {
					if ( $echo ) {
						echo wp_kses_post( sprintf( __( 'Feed URL: %s not valid and removed from fetch.', 'feedzy-rss-feeds' ), '<b>' . esc_url( $url ) . '</b>' ) );
					}
				}
			}
		} else {
			if ( $this->check_valid_xml( $feed_url, $cache ) ) {
				$valid_feed_url[] = $feed_url;
			} else {
				if ( $echo ) {
					echo wp_kses_post( sprintf( __( 'Feed URL: %s not valid and removed from fetch.', 'feedzy-rss-feeds' ), '<b>' . esc_url( $feed_url ) . '</b>' ) );
				}
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
	 *
	 * @return bool
	 */
	protected function check_valid_xml( $url, $cache ) {
		$feed = $this->init_feed( $url, $cache, array() );
		if ( $feed->error() ) {
			return false;
		}

		return true;
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

		if ( empty( $sc['size'] ) || ! ctype_digit( $sc['size'] ) ) {
			$sc['size'] = '150';
		}
		if ( ! empty( $sc['keywords_title'] ) ) {
			$sc['keywords_title'] = rtrim( $sc['keywords_title'], ',' );
			$sc['keywords_title'] = array_map( 'trim', explode( ',', $sc['keywords_title'] ) );
		}
		if ( ! empty( $sc['keywords_inc'] ) ) {
			$sc['keywords_inc'] = rtrim( $sc['keywords_inc'], ',' );
			$sc['keywords_inc'] = array_map( 'trim', explode( ',', $sc['keywords_inc'] ) );
		}
		if ( ! empty( $sc['keywords_ban'] ) ) {
			$sc['keywords_ban'] = rtrim( $sc['keywords_ban'], ',' );
			$sc['keywords_ban'] = array_map( 'trim', explode( ',', $sc['keywords_ban'] ) );
		}
		if ( ! empty( $sc['keywords_exc'] ) ) {
			$sc['keywords_exc'] = rtrim( $sc['keywords_exc'], ',' );
			$sc['keywords_exc'] = array_map( 'trim', explode( ',', $sc['keywords_exc'] ) );
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
	 * @param   array  $sc The shorcode attributes array.
	 * @param   object $feed The feed object.
	 * @param   string $content The original content.
	 * @param   string $feed_url The feed url.
	 *
	 * @return  string
	 */
	private function render_content( $sc, $feed, $content = '', $feed_url ) {
		$count                   = 0;
		$sizes                   = array(
			'width'  => $sc['size'],
			'height' => $sc['size'],
		);
		$sizes                   = apply_filters( 'feedzy_thumb_sizes', $sizes, $feed_url );
		$feed_title['use_title'] = false;
		if ( 'yes' === $sc['feed_title'] ) {
			$feed_title              = $this->get_feed_title_filter( $feed, $sc, $feed_url );
			$feed_title['use_title'] = true;
		}
		// Display the error message and quit (before showing the template for pro).
		if ( $feed->error() ) {
			$content .= apply_filters( 'feedzy_default_error', $feed->error(), $feed, $feed_url );
		}

		$feed_items = apply_filters( 'feedzy_get_feed_array', array(), $sc, $feed, $feed_url, $sizes );
		$class      = array_filter( apply_filters( 'feedzy_add_classes_block', array( $sc['className'], 'feedzy-' . md5( is_array( $feed_url ) ? implode( ',', $feed_url ) : $feed_url ) ), $sc, $feed, $feed_url ) );
		$content   .= '<div class="feedzy-rss ' . implode( ' ', $class ) . '">';
		if ( $feed_title['use_title'] ) {
			$content .= '<div class="rss_header">';
			$content .= '<h2><a href="' . $feed->get_permalink() . '" class="rss_title" rel="noopener">' . html_entity_decode( $feed->get_title() ) . '</a> <span class="rss_description"> ' . $feed->get_description() . '</span></h2>';
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
					$item['itemAttr'],
					sprintf( $anchor2, $item['item_url'], $item['item_url_target'], $item['item_url_follow'], $item['item_title'] ),
					$details
				);
			} else {
				$content .= sprintf(
					$line_item,
					$item['itemAttr'],
					! empty( $item['item_img'] ) && 'no' !== $sc['thumb'] ? sprintf( '<div class="%s" style="%s">%s</div>', $item['item_img_class'], $item['item_img_style'], sprintf( $anchor1, $item['item_url'], $item['item_url_target'], $item['item_url_follow'], $item['item_url_title'], $item['item_img_style'], $item['item_img'] ) ) : '',
					sprintf( $anchor2, $item['item_url'], $item['item_url_target'], $item['item_url_follow'], $item['item_title'] ),
					$item['item_content_class'],
					$item['item_content_style'],
					empty( $item['item_meta'] ) ? '' : sprintf( '<small>%s</small>', $item['item_meta'] ),
					empty( $item['item_description'] ) ? '' : sprintf( '<p>%s</p>', $item['item_description'] )
				);
			}
		}
		$content .= '</ul> </div>';
		if ( ! $is_dry_run ) {
			$content = apply_filters( 'feedzy_global_output', $content, $sc, $feed_title, $feed_items );
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
					'msg'     => sprintf( __( 'Full content: %s', 'feedzy-rss-feeds' ), $item['full_content_error'] ),
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
			'rss_title'             => html_entity_decode( $feed->get_title() ),
			'rss_description_class' => 'rss_description',
			'rss_description'       => $feed->get_description(),
			'rss_classes'           => array( $sc['className'], 'feedzy-' . md5( $feed_url ) ),
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
					$feed = 'http://' . $feed;
				}
				if ( FEEDZY_ALLOW_HTTPS ) {
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
				$item_attr                        = apply_filters( 'feedzy_item_attributes', $item_attr = '', $sizes, $item, $feed_url, $sc, $index );
				$feed_items[ $count ]             = $this->get_feed_item_filter( $sc, $sizes, $item, $feed_url, $count, $index );
				$feed_items[ $count ]['itemAttr'] = $item_attr;
				$count++;
			}
			$index++;
		}

		return $feed_items;
	}

	/**
	 * Retrive the filter item array
	 *
	 * @since   3.0.0
	 * @access  private
	 *
	 * @param   array  $sc The shorcode attributes array.
	 * @param   array  $sizes The sizes array.
	 * @param   object $item The feed item object.
	 * @param   string $feed_url The feed url.
	 * @param   int    $index The item number (may not be the same as the item_index).
	 * @param   int    $item_index The real index of this items in the feed (maybe be different from $index if filters are used).
	 *
	 * @return array
	 */
	private function get_feed_item_filter( $sc, $sizes, $item, $feed_url, $index, $item_index ) {
		$item_link = $item->get_permalink();
		// if the item has no link (possible in some cases), use the feed link.
		if ( empty( $item_link ) ) {
			$item_link = $item->get_feed()->get_permalink();
		}
		$new_link = apply_filters( 'feedzy_item_url_filter', $item_link, $sc, $item );

		// Fetch image thumbnail.
		if ( 'yes' === $sc['thumb'] || 'auto' === $sc['thumb'] ) {
			$the_thumbnail = $this->feedzy_retrieve_image( $item, $sc );
		}
		if ( 'yes' === $sc['thumb'] || 'auto' === $sc['thumb'] ) {
			$content_thumb = '';
			if ( ( ! empty( $the_thumbnail ) && 'auto' === $sc['thumb'] ) || 'yes' === $sc['thumb'] ) {
				if ( ! empty( $the_thumbnail ) ) {
					$the_thumbnail  = $this->feedzy_image_encode( $the_thumbnail );
					$content_thumb .= '<span class="fetched" style="background-image:  url(\'' . $the_thumbnail . '\');" title="' . esc_html( $item->get_title() ) . '"></span>';
					if ( ! isset( $sc['amp'] ) || 'no' !== $sc['amp'] ) {
						$content_thumb .= '<amp-img width="' . $sizes['width'] . '" height="' . $sizes['height'] . '" src="' . $the_thumbnail . '">';
					}
				}
				if ( empty( $the_thumbnail ) && 'yes' === $sc['thumb'] ) {
					$content_thumb .= '<span class="default" style="background-image:url(' . $sc['default'] . ');" title="' . esc_html( $item->get_title() ) . '"></span>';
					if ( ! isset( $sc['amp'] ) || 'no' !== $sc['amp'] ) {
						$content_thumb .= '<amp-img width="' . $sizes['width'] . '" height="' . $sizes['height'] . '" src="' . $sc['default'] . '">';
					}
				}
			}
			$content_thumb = apply_filters( 'feedzy_thumb_output', $content_thumb, $feed_url, $sizes, $item );
		} else {
			$content_thumb  = '';
			$content_thumb .= '<span class="default" style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px; background-image:url(' . $sc['default'] . ');" title="' . $item->get_title() . '"></span>';
			if ( ! isset( $sc['amp'] ) || 'no' !== $sc['amp'] ) {
				$content_thumb .= '<amp-img width="' . $sizes['width'] . '" height="' . $sizes['height'] . '" src="' . $sc['default'] . '">';
			}
			$content_thumb = apply_filters( 'feedzy_thumb_output', $content_thumb, $feed_url, $sizes, $item );
		}
		$content_title = html_entity_decode( $item->get_title(), ENT_QUOTES, 'UTF-8' );
		if ( is_numeric( $sc['title'] ) ) {
			$length = intval( $sc['title'] );
			if ( $length > 0 && strlen( $content_title ) > $length ) {
				$content_title = preg_replace( '/\s+?(\S+)?$/', '', substr( $content_title, 0, $length ) ) . '...';
			} elseif ( 0 === $length ) {
				$content_title = '';
			}
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
		$feed_source = $item->get_feed()->get_title();

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
				$author_url                    = '//' . $domain['host'];
				$author_url                    = apply_filters( 'feedzy_author_url', $author_url, $author_name, $feed_url, $item );
				$content_meta_values['author'] = apply_filters( 'feedzy_meta_author', __( 'by', 'feedzy-rss-feeds' ) . ' <a href="' . $author_url . '" target="' . $sc['target'] . '" title="' . $domain['host'] . '" >' . $author_name . '</a> ', $author_name, $author_url, $feed_source, $feed_url, $item );
			}
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
			$content_meta_values['date'] = apply_filters( 'feedzy_meta_date', __( 'on', 'feedzy-rss-feeds' ) . ' ' . date_i18n( $meta_args['date_format'], $date_time ) . ' ', $date_time, $feed_url, $item );
		}

		if ( $meta_args['time'] && ! empty( $meta_args['time_format'] ) ) {
			$content_meta_values['time'] = apply_filters( 'feedzy_meta_time', __( 'at', 'feedzy-rss-feeds' ) . ' ' . date_i18n( $meta_args['time_format'], $date_time ) . ' ', $date_time, $feed_url, $item );
		}

		// categories.
		if ( $meta_args['categories'] && has_filter( 'feedzy_retrieve_categories' ) ) {
			$categories = apply_filters( 'feedzy_retrieve_categories', null, $item );
			if ( ! empty( $categories ) ) {
				$content_meta_values['categories'] = apply_filters( 'feedzy_meta_categories', __( 'in', 'feedzy-rss-feeds' ) . ' ' . $categories . ' ', $categories, $feed_url, $item );
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
		$item_array = array(
			'item_img_class'      => 'rss_image',
			'item_img_style'      => 'width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px;',
			'item_url'            => $new_link,
			'item_url_target'     => $sc['target'],
			'item_url_follow'     => isset( $sc['follow'] ) && 'no' === $sc['follow'] ? 'nofollow' : '',
			'item_url_title'      => $item->get_title(),
			'item_img'            => $content_thumb,
			'item_img_path'       => $this->feedzy_retrieve_image( $item, $sc ),
			'item_title'          => $content_title,
			'item_content_class'  => 'rss_content',
			'item_content_style'  => '',
			'item_meta'           => $content_meta,
			'item_date'           => $item->get_date( 'U' ),
			'item_date_formatted' => $content_meta_date,
			'item_author'         => $item->get_author(),
			'item_description'    => $content_summary,
			'item_content'        => apply_filters( 'feedzy_content', $item->get_content( false ), $item ),
			'item_source'         => $feed_source,
		);
		$item_array = apply_filters( 'feedzy_item_filter', $item_array, $item, $sc, $index, $item_index );

		return $item_array;
	}

	/**
	 * Retrive image from the item object
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   object $item The item object.
	 * @param   array  $sc The shorcode attributes array.
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
			foreach ( (array) $enclosures as $enclosure ) {
				// Item thumbnail.
				$thumbnail = $enclosure->get_thumbnail();
				if ( $thumbnail ) {
					$the_thumbnail = $thumbnail;
				}
				if ( isset( $enclosure->thumbnails ) ) {
					foreach ( (array) $enclosure->thumbnails as $thumbnail ) {
						$the_thumbnail = $thumbnail;
					}
				}
				$thumbnail = $enclosure->embed();
				if ( $thumbnail ) {
					$pattern = '/https?:\/\/.*\.(?:jpg|JPG|jpeg|JPEG|jpe|JPE|gif|GIF|png|PNG)/i';
					if ( preg_match( $pattern, $thumbnail, $matches ) ) {
						$the_thumbnail = $matches[0];
					}
				}
				foreach ( (array) $enclosure->get_link() as $thumbnail ) {
					$pattern = '/https?:\/\/.*\.(?:jpg|JPG|jpeg|JPEG|jpe|JPE|gif|GIF|png|PNG)/i';
					$imgsrc  = $thumbnail;
					if ( preg_match( $pattern, $imgsrc, $matches ) ) {
						$the_thumbnail = $thumbnail;
						break;
					} elseif ( in_array( $enclosure->type, $image_mime_types, true ) ) {
						$the_thumbnail = $thumbnail;
						break;
					}
				}
				// Break loop if thumbnail is found.
				if ( ! empty( $the_thumbnail ) ) {
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
			$feed_description = $item->get_content();
			$the_thumbnail    = $this->feedzy_return_image( $feed_description );
		}
		// Description image.
		if ( empty( $the_thumbnail ) ) {
			$feed_description = $item->get_description();
			$the_thumbnail    = $this->feedzy_return_image( $feed_description );
		}

		// handle HTTP images.
		if ( $sc && isset( $sc['http'] ) && 0 === strpos( $the_thumbnail, 'http://' ) ) {
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
		$the_thumbnail = apply_filters( 'feedzy_retrieve_image', $the_thumbnail, $item );

		return $the_thumbnail;
	}

	/**
	 * Get an image from a string
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $string A string with an <img/> tag.
	 *
	 * @return  string
	 */
	public function feedzy_return_image( $string ) {
		$img     = html_entity_decode( $string, ENT_QUOTES, 'UTF-8' );
		$pattern = '/<img[^>]+\>/i';
		preg_match_all( $pattern, $img, $matches );

		$image = null;
		if ( isset( $matches[0] ) ) {
			foreach ( $matches[0] as $match ) {
				$link         = $this->feedzy_scrape_image( $match );
				$blacklist    = $this->feedzy_blacklist_images();
				$is_blacklist = false;
				foreach ( $blacklist as $string ) {
					if ( strpos( (string) $link, $string ) !== false ) {
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
	 * @param   string $string A string with an <img/> tag.
	 * @param   string $link The link to search for.
	 *
	 * @return  string
	 */
	public function feedzy_scrape_image( $string, $link = '' ) {
		$pattern = '/src=[\'"]?([^\'" >]+)[\'" >]/';
		$match   = $link;
		preg_match( $pattern, $string, $link );
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
		);

		return apply_filters( 'feedzy_feed_blacklist_images', $blacklist );
	}

	/**
	 * Image name encoder and url retrive if in url param
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $string A string containing the image URL.
	 *
	 * @return  string
	 */
	public function feedzy_image_encode( $string ) {
		// Check if img url is set as an URL parameter.
		$url_tab = wp_parse_url( $string );
		if ( isset( $url_tab['query'] ) ) {
			preg_match_all( '/(http|https):\/\/[^ ]+(\.gif|\.GIF|\.jpg|\.JPG|\.jpeg|\.JPEG|\.png|\.PNG)/', $url_tab['query'], $img_url );
			if ( isset( $img_url[0][0] ) ) {
				$string = $img_url[0][0];
			}
		}

		$return = apply_filters( 'feedzy_image_encode', esc_url( $string ), $string );
		do_action( 'themeisle_log_event', FEEDZY_NAME, sprintf( 'Changing image URL from %s to %s', $string, $return ), 'debug', __FILE__, __LINE__ );
		return $return;
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
}
