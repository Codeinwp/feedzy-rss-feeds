<?php
namespace Elementor;

/**
 * Elementor date/time control.
 *
 * A base control for creating date time control. Displays a date/time picker
 * based on the Flatpickr library @see https://chmln.github.io/flatpickr/ .
 *
 * @since 1.0.0
 */
class Control_Date_Time_Local extends Base_Data_Control {

	/**
	 * Get date time control type.
	 *
	 * Retrieve the control type, in this case `date_time`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'date_time_local';
	}

	/**
	 * Get date time control default settings.
	 *
	 * Retrieve the default settings of the date time control. Used to return the
	 * default settings while initializing the date time control.
	 *
	 * @since 1.8.0
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		return array(
			'label_block'    => true,
			'picker_options' => array(),
		);
	}

	/**
	 * Render date time control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function content_template() {
		?>
	<div class="elementor-control-field">
		<label for="<?php $this->print_control_uid(); ?>" class="elementor-control-title">{{{ data.label }}}</label>
		<div class="elementor-control-input-wrapper">
		<input id="<?php $this->print_control_uid(); ?>" placeholder="{{ view.getControlPlaceholder() }}" class="fz-datetime-local" type="datetime-local" data-setting="{{ data.name }}">
		</div>
	</div>
	<# if ( data.description ) { #>
		<div class="elementor-control-field-description">{{{ data.description }}}</div>
	<# } #>
		<?php
	}
}
