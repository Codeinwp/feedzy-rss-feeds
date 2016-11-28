<?php
/**
 * The Language function file for tinymce.
 *
 * @link       http://themeisle.com
 * @since      3.0.0
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
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
		    'popup_url' => FEEDZY_ABSURL,
			'plugin_title' => __( 'Insert Feedzy RSS Feeds Shortcode', 'feedzy_rss_translate' ),
			'image_button' => __( 'Use Image', 'feedzy_rss_translate' ),
			'insert_button' => __( 'Insert Shortcode', 'feedzy_rss_translate' ),
			'cancel_button' => __( 'Cancel', 'feedzy_rss_translate' ),
			'pro_button' => __( 'Get Feedzy RSS Feeds PRO', 'feedzy_rss_translate' ),
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


    public function get_form_elements(){
        $elements = array(
            'feeds'             => array(
                'label' => __( 'The feed(s) URL (comma-separated list).', 'feedzy_rss_translate' ) . ' ' . __( 'Check feed before insert.', 'feedzy_rss_translate' ),
                'placeholder' => __( 'Feed URL', 'feedzy_rss_translate' ),
                'type'  => 'text',
                'value' => ''),
            'maximum'           => array(
                'label' => __( 'Number of items to display.', 'feedzy_rss_translate' ),
                'placeholder' => __( '(eg: 5)', 'feedzy_rss_translate' ),
                'type'  => 'text',
                'value' => ''),
            'feed_title'        => array(
                'label' => __( 'Should we display the RSS title?', 'feedzy_rss_translate' ),
                'type'  => 'select',
                'value' => '',
                'opts'  => array(
                    'auto' => array(
                        'label' => __( 'Auto', 'feedzy_rss_translate' ),
                        'value' => ''
                    ),
                    'yes' => array(
                        'label' => __( 'Yes', 'feedzy_rss_translate' ),
                        'value' => 'yes'
                    ),
                    'no' => array(
                        'label' => __( 'No', 'feedzy_rss_translate' ),
                        'value' => 'no'
                    ),
                )),
            'target'            => array(
                'label' => __( 'Links may be opened in the same window or a new tab.', 'feedzy_rss_translate' ),
                'type'  => 'select',
                'value' => '',
                'opts'  => array(
                    'auto' => array(
                        'label' => __( 'Auto', 'feedzy_rss_translate' ),
                        'value' => ''
                    ),
                    '_blank' => array(
                        'label' => __( '_blank', 'feedzy_rss_translate' ),
                        'value' => '_blank'
                    ),
                    '_self' => array(
                        'label' => __( '_self', 'feedzy_rss_translate' ),
                        'value' => '_self'
                    ),
                    '_parent' => array(
                        'label' => __( '_parent', 'feedzy_rss_translate' ),
                        'value' => '_parent'
                    ),
                    '_top' => array(
                        'label' => __( '_top', 'feedzy_rss_translate' ),
                        'value' => '_top'
                    ),
                    'framename' => array(
                        'label' => __( 'framename', 'feedzy_rss_translate' ),
                        'value' => 'framename'
                    )
                )),
            'title'             => array(
                'label' => __( 'Trim the title of the item after X characters.', 'feedzy_rss_translate' ),
                'placeholder' => __( '(eg: 160)', 'feedzy_rss_translate' ),
                'type'  => 'text',
                'value' => ''),
            'meta'              => array(
                'label' => __( 'Should we display the date of publication and the author name?', 'feedzy_rss_translate' ),
                'type'  => 'select',
                'value' => '',
                'opts'  => array(
                    'auto' => array(
                        'label' => __( 'Auto', 'feedzy_rss_translate' ),
                        'value' => ''
                    ),
                    'yes' => array(
                        'label' => __( 'Yes', 'feedzy_rss_translate' ),
                        'value' => 'yes'
                    ),
                    'no' => array(
                        'label' => __( 'No', 'feedzy_rss_translate' ),
                        'value' => 'no'
                    ),
                )),
            'summary'           => array(
                'label' => __( 'Should we display a description (abstract) of the retrieved item?', 'feedzy_rss_translate' ),
                'type'  => 'select',
                'value' => '',
                'opts'  => array(
                    'auto' => array(
                        'label' => __( 'Auto', 'feedzy_rss_translate' ),
                        'value' => ''
                    ),
                    'yes' => array(
                        'label' => __( 'Yes', 'feedzy_rss_translate' ),
                        'value' => 'yes'
                    ),
                    'no' => array(
                        'label' => __( 'No', 'feedzy_rss_translate' ),
                        'value' => 'no'
                    ),
                )),
            'summarylength'     => array(
                'label' => __( 'Crop description (summary) of the element after X characters.', 'feedzy_rss_translate' ),
                'type'  => 'text',
                'placeholder' => __( '(eg: 160)', 'feedzy_rss_translate' ),
                'value' => ''),
            'thumb'             => array(
                'label' => __( 'Should we display the first image of the content if it is available?', 'feedzy_rss_translate' ),
                'type'  => 'select',
                'value' => '',
                'opts'  => array(
                    'auto' => array(
                        'label' => __( 'Auto', 'feedzy_rss_translate' ),
                        'value' => ''
                    ),
                    'yes' => array(
                        'label' => __( 'Yes', 'feedzy_rss_translate' ),
                        'value' => 'yes'
                    ),
                    'no' => array(
                        'label' => __( 'No', 'feedzy_rss_translate' ),
                        'value' => 'no'
                    ),
                )),
            'defaultimg'        => array(
                'label' => __( 'Default thumbnail URL if no image is found.', 'feedzy_rss_translate' ),
                'type'  => 'file',
                'value' => '',
                'button' => array(
                    'button_text' => __('Select Image from Gallery', 'feedzy_rss_translate')
                )),
            'size'              => array(
                'label' => __( 'Thumblails dimension. Do not include "px". Eg: 150', 'feedzy_rss_translate' ),
                'placeholder' => __( '(eg: 150)', 'feedzy_rss_translate' ),
                'type'  => 'text',
                'value' => ''),
            'keywords_title'    => array(
                'label' => __( 'Only display item if title contains specific keyword(s) (comma-separated list/case sensitive).', 'feedzy_rss_translate' ),
                'placeholder' => __( '(eg: news, sports etc.)', 'feedzy_rss_translate' ),
                'type'  => 'text',
                'value' => '')
        );

        $elements = apply_filters('feedzy_get_form_elements', $elements);

        return $elements;
    }

}

$feedzyLangClass = new Feedzy_Rss_Feeds_Ui_Lang();
$strings = $feedzyLangClass->feedzy_tinymce_translation();
