<?php
/**
 * Register elementor widget.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/elementor
 */

/**
 * The Widget functionality of the plugin.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/elementor
 * @author     Themeisle <friends@themeisle.com>
 */
class Feedzy_Rss_Feeds_Elementor {

	/**
	 * Enqueue scripts.
	 */
	public function feedzy_elementor_widgets_assets() {
		wp_register_style( 'feedzy-elementor', FEEDZY_ABSURL . 'css/feedzy-elementor-widget.css', array(), true );
		wp_enqueue_style( 'feedzy-elementor' );
	}

	/**
	 * Editor frontend before script.
	 */
	public function feedzy_elementor_before_enqueue_scripts() {
		wp_register_style( 'feedzy-rss-feeds-elementor', FEEDZY_ABSURL . 'css/feedzy-rss-feeds.css', array( 'elementor-frontend' ), true, 'all' );
		wp_enqueue_style( 'feedzy-rss-feeds-elementor' );
	}

	/**
	 * Register feedzy widget.
	 *
	 * @return void
	 */
	public function feedzy_elementor_widgets_registered() {
		// We check if the Elementor plugin has been installed / activated.
		if ( defined( 'ELEMENTOR_PATH' ) && class_exists( 'Elementor\Widget_Base' ) ) {
			require_once FEEDZY_ABSPATH . '/includes/elementor/widgets/register-widget.php';
			\Elementor\Plugin::instance()->widgets_manager->register( new Feedzy_Register_Widget() );

			add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'feedzy_elementor_widgets_assets' ) );
		}
	}

	/**
	 * Register datetime-local control.
	 *
	 * @param \Elementor\Controls_Manager $controls_manager Elementor controls manager.
	 * @return void
	 */
	public function feedzy_elementor_register_datetime_local_control( $controls_manager ) {
		require_once FEEDZY_ABSPATH . '/includes/elementor/controls/datetime-local.php';
		require_once FEEDZY_ABSPATH . '/includes/elementor/controls/template-layout.php';
		$controls_manager->register( new \Elementor\Control_Date_Time_Local() );
		$controls_manager->register( new \Elementor\Control_Template_Layout() );
	}
}
