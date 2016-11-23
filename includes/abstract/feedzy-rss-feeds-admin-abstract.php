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
		error_log( 'Feedzy RSS Feeds - related feed: ' . print_r( $feedURL ) . ' - Error message: ' . $this->feedzy_array_obj_string( $error ) );
		return '<div id="message" class="error" data-error"' . esc_attr( $this->feedzy_array_obj_string( $error ) ) . '"><p>' . __( 'Sorry, this feed is currently unavailable or does not exists anymore.', 'feedzy_rss_translate' ) . '</p></div>';
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
	 * Returns the attributes of the shortcode
	 *
	 * @param array $atts The attributes passed by WordPress.
	 * @return array
	 */
	public function get_short_code_attributes( $atts ) {
		// Retrieve & extract shorcode parameters
		$sc = shortcode_atts( array(
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
			'keywords_title' => '', // only display item if title contains specific keywords (comma-separated list/case sensitive)
		), $atts, 'feedzy_default' );

		return $sc;
	}

	/**
	 * Get the feed url based on the feeds passed from the shortcode attribute.
	 *
	 * @param string $feeds The feeds from the shortcode attribute.
	 * @return array|mixed
	 */
	public function get_feed_url( $feeds ) {
		// Use "shortcode_atts_feedzy_default" filter to edit shortcode parameters default values or add your owns.
		if ( ! empty( $feeds ) ) {
			$feeds = rtrim( $feeds, ',' );
			$feeds = explode( ',', $feeds );

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
	 * Sanitizes the shortcode array and sets the defaults
	 *
	 * @param array  $sc         The shorcode attributes array.
	 * @param string $feedURL    The feed url.
	 * @return mixed
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
	 * @param array  $sc         The shorcode attributes array.
	 * @param object $feed       The feed object.
	 * @param string $content    The original content.
	 * @param string $feedURL    The feed url.
	 * @return string
	 */
	public function render_content( $sc, $feed, $content, $feedURL ) {
		$count = 0;

		$sc = $this->sanitize_attr( $sc, $feedURL );

		$sizes = array( 'width' => $sc['size'], 'height' => $sc['size'] );
		$sizes = apply_filters( 'feedzy_thumb_sizes', $sizes, $feedURL );

		// Display the error message
		if ( $feed -> error() ) {
			$content .= apply_filters( 'feedzy_default_error', $feed -> error(), $feedURL );
		}

		$content .= '<div class="feedzy-rss">';

		if ( $sc['feed_title'] == 'yes' ) {
			$content .= '<div class="rss_header">';
			$content .= '<h2><a href="' . $feed->get_permalink() . '" class="rss_title">' . html_entity_decode( $feed->get_title() ) . '</a> <span class="rss_description"> ' . $feed->get_description() . '</span></h2>';
			$content .= '</div>';
		}

		$content .= '<ul>';

		// Loop through RSS feed
		$items = apply_filters( 'feedzy_feed_items', $feed->get_items(), $feedURL );
		foreach ( (array) $items as $item ) {

			$continue = apply_filters( 'feedzy_item_keyword', true, $sc['keywords_title'], $item, $feedURL );

			if ( $continue == true ) {
				// Count items
				if ( $count >= $sc['max'] ) {
					break;
				}
				$count++;

				// Fetch image thumbnail
				if ( $sc['thumb'] == 'yes' || $sc['thumb'] == 'auto' ) {
					$theThumbnail = $this->feedzy_retrieve_image( $item );
				}

				$itemAttr = apply_filters( 'feedzy_item_attributes', $itemAttr = '', $sizes, $item, $feedURL );

				// Build element DOM
				$content .= '<li ' . $itemAttr . '>';

				$itemLink = $item->get_permalink();
				$newLink = $itemLink;

				if ( $sc['thumb'] == 'yes' || $sc['thumb'] == 'auto' ) {
					$contentThumb = '';

					if ( ( ! empty( $theThumbnail ) && $sc['thumb'] == 'auto' ) || $sc['thumb'] == 'yes' ) {

						$contentThumb .= '<div class="rss_image" style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px;">';
						$contentThumb .= '<a href="' . $newLink . '" target="' . $sc['target'] . '" title="' . $item->get_title() . '" >';

						if ( ! empty( $theThumbnail ) ) {
							$theThumbnail = $this->feedzy_image_encode( $theThumbnail );
							$contentThumb .= '<span class="default" style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px; background-image:  url(' . $sc['default'] . ');" alt="' . $item->get_title() . '"></span>';
							$contentThumb .= '<span class="fetched" style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px; background-image:  url(' . $theThumbnail . ');" alt="' . $item->get_title() . '"></span>';
						} elseif ( empty( $theThumbnail ) && $sc['thumb'] == 'yes' ) {
							$contentThumb .= '<span style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px; background-image:url(' . $sc['default'] . ');" alt="' . $item->get_title() . '"></span>';
						}

						$contentThumb .= '</a>';
						$contentThumb .= '</div>';

					}

					// Filter: feedzy_thumb_output
					$content .= apply_filters( 'feedzy_thumb_output', $contentThumb, $feedURL );

				}

				$contentTitle = '';
				$contentTitle .= '<span class="title"><a href="' . $newLink . '" target="' . $sc['target'] . '">';

				if ( is_numeric( $sc['title'] ) && strlen( $item->get_title() ) > $sc['title'] ) {
					$contentTitle .= preg_replace( '/\s+?(\S+)?$/', '', substr( $item->get_title(), 0, $sc['title'] ) ) . '...';
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

				if ( $sc['meta'] == 'yes' && ( $metaArgs['author'] || $metaArgs['date'] ) ) {

					$contentMeta = '';
					$contentMeta .= '<small>' . __( 'Posted', 'feedzy_rss_translate' ) . ' ';

					if ( $item->get_author() && $metaArgs['author'] ) {
						$author = $item->get_author();
						if ( ! $authorName = $author->get_name() ) {
							$authorName = $author->get_email();
						}
						if ( $authorName ) {
							$domain = parse_url( $newLink );
							$contentMeta .= __( 'by', 'feedzy_rss_translate' ) . ' <a href="http://' . $domain['host'] . '" target="' . $sc['target'] . '" title="' . $domain['host'] . '" >' . $authorName . '</a> ';
						}
					}

					if ( $metaArgs['date'] ) {
						$contentMeta .= __( 'on', 'feedzy_rss_translate' ) . ' ' . date_i18n( $metaArgs['date_format'], $item->get_date( 'U' ) );
						$contentMeta .= ' ';
						$contentMeta .= __( 'at', 'feedzy_rss_translate' ) . ' ' . date_i18n( $metaArgs['time_format'], $item->get_date( 'U' ) );
					}

					$contentMeta .= '</small>';

					// Filter: feedzy_meta_output
					$content .= apply_filters( 'feedzy_meta_output', $contentMeta, $feedURL );

				}
				if ( $sc['summary'] == 'yes' ) {
					$contentSummary = '';
					$contentSummary .= '<p>';

					// Filter: feedzy_summary_input
					$description = $item->get_description();
					$description = apply_filters( 'feedzy_summary_input', $description, $item->get_content(), $feedURL );

					if ( is_numeric( $sc['summarylength'] ) && strlen( $description ) > $sc['summarylength'] ) {
						$contentSummary .= preg_replace( '/\s+?(\S+)?$/', '', substr( $description, 0, $sc['summarylength'] ) ) . ' […]';
					} else {
						$contentSummary .= $description . ' […]';
					}

					$contentSummary .= '</p>';

					// Filter: feedzy_summary_output
					$content .= apply_filters( 'feedzy_summary_output', $contentSummary, $newLink, $feedURL );

				}

				$content .= '</div>';
				$content .= '</li>';

			}
		}

		$content .= '</ul>';
		$content .= '</div>';

		return $content;
	}

	/**
	 * Main shortcode function
	 *
	 * @param   array  $atts  Shortcode attributes.
	 * @param   string $content  The item feed content.
	 * @return  mixed
	 */
	public function feedzy_rss( $atts, $content = '' ) {
		// Load SimplePie if not already
		if ( ! class_exists( 'SimplePie' ) ) {
			require_once( ABSPATH . WPINC . '/class-feed.php' );
		}

		$sc = $this->get_short_code_attributes( $atts );
		$feedURL = $this->get_feed_url( $sc['feeds'] );

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

		$content = $this->render_content( $sc, $feed, $content, $feedURL );

		return apply_filters( 'feedzy_global_output', $content, $feedURL );
	}

	/**
	 * Render the Template
	 */
	public function render_templates() {
		global $pagenow;

		if ( 'post.php' != $pagenow && 'post-new.php' != $pagenow ) {
			return;
		}

		$render = new Feedzy_Rss_Feeds_Render_Templates();
		$render->render();
	}
}
