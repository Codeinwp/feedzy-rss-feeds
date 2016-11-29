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
class Feedzy_Rss_Feeds_Widget extends WP_Widget {

	/**
	 * The loader class.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      Feedzy_Rss_Feeds_Admin    $plugin_admin    The loader class of the plugin.
	 */
	private $plugin_admin;

	/**
	 * The Feedzy_Rss_feeds_widget constructor method
	 *
	 * @param   Feedzy_Rss_Feeds_Admin $plugin_admin The Feedzy_Rss_Feeds_Admin object.
	 */
	public function __construct( $plugin_admin ) {
		parent::__construct( false, $name = __( 'Feedzy RSS Feeds', 'feedzy_rss_translate' ) );

		$this->plugin_admin = $plugin_admin;

	}

	/**
	 * The register method for registering this widget class
	 */
	public function registerWidget() {
		register_widget( $this );
	}

	/**
	 * The widget form creation
	 *
	 * @param string $instance The Widget instance.
	 */
	public function form( $instance ) {
		// Check values
		$defaults = array(
			'title' => '',
			'textarea' => '',
			'feeds' => '',
			'max' => '',
			'target' => '',
			'meta' => '',
			'summary' => '',
			'summarylength' => '',
			'thumb' => '',
			'default' => '',
			'size' => '',
			'keywords_title' => '',
		);
		$instance = wp_parse_args( $instance, $defaults );
		if ( $instance ) {
			$title 			= esc_attr( $instance['title'] );
			$textarea 		= esc_attr( $instance['textarea'] );
			$feeds			= esc_attr( $instance['feeds'] );
			$max 			= esc_attr( $instance['max'] );
			$target 		= esc_attr( $instance['target'] );
			$titlelength 	= esc_attr( $instance['titlelength'] );
			$meta 			= esc_attr( $instance['meta'] );
			$summary 		= esc_attr( $instance['summary'] );
			$summarylength 	= esc_attr( $instance['summarylength'] );
			$thumb 			= esc_attr( $instance['thumb'] );
			$default 		= esc_attr( $instance['default'] );
			$size 			= esc_attr( $instance['size'] );
			$keywords_title = esc_attr( $instance['keywords_title'] );
		}

		$options = array( '_blank', '_parent', '_self', '_top', 'framename' );
		$option_list_links = '';
		foreach ( $options as $option ) {
			$option_list_links .= '<option value="' . $option . '" id="' . $option . '"' . ( ( $target == $option ) ? ' selected="selected"' : '' ) . '>' . $option . '</option>';
		}

		// Fix for versions before 2.3.1
		if ( $thumb == '1' ) {
			$thumb = 'yes';
		} elseif ( $thumb == '0' ) {
			$thumb = 'no';
		}
		$options = array(
			array( 'no', __( 'No', 'feedzy_rss_translate' ) ),
			array( 'yes', __( 'Yes', 'feedzy_rss_translate' ) ),
			array( 'auto', __( 'Auto', 'feedzy_rss_translate' ) ),
		);
		$option_list_thumbs = '';
		foreach ( $options as $option ) {
			$option_list_thumbs .= '<option value="' . $option[0] . '" id="' . $option[0] . '"' . ( ( $thumb == $option[0] ) ? ' selected="selected"' : '' ) . '>' . $option[1] . '</option>';
		}

		echo '
			<p>
				<label for="' . $this->get_field_id( 'title' ) . '">' . __( 'Widget Title', 'feedzy_rss_translate' ) . '</label>
				<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . $title . '" />
			</p>
			<p>
				<label for="' . $this->get_field_id( 'textarea' ) . '">' . __( 'Intro text', 'feedzy_rss_translate' ) . '</label>
				<textarea class="widefat" id="' . $this->get_field_id( 'textarea' ) . '" name="' . $this->get_field_name( 'textarea' ) . '">' . $textarea . '</textarea>
			</p>
			<p>
				<label for="' . $this->get_field_id( 'feeds' ) . '">' . __( 'The feed(s) URL (comma-separated list).', 'feedzy_rss_translate' ) . '</label>
				<input class="widefat" id="' . $this->get_field_id( 'feeds' ) . '" name="' . $this->get_field_name( 'feeds' ) . '" type="text" value="' . $feeds . '" />
			</p>
			<p>
				<label for="' . $this->get_field_id( 'max' ) . '">' . __( 'Number of items to display.', 'feedzy_rss_translate' ) . '</label>
				<input class="widefat"  id="' . $this->get_field_id( 'max' ) . '" name="' . $this->get_field_name( 'max' ) . '" type="text" value="' . $max . '" />
			</p>
			<p>
				<label for="' . $this->get_field_id( 'target' ) . '">' . __( 'Links may be opened in the same window or a new tab.', 'feedzy_rss_translate' ) . '</label>
				<select id="' . $this->get_field_id( 'target' ) . '" name="' . $this->get_field_name( 'target' ) . '" class="widefat">
					' . $option_list_links . '
				</select>
			</p>
			<p>
				<label for="' . $this->get_field_id( 'titlelength' ) . '">' . __( 'Trim the title of the item after X characters.', 'feedzy_rss_translate' ) . '</label>
				<input class="widefat" id="' . $this->get_field_id( 'titlelength' ) . '" name="' . $this->get_field_name( 'titlelength' ) . '" type="text" value="' . $titlelength . '" />
			</p>
			<p>
				<input id="' . $this->get_field_id( 'meta' ) . '" name="' . $this->get_field_name( 'meta' ) . '" type="checkbox" value="1" ' . checked( '1', $meta, false ) . ' />
				<label for="' . $this->get_field_id( 'meta' ) . '">' . __( 'Should we display the date of publication and the author name?', 'feedzy_rss_translate' ) . '</label>
			</p>
			<p>
				<input id="' . $this->get_field_id( 'summary' ) . '" name="' . $this->get_field_name( 'summary' ) . '" type="checkbox" value="1" ' . checked( '1', $summary, false ) . ' />
				<label for="' . $this->get_field_id( 'summary' ) . '">' . __( 'Should we display a description (abstract) of the retrieved item?', 'feedzy_rss_translate' ) . '</label>
			</p>
			<p>
				<label for="' . $this->get_field_id( 'summarylength' ) . '">' . __( 'Crop description (summary) of the element after X characters.', 'feedzy_rss_translate' ) . '</label>
				<input class="widefat" id="' . $this->get_field_id( 'summarylength' ) . '" name="' . $this->get_field_name( 'summarylength' ) . '" type="text" value="' . $summarylength . '" />
			</p>
			<p>
				<label for="' . $this->get_field_id( 'thumb' ) . '">' . __( 'Should we display the first image of the content if it is available?', 'feedzy_rss_translate' ) . '</label>
				<select id="' . $this->get_field_id( 'thumb' ) . '" name="' . $this->get_field_name( 'thumb' ) . '" class="widefat">
					' . $option_list_thumbs . '
				</select>
			</p>
			<p>
				<label for="' . $this->get_field_id( 'default' ) . '">' . __( 'Default thumbnail URL if no image is found.', 'feedzy_rss_translate' ) . '</label>
				<input class="widefat" id="' . $this->get_field_id( 'default' ) . '" name="' . $this->get_field_name( 'default' ) . '" type="text" value="' . $default . '" />
			</p>
			<p>
				<label for="' . $this->get_field_id( 'size' ) . '">' . __( 'Thumblails dimension. Do not include "px". Eg: 150', 'feedzy_rss_translate' ) . '</label>
				<input class="widefat" id="' . $this->get_field_id( 'size' ) . '" name="' . $this->get_field_name( 'size' ) . '" type="text" value="' . $size . '" />
			</p>
			<p>
				<label for="' . $this->get_field_id( 'keywords_title' ) . '">' . __( 'Only display item if title contains specific keyword(s) (comma-separated list/case sensitive).', 'feedzy_rss_translate' ) . '</label>
				<input class="widefat" id="' . $this->get_field_id( 'keywords_title' ) . '" name="' . $this->get_field_name( 'keywords_title' ) . '" type="text" value="' . $keywords_title . '" />
			</p>
		';

	}

	/**
	 *
	 * The update method
	 *
	 * @param array $new_instance The new widget instance.
	 * @param array $old_instance The old widget instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance['title']			= strip_tags( $new_instance['title'] );
		$instance['textarea'] 	= stripslashes( wp_filter_post_kses( addslashes( $new_instance['textarea'] ) ) );

		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['textarea'] 	= $new_instance['textarea'];
		}

		$instance['feeds'] 			= strip_tags( $new_instance['feeds'] );
		$instance['max'] 				= strip_tags( $new_instance['max'] );
		$instance['target'] 			= strip_tags( $new_instance['target'] );
		$instance['titlelength'] 		= strip_tags( $new_instance['titlelength'] );
		$instance['meta'] 			= strip_tags( $new_instance['meta'] );
		$instance['summary'] 			= strip_tags( $new_instance['summary'] );
		$instance['summarylength'] 	= strip_tags( $new_instance['summarylength'] );
		$instance['thumb'] 			= strip_tags( $new_instance['thumb'] );
		$instance['default'] 			= strip_tags( $new_instance['default'] );
		$instance['size'] 			= strip_tags( $new_instance['size'] );
		$instance['keywords_title'] 	= strip_tags( $new_instance['keywords_title'] );

		return $instance;

	}

	/**
	 *
	 * The widget function
	 *
	 * @param array $args     The args to use.
	 * @param array $instance The widget instance.
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

		// Call the shortcode function
		echo $this->plugin_admin->feedzy_rss( array(
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
		) );

		echo $args['after_widget'];

	}

}
