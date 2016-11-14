<?php
/**
 * The Language function file for tinymce.
 *
 * @link       http://themeisle.com
 * @since      3.0.0
 *
 * @package    Feedzy_Rss_Feeds
 * @subpackage Feedzy_Rss_Feeds/admin
 */
/**
 *
 * SECURITY : Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed!' );
}


/**
 *
 * Translation for TinyMCE
 */

if ( ! class_exists( '_WP_Editors' ) ) {
	require( ABSPATH . WPINC . '/class-wp-editor.php' );
}

/**
 * Class Feedzy_Rss_Feeds_Lang
 */
class Feedzy_Rss_Feeds_Lang {

	/**
	 * The strings for translation.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      array    $strings    The ID of this plugin.
	 */
	private $strings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    3.0.0
	 */
	public function __construct() {
		$this->strings = array(
			'plugin_title' => __( 'Insert Feedzy RSS Feeds Shortcode', 'feedzy-rss-feeds-translate' ),
			'feeds' => __( 'The feed(s) URL (comma-separated list).', 'feedzy-rss-feeds-translate' ) . ' ' . __( 'Check feed before insert.', 'feedzy-rss-feeds-translate' ),
			'maximum' => __( 'Number of items to display.', 'feedzy-rss-feeds-translate' ),
			'feed_title' => __( 'Should we display the RSS title?', 'feedzy-rss-feeds-translate' ),
			'target' => __( 'Links may be opened in the same window or a new tab.', 'feedzy-rss-feeds-translate' ),
			'title' => __( 'Trim the title of the item after X characters.', 'feedzy-rss-feeds-translate' ),
			'meta' => __( 'Should we display the date of publication and the author name?', 'feedzy-rss-feeds-translate' ),
			'summary' => __( 'Should we display a description (abstract) of the retrieved item?', 'feedzy-rss-feeds-translate' ),
			'summarylength' => __( 'Crop description (summary) of the element after X characters.', 'feedzy-rss-feeds-translate' ),
			'thumb' => __( 'Should we display the first image of the content if it is available?', 'feedzy-rss-feeds-translate' ),
			'defaultimg' => __( 'Default thumbnail URL if no image is found.', 'feedzy-rss-feeds-translate' ),
			'size' => __( 'Thumblails dimension. Do not include "px". Eg: 150', 'feedzy-rss-feeds-translate' ),
			'keywords_title' => __( 'Only display item if title contains specific keyword(s) (comma-separated list/case sensitive).', 'feedzy-rss-feeds-translate' ),
			'text_default' => __( 'Do not specify', 'feedzy-rss-feeds-translate' ),
			'text_no' => __( 'No', 'feedzy-rss-feeds-translate' ),
			'text_yes' => __( 'Yes', 'feedzy-rss-feeds-translate' ),
			'text_auto' => __( 'Auto', 'feedzy-rss-feeds-translate' ),
		);
	}

	/**
	 *
	 * The method that returns the translation array
	 *
	 * @since    3.0.0
	 * @return string
	 */
	public function feedzy_tinymce_translation() {

		$locale = _WP_Editors::$mce_locale;
		$translated = 'tinyMCE.addI18n("' . $locale . '.feedzy_tinymce_plugin", ' . json_encode( $this->strings ) . ");\n";

		return $translated;
	}

}

$feedzyLangClass = new Feedzy_Rss_Feeds_Lang();
$strings = $feedzyLangClass->feedzy_tinymce_translation();
