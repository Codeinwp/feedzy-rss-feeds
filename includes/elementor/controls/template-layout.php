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
							<input id="<?php $this->print_control_uid( $control_uid_input_type ); ?>" type="radio" name="{{ data.name }}" value="{{ value }}"{{checked}}>
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
			'toggle'  => true,
		);
	}
}
