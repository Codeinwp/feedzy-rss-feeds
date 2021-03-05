<?php
/**
 * Useful helper functions for plugin.
 *
 * @link       http://themeisle.com
 * @since      3.0.0
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes
 */

/**
 * Adds the featured image to the website's RSS feed.
 *
 * @param string $content The content of feed.
 *
 * @return string
 */
function feedzy_insert_thumbnail( $content ) {
	$settings = apply_filters( 'feedzy_get_settings', array() );
	if ( isset( $settings['general']['rss-feeds'] ) && 1 === intval( $settings['general']['rss-feeds'] ) ) {
		return $content;
	}

	global $post;
	if ( has_post_thumbnail( $post->ID ) ) {
		$content = '' . get_the_post_thumbnail( $post->ID, 'thumbnail' ) . '' . $content;
	}

	return $content;
}

// Alter the main blog feed to insert the thumbnail image.
add_filter( 'the_excerpt_rss', 'feedzy_insert_thumbnail' );
add_filter( 'the_content_feed', 'feedzy_insert_thumbnail' );

/**
 * Boostrap the plugin view.
 *
 * @param array $options The shortcode attributes.
 */
function feedzy_rss( $options = array() ) {
	$admin = Feedzy_Rss_Feeds::instance()->get_admin();
	return $admin->feedzy_rss( $options );
}

/**
 * The helper method for options wrapper
 *
 * @return Feedzy_Rss_Feeds_Options
 */
function feedzy_options() {
	return Feedzy_Rss_Feeds_Options::instance();
}

/**
 * Check if the user is before 3.0.3 or not.
 *
 * @return bool If the users is before 3.0.3 or after
 */
function feedzy_is_new() {
	return feedzy_options()->get_var( 'is_new' ) === 'yes' && ! feedzy_is_pro();
}

/**
 * Check if the user is pro or not.
 *
 * @return bool If the users is pro or not
 */
function feedzy_is_pro() {
	return defined( 'FEEDZY_PRO_ABSPATH' );
}


/**
 * Checks if the PRO version is older than a particular version.
 *
 * @since ?
 */
function feedzy_is_pro_older_than( $version ) {
	return version_compare( FEEDZY_PRO_VERSION, $version, '<' );
}

add_filter(
	'feedzy_wp_kses_allowed_html',
	function( $allowed_html = array() ) {
		return array(
			'select' => array(
				'type'     => array(),
				'id'       => array(),
				'name'     => array(),
				'value'    => array(),
				'class'    => array(),
				'selected' => array(),
			),
			'option' => array(
				'type'     => array(),
				'id'       => array(),
				'name'     => array(),
				'value'    => array(),
				'class'    => array(),
				'selected' => array(),
			),
			'input'  => array(
				'type'    => array(),
				'id'      => array(),
				'name'    => array(),
				'value'   => array(),
				'class'   => array(),
				'checked' => array(),
			),
			'textarea'  => array(
				'id'      => array(),
				'name'    => array(),
				'value'   => array(),
				'class'   => array(),
			),
			'button' => array(
				'class' => array(),
				'id'    => array(),
			),
			'p'      => array(
				'class' => array(),
			),
			'div'    => array(
				'class' => array(),
			),
			'h1'     => array(
				'class' => array(),
			),
			'h2'     => array(
				'class' => array(),
			),
			'h3'     => array(
				'class' => array(),
			),
			'h4'     => array(
				'class' => array(),
			),
			'h5'     => array(
				'class' => array(),
			),
			'h6'     => array(
				'class' => array(),
			),
			'label'  => array(
				'id'  => array(),
				'for' => array(),
			),
			'a'      => array(
				'href'  => array(),
				'title' => array(),
				'class' => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
		);
	}
);
