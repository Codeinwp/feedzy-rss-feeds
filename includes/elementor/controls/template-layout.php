<?php
namespace Elementor;

/**
 * Elementor choose control.
 *
 * A base control for creating choose control. Displays radio buttons styled as
 * groups of buttons with icons for each option.
 *
 * @since 1.0.0
 */
class Control_Template_Layout extends Base_Data_Control {

	/**
	 * Get choose control type.
	 *
	 * Retrieve the control type, in this case `choose`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'fz-layout-template';
	}

	/**
	 * Enqueue assets.
	 */
	public function enqueue() {
		wp_register_script( 'feedzy-elementor', FEEDZY_ABSURL . 'js/feedzy-elementor-widget.js', array( 'jquery' ), true, true );
		wp_enqueue_script( 'feedzy-elementor' );
		$notice_text = '';
		if ( ! feedzy_is_pro() ) {
			$notice_text = '<div class="fz-pro-notice">
			<div class="fz-logo">
				<img src="' . FEEDZY_ABSURL . 'img/{{ui_mode}}-feedzy-logo.png">
			</div>
			<h3>' . esc_html__( 'Discover Feedzy Pro', 'feedzy-rss-feeds' ) . '</h3>
			<p>' . esc_html__( 'With Feedzy Pro you get more features, like Custom Templates, Magic Tags, Keywords filters and much more.', 'feedzy-rss-feeds' ) . '</p>
			<div class="docs-btn">
				<a href="' . tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'more', 'elementoreditor' ) ) . '" target="_blank" class="fz-upgrade-link">' . esc_html__( 'Learn more', 'feedzy-rss-feeds' ) . '</a>
				<span><a href="' . esc_url( 'https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation' ) . '" target="_blank">' . esc_html__( 'Open Feedzy docs', 'feedzy-rss-feeds' ) . '</a></span>
			</div>
		</div>';
		}
		$upsell_notice = '<div class="fz-upsell-notice">';

		$upsell_notice .= wp_sprintf(
			// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
			__( 'Create Reusable Elementor Templates with Feedzy\'s Dynamic Tags Using Feedzy Pro. %1$sLearn more%2$s', 'feedzy-rss-feeds' ),
			'<br/><a target="_blank" href="http://rviv.ly/qjK7R1" >',
			'</a><button type="button" class="remove-alert"><span class="dashicons dashicons-no-alt"></span></button>'
		);

		$upsell_notice .= '<div>';
		wp_localize_script(
			'feedzy-elementor',
			'FeedzyElementorEditor',
			array(
				'notice'         => $notice_text,
				'security'       => wp_create_nonce( FEEDZY_BASEFILE ),
				'pro_title_text' => __( 'Unlock this feature with Feedzy Pro', 'feedzy-rss-feeds' ),
				'upsell_notice'  => ( ! feedzy_is_pro() && ! \Feedzy_Rss_Feeds_Ui::had_dismissed_notice() ) ? $upsell_notice : '',
			)
		);
	}

	/**
	 * Render choose control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function content_template() {
		$control_uid_input_type = '{{value}}';
		?>
	<div class="elementor-control-field">
		<div class="elementor-control-input-wrapper">
			<div class="elementor-choices fz-layout-choices">
				<ul class="fz-layout-list">
					<# _.each( data.template_options, function( options, value ) {
						var checked = '';
						if ( data.controlValue === '' ) {
							data.controlValue = 'default';
						}
						if ( data.controlValue === value ) {
							checked = ' checked';
						}
						#>
						<li>
							<input id="<?php $this->print_control_uid( $control_uid_input_type ); ?>" type="radio" name="elementor-choose-{{ data.name }}-{{ data._cid }}" value="{{ value }}"{{checked}}>
							<label class="elementor-control-unit tooltip-target" for="<?php $this->print_control_uid( $control_uid_input_type ); ?>" data-tooltip="{{ options.title }}" title="{{ options.title }}">
							<div class="img">
								<img src="{{{ options.image }}}">
							</div>
							<span>{{{ options.title }}}</span>
							</label>
						</li>
					<# } ); #>
				</ul>
			</div>
		</div>
	</div>

	<# if ( data.description ) { #>
	<div class="elementor-control-field-description">{{{ data.description }}}</div>
	<# } #>
		<?php
	}

	/**
	 * Get choose control default settings.
	 *
	 * Retrieve the default settings of the choose control. Used to return the
	 * default settings while initializing the choose control.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		return array(
			'template_options' => array(),
			'toggle'           => true,
		);
	}
}
