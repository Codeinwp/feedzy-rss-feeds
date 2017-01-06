<?php
/**
 * The file that alter the main blog feed
 *
 * @link       http://themeisle.com
 * @since      3.0.0
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes
 */
/**
 *
 * Insert cover picture to main rss feed content
 *
 * @since   3.0.0
 * @access  public
 *
 * @param   string $content The item feed content.
 *
 * @return  string
 */
// @codingStandardsIgnoreStart
function feedzy_insert_thumbnail_RSS( $content ) {
// @codingStandardsIgnoreEnd
	global $post;
	if ( has_post_thumbnail( $post->ID ) ) {
		$content = '' . get_the_post_thumbnail( $post->ID, 'thumbnail' ) . '' . $content;
	}

	return $content;
}

// Alter the main blog feed to insert the thumbnail image.
add_filter( 'the_excerpt_rss', 'feedzy_insert_thumbnail_RSS' );
add_filter( 'the_content_feed', 'feedzy_insert_thumbnail_RSS' );
