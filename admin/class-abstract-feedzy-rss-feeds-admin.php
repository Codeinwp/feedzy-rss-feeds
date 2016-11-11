<?php
/**
 * The Abstract class with reusable functionality of the plugin.
 *
 * @link       http://themeisle.com
 * @since      3.0.0
 *
 * @package    Feedzy_Rss_Feeds
 * @subpackage Feedzy_Rss_Feeds/admin
 */
/**
 * The Feedzy RSS functions of the plugin.
 *
 * Abstract class containing functions for the Feedzy Admin Class
 *
 * @package    Feedzy_Rss_Feeds
 * @subpackage Feedzy_Rss_Feeds/admin
 * @author     Themeisle <friends@themeisle.com>
 */
abstract class Feedzy_Rss_Feeds_Abstract {


	/**
	 * Defines the default image to use on RSS Feeds
	 *
	 * @param   string $imageSrc The image source, currently not used.
	 * @return  string
	 */
	public function feedzy_define_default_image( $imageSrc ) {
		return plugins_url( '../img/feedzy-default.jpg', __FILE__ );
	}

	/**
	 * Defines the default error notice
	 *
	 * Logs error to the log file
	 * Returns the error message
	 *
	 * @param   object $error The error Object.
	 * @param   string $feedURL The feed URL.
	 * @return  string
	 */
	public function feedzy_default_error_notice( $error, $feedURL  ) {
		error_log( 'Feedzy RSS Feeds - related feed: ' . $feedURL . ' - Error message: ' . $this->feedzy_array_obj_string( $error ) );
		return '<div id="message" class="error" data-error"' . esc_attr( $this->feedzy_array_obj_string( $error ) ) . '"><p>' . __( 'Sorry, this feed is currently unavailable or does not exists anymore.', 'feedzy-rss-feeds-translate' ) . '</p></div>';
	}

	/**
	 * Converts an object to string
	 *
	 * @param   object $error The error Object.
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
	 * @param   string $itemAttr   The item attribute.
	 * @param   array  $sizes      An array with the current sizes.
	 * @return  string
	 */
	public function feedzy_add_item_padding( $itemAttr, $sizes ) {
		$paddinTop = number_format( ( 15 / 150 ) * $sizes['height'], 0 );
		$paddinBottom = number_format( ( 25 / 150 ) * $sizes['height'], 0 );
		$stylePadding = ' style="padding: ' . $paddinTop . 'px 0 ' . $paddinBottom . 'px"';
		return $itemAttr . $stylePadding;
	}

	/**
	 * Appends classes to the feed item
	 *
	 * @param   string $itemAttr   The item attribute.
	 * @return  string
	 */
	public function feedzy_classes_item( $itemAttr ) {
		$classes = array( 'rss_item' );
		$classes = apply_filters( 'feedzy_add_classes_item', $classes );
		$classes = ' class="' . implode( ' ', $classes ) . '"';
		return $itemAttr . $classes;
	}

	/**
	 * Retrive image from the item object
	 *
	 * @param   object $item   The item object.
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
					$pattern = '/https?:\/\/.*\.(?:jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/iU';
					if ( preg_match( $pattern, $thumbnail, $matches ) ) {
						$theThumbnail = $matches[0];
					}
				}

				foreach ( (array) $enclosure->get_link() as $thumbnail ) {
					$pattern = '/https?:\/\/.*\.(?:jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/iU';
					$imgsrc = $thumbnail;

					if ( preg_match( $pattern, $imgsrc, $matches )  ) {
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
			$theThumbnail = $this->feedzy_return_image( $feedDescription );
		}

		// Description image
		if ( empty( $theThumbnail ) ) {
			$feedDescription = $item->get_description();
			$theThumbnail = $this->feedzy_return_image( $feedDescription );
		}

		dbgx_trace_var( $theThumbnail, false );

		return $theThumbnail;
	}

	/**
	 * Get an image from a string
	 *
	 * @param   string $string     A string with an <img/> tag.
	 * @return  string
	 */
	public function feedzy_return_image( $string ) {
		$img = html_entity_decode( $string, ENT_QUOTES, 'UTF-8' );
		$pattern = '/<img[^>]+\>/i';
		preg_match( $pattern, $img, $matches );
		if ( isset( $matches[0] ) ) {
			$blacklistCount = 0;
			foreach ( $matches as $matche ) {
				$link = $this->feedzy_scrape_image( $matche );
				$blacklist = array();
				$blacklist = apply_filters( 'feedzy_feed_blacklist_images', $this->feedzy_blacklist_images( $blacklist ) );
				foreach ( $blacklist as $string ) {
					if ( strpos( (string) $link, $string ) !== false ) {
						$blacklistCount++;
					}
				}
				if ( $blacklistCount == 0 ) { break;
				}
			}
			if ( $blacklistCount == 0 ) { return $link;
			}
		}
		return '';
	}

	/**
	 * Scrape an image for link from a string with an <img/>
	 *
	 * @param   string $string  A string with an <img/> tag.
	 * @param   string $link    The link to search for.
	 * @return  string
	 */
	function feedzy_scrape_image( $string, $link = '' ) {
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
	 * @param   array $blacklist  An array with blacklisted resources.
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
	 * @param   string $string  A string containing the image URL.
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
		$query = $extention = '';
		$url_tab = parse_url( $string );
		if ( isset( $url_tab['query'] ) ) {
			$query = '?' . $url_tab['query'];
		}
		$path_parts = pathinfo( $string );
		$path = $path_parts['dirname'];
		$file = rawurldecode( $path_parts['filename'] );
		$extention = pathinfo( $url_tab['path'], PATHINFO_EXTENSION );
		if ( ! empty( $extention ) ) {
			$extention = '.' . $extention;
		}

		// Return a well encoded image url
		return $path . '/' . rawurlencode( $file ) . $extention . $query;
	}

	/**
	 * Filter feed description input
	 *
	 * @param   string $description  The feed description.
	 * @param   string $content  The feed description.
	 * @param   string $feedURL  The feed URL.
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
	 * @param   boolean $continue A boolean to stop the script.
	 * @param   array   $keywords_title  The keywords for title.
	 * @param   object  $item  The feed item.
	 * @param   string  $feedURL  The feed URL.
	 * @return  boolean
	 */
	public function feedzy_feed_item_keywords_title( $continue, $keywords_title, $item, $feedURL ) {
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
	 * Insert cover picture to main rss feed content
	 *
	 * @param   string $content  The item feed content.
	 * @return  string
	 */
	public function feedzy_insert_thumbnail_rss( $content ) {
		global $post;
		if ( has_post_thumbnail( $post->ID ) ) {
			$content = '' . get_the_post_thumbnail( $post->ID, 'thumbnail' ) . '' . $content;
		}
		return $content;
	}

	/**
	 * Include cover picture (medium) to rss feed enclosure
	 * and media:content
	 */
	public function feedzy_include_thumbnail_rss() {
		global $post;

		if ( has_post_thumbnail( $post->ID ) ) {

			$postThumbnailId = get_post_thumbnail_id( $post->ID );
			$attachmentMeta = wp_get_attachment_metadata( $postThumbnailId );
			$imageUrl = wp_get_attachment_image_src( $postThumbnailId, 'medium' );

			echo '<enclosure url="' . $imageUrl[0] . '" length="' . filesize( get_attached_file( $postThumbnailId ) ) . '" type="image/jpg" />';
			echo '<media:content url="' . $imageUrl[0] . '" width="' . $attachmentMeta['sizes']['medium']['width'] . '" height="' . $attachmentMeta['sizes']['medium']['height'] . '" medium="image" type="' . $attachmentMeta['sizes']['medium']['mime-type'] . '" />';

		}
	}

	/**
	 * Main shortcode function
	 *
	 * @param   array  $atts  Shortcode attributes.
	 * @param   string $content  The item feed content.
	 * @return  mixed
	 */
	public function feedzy_rss( $atts, $content = '' ) {
		$count = 0;

		// Load SimplePie if not already
		if ( ! class_exists( 'SimplePie' ) ) {
			require_once( ABSPATH . WPINC . '/class-feed.php' );
		}

		// Retrieve & extract shorcode parameters
		$shortcode_atts = shortcode_atts( array(
			'feeds' => '', 			// comma separated feeds url
			'max' => '5', 			// number of feeds items (0 for unlimited)
			'feed_title' => 'yes', 	// display feed title yes/no
			'target' => '_blank', 	// _blank, _self
			'title' => '', 			// strip title after X char
			'meta' => 'yes', 		// yes, no
			'summary' => 'yes', 	// strip title
			'summarylength' => '', 	// strip summary after X char
			'thumb' => 'yes', 		// yes, no, auto
			'default' => '', 		// default thumb URL if no image found (only if thumb is set to yes or auto)
			'size' => '', 			// thumbs pixel size
			'keywords_title' => '',// only display item if title contains specific keywords (comma-separated list/case sensitive)
		), $atts, 'feedzy_default' );

		// Use "shortcode_atts_feedzy_default" filter to edit shortcode parameters default values or add your owns.
		if ( ! empty( $shortcode_atts['feeds'] ) ) {
			$shortcode_atts['feeds'] = rtrim( $shortcode_atts['feeds'], ',' );
			$shortcode_atts['feeds'] = explode( ',', $shortcode_atts['feeds'] );

			// Remove SSL from HTTP request to prevent fetching errors
			foreach ( $shortcode_atts['feeds'] as $feed ) {
				$feedURL[] = preg_replace( '/^https:/i', 'http:', $feed );
			}

			if ( count( $feedURL ) === 1 ) {
				$feedURL = $feedURL[0];
			}
		}

		if ( $shortcode_atts['max'] == '0' ) {
			$shortcode_atts['max'] = '999';
		} elseif ( empty( $shortcode_atts['max'] ) || ! ctype_digit( $shortcode_atts['max'] ) ) {
			$shortcode_atts['max'] = '5';
		}

		if ( empty( $shortcode_atts['size'] ) || ! ctype_digit( $shortcode_atts['size'] ) ) {
			$shortcode_atts['size'] = '150';
		}
		$sizes = array( 'width' => $shortcode_atts['size'], 'height' => $shortcode_atts['size'] );
		$sizes = apply_filters( 'feedzy_thumb_sizes', $sizes, $feedURL );

		if ( ! empty( $shortcode_atts['title'] ) && ! ctype_digit( $shortcode_atts['title'] ) ) {
			$shortcode_atts['title'] = '';
		}

		if ( ! empty( $shortcode_atts['keywords_title'] ) ) {
			$shortcode_atts['keywords_title'] = rtrim( $shortcode_atts['keywords_title'], ',' );
			$shortcode_atts['keywords_title'] = array_map( 'trim', explode( ',', $shortcode_atts['keywords_title'] ) );
		}

		if ( ! empty( $shortcode_atts['summarylength'] ) && ! ctype_digit( $shortcode_atts['summarylength'] ) ) {
			$shortcode_atts['summarylength'] = '';
		}

		if ( empty( $shortcode_atts['default'] ) ) {
			$shortcode_atts['default'] = apply_filters( 'feedzy_default_image', $shortcode_atts['default'], $feedURL );
		}

		// Load SimplePie Instance
		$feed = new SimplePie();
		$feed -> set_feed_url( $feedURL );
		$feed -> enable_cache( true );
		$feed -> enable_order_by_date( true );
		$feed -> set_cache_class( 'WP_Feed_Cache' );
		$feed -> set_file_class( 'WP_SimplePie_File' );
		$feed -> set_cache_duration( apply_filters( 'wp_feed_cache_transient_lifetime', 7200, $feedURL ) );
		do_action_ref_array( 'wp_feed_options', array( $feed, $feedURL ) );
		$feed -> strip_comments( true );
		$feed -> strip_htmltags( array( 'base', 'blink', 'body', 'doctype', 'embed', 'font', 'form', 'frame', 'frameset', 'html', 'iframe', 'input', 'marquee', 'meta', 'noscript', 'object', 'param', 'script', 'style' ) );
		$feed -> init();
		$feed -> handle_content_type();

		// Display the error message
		if ( $feed -> error() ) {
			$content .= apply_filters( 'feedzy_default_error', $feed -> error(), $feedURL );
		}

		$content .= '<div class="feedzy-rss">';

		if ( $shortcode_atts['feed_title'] == 'yes' ) {
			$content .= '<div class="rss_header">';
			$content .= '<h2><a href="' . $feed->get_permalink() . '" class="rss_title">' . html_entity_decode( $feed->get_title() ) . '</a> <span class="rss_description"> ' . $feed->get_description() . '</span></h2>';
			$content .= '</div>';
		}

		$content .= '<ul>';

		// Loop through RSS feed
		$items = apply_filters( 'feedzy_feed_items', $feed->get_items(), $feedURL );
		foreach ( (array) $items as $item ) {

			$continue = apply_filters( 'feedzy_item_keyword', true, $shortcode_atts['keywords_title'], $item, $feedURL );

			if ( $continue == true ) {
				// Count items
				if ( $count >= $shortcode_atts['max'] ) {
					break;
				}
				$count++;

				// Fetch image thumbnail
				if ( $shortcode_atts['thumb'] == 'yes' || $shortcode_atts['thumb'] == 'auto' ) {
					$theThumbnail = $this->feedzy_retrieve_image( $item );
				}

				$itemAttr = apply_filters( 'feedzy_item_attributes', $itemAttr = '', $sizes, $item, $feedURL );

				// Build element DOM
				$content .= '<li ' . $itemAttr . '>';

				if ( $shortcode_atts['thumb'] == 'yes' || $shortcode_atts['thumb'] == 'auto' ) {
					$contentThumb = '';

					if ( ( ! empty( $theThumbnail ) && $shortcode_atts['thumb'] == 'auto' ) || $shortcode_atts['thumb'] == 'yes' ) {

						$contentThumb .= '<div class="rss_image" style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px;">';
						$contentThumb .= '<a href="' . $item->get_permalink() . '" target="' . $shortcode_atts['target'] . '" title="' . $item->get_title() . '" >';

						if ( ! empty( $theThumbnail ) ) {
							$theThumbnail = $this->feedzy_image_encode( $theThumbnail );
							$contentThumb .= '<span class="default" style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px; background-image:  url(' . $shortcode_atts['default'] . ');" alt="' . $item->get_title() . '"></span>';
							$contentThumb .= '<span class="fetched" style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px; background-image:  url(' . $theThumbnail . ');" alt="' . $item->get_title() . '"></span>';
						} elseif ( empty( $theThumbnail ) && $shortcode_atts['thumb'] == 'yes' ) {
							$contentThumb .= '<span style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px; background-image:url(' . $shortcode_atts['default'] . ');" alt="' . $item->get_title() . '"></span>';
						}

						$contentThumb .= '</a>';
						$contentThumb .= '</div>';

					}

					// Filter: feedzy_thumb_output
					$content .= apply_filters( 'feedzy_thumb_output', $contentThumb, $feedURL );

				}

				$contentTitle = '';
				$contentTitle .= '<span class="title"><a href="' . $item->get_permalink() . '" target="' . $shortcode_atts['target'] . '">';

				if ( is_numeric( $shortcode_atts['title'] ) && strlen( $item->get_title() ) > $shortcode_atts['title'] ) {
					$contentTitle .= preg_replace( '/\s+?(\S+)?$/', '', substr( $item->get_title(), 0, $shortcode_atts['title'] ) ) . '...';
				} else {
					$contentTitle .= $item->get_title();
				}

				$contentTitle .= '</a></span>';

				// Filter: feedzy_title_output
				$content .= apply_filters( 'feedzy_title_output', $contentTitle, $feedURL );
				$content .= '<div class="rss_content">';

				// Define Meta args
				$metaArgs = array(
					'author' => true,
					'date' => true,
					'date_format' => get_option( 'date_format' ),
					'time_format' => get_option( 'time_format' ),
				);

				// Filter: feedzy_meta_args
				$metaArgs = apply_filters( 'feedzy_meta_args', $metaArgs, $feedURL );

				if ( $shortcode_atts['meta'] == 'yes' && ( $metaArgs['author'] || $metaArgs['date'] ) ) {

					$contentMeta = '';
					$contentMeta .= '<small>' . __( 'Posted', 'feedzy-rss-feeds-translate' ) . ' ';

					if ( $item->get_author() && $metaArgs['author'] ) {
						$author = $item->get_author();
						if ( ! $authorName = $author->get_name() ) {
							$authorName = $author->get_email();
						}
						if ( $authorName ) {
							$domain = parse_url( $item->get_permalink() );
							$contentMeta .= __( 'by', 'feedzy-rss-feeds-translate' ) . ' <a href="http://' . $domain['host'] . '" target="' . $shortcode_atts['target'] . '" title="' . $domain['host'] . '" >' . $authorName . '</a> ';
						}
					}

					if ( $metaArgs['date'] ) {
						$contentMeta .= __( 'on', 'feedzy-rss-feeds-translate' ) . ' ' . date_i18n( $metaArgs['date_format'], $item->get_date( 'U' ) );
						$contentMeta .= ' ';
						$contentMeta .= __( 'at', 'feedzy-rss-feeds-translate' ) . ' ' . date_i18n( $metaArgs['time_format'], $item->get_date( 'U' ) );
					}

					$contentMeta .= '</small>';

					// Filter: feedzy_meta_output
					$content .= apply_filters( 'feedzy_meta_output', $contentMeta, $feedURL );

				}
				if ( $shortcode_atts['summary'] == 'yes' ) {
					$contentSummary = '';
					$contentSummary .= '<p>';

					// Filter: feedzy_summary_input
					$description = $item->get_description();
					$description = apply_filters( 'feedzy_summary_input', $description, $item->get_content(), $feedURL );

					if ( is_numeric( $shortcode_atts['summarylength'] ) && strlen( $description ) > $shortcode_atts['summarylength'] ) {
						$contentSummary .= preg_replace( '/\s+?(\S+)?$/', '', substr( $description, 0, $shortcode_atts['summarylength'] ) ) . ' […]';
					} else {
						$contentSummary .= $description . ' […]';
					}

					$contentSummary .= '</p>';

					// Filter: feedzy_summary_output
					$content .= apply_filters( 'feedzy_summary_output', $contentSummary, $item->get_permalink(), $feedURL );

				}

				$content .= '</div>';
				$content .= '</li>';

			}
		}

		$content .= '</ul>';
		$content .= '</div>';
		return apply_filters( 'feedzy_global_output', $content, $feedURL );
	}
}
