<?php
use Elementor\Controls_Manager;
use Elementor\Core\Base\Document;
use Elementor\Core\Settings\Manager as SettingsManager;

/**
 * Register feedzy elementor widget.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/elementor
 */
class Feedzy_Register_Widget extends Elementor\Widget_Base {

	/**
	 * Widget name.
	 */
	public function get_name() {
		return 'feedzy-rss-feeds';
	}

	/**
	 * Widget title.
	 */
	public function get_title() {
		return __( 'Feedzy RSS Feeds', 'feedzy-rss-feeds' );
	}

	/**
	 * Widget icon.
	 */
	public function get_icon() {
		return 'dashicons dashicons-rss';
	}

	/**
	 * Widget search keywords.
	 */
	public function get_keywords() {
		return array( 'elementor', 'template', 'feed', 'rss', 'feedzy' );
	}

	/**
	 * Widget register controls.
	 */
	protected function register_controls() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		// Start general setting section.
		$this->start_controls_section(
			'fz-general-settings',
			array(
				'label' => __( 'General Settings', 'feedzy-rss-feeds' ),
			)
		);
		$this->add_control(
			'fz-section-title',
			array(
				'label'       => __( 'Section Title', 'feedzy-rss-feeds' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Add your title, that will be added in page', 'feedzy-rss-feeds' ),
				'classes'     => 'feedzy-el-feed-source',
			)
		);
		$this->add_control(
			'fz-intro-text',
			array(
				'label_block' => true,
				'label'       => __( 'Intro text', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::TEXTAREA,
			)
		);
		$this->add_control(
			'fz-disable-default-style',
			array(
				'label'        => __( 'Disable default style', 'feedzy-rss-feeds' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'label_on'     => esc_html__( 'Yes', 'feedzy-rss-feeds' ),
				'label_off'    => esc_html__( 'No', 'feedzy-rss-feeds' ),
				'return_value' => 'yes',
				'description'  => __( 'If disabled, it will be considered the global setting.', 'feedzy-rss-feeds' ),
			)
		);
		$this->add_control(
			'fz-custom-class',
			array(
				'label_block' => true,
				'label'       => __( 'Wrap custom class', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::TEXT,
				'classes'     => 'feedzy-el-custom-class',
			)
		);
		$this->end_controls_section(); // End general setting section.

		// Start feed source section.
		$this->start_controls_section(
			'fz-feed-source',
			array(
				'label' => __( 'Feed Source', 'feedzy-rss-feeds' ),
			)
		);
		$this->add_control(
			'fz-source',
			array(
				'label_block' => true,
				'label'       => __( 'RSS Feed source', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::TEXTAREA,
				'description' => wp_sprintf(
					// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
					__( 'You can add multiple sources at once, by separating them with commas. %1$s Click here %2$s to check if the feed is valid. Invalid feeds may not import anything.', 'feedzy-rss-feeds' ),
					'<a href="' . esc_url( 'https://validator.w3.org/feed/' ) . '" class="feedzy-source" target="_blank">',
					'</a>'
				),
			)
		);
		$this->add_control(
			'fz-max',
			array(
				'label_block' => true,
				'label'       => __( 'Number of items to display', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::NUMBER,
				'classes'     => 'feedzy-el-full-width',
				'default'     => 5,
				'min'         => 1,
			)
		);
		$this->add_control(
			'fz-orderby',
			array(
				'label_block' => true,
				'label'       => __( 'Order items by', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => array(
					''           => __( 'Default', 'feedzy-rss-feeds' ),
					'date_desc'  => __( 'Date Descending', 'feedzy-rss-feeds' ),
					'date_asc'   => __( 'Date Ascending', 'feedzy-rss-feeds' ),
					'title_desc' => __( 'Title Descending', 'feedzy-rss-feeds' ),
					'title_asc'  => __( 'Title Ascending', 'feedzy-rss-feeds' ),
				),
			)
		);
		$this->add_control(
			'fz-refresh',
			array(
				'label_block' => true,
				'label'       => __( 'For how long we will cache the feed results', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '12_hours',
				'options'     => feedzy_elementor_widget_refresh_options(),
				'separator'   => 'before',
			)
		);
		$this->add_control(
			'fz-error-empty',
			array(
				'label_block' => true,
				'label'       => __( 'Message to show when feed is empty', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::TEXTAREA,
				'separator'   => 'before',
			)
		);
		$this->add_control(
			'fz-dry-run',
			array(
				'label_block' => true,
				'label'       => __( 'Dry run?', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'no',
				'options'     => array(
					'yes' => __( 'Yes', 'feedzy-rss-feeds' ),
					'no'  => __( 'No', 'feedzy-rss-feeds' ),
				),
			)
		);
		$this->add_control(
			'fz-dry-run-tags',
			array(
				'label_block' => true,
				'label'       => __( 'Dry run tags', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::TEXT,
			)
		);
		$this->end_controls_section(); // End feed source section.

		// Start filter items section.
		$this->start_controls_section(
			'fz-filter-items',
			array(
				'label'   => __( 'Filter items', 'feedzy-rss-feeds' ) . $this->upsell_title_label(),
				'classes' => $this->upsell_class(),
			)
		);
		$this->add_control(
			'fz-filter-inc-on',
			array(
				'label'   => __( 'Display items if', 'feedzy-rss-feeds' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'title',
				'options' => array(
					'title'       => __( 'Title', 'feedzy-rss-feeds' ),
					'description' => __( 'Description', 'feedzy-rss-feeds' ),
					'author'      => __( 'Author', 'feedzy-rss-feeds' ),
				),
				'classes' => $this->upsell_class(),
			)
		);
		$this->add_control(
			'fz-filter-inc-key',
			array(
				'label_block' => true,
				'label'       => __( 'Contains:', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::TEXTAREA,
				'description' => __( 'You can add multiple keywords at once by separating them with comma (,) or use the plus sign (+) to bind multiple keywords.', 'feedzy-rss-feeds' ),
				'classes'     => $this->upsell_class(),
			)
		);
		$this->add_control(
			'fz-filter-exc-on',
			array(
				'label'     => __( 'Exclude items if', 'feedzy-rss-feeds' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'title',
				'options'   => array(
					'title'       => __( 'Title', 'feedzy-rss-feeds' ),
					'description' => __( 'Description', 'feedzy-rss-feeds' ),
					'author'      => __( 'Author', 'feedzy-rss-feeds' ),
				),
				'separator' => 'before',
				'classes'   => $this->upsell_class(),
			)
		);
		$this->add_control(
			'fz-filter-exc-key',
			array(
				'label_block' => true,
				'label'       => __( 'Contains:', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::TEXTAREA,
				'description' => __( 'You can add multiple keywords at once by separating them with comma (,) or use the plus sign (+) to bind multiple keywords.', 'feedzy-rss-feeds' ),
				'classes'     => $this->upsell_class(),
			)
		);
		$this->add_control(
			'fz-filter-from-dt',
			array(
				'label_block' => true,
				'label'       => __( 'Filter items by time range, from:', 'feedzy-rss-feeds' ) . ' ',
				'type'        => 'date_time_local',
				'classes'     => $this->upsell_class(),
				'separator'   => 'before',
			)
		);
		$this->add_control(
			'fz-filter-to-dt',
			array(
				'label_block' => true,
				'label'       => __( 'To', 'feedzy-rss-feeds' ) . ' ',
				'type'        => 'date_time_local',
				'classes'     => $this->upsell_class(),
			)
		);
		$this->end_controls_section(); // End filter items section.

		// Start item options section.
		$this->start_controls_section(
			'fz-item-options',
			array(
				'label' => wp_sprintf( __( 'Item Options', 'feedzy-rss-feeds' ) ),
			)
		);
		$this->add_control(
			'fz-item-target',
			array(
				'label_block' => true,
				'label'       => __( 'Links behavior (opened in the same window or a new tab)', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => array(
					''          => __( 'Auto', 'feedzy-rss-feeds' ),
					'_blank'    => __( '_blank', 'feedzy-rss-feeds' ),
					'_self'     => __( '_self', 'feedzy-rss-feeds' ),
					'_top'      => __( '_top', 'feedzy-rss-feeds' ),
					'framename' => __( 'framename', 'feedzy-rss-feeds' ),
				),
			)
		);
		$this->add_control(
			'fz-item-nofollow-link',
			array(
				'label'        => __( 'Add ”nofollow” tag to links', 'feedzy-rss-feeds' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'label_on'     => esc_html__( 'Yes', 'feedzy-rss-feeds' ),
				'label_off'    => esc_html__( 'No', 'feedzy-rss-feeds' ),
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'fz-item-display-title',
			array(
				'label'        => __( 'Display item Title', 'feedzy-rss-feeds' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Yes', 'feedzy-rss-feeds' ),
				'label_off'    => esc_html__( 'No', 'feedzy-rss-feeds' ),
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);
		$this->add_control(
			'fz-item-title-length',
			array(
				'label'       => __( 'Max Title length (in characters)', 'feedzy-rss-feeds' ),
				'label_block' => true,
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'classes'     => 'feedzy-el-full-width',
			)
		);
		$this->add_control(
			'fz-item-display-desc',
			array(
				'label'        => __( 'Display item Description', 'feedzy-rss-feeds' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Yes', 'feedzy-rss-feeds' ),
				'label_off'    => esc_html__( 'No', 'feedzy-rss-feeds' ),
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);
		$this->add_control(
			'fz-item-desc-length',
			array(
				'label'       => __( 'Max Description length (in characters)', 'feedzy-rss-feeds' ),
				'label_block' => true,
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'classes'     => 'feedzy-el-full-width',
			)
		);
		$this->end_controls_section(); // End item options section.

		// Start item thumbnail section.
		$this->start_controls_section(
			'fz-item-thumbnail',
			array(
				'label' => wp_sprintf( __( 'Item Thumbnail Options', 'feedzy-rss-feeds' ) ),
			)
		);
		$this->add_control(
			'fz-item-thumb',
			array(
				'label_block' => true,
				'label'       => __( 'Display first image, when available', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => array(
					''    => __( 'Yes (without a fallback image)', 'feedzy-rss-feeds' ),
					'yes' => __( 'Yes (with a fallback image)', 'feedzy-rss-feeds' ),
					'no'  => __( 'No', 'feedzy-rss-feeds' ),
				),
			)
		);
		$this->add_control(
			'fz-item-fallback-thumb',
			array(
				'label_block' => true,
				'label'       => __( 'Choose the Fallback Image', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::MEDIA,
				'default'     => array(
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				),
			)
		);
		$this->add_control(
			'fz-item-thumb-size',
			array(
				'label'       => __( 'Thumbnails dimensions', 'feedzy-rss-feeds' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_attr__( 'Eg. 250', 'feedzy-rss-feeds' ),
			)
		);
		$this->add_control(
			'fz-item-thumb-http',
			array(
				'label_block' => true,
				'label'       => __( 'How should we treat HTTP images?', 'feedzy-rss-feeds' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => array(
					''        => __( 'Show with HTTP link', 'feedzy-rss-feeds' ),
					'https'   => __( 'Force HTTPS (please verify that the images exist on HTTPS)', 'feedzy-rss-feeds' ),
					'default' => __( 'Ignore and show the default image instead', 'feedzy-rss-feeds' ),
				),
				'separator'   => 'before',
			)
		);
		$this->end_controls_section(); // End item thumbnail section.

		// Start layout section.
		$this->start_controls_section(
			'fz-layout',
			array(
				'label'   => __( 'Feed Layout', 'feedzy-rss-feeds' ) . $this->upsell_title_label(),
				'classes' => $this->upsell_class(),
			)
		);
		$this->add_control(
			'fz-layout-columns',
			array(
				'label'   => esc_html__( 'Columns', 'feedzy-rss-feeds' ),
				'type'    => Controls_Manager::SLIDER,
				'classes' => $this->upsell_class(),
			)
		);

		$this->add_control(
			'fz-template',
			array(
				'label_block'      => true,
				'type'             => 'fz-layout-template',
				'template_options' => array(
					'default' => array(
						'title' => esc_html__( 'Default', 'feedzy-rss-feeds' ),
						'icon'  => 'eicon-text-align-left',
						'image' => FEEDZY_ABSURL . 'img/{{ui_mode}}-mode-default.png',
					),
					'style1'  => array(
						'title' => esc_html__( 'Style 1', 'feedzy-rss-feeds' ),
						'icon'  => 'eicon-text-align-center',
						'image' => FEEDZY_ABSURL . 'img/{{ui_mode}}-mode-style1.png',
					),
					'style2'  => array(
						'title' => esc_html__( 'Style 2', 'feedzy-rss-feeds' ),
						'icon'  => 'eicon-text-align-right',
						'image' => FEEDZY_ABSURL . 'img/{{ui_mode}}-mode-style2.png',
					),
				),
				'default'          => 'default',
				'toggle'           => true,
				'classes'          => $this->upsell_class(),
			)
		);
		$this->end_controls_section(); // End layout section.

		// Start custom options section.
		$this->start_controls_section(
			'fz-custom-options',
			array(
				'label' => __( 'Feed Items Custom Options', 'feedzy-rss-feeds' ),
			)
		);
		$this->add_control(
			'fz-cus-hide-meta',
			array(
				'label'        => __( 'Hide items Meta', 'feedzy-rss-feeds' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'label_on'     => esc_html__( 'Yes', 'feedzy-rss-feeds' ),
				'label_off'    => esc_html__( 'No', 'feedzy-rss-feeds' ),
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'fz-cus-meta-fields',
			array(
				'label'       => __( 'Display additional meta fields (author, date, time or categories)', 'feedzy-rss-feeds' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'You can add multiple tags at once, by separating them with commas.', 'feedzy-rss-feeds' )
					. ' '
					. '<a target="_blank" href="' . esc_url( 'https://docs.themeisle.com/article/1089-how-to-display-author-date-or-time-from-the-feed' ) . '">'
					. __( 'View documentation here.', 'feedzy-rss-feeds' )
					. '</a>',
			)
		);
		$this->add_control(
			'fz-cus-multiple-meta',
			array(
				'label'       => __( 'When using multiple sources, should we display additional meta fields?', 'feedzy-rss-feeds' ),
				// translators: %s is the list of examples.
				'placeholder' => '(' . sprintf( __( 'eg: %s', 'feedzy-rss-feeds' ), 'source' ) . ')',
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				// translators: this followed by sentence: View documentation here.
				'description' => __( 'You can find more info about available meta field values here.', 'feedzy-rss-feeds' )
					. ' '
					. '<a target="_blank" href="' . esc_url( 'https://docs.themeisle.com/article/1089-how-to-display-author-date-or-time-from-the-feed' ) . '">'
					// translators: this sentence is wrapped with a link tag.
					. __( 'View documentation here.', 'feedzy-rss-feeds' )
					. '</a>',
			)
		);
		$this->add_control(
			'fz-cus-display-price',
			array(
				'label'        => __( 'Display price if available', 'feedzy-rss-feeds' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'label_on'     => esc_html__( 'Yes', 'feedzy-rss-feeds' ),
				'label_off'    => esc_html__( 'No', 'feedzy-rss-feeds' ),
				'return_value' => 'yes',
				'separator'    => 'before',
				'description'  => wp_sprintf(
					// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
					__(
						'Learn more about this feature in %1$s Feedzy docs %2$s .',
						'feedzy-rss-feeds'
					),
					'<a target="_blank" href="' . esc_url( 'https://docs.themeisle.com/article/923-how-price-is-displayed-from-the-feed' ) . '">',
					'</a>'
				),
				'classes'      => $this->upsell_class(),
			)
		);
		$this->add_control(
			'fz-show-item-source',
			array(
				'label'        => __( 'Show item source', 'feedzy-rss-feeds' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'label_on'     => esc_html__( 'Yes', 'feedzy-rss-feeds' ),
				'label_off'    => esc_html__( 'No', 'feedzy-rss-feeds' ),
				'return_value' => 'yes',
				'separator'    => 'before',
				'description'  => __( 'When using multiple sources this will append the item source to the author tag (required).', 'feedzy-rss-feeds' ),
			)
		);
		$this->add_control(
			'fz-offset',
			array(
				'label'       => __( 'Ignore the first N items of the feed', 'feedzy-rss-feeds' ),
				'label_block' => true,
				'type'        => Controls_Manager::NUMBER,
				'separator'   => 'before',
				'min'         => 0,
				'classes'     => 'feedzy-el-full-width',
				'default'     => 0,
			)
		);
		$this->add_control(
			'fz-lazy-load',
			array(
				'label'        => __( 'Lazy load the feed', 'feedzy-rss-feeds' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'label_on'     => esc_html__( 'Yes', 'feedzy-rss-feeds' ),
				'label_off'    => esc_html__( 'No', 'feedzy-rss-feeds' ),
				'return_value' => 'yes',
				'separator'    => 'before',
				'description'  => __( 'Enabling this won\'t slow down the page.', 'feedzy-rss-feeds' ),
			)
		);
		$this->end_controls_section(); // End custom options section.

		// Start referral URL section.
		$this->start_controls_section(
			'fz-referral-url',
			array(
				'label'   => __( 'Referral URL', 'feedzy-rss-feeds' ) . $this->upsell_title_label(),
				'classes' => $this->upsell_class(),
			)
		);
		$this->add_control(
			'fz-referral-parameters',
			array(
				'label'       => __( 'Add your referral parameters', 'feedzy-rss-feeds' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'description' => (
					! feedzy_is_pro() ?
						wp_sprintf(
							// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
							__( 'Unlock this feature and more advanced options with %1$s Feedzy Pro %1$s.', 'feedzy-rss-feeds' ),
							'<a target="_blank" href="' . esc_url( tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'refferal', 'widget-area' ) ) ) . '">',
							'</a>'
						)
						: ''
					),
				'classes'     => $this->upsell_class(),
			)
		);
		$this->end_controls_section(); // End referral URL section.
	}

	/**
	 * Upsell class.
	 *
	 * @param string $css_class ClassName.
	 * @return string
	 */
	public function upsell_class( $css_class = '' ) {
		if ( ! feedzy_is_pro() ) {
			$css_class .= ' fz-feat-locked';
		}
		return trim( $css_class );
	}

	/**
	 * Append pro lable.
	 *
	 * @return string
	 */
	public function upsell_title_label() {
		if ( ! feedzy_is_pro() ) {
			return '<i class="eicon-lock"></i>';
		}
		return '';
	}

	/**
	 * Render widget.
	 */
	protected function render() {
		$title         = $this->get_settings_for_display( 'fz-section-title' );
		$intro         = $this->get_settings_for_display( 'fz-intro-text' );
		$lazy          = $this->get_settings_for_display( 'fz-lazy-load' );
		$fallback_img  = $this->get_settings_for_display( 'fz-item-fallback-thumb' );
		$columns       = $this->get_settings_for_display( 'fz-layout-columns' );
		$hide_meta     = $this->get_settings_for_display( 'fz-cus-hide-meta' );
		$hide_title    = $this->get_settings_for_display( 'fz-item-display-title' );
		$multiple_meta = $this->get_settings_for_display( 'fz-cus-multiple-meta' );

		// Disable lazy load for elementor frontend editor.
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$lazy = false;
		}

		$settings = array(
			'referral_url' => $this->get_settings_for_display( 'fz-referral-parameters' ),
			'price'        => $this->get_settings_for_display( 'fz-cus-display-price' ),
			'template'     => $this->get_settings_for_display( 'fz-template' ),
			'columns'      => ! empty( $columns['size'] ) ? $columns['size'] : 1,
		);

		if ( ! empty( $settings['columns'] ) && $settings['columns'] > 6 ) {
			$settings['columns'] = 6;
		}

		// Check if title is set.
		if ( ! empty( $title ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<p class="widget-title">' . wp_kses_post( $title ) . '</p>';
		}
		// Check if text intro is set.
		if ( ! empty( $intro ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<p class="feedzy-widget-intro">' . wp_kses_post( $intro ) . '</p>';
		}
		$display_source                     = $this->get_settings_for_display( 'fz-show-item-source' );
		$feedzy_widget_shortcode_attributes = array(
			'feeds'                 => $this->get_settings_for_display( 'fz-source' ),
			'max'                   => $this->get_settings_for_display( 'fz-max' ),
			'feed_title'            => 'no',
			'target'                => $this->get_settings_for_display( 'fz-item-target' ),
			'title'                 => $this->get_settings_for_display( 'fz-item-title-length' ),
			'meta'                  => self::bool_to_enum( $this->get_settings_for_display( 'fz-cus-meta-fields' ) ),
			'summary'               => self::bool_to_enum( $this->get_settings_for_display( 'fz-item-display-desc' ) ),
			'summarylength'         => $this->get_settings_for_display( 'fz-item-desc-length' ),
			'thumb'                 => self::bool_to_enum( $this->get_settings_for_display( 'fz-item-thumb' ) ),
			'default'               => ! empty( $fallback_img['url'] ) ? $fallback_img['url'] : '',
			'size'                  => $this->get_settings_for_display( 'fz-item-thumb-size' ),
			'http'                  => $this->get_settings_for_display( 'fz-item-thumb-http' ),
			'keywords_title'        => $this->get_settings_for_display( 'fz-filter-inc-key' ),
			'keywords_inc_on'       => $this->get_settings_for_display( 'fz-filter-inc-on' ),
			'keywords_ban'          => $this->get_settings_for_display( 'fz-filter-exc-key' ),
			'keywords_exc_on'       => $this->get_settings_for_display( 'fz-filter-exc-on' ),
			'error_empty'           => $this->get_settings_for_display( 'fz-error-empty' ),
			'sort'                  => $this->get_settings_for_display( 'fz-orderby' ),
			'refresh'               => $this->get_settings_for_display( 'fz-refresh' ),
			'follow'                => $this->get_settings_for_display( 'fz-item-nofollow-link' ),
			'lazy'                  => $lazy ? self::bool_to_enum( $lazy ) : false,
			'offset'                => $this->get_settings_for_display( 'fz-offset' ),
			'from_datetime'         => $this->get_settings_for_display( 'fz-filter-from-dt' ),
			'to_datetime'           => $this->get_settings_for_display( 'fz-filter-to-dt' ),
			'multiple_meta'         => $display_source ? 'source' : '',
			'disable_default_style' => $this->get_settings_for_display( 'fz-disable-default-style' ),
			'className'             => $this->get_settings_for_display( 'fz-custom-class' ),
			'_dryrun_'              => $this->get_settings_for_display( 'fz-dry-run' ),
			'_dry_run_tags_'        => $this->get_settings_for_display( 'fz-dry-run-tags' ),
		);
		$feedzy_widget_shortcode_attributes = apply_filters( 'feedzy_widget_shortcode_attributes_filter', $feedzy_widget_shortcode_attributes, array(), $settings );
		// Hide item meta.
		if ( empty( $hide_meta ) ) {
			unset( $feedzy_widget_shortcode_attributes['meta'] );
		}
		// Hide item title.
		if ( empty( $hide_title ) || 'yes' !== $hide_title ) {
			$feedzy_widget_shortcode_attributes['title'] = 0;
		}
		// Multiple meta.
		if ( ! empty( $multiple_meta ) ) {
			$feedzy_widget_shortcode_attributes['multiple_meta'] = $multiple_meta;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo feedzy_rss( $feedzy_widget_shortcode_attributes );
	}

	/**
	 * Convert binary values to yes/no touple.
	 *
	 * @param mixed $value string Value to convert to yes/no.
	 *
	 * @return bool
	 */
	public static function bool_to_enum( $value ) {
		if ( in_array( $value, array( 'yes', 'no' ), true ) ) {
			return $value;
		}
		$value = strval( $value );
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( '1' == $value || 'true' == $value ) {
			return 'yes';
		}
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( '0' == $value || 'false' == $value ) {
			return 'no';
		}
		if ( '' === $value ) {
			return 'auto';
		}
		return $value;
	}

	/**
	 * Render content template.
	 */
	protected function content_template() {}

	/**
	 * Render plain content.
	 *
	 * @param object $instance Field instance.
	 */
	public function render_plain_content( $instance = array() ) {}
}
