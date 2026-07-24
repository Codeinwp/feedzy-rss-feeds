<?php
/**
 * Generic plugin health tests: bootstrap, singletons, post types,
 * shortcode, REST routes and constants.
 *
 * @since      5.1.0
 *
 * @package    feedzy-rss-feeds
 */
class Test_Plugin_Health extends WP_UnitTestCase {

	/**
	 * Test the main plugin class is a singleton and exposes the admin object.
	 *
	 * @access public
	 */
	public function test_main_plugin_singleton() {
		$this->assertTrue( class_exists( 'Feedzy_Rss_Feeds' ) );

		$instance1 = Feedzy_Rss_Feeds::instance();
		$instance2 = Feedzy_Rss_Feeds::instance();

		$this->assertSame( $instance1, $instance2 );
		$this->assertInstanceOf( 'Feedzy_Rss_Feeds', $instance1 );
		$this->assertInstanceOf( 'Feedzy_Rss_Feeds_Admin', $instance1->get_admin() );
	}

	/**
	 * Test the core plugin classes are loaded.
	 *
	 * @access public
	 */
	public function test_core_classes_exist() {
		$core_classes = array(
			'Feedzy_Rss_Feeds_Admin',
			'Feedzy_Rss_Feeds_Admin_Abstract',
			'Feedzy_Rss_Feeds_Import',
			'Feedzy_Rss_Feeds_Log',
			'Feedzy_Rss_Feeds_Options',
			'Feedzy_Rss_Feeds_Conditions',
			'Feedzy_Rss_Feeds_Util_Scheduler',
			'Feedzy_Rss_Feeds_Util_SimplePie',
			'Feedzy_Rss_Feeds_Loader',
		);

		foreach ( $core_classes as $class_name ) {
			$this->assertTrue( class_exists( $class_name ), "Class $class_name should be loaded" );
		}
	}

	/**
	 * Test plugin constants are defined with the expected shape.
	 *
	 * @access public
	 */
	public function test_plugin_constants() {
		$constants = array(
			'FEEDZY_BASEFILE',
			'FEEDZY_ABSURL',
			'FEEDZY_BASENAME',
			'FEEDZY_ABSPATH',
			'FEEDZY_DIRNAME',
			'FEEDZY_ALLOW_HTTPS',
			'FEEDZY_REST_VERSION',
			'FEEDZY_NAME',
		);

		foreach ( $constants as $constant ) {
			$this->assertTrue( defined( $constant ), "Constant $constant should be defined" );
		}

		$this->assertEquals( '1', FEEDZY_REST_VERSION );
		$this->assertStringEndsWith( 'feedzy-rss-feed.php', FEEDZY_BASEFILE );
	}

	/**
	 * Test both custom post types are registered.
	 *
	 * @access public
	 */
	public function test_custom_post_types_registered() {
		$this->assertTrue( post_type_exists( 'feedzy_imports' ), 'feedzy_imports CPT should be registered' );
		$this->assertTrue( post_type_exists( 'feedzy_categories' ), 'feedzy_categories CPT should be registered' );

		// Neither is queryable on the front end or included in search.
		$imports    = get_post_type_object( 'feedzy_imports' );
		$categories = get_post_type_object( 'feedzy_categories' );

		$this->assertFalse( $imports->publicly_queryable );
		$this->assertTrue( $imports->exclude_from_search );
		$this->assertFalse( $categories->publicly_queryable );
		$this->assertTrue( $categories->exclude_from_search );
	}

	/**
	 * Test the feedzy-rss shortcode is registered.
	 *
	 * @access public
	 */
	public function test_shortcode_registered() {
		$this->assertTrue( shortcode_exists( 'feedzy-rss' ) );
	}

	/**
	 * Test the feedzy REST namespace and lazy loading route are registered.
	 *
	 * @access public
	 */
	public function test_rest_routes_registered() {
		$server = rest_get_server();
		$routes = $server->get_routes();

		$namespace = '/feedzy/v' . FEEDZY_REST_VERSION;
		$feedzy_routes = array();
		foreach ( array_keys( $routes ) as $route ) {
			if ( 0 === strpos( $route, $namespace ) ) {
				$feedzy_routes[] = $route;
			}
		}

		$this->assertNotEmpty( $feedzy_routes, 'The feedzy REST namespace should register at least one route' );
		$this->assertContains( $namespace . '/lazy', $feedzy_routes, 'The lazy loading route should be registered' );
	}

	/**
	 * Test the options wrapper returns the singleton options instance.
	 *
	 * @access public
	 */
	public function test_feedzy_options_singleton() {
		$options1 = feedzy_options();
		$options2 = feedzy_options();

		$this->assertInstanceOf( 'Feedzy_Rss_Feeds_Options', $options1 );
		$this->assertSame( $options1, $options2 );
	}

	/**
	 * Test feedzy_current_user_can honours the publish_posts capability.
	 *
	 * @access public
	 */
	public function test_feedzy_current_user_can() {
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$this->assertTrue( feedzy_current_user_can() );

		$subscriber_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );
		$this->assertFalse( feedzy_current_user_can() );
	}

	/**
	 * Test the custom cron schedules filter is a no-op in the free plugin.
	 *
	 * @access public
	 */
	public function test_custom_cron_schedules_noop_in_free() {
		$schedules = wp_get_schedules();

		// The standard WP schedules survive the plugin filter.
		$this->assertArrayHasKey( 'hourly', $schedules );
		$this->assertArrayHasKey( 'daily', $schedules );

		// The free plugin does not append custom schedules.
		$admin  = Feedzy_Rss_Feeds::instance()->get_admin();
		$input  = array( 'hourly' => array( 'interval' => HOUR_IN_SECONDS, 'display' => 'Once Hourly' ) );
		$result = $admin->append_custom_cron_schedules( $input );
		$this->assertEquals( $input, $result );
	}
}
