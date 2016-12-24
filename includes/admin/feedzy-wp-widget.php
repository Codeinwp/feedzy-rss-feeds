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
class feedzy_wp_widget extends WP_Widget {

	/**
	 * The loader class.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      Feedzy_Rss_Feeds_Admin    $plugin_admin    The loader class of the plugin.
	 */
	private $plugin_admin;

    /**
     * @access  private
     * @since   3.0.0
     * @var     array $elements The form elements of the widget
     */
    private $elements;

    /**
     * @access  private
     * @since   3.0.0
     * @var     array $defaults The defaults of the elements
     */
    private $defaults;


	/**
	 * The class instance.
	 *
	 * @since    3.0.0
	 * @access   public
	 * @var      Feedzy_Rss_Feeds_Widget    $instance    The instance of the class.
	 */
	public static $instance;

	/**
	 * The Feedzy_Rss_feeds_widget constructor method
	 *
	 * @since   3.0.0
	 * @access  public
	 * @param   Feedzy_Rss_Feeds_Admin $plugin_admin The Feedzy_Rss_Feeds_Admin object.
	 */
	public function __construct( $plugin_admin ) {
		parent::__construct( false, $name = __( 'Feedzy RSS Feeds', 'feedzy_rss_translate' ) );

		$this->plugin_admin = $plugin_admin;


		self::$instance = $this;

	}

	/**
	 * Returns the instance of this class as in the singleton pattern
	 *
	 * @since    3.0.0
	 * @access   public
	 * @return Feedzy_Rss_Feeds_Widget
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
        $this->defaults = Feedzy_Rss_Feeds_Ui_Lang::get_form_defaults();
        // rename title to title length as widget instance already have one
        $this->defaults['titlelength'] = $this->defaults['title'];
        unset($this->defaults['title']);

        $this->elements = Feedzy_Rss_Feeds_Ui_Lang::get_form_elements();

        register_widget( $this );
	}
	/**
	 * The widget form creation
	 *
	 * @since   3.0.0
	 * @access  public
	 * @param   string $instance The Widget instance.
	 * @return mixed
	 */
	public function form( $instance ) {

        $instance = wp_parse_args( $instance, $this->defaults );

		$widget_form ='<p>
				<label for="' . $this->get_field_id( 'title' ) . '">' . __( 'Widget Title', 'feedzy_rss_translate' ) . '</label>
				<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr($instance['title']) . '" />
			</p>
			<p>
				<label for="' . $this->get_field_id( 'textarea' ) . '">' . __( 'Intro text', 'feedzy_rss_translate' ) . '</label>
				<textarea class="widefat" id="' . $this->get_field_id( 'textarea' ) . '" name="' . $this->get_field_name( 'textarea' ) . '">' . esc_attr($instance['textarea']) . '</textarea>
			</p>';



        foreach( $this->elements as $key_section => $section ){
		    $widget_form .= '<hr/><h4>'.$section['title'].'</h4>';
		    $widget_form .= '<small>'.$section['description'].'</small>';
		    foreach ($section['elements'] as $id=>$element){
		        if($element['disabled']) continue;
		        if($id == 'title') $id = 'titlelength';
		        $widget_form .='<p>';
		        $widget_form .='<label for="'.$this->get_field_id($id).'">'.$element['label'].'</label>';
		        if($element['type'] == 'text' ){
		            $widget_form .= '<input class="widefat" id="'.$this->get_field_id($id).'" name="'.$this->get_field_name($id).'" type="text" value="'.esc_attr($instance[$id]).'" />';
                }
		        if($element['type'] == 'select'){
		            $widget_form .= '<select class="widefat" id="'.$this->get_field_id($id).'" name="'.$this->get_field_name($id).'" >';
		             foreach($element['opts'] as $select_option){
		                 $widget_form .= '<option '.selected(esc_attr($select_option['value']),$instance[$id],false).'value="'.esc_attr($select_option['value']).'">'.esc_html($select_option['label']).'</option>';
                     }

		            $widget_form .='</select>';
                }
		        $widget_form .='</p>';

            }
        }
        $widget_form .='<hr/>';
		$widget_form = apply_filters( 'feedzy_widget_form_filter', $widget_form, $instance, $this->defaults );
		echo $widget_form;

	}

	/**
	 *
	 * The update method
	 *
	 * @since   3.0.0
	 * @access  public
	 * @param   array $new_instance The new widget instance.
	 * @param   array $old_instance The old widget instance.
	 * @return  array
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']			= strip_tags( $new_instance['title'] );

		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['textarea'] 	= $new_instance['textarea'];
		}else{
            $instance['textarea'] 	    = stripslashes( wp_filter_post_kses( addslashes( $new_instance['textarea'] ) ) );
        }
        $forms_ids = array_keys($this->defaults);

		foreach($forms_ids as $key){

            $instance[$key] 			    = strip_tags( $new_instance[$key] );
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
	 * @param   array $args     The args to use.
	 * @param   array $instance The widget instance.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
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

		$items = array( 'meta', 'summary' );
		foreach ( $items as $item ) {
			$instance[ $item ] = 'no';
			if ( $instance[ $item ] == true ) {
				$instance[ $item ] = 'yes';
			}
		}

		// Fix for versions before 2.3.1
		if ( $instance['thumb'] == '1' ) {
			$instance['thumb'] = 'yes';
		} elseif ( $instance['thumb'] == '0' ) {
			$instance['thumb'] = 'no';
		}

		$feedzy_widget_shortcode_attributes = array(
			'feeds' 			=> $instance['feeds'],
			'max' 				=> $instance['max'],
			'feed_title' 		=> 'no',
			'target' 			=> $instance['target'],
			'title' 			=> $instance['titlelength'],
			'meta' 				=> $instance['meta'],
			'summary' 			=> $instance['summary'],
			'summarylength'	 	=> $instance['summarylength'],
			'thumb' 			=> $instance['thumb'],
			'default' 			=> $instance['default'],
			'size' 				=> $instance['size'],
			'keywords_title' 	=> $instance['keywords_title'],
		);

		$feedzy_widget_shortcode_attributes = apply_filters( 'feedzy_widget_shortcode_attributes_filter', $feedzy_widget_shortcode_attributes, $args, $instance );

		// Call the shortcode function
		echo $this->plugin_admin->feedzy_rss( $feedzy_widget_shortcode_attributes );

		echo $args['after_widget'];

	}

}
