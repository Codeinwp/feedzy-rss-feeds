<?php
/**
 * Useful helper functions for plugin.
 *
 * @link       https://themeisle.com
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
function feedzy_display_external_post_image( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	// If check post thumbnail exists OR not.
	if ( $post_thumbnail_id ) {
		return $html;
	}

	// Post thumbnail size.
	$size = ! empty( $size ) ? $size : 'thumbnail';

	// Attributes.
	$attr          = (array) $attr;
	$attr['style'] = isset( $attr['style'] ) ? $attr['style'] : '';

	// Get image dimensions.
	if ( is_array( $size ) ) {
		$dimensions     = wp_sprintf( 'width:%dpx; height:%dpx;', $size[0], $size[1] );
		$attr['style'] .= $dimensions;
	} elseif ( function_exists( 'wp_get_registered_image_subsizes' ) ) {
		$_wp_additional_image_sizes = wp_get_registered_image_subsizes();
		if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
			$sizes          = $_wp_additional_image_sizes[ $size ];
			$dimensions     = wp_sprintf( 'width:%dpx; height:%dpx;', $sizes['width'], $sizes['height'] );
			$attr['style'] .= $dimensions;
		}
	}

	$url = get_post_meta( $post_id, 'feedzy_item_external_url', true );
	if ( ! empty( $url ) ) {
		$alt         = get_the_title( $post_id );
		$attr['alt'] = $alt;
		$attr        = apply_filters( 'wp_get_attachment_image_attributes', $attr, '', '' );
		if ( isset( $attr['style'] ) ) {
			unset( $attr['style'] );
		}
		$attr = array_map( 'esc_attr', $attr );
		$html = sprintf( '<img src="%s"', esc_url( $url ) );
		foreach ( $attr as $name => $value ) {
			$html .= " $name=" . '"' . $value . '"';
		}
		$html .= ' />';
	}
	return $html;
}
add_filter( 'post_thumbnail_html', 'feedzy_display_external_post_image', 10, 5 );

/**
 * Filters the Attachment Image HTML
 *
 * @param string $html the attachment image HTML string.
 * @param array  $settings       Control settings.
 * @param string $image_size_key Settings key for image size.
 * @param string $image_key      Settings key for image.
 */
function feedzy_el_display_external_post_image( $html, $settings, $image_size_key, $image_key ) {
	if ( ! empty( $html ) ) {
		return $html;
	}

	if ( ! $image_key ) {
		$image_key = $image_size_key;
	}

	$image = $settings[ $image_key ];

	// Old version of image settings.
	if ( ! isset( $settings[ $image_size_key . '_size' ] ) ) {
		$settings[ $image_size_key . '_size' ] = '';
	}

	$size = $settings[ $image_size_key . '_size' ];
	// If is the new version - with image size.
	// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_intermediate_image_sizes_get_intermediate_image_sizes
	$image_sizes   = get_intermediate_image_sizes();
	$image_sizes[] = 'full';

	$image_class           = ! empty( $settings['hover_animation'] ) ? 'elementor-animation-' . $settings['hover_animation'] : '';
	$is_static_render_mode = \Elementor\Plugin::instance()->frontend->is_static_render_mode();

	// On static mode don't use WP responsive images.
	if ( in_array( $size, $image_sizes, true ) && ! $is_static_render_mode ) {
		$image_class .= " attachment-$size size-$size";
	}
	$post_id   = get_the_ID();
	$image_src = get_post_meta( $post_id, 'feedzy_item_external_url', true );

	if ( empty( $image_src ) ) {
		return $html;
	}

	if ( ! empty( $image_src ) ) {
		$attr['class'] = ! empty( $image_class ) ? ' class="' . trim( $image_class ) . '"' : '';
		$attr['alt']   = get_the_title( $post_id );
		$attr          = apply_filters( 'wp_get_attachment_image_attributes', $attr, '', '' );
		$attr          = array_map( 'esc_attr', $attr );
		$html          = sprintf( '<img src="%s"', esc_url( $image_src ) );
		foreach ( $attr as $name => $value ) {
			$html .= " $name=" . '"' . $value . '"';
		}
		$html .= ' />';

	}
	return $html;
}
add_filter( 'elementor/image_size/get_attachment_image_html', 'feedzy_el_display_external_post_image', 10, 4 );



/**
 * Generates an upgrade link for the Feedzy plugin.
 *
 * This function creates a URL for the Feedzy upsell link with UTM parameters.
 *
 * @param string $area     The area where the link is used.
 * @param string $location Optional. The specific page location. Default is null.
 *
 * @return string The translated and UTM-ified upgrade link.
 */
function feedzy_upgrade_link( $area, $location = null ) {
	return tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, $area, $location ) );
}
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
function feedzy_enable_external_url_support( $has_thumbnail, $post, $thumbnail_id ) {
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
add_filter( 'has_post_thumbnail', 'feedzy_enable_external_url_support', 10, 3 );

/**
 * Filters the attachment image source.
 *
 * @param array|false  $image         Either array with src, width & height, icon src, or false.
 * @param int          $attachment_id Image attachment ID.
 * @param string|int[] $size          Size of image. Image size or array of width and height values (in that order).
 * @param bool         $icon          Whether the image should be treated as an icon.
 * @return array|false
 */
function feedzy_get_attachment_image_src( $image, $attachment_id, $size, $icon ) {
	if ( 0 === $attachment_id ) {
		$external_url = get_post_meta( get_the_ID(), 'feedzy_item_external_url', true );
		if ( $external_url ) {
			$image = array( $external_url, 0, 0, false );
		}
	}
	return $image;
}

add_filter( 'wp_get_attachment_image_src', 'feedzy_get_attachment_image_src', 10, 4 );

/**
 * Boostrap the plugin view.
 *
 * @param array $options The shortcode attributes.
 * 
 * @return string The feeds HTML view.
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
	return 'yes' === feedzy_options()->get_var( 'is_new' ) && ! feedzy_is_pro();
}

/**
 * Check if the this is a legacy version v5.
 * 
 * @return bool
 */
function feedzy_is_legacyv5() {
	$legacy = (int) get_option( 'feedzy_legacyv5', 0 );

	return 1 === $legacy;
}

/**
 * Check if the user is pro or not.
 * 
 * @param bool $check_license Should check if the license if valid.
 *
 * @return bool If the users is pro or not
 */
function feedzy_is_pro( $check_license = true ) {
	$status = 'valid' === apply_filters( 'product_feedzy_license_status', false );
	if ( ! $check_license ) {
		$status = true;
	}
	return defined( 'FEEDZY_PRO_ABSPATH' ) && true === $status;
}


/**
 * Checks if the PRO version is older than a particular version.
 * 
 * @param string $version The plugin version.
 * 
 * @return int
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
	return trim( $content );
}

/**
 * Create pattern for feedzy keyword filter.
 *
 * @param string $keyword Keyword.
 * @return string
 */
function feedzy_filter_custom_pattern( $keyword = '' ) {
	$pattern = '';
	$regex   = array();
	if ( ! empty( $keyword ) && strlen( preg_replace( '/[^a-zA-Z]/', '', $keyword ) ) <= 500 ) {
		$keywords = explode( ',', $keyword );
		$keywords = array_filter( $keywords );
		$keywords = array_map( 'trim', $keywords );
		if ( ! empty( $keywords ) ) {
			foreach ( $keywords as $keyword ) {
				$keyword = explode( '+', $keyword );
				$keyword = array_map(
					function ( $k ) {
						$k = trim( $k );
						return "(?=.*$k)";
					},
					$keyword
				);
				$regex[] = implode( '', $keyword );
			}
			$pattern .= implode( '|', $regex );
		}
	}
	return $pattern;
}

/**
 * Get the plugin install time.
 *
 * @return int
 */
function feedzy_install_time(): int {
	return ( new ThemeisleSDK\Product( FEEDZY_BASEFILE ) )->get_install_time();
}
/**
 * Feedzy CSS.
 *
 * @param string $css Inline CSS.
 * @return string
 */
function feedzy_minimize_css( $css ) {
	if ( empty( $css ) ) {
		return $css;
	}
	// Normalize whitespace.
	$css = preg_replace( '/\s+/', ' ', $css );
	// Remove spaces before and after comment.
	$css = preg_replace( '/(\s+)(\/\*(.*?)\*\/)(\s+)/', '$2', $css );
	// Remove comment blocks, everything between /* and */, unless.
	// preserved with /*! ... */ or /** ... */.
	$css = preg_replace( '~/\*(?![\!|\*])(.*?)\*/~', '', $css );
	// Remove ; before }.
	$css = preg_replace( '/;(?=\s*})/', '', $css );
	// Remove space after , : ; { } */ >.
	$css = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $css );
	// Remove space before , ; { } ( ) >.
	$css = preg_replace( '/ (,|;|\{|}|\(|\)|>)/', '$1', $css );
	// Strips leading 0 on decimal values (converts 0.5px into .5px).
	$css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );
	// Strips units if value is 0 (converts 0px to 0).
	$css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );
	// Converts all zeros value into short-hand.
	$css = preg_replace( '/0 0 0 0/', '0', $css );
	// Shortern 6-character hex color codes to 3-character where possible.
	$css = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $css );

	return trim( $css );
}

/**
 * Feedzy default CSS.
 *
 * @param string $suffix_class CSS class prefix.
 * @return string
 */
function feedzy_default_css( $suffix_class = '' ) {
	$default_css = '.feedzy-rss .rss_item .rss_image {
		float: left;
		position: relative;
		border: none;
		text-decoration: none;
		max-width: 100%;
	}
	.feedzy-rss .rss_item .rss_image span {
		display: inline-block;
		position: absolute;
		width: 100%;
		height: 100%;
		background-position: 50%;
		background-size: cover;
	}
	.feedzy-rss .rss_item .rss_image {
		margin: 0.3em 1em 0 0;
		content-visibility: auto;
	}
	.feedzy-rss ul {
		list-style: none;
	}
	.feedzy-rss ul li {
		display: inline-block;
	}';
	if ( ! empty( $suffix_class ) ) {
		$default_css = str_replace( 'feedzy-rss', $suffix_class, $default_css );
	}
	return feedzy_minimize_css( $default_css );
}

add_filter(
	'feedzy_wp_kses_allowed_html',
	function ( $allowed_html = array() ) {
		$allowed_html = array(
			'select'   => array(
				'type'        => array(),
				'id'          => array(),
				'name'        => array(),
				'value'       => array(),
				'class'       => array(),
				'selected'    => array(),
				'data-feedzy' => array(),
				'disabled'    => array(),
			),
			'option'   => array(
				'type'     => array(),
				'id'       => array(),
				'name'     => array(),
				'value'    => array(),
				'class'    => array(),
				'selected' => array(),
			),
			'optgroup' => array(
				'label'    => array(),
				'disabled' => array(),
			),
			'input'    => array(
				'type'        => array(),
				'id'          => array(),
				'name'        => array(),
				'value'       => array(),
				'class'       => array(),
				'checked'     => array(),
				'placeholder' => array(),
				'data-feedzy' => array(),
				'disabled'    => array(),
			),
			'textarea' => array(
				'id'          => array(),
				'name'        => array(),
				'value'       => array(),
				'class'       => array(),
				'data-feedzy' => array(),
				'placeholder' => array(),
				'rows'        => array(),
			),
			'button'   => array(
				'class'    => array(),
				'id'       => array(),
				'disabled' => array(),
			),
			'p'        => array(
				'class' => array(),
			),
			'span'     => array(
				'class'    => array(),
				'disabled' => array(),
				'style'    => array(),
			),
			'div'      => array(
				'class' => array(),
			),
			'h1'       => array(
				'class' => array(),
			),
			'h2'       => array(
				'class' => array(),
			),
			'h3'       => array(
				'class' => array(),
			),
			'h4'       => array(
				'class' => array(),
			),
			'h5'       => array(
				'class' => array(),
			),
			'h6'       => array(
				'class' => array(),
			),
			'label'    => array(
				'id'    => array(),
				'for'   => array(),
				'class' => array(),
			),
			'a'        => array(
				'href'   => array(),
				'title'  => array(),
				'class'  => array(),
				'target' => array(),
			),
			'br'       => array(),
			'em'       => array(),
			'strong'   => array(
				'class' => array(),
				'style' => array(),
			),
			'iframe'   => array(
				'src'             => array(),
				'height'          => array(),
				'width'           => array(),
				'frameborder'     => array(),
				'allowfullscreen' => array(),
				'data-*'          => true,
			),
			'small'    => array(
				'class' => array(),
			),
		);

		return $allowed_html;
	}
);

/**
 * Elementor feed refresh options.
 *
 * @return array
 */
function feedzy_elementor_widget_refresh_options() {
	$options = array(
		'1_hours'  => wp_sprintf(
			// Translators: %d represents the number of one hour.
			__( '%d Hour', 'feedzy-rss-feeds' ),
			1
		),
		'3_hours'  => wp_sprintf(
			// Translators: %d represents the number of hours.
			__( '%d Hours', 'feedzy-rss-feeds' ),
			3
		),
		'12_hours' => wp_sprintf(
			// Translators: %d represents the number of hours.
			__( '%d Hours', 'feedzy-rss-feeds' ),
			12
		),
		'1_days'   => wp_sprintf(
			// Translators: %d represents the number of one day.
			__( '%d Day', 'feedzy-rss-feeds' ),
			1
		),
		'3_days'   => wp_sprintf(
			// Translators: %d represents the number of days.
			__( '%d Days', 'feedzy-rss-feeds' ),
			3
		),
		'15_days'  => wp_sprintf(
			// Translators: %d represents the number of days.
			__( '%d Days', 'feedzy-rss-feeds' ),
			15
		),
	);
	return apply_filters( 'feedzy_elementor_widget_refresh_options', $options );
}

/**
 * Classic widget feed refresh options.
 *
 * @return array
 */
function feedzy_classic_widget_refresh_options() {
	$options = array(
		'1_hours'  => array(
			'label' => '1 ' . __( 'Hour', 'feedzy-rss-feeds' ),
			'value' => '1_hours',
		),
		'3_hours'  => array(
			'label' => '3 ' . __( 'Hours', 'feedzy-rss-feeds' ),
			'value' => '3_hours',
		),
		'12_hours' => array(
			'label' => '12 ' . __( 'Hours', 'feedzy-rss-feeds' ),
			'value' => '12_hours',
		),
		'1_days'   => array(
			'label' => '1 ' . __( 'Day', 'feedzy-rss-feeds' ),
			'value' => '1_days',
		),
		'3_days'   => array(
			'label' => '3 ' . __( 'Days', 'feedzy-rss-feeds' ),
			'value' => '3_days',
		),
		'15_days'  => array(
			'label' => '15 ' . __( 'Days', 'feedzy-rss-feeds' ),
			'value' => '15_days',
		),
	);
	return apply_filters( 'feedzy_classic_widget_refresh_options', $options );
}

/**
 * Check user capability.
 */
function feedzy_current_user_can() {
	if ( function_exists( 'current_user_can' ) && current_user_can( apply_filters( 'feedzy_admin_menu_capability', 'publish_posts' ) ) ) {
		return true;
	}
	return false;
}

/**
 * Show import tour.
 *
 * @return bool
 */
function feedzy_show_import_tour() {
	if ( is_user_logged_in() && 'no' === get_option( 'feedzy_import_tour', 'no' ) ) {
		return get_user_meta( get_current_user_id(), 'feedzy_import_tour', true );
	}
	return false;
}

/**
 * Show review notice.
 *
 * @return bool
 */
function feedzy_show_review_notice() {
	$has_dismissed = get_option( 'feedzy_review_notice', 'no' );

	if ( 'yes' === $has_dismissed ) {
		return false;
	}

	$install_date = get_option( 'feedzy_rss_feeds_install', 0 );
	$days_since   = ( time() - $install_date ) / DAY_IN_SECONDS;

	if ( $days_since < 7 ) {
		return false;
	}

	$args = array(
		'post_type'      => 'feedzy_imports',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => 'imported_items_count',
				'value'   => 100,
				'type'    => 'numeric',
				'compare' => '>=',
			),
		),
	);

	$imported_posts = new WP_Query( $args );

	return $imported_posts->have_posts();
}
