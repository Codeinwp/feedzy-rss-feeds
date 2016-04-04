<?php
/***************************************************************
 * SECURITY : Exit if accessed directly
***************************************************************/
if ( !defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed!' );
}

	
/***************************************************************
 * Main shortcode function
 ***************************************************************/
function feedzy_rss( $atts, $content = '' ) {

	global $feedzyStyle;
	$feedzyStyle = true;
	$count = 0;

	//Load SimplePie if not already
	if ( !class_exists( 'SimplePie' ) ){
		require_once( ABSPATH . WPINC . '/class-feed.php' );
	}

	//Retrieve & extract shorcode parameters
	extract( shortcode_atts( array(
		"feeds" => '', 			//comma separated feeds url
		"max" => '5', 			//number of feeds items (0 for unlimited)
		"feed_title" => 'yes', 	//display feed title yes/no
		"target" => '_blank', 	//_blank, _self
		"title" => '', 			//strip title after X char
		"meta" => 'yes', 		//yes, no
		"summary" => 'yes', 	//strip title
		"summarylength" => '', 	//strip summary after X char
		"thumb" => 'yes', 		//yes, no, auto
		"default" => '', 		//default thumb URL if no image found (only if thumb is set to yes or auto)
		"size" => '', 			//thumbs pixel size
		"keywords_title" => '' 	//only display item if title contains specific keywords (comma-separated list/case sensitive)
		), $atts, 'feedzy_default' ) );

	//Use "shortcode_atts_feedzy_default" filter to edit shortcode parameters default values or add your owns.

	if ( !empty( $feeds ) ) {
		$feeds = rtrim( $feeds, ',' );
		$feeds = explode( ',', $feeds );
		
		//Remove SSL from HTTP request to prevent fetching errors
		foreach( $feeds as $feed ){
			$feedURL[] = preg_replace("/^https:/i", "http:", $feed);
		}

		if ( count( $feedURL ) === 1 ) {
			$feedURL = $feedURL[0];
		}
		
	}
	
	if ( $max == '0' ) {
		$max = '999';
	} else if ( empty( $max ) || !ctype_digit( $max ) ) {
		$max = '5';
	}

	if ( empty( $size ) || !ctype_digit( $size ) ){
		$size = '150';
	}
	$sizes = array( 'width' => $size, 'height' => $size );
	$sizes = apply_filters( 'feedzy_thumb_sizes', $sizes, $feedURL );

	if ( !empty( $title ) && !ctype_digit( $title ) ){
		$title = '';
	}

	if ( !empty($keywords_title)){
		$keywords_title = rtrim( $keywords_title, ',' );
		$keywords_title = array_map( 'trim', explode( ',', $keywords_title ) );
	}

	if ( !empty( $summarylength ) && !ctype_digit( $summarylength ) ){
		$summarylength = '';
	}

	if ( !empty( $default ) ) {
		$default = $default;
	
	} else {
		$default = apply_filters( 'feedzy_default_image', $default, $feedURL );
	}
 
 	//Load SimplePie Instance
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

	if ( $feed_title == 'yes' ) {

		$content .= '<div class="rss_header">';
		$content .= '<h2><a href="' . $feed->get_permalink() . '" class="rss_title">' . html_entity_decode( $feed->get_title() ) . '</a> <span class="rss_description"> ' . $feed->get_description() . '</span></h2>';
		$content .= '</div>';
		
	}

	$content .= '<ul>';

	//Loop through RSS feed
	$items = apply_filters( 'feedzy_feed_items', $feed->get_items(), $feedURL );
	foreach ( (array) $items as $item ) {

		$continue = apply_filters( 'feedzy_item_keyword', true, $keywords_title, $item, $feedURL );

		if ( $continue == true ) {

			//Count items
			if ( $count >= $max ){
				break;
			}
			$count++;

			//Fetch image thumbnail
			if ( $thumb == 'yes' || $thumb == 'auto' ) {
				$thethumbnail = feedzy_retrieve_image( $item );
			}

			
			$itemAttr = apply_filters( 'feedzy_item_attributes', $itemAttr = '', $sizes, $item, $feedURL );

			//Build element DOM
			$content .= '<li ' . $itemAttr . '>';
			
			if ( $thumb == 'yes' || $thumb == 'auto' ) {
				
				$contentThumb = '';
				
				if ( ( ! empty( $thethumbnail ) && $thumb == 'auto' ) || $thumb == 'yes' ){
					
					$contentThumb .= '<div class="rss_image" style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px;">';
					$contentThumb .= '<a href="' . $item->get_permalink() . '" target="' . $target . '" title="' . $item->get_title() . '" >';
				
					if ( !empty( $thethumbnail )) {
						
						$thethumbnail = feedzy_image_encode( $thethumbnail );
						$contentThumb .= '<span class="default" style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px; background-image:  url(' . $default . ');" alt="' . $item->get_title() . '"></span>';
						$contentThumb .= '<span class="fetched" style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px; background-image:  url(' . $thethumbnail . ');" alt="' . $item->get_title() . '"></span>';
					
					} else if ( empty( $thethumbnail ) && $thumb == 'yes' ) {
					
						$contentThumb .= '<span style="width:' . $sizes['width'] . 'px; height:' . $sizes['height'] . 'px; background-image:url(' . $default . ');" alt="' . $item->get_title() . '"></span>';
					
					}

					$contentThumb .= '</a>';
					$contentThumb .= '</div>';
					
				}

				//Filter: feedzy_thumb_output
				$content .= apply_filters( 'feedzy_thumb_output', $contentThumb, $feedURL );
				
			}
			
			$contentTitle = '';
			$contentTitle .= '<span class="title"><a href="' . $item->get_permalink() . '" target="' . $target . '">';
		   
			if ( is_numeric( $title ) && strlen( $item->get_title() ) > $title ) {

				$contentTitle .= preg_replace( '/\s+?(\S+)?$/', '', substr( $item->get_title(), 0, $title ) ) . '...';
			
			} else {

				$contentTitle .= $item->get_title();
			
			}
			
			$contentTitle .= '</a></span>';

			//Filter: feedzy_title_output
			$content .= apply_filters( 'feedzy_title_output', $contentTitle, $feedURL );

			$content .= '<div class="rss_content">';

			
			//Define Meta args
			$metaArgs = array(
						'author' => true,
						'date' => true,
						'date_format' => get_option( 'date_format' ),
						'time_format' => get_option( 'time_format' )
					);
					
			//Filter: feedzy_meta_args
			$metaArgs = apply_filters( 'feedzy_meta_args', $metaArgs, $feedURL );

			if ( $meta == 'yes' && ( $metaArgs[ 'author' ] || $metaArgs[ 'date' ] ) ) {

				$contentMeta = '';
				$contentMeta .= '<small>' . __( 'Posted', 'feedzy_rss_translate' ) . ' ';

				if ( $item->get_author() && $metaArgs[ 'author' ] ) {
					
					$author = $item->get_author();
					if ( !$authorName = $author->get_name() ){
						$authorName = $author->get_email();
					}
					
					if( $authorName ){
						$domain = parse_url( $item->get_permalink() );
						$contentMeta .= __( 'by', 'feedzy_rss_translate' ) . ' <a href="http://' . $domain[ 'host' ] . '" target="' . $target . '" title="' . $domain[ 'host' ] . '" >' . $authorName . '</a> ';
					}

				}
				
				if ( $metaArgs[ 'date' ] ) {
					$contentMeta .= __( 'on', 'feedzy_rss_translate') . ' ' . date_i18n( $metaArgs[ 'date_format' ], $item->get_date( 'U' ) );
					$contentMeta .= ' ';
					$contentMeta .= __( 'at', 'feedzy_rss_translate' ) . ' ' . date_i18n( $metaArgs[ 'time_format' ], $item->get_date( 'U' ) );
				}
				
				$contentMeta .= '</small>';
				
				//Filter: feedzy_meta_output
				$content .= apply_filters( 'feedzy_meta_output', $contentMeta, $feedURL );

			}
			if ( $summary == 'yes' ) {


				$contentSummary = '';
				$contentSummary .= '<p>';

				//Filter: feedzy_summary_input
				$description = $item->get_description();
				$description = apply_filters( 'feedzy_summary_input', $description, $item->get_content(), $feedURL );

				if ( is_numeric( $summarylength ) && strlen( $description ) > $summarylength ) {

					$contentSummary .= preg_replace( '/\s+?(\S+)?$/', '', substr( $description, 0, $summarylength ) ) . ' […]';
				
				} else {

					$contentSummary .= $description . ' […]';
				}

				$contentSummary .= '</p>';

				//Filter: feedzy_summary_output
				$content .= apply_filters( 'feedzy_summary_output', $contentSummary, $item->get_permalink(), $feedURL );

			}
			
			$content .= '</div>';
			$content .= '</li>';
			
		} //endContinue
		
	} //endforeach

	$content .= '</ul>';
	$content .= '</div>';
	return apply_filters( 'feedzy_global_output', $content, $feedURL );
	
}//end of feedzy_rss
add_shortcode( 'feedzy-rss', 'feedzy_rss' );