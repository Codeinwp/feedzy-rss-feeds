<?php
/**
 * The Widget functionality of the plugin.
 *
 * @link       https://themeisle.com
 * @since      3.0.0
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
 */

/**
 * The Widget functionality of the plugin.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
 * @author     Themeisle <friends@themeisle.com>
 */
// @codingStandardsIgnoreStart
class feedzy_wp_widget extends WP_Widget {
	// @codingStandardsIgnoreEnd
	/**
	 * The class instance.
	 *
	 * @since    3.0.0
	 * @access   public
	 * @var      feedzy_wp_widget $instance The instance of the class.
	 */
	public static $instance;

	/**
	 * The feedzy_wp_widget constructor method
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   Feedzy_Rss_Feeds_Admin $plugin_admin The Feedzy_Rss_Feeds_Admin object.
	 */
	public function __construct( $plugin_admin = null ) {
		parent::__construct( false, $name = __( 'Feedzy RSS Feeds', 'feedzy-rss-feeds' ) );
		self::$instance = $this;
	}

	/**
	 * Returns the instance of this class as in the singleton pattern
	 *
	 * @since    3.0.0
	 * @access   public
	 * @return feedzy_wp_widget
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * The widget form creation
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $instance The Widget instance.
	 *
	 * @return mixed
	 */
	public function form( $instance ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : array();
		// to prevent conflicts with plugins such as siteorigin page builder that call this function from outside of the 'widgets' screen.
		if ( ! empty( $screen ) && ! in_array( $screen->id, apply_filters( 'feedzy_allow_widgets_in_screen', array( 'widgets', 'customize' ) ), true ) ) {
			return;
		}
		$instance    = wp_parse_args( $instance, $this->get_widget_defaults() );
		$widget_form = '<p>
				<label for="' . $this->get_field_id( 'title' ) . '">' . __( 'Widget Title', 'feedzy-rss-feeds' ) . '</label>
				<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr( $instance['title'] ) . '" />
			</p>
			<p>
				<label for="' . $this->get_field_id( 'textarea' ) . '">' . __( 'Intro text', 'feedzy-rss-feeds' ) . '</label>
				<textarea class="widefat" id="' . $this->get_field_id( 'textarea' ) . '" name="' . $this->get_field_name( 'textarea' ) . '">' . esc_textarea( $instance['textarea'] ) . '</textarea>
			</p>';
		foreach ( Feedzy_Rss_Feeds_Ui_Lang::get_form_elements() as $key_section => $section ) {
			$widget_form .= '<hr/><h4>' . $section['title'] . '</h4>';
			if ( isset( $section['description'] ) ) {
				$widget_form .= '<small>' . $section['description'] . '</small>';
			}
			foreach ( $section['elements'] as $id => $element ) {
				if ( isset( $element['disabled'] ) && $element['disabled'] ) {
					continue;
				}
				if ( 'feed_title' === $id ) {
					continue;
				}
				if ( 'title' === $id ) {
					$id = 'titlelength';
				}
				$widget_form .= '<p>';
				$widget_form .= '<label for="' . $this->get_field_id( $id ) . '">' . $element['label'] . '</label>';
				if ( 'text' === $element['type'] || 'file' === $element['type'] ) {
					$widget_form .= '<input class="widefat" id="' . $this->get_field_id( $id ) . '" name="' . $this->get_field_name( $id ) . '" type="text" value="' . esc_attr( $instance[ $id ] ) . '" />';
				}
				if ( 'number' === $element['type'] ) {
					$widget_form .= '<input class="widefat" id="' . $this->get_field_id( $id ) . '" name="' . $this->get_field_name( $id ) . '" type="number" value="' . esc_attr( $instance[ $id ] ) . '" />';
				}
				if ( 'datetime-local' === $element['type'] ) {
					$widget_form .= '<input class="widefat" id="' . $this->get_field_id( $id ) . '" name="' . $this->get_field_name( $id ) . '" type="datetime-local" value="' . esc_attr( $instance[ $id ] ) . '" />';
				}
				if ( 'select' === $element['type'] || 'radio' === $element['type'] ) {
					$widget_form .= '<select class="widefat" id="' . $this->get_field_id( $id ) . '" name="' . $this->get_field_name( $id ) . '" >';
					foreach ( $element['opts'] as $select_option ) {
						$widget_form .= '<option ' . selected( esc_attr( $select_option['value'] ), self::bool_to_enum( $instance[ $id ] ), false ) . 'value="' . esc_attr( $select_option['value'] ) . '">' . esc_html( $select_option['label'] ) . '</option>';
					}
					$widget_form .= '</select>';
				}
				$widget_form .= '</p>';

			}
		}
		$widget_form .= '<hr/>';
		$widget_form  = apply_filters( 'feedzy_widget_form_filter', $widget_form, $instance, $this->get_widget_defaults() );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $widget_form;
	}

	/**
	 * Get widget default values for params
	 *
	 * @return array List of defaults values
	 */
	public function get_widget_defaults() {
		$defaults = Feedzy_Rss_Feeds_Ui_Lang::get_form_defaults();
		// rename title to title length as widget instance already have one.
		$defaults['titlelength'] = $defaults['title'];
		$defaults['title']       = '';
		$defaults['textarea']    = '';

		return $defaults;
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
	 *
	 * The update method
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   array $new_instance The new widget instance.
	 * @param   array $old_instance The old widget instance.
	 *
	 * @return  array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = wp_strip_all_tags( $new_instance['title'] );
		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['textarea'] = $new_instance['textarea'];
		} else {
			$instance['textarea'] = stripslashes( wp_filter_post_kses( addslashes( $new_instance['textarea'] ) ) );
		}
		$forms_ids = array_keys( $this->get_widget_defaults() );
		foreach ( $forms_ids as $key ) {
			$instance[ $key ] = wp_strip_all_tags( isset( $new_instance[ $key ] ) ? $new_instance[ $key ] : '' );
		}
		$instance = apply_filters( 'feedzy_widget_update_filter', $instance, $new_instance );

		return $instance;
	}

	/**
	 *
	 * The widget function
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   array $args The args to use.
	 * @param   array $instance The widget instance.
	 */
	public function widget( $args, $instance ) {
		$title    = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
		$textarea = apply_filters( 'widget_textarea', empty( $instance['textarea'] ) ? '' : $instance['textarea'], $instance );
		// Display the widget body.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_widget'];
		// Check if title is set.
		if ( $title ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['before_title'] . wp_kses_post( $title ) . $args['after_title'];
		}
		// Check if text intro is set.
		if ( isset( $instance['textarea'] ) && ! empty( $instance['textarea'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<p class="feedzy-widget-intro">' . wp_kses_post( wpautop( $textarea ) ) . '</p>';
		}
		$feedzy_widget_shortcode_attributes = array(
			'feeds'                 => isset( $instance['feeds'] ) ? $instance['feeds'] : '',
			'max'                   => isset( $instance['max'] ) ? $instance['max'] : '',
			'feed_title'            => 'no',
			'target'                => isset( $instance['target'] ) ? $instance['target'] : '',
			'title'                 => isset( $instance['titlelength'] ) ? $instance['titlelength'] : '',
			'meta'                  => self::bool_to_enum( isset( $instance['meta'] ) ? $instance['meta'] : '' ),
			'summary'               => self::bool_to_enum( isset( $instance['summary'] ) ? $instance['summary'] : '' ),
			'summarylength'         => isset( $instance['summarylength'] ) ? $instance['summarylength'] : '',
			'thumb'                 => self::bool_to_enum( isset( $instance['thumb'] ) ? $instance['thumb'] : '' ),
			'default'               => isset( $instance['default'] ) ? $instance['default'] : '',
			'size'                  => isset( $instance['size'] ) ? $instance['size'] : '',
			'keywords_title'        => isset( $instance['keywords_title'] ) ? $instance['keywords_title'] : '',
			'keywords_ban'          => isset( $instance['keywords_ban'] ) ? $instance['keywords_ban'] : '',
			'error_empty'           => isset( $instance['error_empty'] ) ? $instance['error_empty'] : '',
			'sort'                  => isset( $instance['sort'] ) ? $instance['sort'] : '',
			'refresh'               => isset( $instance['refresh'] ) ? $instance['refresh'] : '',
			'follow'                => isset( $instance['follow'] ) ? $instance['follow'] : '',
			'http'                  => isset( $instance['http'] ) ? $instance['http'] : '',
			'lazy'                  => isset( $instance['lazy'] ) ? self::bool_to_enum( $instance['lazy'] ) : false,
			'offset'                => isset( $instance['offset'] ) ? $instance['offset'] : '',
			'multiple_meta'         => isset( $instance['multiple_meta'] ) ? $instance['multiple_meta'] : '',
			'keywords_inc_on'       => isset( $instance['keywords_inc_on'] ) ? $instance['keywords_inc_on'] : '',
			'keywords_exc_on'       => isset( $instance['keywords_exc_on'] ) ? $instance['keywords_exc_on'] : '',
			'from_datetime'         => isset( $instance['from_datetime'] ) ? $instance['from_datetime'] : '',
			'to_datetime'           => isset( $instance['to_datetime'] ) ? $instance['to_datetime'] : '',
			'disable_default_style' => isset( $instance['disable_default_style'] ) ? $instance['disable_default_style'] : 'no',
			'className'             => isset( $instance['classname'] ) ? $instance['classname'] : '',
			'_dryrun_'              => isset( $instance['dryrun'] ) ? $instance['dryrun'] : '',
			'_dry_run_tags_'        => isset( $instance['dry_run_tags'] ) ? $instance['dry_run_tags'] : '',
		);
		$feedzy_widget_shortcode_attributes = apply_filters( 'feedzy_widget_shortcode_attributes_filter', $feedzy_widget_shortcode_attributes, $args, $instance );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo feedzy_rss( $feedzy_widget_shortcode_attributes );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget'];
	}
}
