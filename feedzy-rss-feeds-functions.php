<?php
/***************************************************************
 * SECURITY : Exit if accessed directly
 ***************************************************************/
if ( !defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed!' );
}


/***************************************************************
 * Define the default image thumbnail
 ***************************************************************/
function feedzy_define_default_image( $imageSrc ){
	return plugins_url( 'img/feedzy-default.jpg', __FILE__ );
}
add_filter( 'feedzy_default_image', 'feedzy_define_default_image' );


/***************************************************************
 * Default error message + log errors
 ***************************************************************/
function feedzy_default_error_notice( $error, $feedURL  ){
	//Write in the log file
	error_log( 'Feedzy RSS Feeds - related feed: ' .$feedURL . ' - Error message: ' . feedzy_array_obj_string( $error ) );
	//Display the error message
	return '<div id="message" class="error" data-error"' . esc_attr( feedzy_array_obj_string( $error ) ) . '"><p>' . __('Sorry, this feed is currently unavailable or does not exists anymore.', 'feedzy_rss_translate') . '</p></div>';
}
add_filter( 'feedzy_default_error', 'feedzy_default_error_notice', 9, 2 );



/***************************************************************
 * Convert array or object into string
 ***************************************************************/
function feedzy_array_obj_string ( $error ){
	if ( is_array( $error ) || is_object( $error ) ) {
         return print_r( $error, true );
      } else {
         return $error;
      }
}


/***************************************************************
 * Enqueue feedzy CSS
 ***************************************************************/
function feedzy_register_custom_style() {
	wp_register_style( 'feedzy-style', plugins_url( 'css/feedzy-rss-feeds.css', __FILE__ ), array(), FEEDZY_VERSION );
}
function feedzy_print_custom_style() {
	global $feedzyStyle;
	if ( !$feedzyStyle )
		return;

	wp_print_styles( 'feedzy-style' );
}
add_action( 'init', 'feedzy_register_custom_style' );
add_action( 'wp_footer', 'feedzy_print_custom_style' );


/***************************************************************
 * Padding ratio based on image size
 ***************************************************************/
function feedzy_add_item_padding( $itemAttr, $sizes ){
	$paddinTop = number_format( ( 15 / 150 ) * $sizes[ 'height' ], 0 );
	$paddinBottom = number_format( ( 25 / 150 ) * $sizes[ 'height' ], 0 );
	$stylePadding = ' style="padding: ' . $paddinTop . 'px 0 ' . $paddinBottom . 'px"';
	return $itemAttr . $stylePadding;
}
add_filter( 'feedzy_item_attributes', 'feedzy_add_item_padding', 10, 2 );


/***************************************************************
 * Feed item container class
 ***************************************************************/
function feedzy_classes_item( $itemAttr ){
	$classes = array( 'rss_item' );
	$classes = apply_filters( 'feedzy_add_classes_item', $classes );
	$classes = ' class="' . implode( ' ', $classes ) . '"';
	return $itemAttr . $classes;
}
add_filter( 'feedzy_item_attributes', 'feedzy_classes_item' );


/***************************************************************
 * Retrive image from the item object
 ***************************************************************/
function feedzy_retrieve_image( $item ) {
	$thethumbnail = "";
	if ( $enclosures = $item->get_enclosures() ) {
		
		foreach( (array) $enclosures as $enclosure ){
			

			//item thumb
			if ( $thumbnail = $enclosure->get_thumbnail() ) {
				$thethumbnail = $thumbnail;
			}

			//media:thumbnail
			if ( isset( $enclosure->thumbnails ) ) {

				foreach ( (array) $enclosure->thumbnails as $thumbnail ) {
					$thethumbnail = $thumbnail;
				}
				
			}

			//enclosure
			if ( $thumbnail = $enclosure->embed() ) {
				
				
				$pattern = '/https?:\/\/.*\.(?:jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/iU';

				if ( preg_match( $pattern, $thumbnail, $matches ) ) {
					$thethumbnail = $matches[0];
				}
				
			}

			//media:content && strpos( $enclosure->type, 'image' ) !== false 
			foreach ( (array) $enclosure->get_link() as $thumbnail ) {

				$pattern = '/https?:\/\/.*\.(?:jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/iU';
				$imgsrc = $thumbnail;


				if ( preg_match( $pattern, $imgsrc, $matches )  ) {
					$thethumbnail = $matches[0];
					break;
				}
				
			}

			//break loop if thumbnail found
			if ( ! empty( $thethumbnail ) ) {
				break;
			}

		}
		
	}

	//xmlns:itunes podcast
	if ( empty( $thethumbnail ) ) {
		$data = $item->get_item_tags('http://www.itunes.com/dtds/podcast-1.0.dtd', 'image');
		if ( isset( $data['0']['attribs']['']['href'] ) && !empty( $data['0']['attribs']['']['href'] ) ){
			$thethumbnail = $data['0']['attribs']['']['href'];
		}
	}
	
	//content image
	if ( empty( $thethumbnail ) ) {

		$feedDescription = $item->get_content();
		$thethumbnail = feedzy_returnImage( $feedDescription );
		
	}

	//description image
	if ( empty( $thethumbnail ) ) {
		
		$feedDescription = $item->get_description();
		$thethumbnail = feedzy_returnImage( $feedDescription );
	
	}

	return $thethumbnail;
}


/***************************************************************
 * Get an image from a string
 ***************************************************************/
function feedzy_returnImage( $string ) {
	$img = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
	$pattern = "/<img[^>]+\>/i";
	preg_match( $pattern, $img, $matches );
	if( isset( $matches[0] ) ){
		$blacklistCount = 0;
		foreach( $matches as $matche){
			$link = feedzy_scrapeImage( $matche );
			$blacklist = array();
			$blacklist = apply_filters( 'feedzy_feed_blacklist_images', feedzy_blacklist_images( $blacklist ) );
			foreach( $blacklist as $string ) {
				if ( strpos( (string) $link, $string ) !== false) {
					$blacklistCount++;
				}
			}
			if( $blacklistCount == 0) break;
		}
		if( $blacklistCount == 0) return $link;
	}
	return;
}

function feedzy_scrapeImage( $string, $link = '' ) {
	$pattern = '/src=[\'"]?([^\'" >]+)[\'" >]/';     
	preg_match( $pattern, $string, $link );
	if( isset( $link[1] ) ){
		$link = urldecode( $link[1] );
	}
	return $link;
}

/***************************************************************
 * List blacklisted images to prevent fetching emoticons
 ***************************************************************/
function feedzy_blacklist_images( $blacklist ) {
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


/***************************************************************
 * Image name encode + get image url if in url param
 ***************************************************************/
function feedzy_image_encode( $string ) {	
	//Check if img url is set as an URL parameter
	$url_tab = parse_url( $string );
	if( isset( $url_tab['query'] ) ){
		preg_match_all( '/(http|https):\/\/[^ ]+(\.gif|\.GIF|\.jpg|\.JPG|\.jpeg|\.JPEG|\.png|\.PNG)/', $url_tab['query'], $imgUrl );
		if( isset( $imgUrl[0][0] ) ){
			$string = $imgUrl[0][0];
		}
	}
	
	//Encode image name only en keep extra parameters
	$query = $extention = '';
	$url_tab = parse_url( $string );
	if( isset( $url_tab['query'] ) ){
		$query = '?' . $url_tab['query'];
	}
	$path_parts = pathinfo( $string );
	$path = $path_parts['dirname'];
	$file = rawurldecode( $path_parts['filename'] );
	$extention = pathinfo( $url_tab['path'], PATHINFO_EXTENSION );
	if( !empty( $extention ) ){
		$extention =  '.' . $extention;
	}
	
	//Return a well encoded image url
	return $path . '/' . rawurlencode( $file ) . $extention . $query;
}

/***************************************************************
 * Filter feed description input
 ***************************************************************/
function feedzy_summary_input_filter( $description, $content, $feedURL ) {
	$description = trim( strip_tags( $description ) );
	$description = trim( chop( $description, '[&hellip;]' ) );
 
    return $description;
}
add_filter('feedzy_summary_input', 'feedzy_summary_input_filter', 9, 3);	


/***************************************************************
 * Check if keywords are in title
 ***************************************************************/
function feedzy_feed_item_keywords_title( $continue, $keywords_title, $item, $feedURL ){
	if ( !empty( $keywords_title ) ) {
		$continue = false;
		foreach ( $keywords_title as $keyword ) {
			if ( strpos( $item->get_title(), $keyword ) !== false ) {
				$continue = true;
			}
		}
	}
	return $continue;
}
add_filter( 'feedzy_item_keyword', 'feedzy_feed_item_keywords_title', 9, 4 ); 


/***************************************************************
 * Insert cover picture to main rss feed content
 ***************************************************************/
function feedzy_insert_thumbnail_RSS( $content ) {
	 global $post;
	 if ( has_post_thumbnail( $post->ID ) ){
		  $content = '' . get_the_post_thumbnail( $post->ID, 'thumbnail' ) . '' . $content;
	 }
	 return $content;
}
add_filter( 'the_excerpt_rss', 'feedzy_insert_thumbnail_RSS' );
add_filter( 'the_content_feed', 'feedzy_insert_thumbnail_RSS' );


/***************************************************************
 * Include cover picture (medium) to rss feed enclosure 
 * and media:content
 ***************************************************************/
function feedzy_include_thumbnail_RSS (){
	 global $post;
	 
	 if ( has_post_thumbnail( $post->ID ) ){
		 
		$postThumbnailId = get_post_thumbnail_id( $post->ID );
		$attachmentMeta = wp_get_attachment_metadata( $postThumbnailId );
		$imageUrl = wp_get_attachment_image_src( $postThumbnailId, 'medium' );
		
		echo '<enclosure url="' . $imageUrl[0] . '" length="' . filesize( get_attached_file( $postThumbnailId ) ) . '" type="image/jpg" />';				
		echo '<media:content url="' . $imageUrl[0] . '" width="' . $attachmentMeta['sizes']['medium']['width'] . '" height="' . $attachmentMeta['sizes']['medium']['height'] . '" medium="image" type="' . $attachmentMeta['sizes']['medium']['mime-type'] . '" />';
	
	}
}
//add_action('rss_item', 'feedzy_include_thumbnail_RSS');
//add_action('rss2_item', 'feedzy_include_thumbnail_RSS');