<?php
/**
 * The Language function file for tinymce.
 *
 * @link       https://themeisle.com
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
	require ABSPATH . WPINC . '/class-wp-editor.php';
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
	 * @var      array $strings The ID of this plugin.
	 */
	protected $strings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    3.0.0
	 * @access   public
	 */
	public function __construct() {
		$this->strings = array(
			'popup_url'     => wp_nonce_url( 'admin-ajax.php', 'feedzy_ajax_token', 'feedzy_request_form_token' ),
			'pro_url'       => tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'shortcode' ) ),
			'plugin_label'  => __( 'Feedzy Lite', 'feedzy-rss-feeds' ),
			'plugin_title'  => __( 'Insert Feedzy RSS Feeds Shortcode', 'feedzy-rss-feeds' ),
			'image_button'  => __( 'Use Image', 'feedzy-rss-feeds' ),
			'insert_button' => __( 'Insert Shortcode', 'feedzy-rss-feeds' ),
			'cancel_button' => __( 'Cancel', 'feedzy-rss-feeds' ),
			'pro_button'    => __( 'Get Feedzy RSS Feeds Premium', 'feedzy-rss-feeds' ),
		);
	}

	/**
	 * Return the default values of the forms elements
	 *
	 * @return array The default values of shortcode elements
	 */
	public static function get_form_defaults() {

		$html_parts  = self::get_form_elements();
		$all_options = wp_list_pluck( array_values( $html_parts ), 'elements' );
		$all_options = call_user_func_array( 'array_merge', $all_options );
		$defaults    = array();

		foreach ( $all_options as $id => $option ) {
			$defaults[ $id ] = $option['value'];
		}

		return $defaults;
	}

	/**
	 * The method for localizing and generating of the tinyMCE popup form.
	 *
	 * It returns an array, use it to add more options to the popup window.
	 * Can be hook-ed into via 'feedzy_get_form_elements'.
	 *
	 * @since   3.0.0
	 * @access  public
	 * @return array|mixed|void
	 */
	public static function get_form_elements() {
		$meta = sprintf(
			// translators: 1: <code> tag for author, 2: closing </code> tag, 3: <code> tag for date, 4: closing </code> tag, 5: <code> tag for time, 6: closing </code> tag, 7: <a> tag for documentation link, 8: closing </a> tag.
			__( 'Should we display additional meta fields out of %1$s author %2$s, %3$s date %4$s and %5$s time %6$s? (comma-separated list, in order of display). View documentation %7$s here %8$s.', 'feedzy-rss-feeds' ),
			'<code>',
			'</code>',
			'<code>',
			'</code>',
			'<code>',
			'</code>',
			'<a href="' . esc_url( 'https://docs.themeisle.com/article/1089-how-to-display-author-date-or-time-from-the-feed' ) . '" target="_new">',
			'</a>'
		);
		if ( has_filter( 'feedzy_retrieve_categories' ) ) {
			$meta = sprintf(
				// translators: 1: <code> tag for author, 2: closing </code> tag, 3: <code> tag for date, 4: closing </code> tag, 5: <code> tag for time, 6: closing </code> tag, 7: <code> tag for categories, 8: closing </code> tag, 9: <a> tag for documentation link, 10: closing </a> tag.
				__( 'Should we display additional meta fields out of %1$s author%2$s, %3$s date%4$s, %5$s time %6$s and %7$s categories %8$s? (comma-separated list). View documentation %9$s here %10$s.', 'feedzy-rss-feeds' ),
				'<code>',
				'</code>',
				'<code>',
				'</code>',
				'<code>',
				'</code>',
				'<code>',
				'</code>',
				'<a href="' . esc_url( 'https://docs.themeisle.com/article/1089-how-to-display-author-date-or-time-from-the-feed' ) . '" target="_new">',
				'</a>'
			);
		}

		$multiple = sprintf(
			// translators: 1: <code> tag for source, 2: closing </code> tag.
			__( 'When using multiple sources, should we display additional meta fields? %1$s source %2$s (feed title).', 'feedzy-rss-feeds' ),
			'<code>',
			'</code>'
		);

		$elements = array(
			'section_feed'  => array(
				'title'    => __( 'Feed Source', 'feedzy-rss-feeds' ),
				'elements' => array(
					'feeds'                 => array(
						'label'       => (
							__( 'The feed(s) URL (comma-separated list).', 'feedzy-rss-feeds' ) . ' ' .
							sprintf(
								// translators: 1: <a> tag opening, 2: </a> tag closing.
								__( 'Click %1$s here %2$s to check if feed is valid.', 'feedzy-rss-feeds' ),
								'<a href="' . esc_url( 'https://validator.w3.org/feed/' ) . '" target="_new">',
								'</a>'
							)
							. '<br><b>' . __( 'Invalid feeds will NOT display items.', 'feedzy-rss-feeds' ) . '</b>'
						),
						'placeholder' => __( 'Feed URL', 'feedzy-rss-feeds' ),
						'type'        => 'text',
						'value'       => '',
					),
					'max'                   => array(
						'label'       => __( 'Number of items to display.', 'feedzy-rss-feeds' ),
						// translators: %s is the list of examples.
						'placeholder' => '(' . sprintf( __( 'eg: %s', 'feedzy-rss-feeds' ), '5' ) . ')',
						'type'        => 'text',
						'value'       => '',
					),
					'offset'                => array(
						'label'       => __( 'Ignore the first N items of the feed.', 'feedzy-rss-feeds' ),
						'placeholder' => __( '(eg: 5, if you want to start from the 6th item.)', 'feedzy-rss-feeds' ),
						'type'        => 'text',
						'value'       => '0',
					),
					'feed_title'            => array(
						'label' => __( 'Should we display the RSS title?', 'feedzy-rss-feeds' ),
						'type'  => 'select',
						'value' => '',
						'opts'  => array(
							'yes' => array(
								'label' => __( 'Yes', 'feedzy-rss-feeds' ),
								'value' => 'yes',
							),
							'no'  => array(
								'label' => __( 'No', 'feedzy-rss-feeds' ),
								'value' => 'no',
							),
						),
					),
					'refresh'               => array(
						'label'       => __( 'For how long we will cache the feed results.', 'feedzy-rss-feeds' ),
						'placeholder' => '('
							// translators: %s is the list of examples.
							. sprintf( __( 'eg: %s', 'feedzy-rss-feeds' ), '1_days' )
							// translators: %s is the default value.
							. sprintf( __( 'default: %s', 'feedzy-rss-feeds' ), '12_hours' )
							. ')',
						'type'        => 'select',
						'value'       => '12_hours',
						'opts'        => feedzy_classic_widget_refresh_options(),
					),
					'sort'                  => array(
						'label' => __( 'Sorting order.', 'feedzy-rss-feeds' ),
						'type'  => 'select',
						'value' => '',
						'opts'  => array(
							''           => array(
								'label' => __( 'Default', 'feedzy-rss-feeds' ),
								'value' => '',
							),
							'date_desc'  => array(
								'label' => __( 'Date Descending', 'feedzy-rss-feeds' ),
								'value' => 'date_desc',
							),
							'date_asc'   => array(
								'label' => __( 'Date Ascending', 'feedzy-rss-feeds' ),
								'value' => 'date_asc',
							),
							'title_desc' => array(
								'label' => __( 'Title Descending', 'feedzy-rss-feeds' ),
								'value' => 'title_desc',
							),
							'title_asc'  => array(
								'label' => __( 'Title Ascending', 'feedzy-rss-feeds' ),
								'value' => 'title_asc',
							),
						),
					),
					'error_empty'           => array(
						'label'       => __( 'Message to show when feed is empty', 'feedzy-rss-feeds' ),
						'placeholder' => __( 'Feed has no items.', 'feedzy-rss-feeds' ),
						'type'        => 'text',
						'value'       => '',
					),
					'lazy'                  => array(
						'label' => __( 'Lazy load the feed (without slowing down the page)', 'feedzy-rss-feeds' ),
						'type'  => 'select',
						'value' => 'no',
						'opts'  => array(
							'yes' => array(
								'label' => __( 'Yes', 'feedzy-rss-feeds' ),
								'value' => 'yes',
							),
							'no'  => array(
								'label' => __( 'No', 'feedzy-rss-feeds' ),
								'value' => 'no',
							),
						),
					),
					'disable_default_style' => array(
						'label'       => __( 'Disable default style', 'feedzy-rss-feeds' ),
						'description' => __( 'If disabled, it will be considered the global setting.', 'feedzy-rss-feeds' ),
						'type'        => 'select',
						'value'       => 'no',
						'opts'        => array(
							'yes' => array(
								'label' => __( 'Yes', 'feedzy-rss-feeds' ),
								'value' => 'yes',
							),
							'no'  => array(
								'label' => __( 'No', 'feedzy-rss-feeds' ),
								'value' => 'no',
							),
						),
					),
					'classname'             => array(
						'label' => __( 'Wrap custom class', 'feedzy-rss-feeds' ),
						'type'  => 'text',
						'value' => '',
					),
					'dryrun'                => array(
						'label' => __( 'Dry run?', 'feedzy-rss-feeds' ),
						'type'  => 'select',
						'value' => 'no',
						'opts'  => array(
							'yes' => array(
								'label' => __( 'Yes', 'feedzy-rss-feeds' ),
								'value' => 'yes',
							),
							'no'  => array(
								'label' => __( 'No', 'feedzy-rss-feeds' ),
								'value' => 'no',
							),
						),
					),
					'dry_run_tags'          => array(
						'label' => __( 'Dry run tags', 'feedzy-rss-feeds' ),
						'type'  => 'text',
						'value' => '',
					),
				),
			),
			'section_item'  => array(
				'title'    => __( 'Item Options', 'feedzy-rss-feeds' ),
				'elements' => array(
					'target'          => array(
						'label' => __( 'Links may be opened in the same window or a new tab.', 'feedzy-rss-feeds' ),
						'type'  => 'select',
						'value' => '',
						'opts'  => array(
							'auto'      => array(
								'label' => __( 'Auto', 'feedzy-rss-feeds' ),
								'value' => '',
							),
							'_blank'    => array(
								'label' => __( '_blank', 'feedzy-rss-feeds' ),
								'value' => '_blank',
							),
							'_self'     => array(
								'label' => __( '_self', 'feedzy-rss-feeds' ),
								'value' => '_self',
							),
							'_parent'   => array(
								'label' => __( '_parent', 'feedzy-rss-feeds' ),
								'value' => '_parent',
							),
							'_top'      => array(
								'label' => __( '_top', 'feedzy-rss-feeds' ),
								'value' => '_top',
							),
							'framename' => array(
								'label' => __( 'framename', 'feedzy-rss-feeds' ),
								'value' => 'framename',
							),
						),
					),
					'follow'          => array(
						'label' => __( 'Make this link a "nofollow" link?', 'feedzy-rss-feeds' ),
						'type'  => 'select',
						'value' => '',
						'opts'  => array(
							'auto'   => array(
								'label' => __( 'No', 'feedzy-rss-feeds' ),
								'value' => '',
							),
							'_blank' => array(
								'label' => __( 'Yes', 'feedzy-rss-feeds' ),
								'value' => 'no',
							),
						),
					),
					'title'           => array(
						'label'       => __( 'Trim the title of the item after X characters. A value of 0 will remove the title.', 'feedzy-rss-feeds' ),
						// translators: %s is the list of examples.
						'placeholder' => '(' . sprintf( __( 'eg: %s', 'feedzy-rss-feeds' ), '160' ) . ')',
						'type'        => 'text',
						'value'       => '',
					),
					'meta'            => array(
						'label'       => $meta,
						// translators: %s is the list of examples.
						'placeholder' => '(' . sprintf( __( 'eg: %s', 'feedzy-rss-feeds' ), 'author, date, time, tz=local' ) . ')',
						'type'        => 'text',
						'value'       => '',
					),
					'multiple_meta'   => array(
						'label'       => $multiple,
						// translators: %s is the list of examples.
						'placeholder' => '(' . sprintf( __( 'eg: %s', 'feedzy-rss-feeds' ), 'source' ) . ')',
						'type'        => 'text',
						'value'       => '',
					),
					'summary'         => array(
						'label' => __( 'Should we display a description (abstract) of the retrieved item?', 'feedzy-rss-feeds' ),
						'type'  => 'select',
						'value' => '',
						'opts'  => array(
							'yes' => array(
								'label' => __( 'Yes', 'feedzy-rss-feeds' ),
								'value' => 'yes',
							),
							'no'  => array(
								'label' => __( 'No', 'feedzy-rss-feeds' ),
								'value' => 'no',
							),
						),
					),
					'summarylength'   => array(
						'label'       => __( 'Crop description (summary) of the element after X characters.', 'feedzy-rss-feeds' ),
						'type'        => 'text',
						// translators: %s is the list of examples.
						'placeholder' => '(' . sprintf( __( 'eg: %s', 'feedzy-rss-feeds' ), '160' ) . ')',
						'value'       => '',
					),
					'keywords_title'  => array(
						'label'       => __( 'Only display item if selected field contains specific keyword(s) (Use comma(,) and plus(+) keyword).', 'feedzy-rss-feeds' ),
						'placeholder' => __( '(eg: news, stock + market etc.)', 'feedzy-rss-feeds' ),
						'type'        => 'text',
						'value'       => '',
						'disabled'    => feedzy_is_new(),
					),
					'keywords_inc_on' => array(
						'label'    => __( 'Select a specific item if you want to include a keyword filter.', 'feedzy-rss-feeds' ),
						'type'     => 'select',
						'value'    => '',
						'disabled' => feedzy_is_new(),
						'opts'     => array(
							'title'       => array(
								'label' => __( 'Title', 'feedzy-rss-feeds' ),
								'value' => 'title',
							),
							'author'      => array(
								'label' => __( 'Author', 'feedzy-rss-feeds' ),
								'value' => 'author',
							),
							'description' => array(
								'label' => __( 'Description', 'feedzy-rss-feeds' ),
								'value' => 'description',
							),
						),
					),
					'keywords_ban'    => array(
						'label'       => __( 'Exclude items if selected field contains specific keyword(s) (Use comma(,) and plus(+) keyword).', 'feedzy-rss-feeds' ),
						'placeholder' => __( '(eg: politics, gossip + stock etc.)', 'feedzy-rss-feeds' ),
						'type'        => 'text',
						'value'       => '',
						'disabled'    => true,
					),
					'keywords_exc_on' => array(
						'label'    => __( 'Select a specific item if you want to exclude a keyword filter.', 'feedzy-rss-feeds' ),
						'type'     => 'select',
						'value'    => '',
						'disabled' => feedzy_is_new(),
						'opts'     => array(
							'title'       => array(
								'label' => __( 'Title', 'feedzy-rss-feeds' ),
								'value' => 'title',
							),
							'author'      => array(
								'label' => __( 'Author', 'feedzy-rss-feeds' ),
								'value' => 'author',
							),
							'description' => array(
								'label' => __( 'Description', 'feedzy-rss-feeds' ),
								'value' => 'description',
							),
						),
					),
				),
			),
			'section_image' => array(
				'title'    => __( 'Item Image Options', 'feedzy-rss-feeds' ),
				'elements' => array(
					'thumb'   => array(
						'label' => __( 'Should we display the first image of the content if it is available?', 'feedzy-rss-feeds' ),
						'type'  => 'select',
						'value' => '',
						'opts'  => array(
							'auto' => array(
								'label' => __( 'Yes (without a fallback image)', 'feedzy-rss-feeds' ),
								'value' => '',
							),
							'yes'  => array(
								'label' => __( 'Yes (with a fallback image)', 'feedzy-rss-feeds' ),
								'value' => 'yes',
							),
							'no'   => array(
								'label' => __( 'No', 'feedzy-rss-feeds' ),
								'value' => 'no',
							),
						),
					),
					'default' => array(
						'label'       => __( 'Fallback image if no image is found.', 'feedzy-rss-feeds' ),
						'placeholder' => __( 'Image URL', 'feedzy-rss-feeds' ),
						'type'        => 'file',
						'value'       => '',
						'button'      => array(
							'button_text' => __( 'Select from Gallery', 'feedzy-rss-feeds' ),
						),
					),
					'size'    => array(
						'label'       => __( 'Thumbnails dimension. Do not include "px". Eg: 150', 'feedzy-rss-feeds' ),
						// translators: %s is the list of examples.
						'placeholder' => '(' . sprintf( __( 'eg: %s', 'feedzy-rss-feeds' ), '150' ) . ')',
						'type'        => 'text',
						'value'       => '',
					),
					'http'    => array(
						'label' => __( 'How should we treat HTTP images?', 'feedzy-rss-feeds' ),
						'type'  => 'select',
						'value' => '',
						'opts'  => array(
							'auto' => array(
								'label' => __( 'Show with HTTP link', 'feedzy-rss-feeds' ),
								'value' => '',
							),
							'yes'  => array(
								'label' => __( 'Force HTTPS (please verify that the images exist on HTTPS)', 'feedzy-rss-feeds' ),
								'value' => 'https',
							),
							'no'   => array(
								'label' => __( 'Ignore and show the default image instead', 'feedzy-rss-feeds' ),
								'value' => 'default',
							),
						),
					),
				),
			),
			'section_pro'   => array(
				'title'       => __( 'PRO Options', 'feedzy-rss-feeds' ),
				'description' => __( 'Get access to more options and customizations with full version of Feedzy RSS Feeds . Use existing templates or extend them and make them your own.', 'feedzy-rss-feeds' ) . '<br/><a href="' . esc_url( tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'sectionpro' ) ) ) . '" target="_blank"><small>' . __( 'See more features of Feedzy RSS Feeds PRO', 'feedzy-rss-feeds' ) . '</small></a>',
				'elements'    => array(
					'price'        => array(
						'label'    => sprintf(
							// translators: 1: <br/> tag, 2: <a> tag opening, 3: </a> tag closing.
							__( 'Should we display the price from the feed if it is available? %1$s You can read about how to extract price from a custom tag %2$s here %3$s', 'feedzy-rss-feeds' ),
							'<br/>',
							'<a href="' . esc_url( 'https://docs.themeisle.com/article/977-how-do-i-extract-values-from-custom-tags-in-feedzy' ) . '" target="_blank">',
							'</a>'
						),
						'type'     => 'select',
						'disabled' => true,
						'value'    => '',
						'opts'     => array(
							'yes' => array(
								'label' => __( 'Yes', 'feedzy-rss-feeds' ),
								'value' => 'yes',
							),
							'no'  => array(
								'label' => __( 'No', 'feedzy-rss-feeds' ),
								'value' => 'no',
							),
						),
					),
					'referral_url' => array(
						'label'       => sprintf(
							// translators: 1: <a> tag opening, 2: </a> tag closing.
							__( 'Referral URL parameters as per %1$s this document here %2$s', 'feedzy-rss-feeds' ),
							'<a href="' . esc_url( 'https://docs.themeisle.com/article/1073-how-to-add-referral-parameters-in-feedzy' ) . '" target="_blank">',
							'</a>'
						),
						'placeholder' => '',
						'type'        => 'text',
						'disabled'    => true,
						'value'       => '',
					),
					'columns'      => array(
						'label'       => __( 'How many columns we should use to display the feed items', 'feedzy-rss-feeds' ),
						// translators: %s is the list of examples.
						'placeholder' => '(' . sprintf( __( 'eg: %s', 'feedzy-rss-feeds' ), '1, 2, ..., 6' ) . ')',
						'type'        => 'number',
						'disabled'    => true,
						'value'       => '1',
					),
					'mapping'      => array(
						'label'       => sprintf(
							// translators: 1: <a> tag opening, 2: </a> tag closing.
							__( 'Provide mapping for custom feed elements as per %1$s this document here %2$s. This will only work for single feeds, not comma-separated feeds.', 'feedzy-rss-feeds' ),
							'<a href="' . esc_url( 'https://docs.themeisle.com/article/977-how-do-i-extract-values-from-custom-tags-in-feedzy' ) . '" target="_blank">',
							'</a>'
						),
						'type'        => 'text',
						'disabled'    => true,
						'value'       => '',
						'placeholder' => '',
					),
					'template'     => array(
						'label'    => __( 'Template to use when displaying the feed.', 'feedzy-rss-feeds' ),
						'type'     => 'radio',
						'disabled' => true,
						'value'    => '',
						'opts'     => array(
							'auto' => array(
								'label' => __( 'Default', 'feedzy-rss-feeds' ),
								'value' => 'default',
							),
							'yes'  => array(
								'label' => __( 'Style 1', 'feedzy-rss-feeds' ),
								'value' => 'style1',
							),
							'no'   => array(
								'label' => __( 'Style 2', 'feedzy-rss-feeds' ),
								'value' => 'style2',
							),
						),
					),
				),
			),
		);

		$elements = apply_filters( 'feedzy_get_form_elements_filter', $elements );

		return $elements;
	}

	/**
	 *
	 * The method that returns the translation array
	 *
	 * @since    3.0.0
	 * @access   public
	 * @return string
	 */
	public function feedzy_tinymce_translation() {

		$locale     = _WP_Editors::$mce_locale;
		$translated = 'tinyMCE.addI18n("' . $locale . '.feedzy_tinymce_plugin", ' . wp_json_encode( $this->strings ) . ");\n";

		return $translated;
	}

	/**
	 *
	 * The method that returns the strings array
	 *
	 * @since    ?
	 * @access   public
	 * @return array
	 */
	public function get_strings() {
		return $this->strings;
	}
}

$feedzy_lang_class = new Feedzy_Rss_Feeds_Ui_Lang();
$strings           = $feedzy_lang_class->feedzy_tinymce_translation();
