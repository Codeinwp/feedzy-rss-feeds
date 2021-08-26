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
 * Filters the post thumbnail HTML.
 *
 * @since      3.5.2
 *
 * @param string       $html              The post thumbnail HTML.
 * @param int          $post_id           The post ID.
 * @param int          $post_thumbnail_id The post thumbnail ID.
 * @param string|int[] $size              Requested image size. Can be any registered image size name, or
 *                                        an array of width and height values in pixels (in that order).
 * @param string       $attr              Query string of attributes.
 * @return string post thumbnail HTML.
 */
function display_external_post_image( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	// If check post thumbnail exists OR not.
	if ( $post_thumbnail_id ) {
		return $html;
	}

	// Post thumbnail size.
	$size = ! empty( $size ) ? $size : 'thumbnail';

	// Attributes.
	$attr = (array) $attr;
	$attr['style'] = isset( $attr['style'] ) ? $attr['style'] : '';

	// Get image dimensions.
	if ( is_array( $size ) ) {
		$dimensions = wp_sprintf( 'width:%dpx; height:%dpx;', $size[0], $size[1] );
		$attr['style'] .= $dimensions;
	} elseif ( function_exists( 'wp_get_registered_image_subsizes' ) ) {
		$_wp_additional_image_sizes = wp_get_registered_image_subsizes();
		if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
			$sizes = $_wp_additional_image_sizes[ $size ];
			$dimensions = wp_sprintf( 'width:%dpx; height:%dpx;', $sizes['width'], $sizes['height'] );
			$attr['style'] .= $dimensions;
		}
	}

	$url = get_post_meta( $post_id, 'feedzy_item_external_url', true );
	if ( ! empty( $url ) ) {
		$alt  = get_the_title( $post_id );
		$attr['alt'] = $alt;
		$attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, '', '' );
		$attr = array_map( 'esc_attr', $attr );
		$html = sprintf( '<img src="%s"', esc_url( $url ) );
		foreach ( $attr as $name => $value ) {
			$html .= " $name=" . '"' . $value . '"';
		}
		$html .= ' />';
	}
	return $html;
}
add_filter( 'post_thumbnail_html', 'display_external_post_image', 10, 5 );

/**
 * Filters whether a post has a post thumbnail.
 *
 * @since      3.5.2
 *
 * @param bool             $has_thumbnail true if the post has a post thumbnail, otherwise false.
 * @param int|WP_Post|null $post          Post ID or WP_Post object. Default is global `$post`.
 * @param int|false        $thumbnail_id  Post thumbnail ID or false if the post does not exist.
 * @return bool
 */
function enable_external_url_support( $has_thumbnail, $post, $thumbnail_id ) {
	$post_id = get_the_ID();
	if ( $post && is_object( $post ) ) {
		$post_id = $post->ID;
	} elseif ( $post && is_numeric( $post ) ) {
		$post_id = $post;
	}
	$feedzy_item_external_url = get_post_meta( $post_id, 'feedzy_item_external_url', true );
	// Check external URL exists OR not.
	if ( ! empty( $feedzy_item_external_url ) ) {
		$has_thumbnail = true;
	}
	return $has_thumbnail;
}
add_filter( 'has_post_thumbnail', 'enable_external_url_support', 10, 3 );

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

/**
 * Feedzy escape custom tag in html attributes.
 *
 * @param string $content Content.
 * @return string
 */
function feedzy_custom_tag_escape( $content = '' ) {
	if ( $content ) {
		// Match feedzy custom tags in the src attribute.
		preg_match_all( '/(\w+)="([^"]*)"/i', $content, $matches, PREG_SET_ORDER );

		// If preg match found custom tags in src attribute.
		if ( ! empty( $matches ) ) {
			foreach ( $matches as $key => $match ) {
				if ( isset( $match[2] ) && false !== stripos( $match[2], '[#item_custom_media:' ) ) {
					$replace_with = $match[1];
					$replace_to   = wp_sprintf( 'data-feedzy_%d_%s', $key, $replace_with );
					$content      = str_replace( $replace_with, $replace_to, $content );
				}
			}
			$content = wp_kses( $content, wp_kses_allowed_html( 'post' ) );
			$content = preg_replace( '/data-feedzy_([0-9_]+)/', '', $content );
		} else {
			$content = wp_kses( $content, wp_kses_allowed_html( 'post' ) );
		}
	}
	return $content;
}

add_filter(
	'feedzy_wp_kses_allowed_html',
	function( $allowed_html = array() ) {
		return array(
			'select' => array(
				'type'        => array(),
				'id'          => array(),
				'name'        => array(),
				'value'       => array(),
				'class'       => array(),
				'selected'    => array(),
				'data-feedzy' => array(),
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
				'type'        => array(),
				'id'          => array(),
				'name'        => array(),
				'value'       => array(),
				'class'       => array(),
				'checked'     => array(),
				'placeholder' => array(),
				'data-feedzy' => array(),
			),
			'textarea'  => array(
				'id'          => array(),
				'name'        => array(),
				'value'       => array(),
				'class'       => array(),
				'data-feedzy' => array(),
			),
			'button' => array(
				'class' => array(),
				'id'    => array(),
			),
			'p'      => array(
				'class' => array(),
			),
			'span'   => array(
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
			'iframe' => array(
				'src'             => array(),
				'height'          => array(),
				'width'           => array(),
				'frameborder'     => array(),
				'allowfullscreen' => array(),
				'data-*'          => true,
			),
		);
	}
);
