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
	 * Defines the default image to use on RSS Feeds
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $imageSrc The image source, currently not used.
	 *
	 * @return  string
	 */
	public function feedzy_define_default_image( $imageSrc ) {
		$defaultImg = FEEDZY_ABSURL . '/img/feedzy-default.jpg';

		return apply_filters( 'feedzy_define_default_image_filter', $defaultImg );
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
	 * @param   object $error The error Object.
	 * @param   string $feedURL The feed URL.
	 *
	 * @return  string
	 */
	public function feedzy_default_error_notice( $error, $feedURL ) {
		error_log( 'Feedzy RSS Feeds - related feed: ' . print_r( $feedURL ) . ' - Error message: ' . $this->feedzy_array_obj_string( $error ) );

		return '<div id="message" class="error" data-error"' . esc_attr( $this->feedzy_array_obj_string( $error ) ) . '"><p>' . __( 'Sorry, this feed is currently unavailable or does not exists anymore.', 'feedzy-rss-feeds' ) . '</p></div>';
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
	 * @param   string $itemAttr The item attribute.
	 * @param   array  $sizes An array with the current sizes.
	 *
	 * @return  string
	 */
	public function feedzy_add_item_padding( $itemAttr, $sizes ) {
		$paddinTop    = number_format( ( 15 / 150 ) * $sizes['height'], 0 );
		$paddinBottom = number_format( ( 25 / 150 ) * $sizes['height'], 0 );
		$stylePadding = ' style="padding: ' . $paddinTop . 'px 0 ' . $paddinBottom . 'px"';

		return $itemAttr . $stylePadding;
	}

	/**
	 * Appends classes to the feed item
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $itemAttr The item attribute.
	 *
	 * @return  string
	 */
	public function feedzy_classes_item( $itemAttr = '', $sizes = '', $item = '', $feedURL = '', $sc = '' ) {
		$classes = array( 'rss_item' );
		$classes = apply_filters( 'feedzy_add_classes_item', $classes, $sc );
		$classes = ' class="' . implode( ' ', $classes ) . '"';

		return $itemAttr . $classes;
	}

	/**
	 * Filter feed description input
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $description The feed description.
	 * @param   string $content The feed description.
	 * @param   string $feedURL The feed URL.
	 *
	 * @return  string
	 */
	public function feedzy_summary_input_filter( $description, $content, $feedURL ) {
		$description = trim( strip_tags( $description ) );
		$description = trim( chop( $description, '[&hellip;]' ) );

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
	 * @param   string  $feedURL The feed URL.
	 *
	 * @return  boolean
	 */
	public function feedzy_feed_item_keywords_title( $continue, $sc, $item, $feedURL ) {
		if ( feedzy_is_new() && ! feedzy_is_pro() ) {
			return true;
		}
		$keywords_title = $sc['keywords_title'];
		if ( ! empty( $keywords_title ) ) {
			$continue = false;
			foreach ( $keywords_title as $keyword ) {
				if ( strpos( $item->get_title(), $keyword ) !== false ) {
					$continue = true;
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
			$postThumbnailId = get_post_thumbnail_id( $post->ID );
			$attachmentMeta  = wp_get_attachment_metadata( $postThumbnailId );
			$imageUrl        = wp_get_attachment_image_src( $postThumbnailId, 'medium' );
			echo '<enclosure url="' . $imageUrl[0] . '" length="' . filesize( get_attached_file( $postThumbnailId ) ) . '" type="image/jpg" />';
			echo '<media:content url="' . $imageUrl[0] . '" width="' . $attachmentMeta['sizes']['medium']['width'] . '" height="' . $attachmentMeta['sizes']['medium']['height'] . '" medium="image" type="' . $attachmentMeta['sizes']['medium']['mime-type'] . '" />';

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
		$regex = '((https?|ftp)\:\/\/)?';                                       // Contains Protocol
		$regex .= '([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?';  // Uses User and Pass
		$regex .= '([a-z0-9-.]*)\.([a-z]{2,3})';                                // Has Host or IP
		$regex .= '(\:[0-9]{2,5})?';                                            // Uses Port
		$regex .= '(\/([a-z0-9+\$_-]\.?)+)*\/?';                                // Has Path
		$regex .= '(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?';                   // Has GET Query
		$regex .= '(#[a-z_.-][a-z0-9+\$_.-]*)?';                                // Uses Anchor
		if ( preg_match( "/^$regex$/", $src ) ) {
			// If it matches Regex ( it's not a slug ) so return the sources.
			return $src;
		} else {
			$src = trim( $src );
			if ( $post = get_page_by_path( $src, OBJECT, 'feedzy_categories' ) ) {
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
		$sc      = $this->get_short_code_attributes( $atts );
		$feedURL = $this->normalize_urls( $sc['feeds'] );
		$feed    = $this->fetch_feed( $feedURL );
		if ( is_string( $feed ) ) {
			return $feed;
		}
		$sc      = $this->sanitize_attr( $sc, $feedURL );
		$content = $this->render_content( $sc, $feed, $content, $feedURL );

		return $content;
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
		// Retrieve & extract shorcode parameters
		$sc = shortcode_atts( array(
			'feeds'          => '',
			// comma separated feeds url
			'max'            => '5',
			// number of feeds items (0 for unlimited)
			'feed_title'     => 'yes',
			// display feed title yes/no
			'target'         => '_blank',
			// _blank, _self
			'title'          => '',
			// strip title after X char
			'meta'           => 'yes',
			// yes, no
			'summary'        => 'yes',
			// strip title
			'summarylength'  => '',
			// strip summary after X char
			'thumb'          => 'auto',
			// yes, no, auto
			'default'        => '',
			// default thumb URL if no image found (only if thumb is set to yes or auto)
			'size'           => '',
			// thumbs pixel size
			'keywords_title' => '',
			// only display item if title contains specific keywords (comma-separated list/case sensitive)
		), $atts, 'feedzy_default' );
		$sc = array_merge( $sc, apply_filters( 'feedzy_get_short_code_attributes_filter', $atts ) );

		return $sc;
	}

	/**
	 * Validate feeds attribute.
	 *
	 * @param string $raw Url or list of urls.
	 *
	 * @return mixed|void Urls of the feeds.
	 */
	public function normalize_urls( $raw ) {
		$feeds   = apply_filters( 'feedzy_process_feed_source', $raw );
		$feedURL = apply_filters( 'feedzy_get_feed_url', $feeds );

		return $feedURL;
	}

	/**
	 * Fetch the content feed from a group of urls.
	 *
	 * @param array $feedURL The feeds urls to fetch content from.
	 *
	 * @return SimplePie|string|void|WP_Error The feed resource.
	 */
	public function fetch_feed( $feedURL ) {
		// Load SimplePie if not already
		if ( ! class_exists( 'SimplePie' ) ) {
			require_once( ABSPATH . WPINC . '/feed.php' );
		}
		// Load SimplePie Instance
		$feed = fetch_feed( $feedURL );

		// Report error when is an error loading the feed
		if ( is_wp_error( $feed ) ) {
			// Fallback for different edge cases.
			if ( is_array( $feedURL ) ) {
				$feedURL = array_map( 'html_entity_decode', $feedURL );
			} else {
				$feedURL = html_entity_decode( $feedURL );
			}
			$feed = fetch_feed( $feedURL );
			if ( is_wp_error( $feed ) ) {
				return __( 'An error occured for when trying to retrieve feeds! Check the URL\'s provided as feed sources.', 'feedzy-rss-feeds' );
			}
		}
		return $feed;
	}

	/**
	 * Sanitizes the shortcode array and sets the defaults
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   array  $sc The shorcode attributes array.
	 * @param   string $feedURL The feed url.
	 *
	 * @return  mixed
	 */
	public function sanitize_attr( $sc, $feedURL ) {
		if ( $sc['max'] == '0' ) {
			$sc['max'] = '999';
		} elseif ( empty( $sc['max'] ) || ! ctype_digit( $sc['max'] ) ) {
			$sc['max'] = '5';
		}
		if ( empty( $sc['size'] ) || ! ctype_digit( $sc['size'] ) ) {
			$sc['size'] = '150';
		}
		if ( ! empty( $sc['title'] ) && ! ctype_digit( $sc['title'] ) ) {
			$sc['title'] = '';
		}
		if ( ! empty( $sc['keywords_title'] ) ) {
			$sc['keywords_title'] = rtrim( $sc['keywords_title'], ',' );
			$sc['keywords_title'] = array_map( 'trim', explode( ',', $sc['keywords_title'] ) );
		}
		if ( ! empty( $sc['keywords_ban'] ) ) {
			$sc['keywords_ban'] = rtrim( $sc['keywords_ban'], ',' );
			$sc['keywords_ban'] = array_map( 'trim', explode( ',', $sc['keywords_ban'] ) );
		}
		if ( ! empty( $sc['summarylength'] ) && ! ctype_digit( $sc['summarylength'] ) ) {
			$sc['summarylength'] = '';
		}
		if ( empty( $sc['default'] ) ) {
			$sc['default'] = apply_filters( 'feedzy_default_image', $sc['default'], $feedURL );
		}

		return $sc;
	}

	/**
	 * Render the content to be displayed
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   array  $sc The shorcode attributes array.
	 * @param   object $feed The feed object.
	 * @param   string $content The original content.
	 * @param   string $feedURL The feed url.
	 *
	 * @return  string
	 */
	public function render_content( $sc, $feed, $content = '', $feedURL ) {
		$count                   = 0;
		$sizes                   = array(
			'width'  => $sc['size'],
			'height' => $sc['size'],
		);
		$sizes                   = apply_filters( 'feedzy_thumb_sizes', $sizes, $feedURL );
		$feed_title['use_title'] = false;
		if ( $sc['feed_title'] == 'yes' ) {
			$feed_title              = $this->get_feed_title_filter( $feed );
			$feed_title['use_title'] = true;
		}
		// Display the error message
		if ( $feed->error() ) {
			$content .= apply_filters( 'feedzy_default_error', $feed->error(), $feedURL );
		}
		$feed_items = apply_filters( 'feedzy_get_feed_array', array(), $sc, $feed, $feedURL, $sizes );
		$content    = '<div class="feedzy-rss">';
		if ( $feed_title['use_title'] ) {
			$content .= '<div class="rss_header">';
			$content .= '<h2><a href="' . $feed->get_permalink() . '" class="rss_title">' . html_entity_decode( $feed->get_title() ) . '</a> <span class="rss_description"> ' . $feed->get_description() . '</span></h2>';
			$content .= '</div>';
		}
		$content .= '<ul>';
		foreach ( $feed_items as $item ) {
			$content .= '
            <li ' . $item['itemAttr'] . '>
                ' . ( ( ! empty( $item['item_img'] ) && $sc['thumb'] != 'no' ) ? '
                <div class="' . $item['item_img_class'] . '" style="' . $item['item_img_style'] . '">
					<a href="' . $item['item_url'] . '" target="' . $item['item_url_target'] . '" title="' . $item['item_url_title'] . '"   style="' . $item['item_img_style'] . '">
						' . $item['item_img'] . '
					</a>
				</div>' : '' ) . '
				<span class="title">
					<a href="' . $item['item_url'] . '" target="' . $item['item_url_target'] . '">
						' . $item['item_title'] . '
					</a>
				</span>
				<div class="' . $item['item_content_class'] . '" style="' . $item['item_content_style'] . '">
					' . ( ! empty( $item['item_meta'] ) ? '<small>
						' . $item['item_meta'] . '
					</small>' : '' ) . '
					' . ( ! empty( $item['item_description'] ) ? '<p>' . $item['item_description'] . '</p>' : '' ) . '
				</div>
			</li>
            ';
		}
		$content .= '</ul> </div>';
		$content = apply_filters( 'feedzy_global_output', $content, $sc, $feed_title, $feed_items );

		return $content;
	}

	/**
	 * Retrive the filter rss title array
	 *
	 * @since   3.0.0
	 * @access  private
	 *
	 * @param   object $feed The feed object.
	 *
	 * @return array
	 */
	private function get_feed_title_filter( $feed ) {
		return array(
			'rss_url'               => $feed->get_permalink(),
			'rss_title_class'       => 'rss_title',
			'rss_title'             => html_entity_decode( $feed->get_title() ),
			'rss_description_class' => 'rss_description',
			'rss_description'       => $feed->get_description(),
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
		$feedURL = '';
		if ( ! empty( $feeds ) ) {
			$feeds   = rtrim( $feeds, ',' );
			$feeds   = explode( ',', $feeds );
			$feedURL = array();
			// Remove SSL from HTTP request to prevent fetching errors
			foreach ( $feeds as $feed ) {
				$feedURL[] = preg_replace( '/^https:/i', 'http:', $feed );
			}
			if ( count( $feedURL ) === 1 ) {
				$feedURL = $feedURL[0];
			}
		}

		return $feedURL;
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
	 * @param   string $feedURL The feed URL source/s.
	 * @param   array  $sizes Sizes array.
	 *
	 * @return array
	 */
	public function get_feed_array( $feed_items = array(), $sc, $feed, $feedURL, $sizes ) {
		$count = 0;
		$items = apply_filters( 'feedzy_feed_items', $feed->get_items(), $feedURL );
		foreach ( (array) $items as $item ) {
			if ( trim( $item->get_title() ) != '' ) {
				$continue = apply_filters( 'feedzy_item_keyword', true, $sc, $item, $feedURL );
				if ( $continue == true ) {
					// Count items
					if ( $count >= $sc['max'] ) {
						break;
					}
					$itemAttr                         = apply_filters( 'feedzy_item_attributes', $itemAttr = '', $sizes, $item, $feedURL, $sc );
					$feed_items[ $count ]             = $this->get_feed_item_filter( $sc, $sizes, $item, $feedURL );
					$feed_items[ $count ]['itemAttr'] = $itemAttr;
					$count ++;
				}
			}
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
	 * @param   string $feedURL The feed url.
	 *
	 * @return array
	 */
	private function get_feed_item_filter( $sc, $sizes, $item, $feedURL ) {
		$itemLink = $item->get_permalink();
		$newLink  = apply_filters( 'feedzy_item_url_filter', $itemLink, $sc );
		// Fetch image thumbnail
		if ( $sc['thumb'] == 'yes' || $sc['thumb'] == 'auto' ) {
			$theThumbnail = $this->feedzy_retrieve_image( $item );
		}
		if ( $sc['thumb'] == 'yes' || $sc['thumb'] == 'auto' ) {
			$contentThumb = '';
			if ( ( ! empty( $theThumbnail ) && $sc['thumb'] == 'auto' ) || $sc['thumb'] == 'yes' ) {
				if ( ! empty( $theThumbnail ) ) {
					$theThumbnail = $this->feedzy_image_encode( $theThumbnail );
					$contentThumb .= '<span class="fetched" style="background-image:  url(' . $theThumbnail . ');" title="' . $item->get_title() . '"></span>';
				}
				if ( $sc['thumb'] == 'yes' ) {
					$contentThumb .= '<span class="default" style="background-image:url(' . $sc['default'] . ');" title="' . $item->get_title() . '"></span>';
				}
			}
			$contentThumb = apply_filters( 'feedzy_thumb_output', $contentThumb, $feedURL, $sizes );
		} else {
			$contentThumb = '';
			$contentThumb .= '<span class="default" style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px; background-image:url(' . $sc['default'] . ');" title="' . $item->get_title() . '"></span>';
			$contentThumb = apply_filters( 'feedzy_thumb_output', $contentThumb, $feedURL, $sizes );
		}
		$contentTitle = '';
		if ( is_numeric( $sc['title'] ) && strlen( $item->get_title() ) > $sc['title'] ) {
			$contentTitle .= preg_replace( '/\s+?(\S+)?$/', '', substr( $item->get_title(), 0, $sc['title'] ) ) . '...';
		} else {
			$contentTitle .= $item->get_title();
		}
		$contentTitle = apply_filters( 'feedzy_title_output', $contentTitle, $feedURL );
		// Define Meta args
		$metaArgs = array(
			'author'      => true,
			'date'        => true,
			'date_format' => get_option( 'date_format' ),
			'time_format' => get_option( 'time_format' ),
		);
		// Filter: feedzy_meta_args
		$metaArgs    = apply_filters( 'feedzy_meta_args', $metaArgs, $feedURL );
		$contentMeta = '';
		if ( $sc['meta'] == 'yes' && ( $metaArgs['author'] || $metaArgs['date'] ) ) {
			$contentMeta = '';
			if ( $item->get_author() && $metaArgs['author'] ) {
				$author = $item->get_author();
				if ( ! $authorName = $author->get_name() ) {
					$authorName = $author->get_email();
				}
				if ( $authorName ) {
					$domain      = parse_url( $newLink );
					$authorURL   = '//' . $domain['host'];
					$authorURL   = apply_filters( 'feedzy_author_url', $authorURL, $authorName, $feedURL );
					$contentMeta .= __( 'by', 'feedzy-rss-feeds' ) . ' <a href="' . $authorURL . '" target="' . $sc['target'] . '" title="' . $domain['host'] . '" >' . $authorName . '</a> ';
				}
			}
			if ( $metaArgs['date'] ) {
				$date_time   = $item->get_date( 'U' );
				$date_time   = apply_filters( 'feedzy_feed_timestamp', $date_time, $feedURL );
				$contentMeta .= __( 'on', 'feedzy-rss-feeds' ) . ' ' . date_i18n( $metaArgs['date_format'], $date_time );
				$contentMeta .= ' ';
				$contentMeta .= __( 'at', 'feedzy-rss-feeds' ) . ' ' . date_i18n( $metaArgs['time_format'], $date_time );
			}
		}
		$contentMeta    = apply_filters( 'feedzy_meta_output', $contentMeta, $feedURL );
		$contentSummary = '';
		if ( $sc['summary'] == 'yes' ) {
			$contentSummary = '';
			$description    = $item->get_description();
			$description    = apply_filters( 'feedzy_summary_input', $description, $item->get_content(), $feedURL );
			if ( is_numeric( $sc['summarylength'] ) && strlen( $description ) > $sc['summarylength'] ) {
				$contentSummary .= preg_replace( '/\s+?(\S+)?$/', '', substr( $description, 0, $sc['summarylength'] ) ) . ' […]';
			} else {
				$contentSummary .= $description . ' […]';
			}
			$contentSummary = apply_filters( 'feedzy_summary_output', $contentSummary, $newLink, $feedURL );
		}
		$itemArray = array(
			'item_img_class'     => 'rss_image',
			'item_img_style'     => 'width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px;',
			'item_url'           => $newLink,
			'item_url_target'    => $sc['target'],
			'item_url_title'     => $item->get_title(),
			'item_img'           => $contentThumb,
			'item_img_path'      => $this->feedzy_retrieve_image( $item ),
			'item_title'         => $contentTitle,
			'item_content_class' => 'rss_content',
			'item_content_style' => '',
			'item_meta'          => $contentMeta,
			'item_date'          => $item->get_date( 'U' ),
			'item_author'        => $item->get_author(),
			'item_description'   => $contentSummary,
		);
		$itemArray = apply_filters( 'feedzy_item_filter', $itemArray, $item );

		return $itemArray;
	}

	/**
	 * Retrive image from the item object
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   object $item The item object.
	 *
	 * @return  string
	 */
	public function feedzy_retrieve_image( $item ) {
		$theThumbnail = '';
		if ( $enclosures = $item->get_enclosures() ) {
			foreach ( (array) $enclosures as $enclosure ) {
				// Item thumbnail
				if ( $thumbnail = $enclosure->get_thumbnail() ) {
					$theThumbnail = $thumbnail;
				}
				if ( isset( $enclosure->thumbnails ) ) {
					foreach ( (array) $enclosure->thumbnails as $thumbnail ) {
						$theThumbnail = $thumbnail;
					}
				}
				if ( $thumbnail = $enclosure->embed() ) {
					$pattern = '/https?:\/\/.*\.(?:jpg|JPG|jpeg|JPEG|jpe|JPE|gif|GIF|png|PNG)/i';
					if ( preg_match( $pattern, $thumbnail, $matches ) ) {
						$theThumbnail = $matches[0];
					}
				}
				foreach ( (array) $enclosure->get_link() as $thumbnail ) {
					$pattern = '/https?:\/\/.*\.(?:jpg|JPG|jpeg|JPEG|jpe|JPE|gif|GIF|png|PNG)/i';
					$imgsrc  = $thumbnail;
					if ( preg_match( $pattern, $imgsrc, $matches ) ) {
						$theThumbnail = $matches[0];
						break;
					}
				}
				// Break loop if thumbnail is found
				if ( ! empty( $theThumbnail ) ) {
					break;
				}
			}
		}
		// xmlns:itunes podcast
		if ( empty( $theThumbnail ) ) {
			$data = $item->get_item_tags( 'http://www.itunes.com/dtds/podcast-1.0.dtd', 'image' );
			if ( isset( $data['0']['attribs']['']['href'] ) && ! empty( $data['0']['attribs']['']['href'] ) ) {
				$theThumbnail = $data['0']['attribs']['']['href'];
			}
		}
		// Content image
		if ( empty( $theThumbnail ) ) {
			$feedDescription = $item->get_content();
			$theThumbnail    = $this->feedzy_return_image( $feedDescription );

		}
		// Description image
		if ( empty( $theThumbnail ) ) {
			$feedDescription = $item->get_description();
			$theThumbnail    = $this->feedzy_return_image( $feedDescription );
		}
		return $theThumbnail;
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
		preg_match( $pattern, $img, $matches );
		if ( isset( $matches[0] ) ) {
			$blacklistCount = 0;
			foreach ( $matches as $matche ) {
				$link      = $this->feedzy_scrape_image( $matche );
				$blacklist = array();
				$blacklist = apply_filters( 'feedzy_feed_blacklist_images', $this->feedzy_blacklist_images( $blacklist ) );
				foreach ( $blacklist as $string ) {
					if ( strpos( (string) $link, $string ) !== false ) {
						$blacklistCount ++;
					}
				}
				if ( $blacklistCount == 0 ) {
					break;
				}
			}
			if ( $blacklistCount == 0 ) {
				return $link;
			}
		}

		return '';
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
		preg_match( $pattern, $string, $link );
		if ( isset( $link[1] ) ) {
			$link = urldecode( $link[1] );
		}

		return $link;
	}

	/**
	 * List blacklisted images to prevent fetching emoticons
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   array $blacklist An array with blacklisted resources.
	 *
	 * @return  array
	 */
	public function feedzy_blacklist_images( $blacklist ) {
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

		return $blacklist;
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
		// Check if img url is set as an URL parameter
		$url_tab = parse_url( $string );
		if ( isset( $url_tab['query'] ) ) {
			preg_match_all( '/(http|https):\/\/[^ ]+(\.gif|\.GIF|\.jpg|\.JPG|\.jpeg|\.JPEG|\.png|\.PNG)/', $url_tab['query'], $imgUrl );
			if ( isset( $imgUrl[0][0] ) ) {
				$string = $imgUrl[0][0];
			}
		}
		// Encode image name only en keep extra parameters
		$query   = $extention = '';
		$url_tab = parse_url( $string );
		if ( isset( $url_tab['query'] ) ) {
			$query = '?' . $url_tab['query'];
		}
		$path_parts = pathinfo( $string );
		$path       = $path_parts['dirname'];
		$file       = rawurldecode( $path_parts['filename'] );
		$extention  = pathinfo( $url_tab['path'], PATHINFO_EXTENSION );
		if ( ! empty( $extention ) ) {
			$extention = '.' . $extention;
		} else {
			if ( isset( $path_parts['extension'] ) ) {
				$extention = '.' . $path_parts['extension'];
			}
		}

		// Return a well encoded image url
		return $path . '/' . rawurlencode( $file ) . $extention . $query;
	}

	/**
	 * Render the form template for tinyMCE popup.
	 * Called via ajax.
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function get_tinymce_form() {
		die( include FEEDZY_ABSPATH . '/form/form.php' );
	}

	/**
	 * Method used to render upsell page.
	 *
	 * @since   3.0.12
	 * @access  public
	 */
	public function render_upsell() {
		$this->load_layout( 'feedzy-upsell' );
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
		wp_enqueue_style( 'feedzy-upsell', FEEDZY_ABSURL . '/includes/layouts/css/upsell.css' );
		include( FEEDZY_ABSPATH . '/includes/layouts/' . $layout_name . '.php' );
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
