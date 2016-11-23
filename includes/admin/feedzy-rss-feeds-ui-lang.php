<?php
/**
 * The Language function file for tinymce.
 *
 * @link       http://themeisle.com
 * @since      3.0.0
 *
 * @package    Feedzy_Rss_Feeds
 * @subpackage Feedzy_Rss_Feeds/includes/admin
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
 * Class Feedzy_Rss_Feeds_Ui_Lang
 */
class Feedzy_Rss_Feeds_Ui_Lang {

	/**
	 * The strings for translation.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      array    $strings    The ID of this plugin.
	 */
	protected $strings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    3.0.0
	 */
	public function __construct() {
		$this->strings = array(
			'plugin_title' => __( 'Insert Feedzy RSS Feeds Shortcode', 'feedzy_rss_translate' ),
			'feeds' => __( 'The feed(s) URL (comma-separated list).', 'feedzy_rss_translate' ) . ' ' . __( 'Check feed before insert.', 'feedzy_rss_translate' ),
			'maximum' => __( 'Number of items to display.', 'feedzy_rss_translate' ),
			'feed_title' => __( 'Should we display the RSS title?', 'feedzy_rss_translate' ),
			'target' => __( 'Links may be opened in the same window or a new tab.', 'feedzy_rss_translate' ),
			'title' => __( 'Trim the title of the item after X characters.', 'feedzy_rss_translate' ),
			'meta' => __( 'Should we display the date of publication and the author name?', 'feedzy_rss_translate' ),
			'summary' => __( 'Should we display a description (abstract) of the retrieved item?', 'feedzy_rss_translate' ),
			'summarylength' => __( 'Crop description (summary) of the element after X characters.', 'feedzy_rss_translate' ),
			'thumb' => __( 'Should we display the first image of the content if it is available?', 'feedzy_rss_translate' ),
			'defaultimg' => __( 'Default thumbnail URL if no image is found.', 'feedzy_rss_translate' ),
			'size' => __( 'Thumblails dimension. Do not include "px". Eg: 150', 'feedzy_rss_translate' ),
			'keywords_title' => __( 'Only display item if title contains specific keyword(s) (comma-separated list/case sensitive).', 'feedzy_rss_translate' ),
			'text_default' => __( 'Do not specify', 'feedzy_rss_translate' ),
			'text_no' => __( 'No', 'feedzy_rss_translate' ),
			'text_yes' => __( 'Yes', 'feedzy_rss_translate' ),
			'text_auto' => __( 'Auto', 'feedzy_rss_translate' ),
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

$feedzyLangClass = new Feedzy_Rss_Feeds_Ui_Lang();
$strings = $feedzyLangClass->feedzy_tinymce_translation();
