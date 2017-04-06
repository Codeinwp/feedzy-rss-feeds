<?php
/**
 * The Widget functionality of the plugin.
 *
 * @link       http://themeisle.com
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
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * The register method for registering this widget class
	 *
	 * @since    3.0.0
	 * @access   public
	 */
	public function registerWidget() {
		register_widget( 'feedzy_wp_widget' );
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
		$instance    = wp_parse_args( $instance, $this->get_widget_defaults() );
		$widget_form = '<p>
				<label for="' . $this->get_field_id( 'title' ) . '">' . __( 'Widget Title', 'feedzy-rss-feeds' ) . '</label>
				<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr( $instance['title'] ) . '" />
			</p>
			<p>
				<label for="' . $this->get_field_id( 'textarea' ) . '">' . __( 'Intro text', 'feedzy-rss-feeds' ) . '</label>
				<textarea class="widefat" id="' . $this->get_field_id( 'textarea' ) . '" name="' . $this->get_field_name( 'textarea' ) . '">' . esc_attr( $instance['textarea'] ) . '</textarea>
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
				if ( $id == 'feed_title' ) {
					continue;
				}
				if ( $id == 'title' ) {
					$id = 'titlelength';
				}
				$widget_form .= '<p>';
				$widget_form .= '<label for="' . $this->get_field_id( $id ) . '">' . $element['label'] . '</label>';
				if ( $element['type'] == 'text' || $element['type'] == 'file' ) {
					$widget_form .= '<input class="widefat" id="' . $this->get_field_id( $id ) . '" name="' . $this->get_field_name( $id ) . '" type="text" value="' . esc_attr( $instance[ $id ] ) . '" />';
				}
				if ( $element['type'] == 'number' ) {
					$widget_form .= '<input class="widefat" id="' . $this->get_field_id( $id ) . '" name="' . $this->get_field_name( $id ) . '" type="number" value="' . esc_attr( $instance[ $id ] ) . '" />';
				}
				if ( $element['type'] == 'select' || $element['type'] == 'radio' ) {
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
		$widget_form = apply_filters( 'feedzy_widget_form_filter', $widget_form, $instance, $this->get_widget_defaults() );
		echo $widget_form;

	}

	/**
	 * Get widget default values for params
	 *
	 * @return array List of defaults values
	 */
	public function get_widget_defaults() {
		$defaults = Feedzy_Rss_Feeds_Ui_Lang::get_form_defaults();
		// rename title to title length as widget instance already have one
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
		if ( in_array( $value, array( 'yes', 'no' ) ) ) {
			return $value;
		}
		$value = strval( $value );
		if ( $value == '1' || $value == 'true' ) {
			return 'yes';
		}
		if ( $value == '0' || $value == 'false' ) {
			return 'no';
		}
		if ( $value == '' ) {
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
		$instance['title'] = strip_tags( $new_instance['title'] );
		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['textarea'] = $new_instance['textarea'];
		} else {
			$instance['textarea'] = stripslashes( wp_filter_post_kses( addslashes( $new_instance['textarea'] ) ) );
		}
		$forms_ids = array_keys( $this->get_widget_defaults() );
		foreach ( $forms_ids as $key ) {
			$instance[ $key ] = strip_tags( isset( $new_instance[ $key ] ) ? $new_instance[ $key ] : '' );
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
		$title    = apply_filters( 'widget_title', $instance['title'] );
		$textarea = apply_filters( 'widget_textarea', empty( $instance['textarea'] ) ? '' : $instance['textarea'], $instance );
		// Display the widget body
		echo $args['before_widget'];
		// Check if title is set
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		// Check if text intro is set
		if ( isset( $instance['textarea'] ) && ! empty( $instance['textarea'] ) ) {
			echo '<p class="feedzy-widget-intro">' . wpautop( $textarea ) . '</p>';
		}
		$feedzy_widget_shortcode_attributes = array(
			'feeds'          => $instance['feeds'],
			'max'            => $instance['max'],
			'feed_title'     => 'no',
			'target'         => $instance['target'],
			'title'          => $instance['titlelength'],
			'meta'           => self::bool_to_enum( $instance['meta'] ),
			'summary'        => self::bool_to_enum( $instance['summary'] ),
			'summarylength'  => $instance['summarylength'],
			'thumb'          => self::bool_to_enum( $instance['thumb'] ),
			'default'        => $instance['default'],
			'size'           => $instance['size'],
			'keywords_title' => $instance['keywords_title'],
		);
		$feedzy_widget_shortcode_attributes = apply_filters( 'feedzy_widget_shortcode_attributes_filter', $feedzy_widget_shortcode_attributes, $args, $instance );

		echo feedzy_rss( $feedzy_widget_shortcode_attributes );
		echo $args['after_widget'];

	}

}
