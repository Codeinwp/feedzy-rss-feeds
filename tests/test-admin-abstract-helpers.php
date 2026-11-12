<?php
/**
 * Tests for the helper methods of the admin abstract class
 * (cache time parsing, url normalization, feed url resolution
 * and shortcode attribute sanitization).
 *
 * @since      5.1.0
 *
 * @package    feedzy-rss-feeds
 */
class Test_Admin_Abstract_Helpers extends WP_UnitTestCase {

	/**
	 * Instance of the class being tested.
	 *
	 * @var Feedzy_Rss_Feeds_Admin_Abstract
	 */
	private $feedzy_abstract;

	/**
	 * Set up test environment.
	 *
	 * @access public
	 */
	public function setUp(): void {
		parent::setUp();

		// Create a concrete implementation of the abstract class for testing.
		$this->feedzy_abstract = new class extends Feedzy_Rss_Feeds_Admin_Abstract {
			protected $plugin_name = 'feedzy-rss-feeds';
		};
	}

	/**
	 * Invoke the private calculate_cache_time() method through reflection.
	 *
	 * @param string $cache The cache string.
	 *
	 * @return int
	 */
	private function calculate_cache_time( $cache ) {
		$method = new ReflectionMethod( 'Feedzy_Rss_Feeds_Admin_Abstract', 'calculate_cache_time' );
		$method->setAccessible( true );

		return $method->invoke( $this->feedzy_abstract, $cache );
	}

	/**
	 * Test calculate_cache_time with hour based values.
	 *
	 * @access public
	 */
	public function test_calculate_cache_time_hours() {
		$this->assertEquals( 12 * HOUR_IN_SECONDS, $this->calculate_cache_time( '12_hours' ) );
		$this->assertEquals( 2 * HOUR_IN_SECONDS, $this->calculate_cache_time( '2_hours' ) );
		$this->assertEquals( HOUR_IN_SECONDS, $this->calculate_cache_time( '1_hours' ) );
	}

	/**
	 * Test calculate_cache_time with day based values.
	 *
	 * @access public
	 */
	public function test_calculate_cache_time_days() {
		$this->assertEquals( DAY_IN_SECONDS, $this->calculate_cache_time( '1_days' ) );
		$this->assertEquals( 3 * DAY_IN_SECONDS, $this->calculate_cache_time( '3_days' ) );
		$this->assertEquals( 15 * DAY_IN_SECONDS, $this->calculate_cache_time( '15_days' ) );
	}

	/**
	 * Test calculate_cache_time with minute based values.
	 *
	 * @access public
	 */
	public function test_calculate_cache_time_minutes() {
		$this->assertEquals( 30 * MINUTE_IN_SECONDS, $this->calculate_cache_time( '30_mins' ) );
		$this->assertEquals( MINUTE_IN_SECONDS, $this->calculate_cache_time( '1_mins' ) );
	}

	/**
	 * Test calculate_cache_time with singular/alias unit names.
	 *
	 * @access public
	 */
	public function test_calculate_cache_time_unit_aliases() {
		$this->assertEquals( HOUR_IN_SECONDS, $this->calculate_cache_time( '1_hour' ) );
		$this->assertEquals( DAY_IN_SECONDS, $this->calculate_cache_time( '1_day' ) );
		$this->assertEquals( MINUTE_IN_SECONDS, $this->calculate_cache_time( '1_min' ) );
		$this->assertEquals( 5 * MINUTE_IN_SECONDS, $this->calculate_cache_time( '5_minutes' ) );
		// Unit is case-insensitive and trimmed.
		$this->assertEquals( 2 * HOUR_IN_SECONDS, $this->calculate_cache_time( '2_HOURS' ) );
	}

	/**
	 * Test calculate_cache_time falls back to 12 hours for invalid input.
	 *
	 * @access public
	 */
	public function test_calculate_cache_time_invalid_input_falls_back_to_default() {
		$default = 12 * HOUR_IN_SECONDS;

		$this->assertEquals( $default, $this->calculate_cache_time( '' ) );
		$this->assertEquals( $default, $this->calculate_cache_time( 'garbage' ) );
		$this->assertEquals( $default, $this->calculate_cache_time( '12' ) );
		$this->assertEquals( $default, $this->calculate_cache_time( 'abc_hours' ) );
		$this->assertEquals( $default, $this->calculate_cache_time( '1_fortnights' ) );
	}

	/**
	 * Test calculate_cache_time rejects out of range numeric values.
	 *
	 * @access public
	 */
	public function test_calculate_cache_time_out_of_range_values() {
		$default = 12 * HOUR_IN_SECONDS;

		// The value must be between 1 and 100.
		$this->assertEquals( $default, $this->calculate_cache_time( '0_hours' ) );
		$this->assertEquals( $default, $this->calculate_cache_time( '101_hours' ) );
		$this->assertEquals( $default, $this->calculate_cache_time( '-1_hours' ) );
		// Boundary value 100 is accepted.
		$this->assertEquals( 100 * HOUR_IN_SECONDS, $this->calculate_cache_time( '100_hours' ) );
	}

	/**
	 * Test normalize_urls with a single valid url returns a string.
	 *
	 * @access public
	 */
	public function test_normalize_urls_single_url_returns_string() {
		// NOTE: urls on the test site host (example.org) are used so that
		// wp_http_validate_url() does not perform a DNS lookup.
		$result = $this->feedzy_abstract->normalize_urls( 'https://example.org/feed' );

		$this->assertIsString( $result );
		$this->assertEquals( 'https://example.org/feed', $result );
	}

	/**
	 * Test normalize_urls with a comma separated list returns a trimmed array.
	 *
	 * @access public
	 */
	public function test_normalize_urls_comma_separated_returns_trimmed_array() {
		$result = $this->feedzy_abstract->normalize_urls( 'https://example.org/feed1, https://example.org/feed2' );

		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );
		$this->assertEquals( 'https://example.org/feed1', $result[0] );
		$this->assertEquals( 'https://example.org/feed2', $result[1] );
	}

	/**
	 * Test normalize_urls with invalid input returns an empty string.
	 *
	 * @access public
	 */
	public function test_normalize_urls_invalid_input_returns_empty_string() {
		$this->assertEquals( '', $this->feedzy_abstract->normalize_urls( 'not a valid url at all' ) );
	}

	/**
	 * Test normalize_urls adds an http scheme to scheme-less urls.
	 *
	 * @access public
	 */
	public function test_normalize_urls_scheme_less_url_gets_http_scheme() {
		$result = $this->feedzy_abstract->normalize_urls( 'example.org/feed' );

		$this->assertEquals( 'http://example.org/feed', $result );
	}

	/**
	 * Test get_feed_url with a comma separated list of urls.
	 *
	 * @access public
	 */
	public function test_get_feed_url_comma_separated_urls() {
		$result = $this->feedzy_abstract->get_feed_url( 'https://example.com/feed1,https://example.com/feed2' );

		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );
		$this->assertEquals( 'https://example.com/feed1', $result[0] );
		$this->assertEquals( 'https://example.com/feed2', $result[1] );
	}

	/**
	 * Test get_feed_url with a single url (and trailing comma) returns a string.
	 *
	 * @access public
	 */
	public function test_get_feed_url_single_url_with_trailing_comma() {
		$result = $this->feedzy_abstract->get_feed_url( 'https://example.com/feed,' );

		$this->assertIsString( $result );
		$this->assertEquals( 'https://example.com/feed', $result );
	}

	/**
	 * Seed the feedzy_category_feed meta bypassing the write-time validation.
	 *
	 * The plugin hooks validate_category_feeds() on add/update_post_metadata,
	 * which fetches every URL over HTTP and drops the ones that are not real
	 * feeds. These tests cover slug RESOLUTION, not validation, so the meta is
	 * seeded with the validation filters removed (the WP test framework
	 * restores all hooks after each test).
	 *
	 * @param int    $post_id The feed group post id.
	 * @param string $value   The raw meta value to store.
	 */
	private function seed_category_feed_meta( $post_id, $value ) {
		$admin = Feedzy_Rss_Feeds::instance()->get_admin();
		remove_filter( 'update_post_metadata', array( $admin, 'validate_category_feeds' ) );
		remove_filter( 'add_post_metadata', array( $admin, 'validate_category_feeds' ) );
		update_post_meta( $post_id, 'feedzy_category_feed', $value );
	}

	/**
	 * Test get_feed_url resolves a feedzy_categories slug into its stored feed urls.
	 *
	 * @access public
	 */
	public function test_get_feed_url_resolves_category_slug() {
		$category = $this->factory->post->create_and_get(
			array(
				'post_title'  => 'My Feed Group',
				'post_name'   => 'my-feed-group',
				'post_type'   => 'feedzy_categories',
				'post_status' => 'publish',
			)
		);
		$this->seed_category_feed_meta( $category->ID, 'https://example.com/feed-a, https://example.com/feed-b' );

		$result = $this->feedzy_abstract->get_feed_url( 'my-feed-group' );

		$this->assertIsArray( $result );
		$this->assertCount( 2, $result );
		$this->assertEquals( 'https://example.com/feed-a', $result[0] );
		$this->assertEquals( 'https://example.com/feed-b', $result[1] );
	}

	/**
	 * Test process_feed_source resolves a feedzy_categories slug into the stored meta.
	 *
	 * @access public
	 */
	public function test_process_feed_source_resolves_category_slug() {
		$category = $this->factory->post->create_and_get(
			array(
				'post_title'  => 'Another Group',
				'post_name'   => 'another-group',
				'post_type'   => 'feedzy_categories',
				'post_status' => 'publish',
			)
		);
		$this->seed_category_feed_meta( $category->ID, "https://example.com/one.xml,\nhttps://example.com/two.xml" );

		$result = $this->feedzy_abstract->process_feed_source( 'another-group' );

		// Whitespace/newlines are collapsed to a single space.
		$this->assertEquals( 'https://example.com/one.xml, https://example.com/two.xml', $result );

		// A url is returned untouched.
		$this->assertEquals( 'https://example.com/feed', $this->feedzy_abstract->process_feed_source( 'https://example.com/feed' ) );
	}

	/**
	 * Returns a baseline valid shortcode attributes array for sanitize_attr tests.
	 *
	 * @return array
	 */
	private function get_baseline_sc() {
		return array(
			'max'           => '5',
			'offset'        => '0',
			'size'          => '150',
			'summarylength' => '',
			'default'       => 'https://example.com/default.jpg',
		);
	}

	/**
	 * Test sanitize_attr max value casting.
	 *
	 * @access public
	 */
	public function test_sanitize_attr_max_value() {
		$sc = $this->get_baseline_sc();

		// 0 means unlimited and becomes 999.
		$sc['max'] = '0';
		$result    = $this->feedzy_abstract->sanitize_attr( $sc, 'https://example.com/feed' );
		$this->assertEquals( '999', $result['max'] );

		// Non-numeric falls back to 5.
		$sc['max'] = 'abc';
		$result    = $this->feedzy_abstract->sanitize_attr( $sc, 'https://example.com/feed' );
		$this->assertEquals( '5', $result['max'] );

		// Empty falls back to 5.
		$sc['max'] = '';
		$result    = $this->feedzy_abstract->sanitize_attr( $sc, 'https://example.com/feed' );
		$this->assertEquals( '5', $result['max'] );

		// A valid number is kept.
		$sc['max'] = '7';
		$result    = $this->feedzy_abstract->sanitize_attr( $sc, 'https://example.com/feed' );
		$this->assertEquals( '7', $result['max'] );
	}

	/**
	 * Test sanitize_attr offset, size and summarylength defaults.
	 *
	 * @access public
	 */
	public function test_sanitize_attr_defaults() {
		$sc = $this->get_baseline_sc();

		$sc['offset']        = 'xyz';
		$sc['size']          = 'abc';
		$sc['summarylength'] = 'not-a-number';

		$result = $this->feedzy_abstract->sanitize_attr( $sc, 'https://example.com/feed' );

		$this->assertEquals( '0', $result['offset'] );
		$this->assertEquals( '150', $result['size'] );
		$this->assertEquals( '', $result['summarylength'] );

		// Valid values are kept.
		$sc['offset']        = '2';
		$sc['size']          = '200';
		$sc['summarylength'] = '25';

		$result = $this->feedzy_abstract->sanitize_attr( $sc, 'https://example.com/feed' );

		$this->assertEquals( '2', $result['offset'] );
		$this->assertEquals( '200', $result['size'] );
		$this->assertEquals( '25', $result['summarylength'] );
	}

	/**
	 * Test sanitize_attr default image handling.
	 *
	 * @access public
	 */
	public function test_sanitize_attr_default_image() {
		$sc = $this->get_baseline_sc();

		// An explicitly provided default is kept.
		$result = $this->feedzy_abstract->sanitize_attr( $sc, 'https://example.com/feed' );
		$this->assertEquals( 'https://example.com/default.jpg', $result['default'] );

		// An empty default goes through the feedzy_default_image filter,
		// which sets the bundled feedzy.svg image by default.
		$sc['default'] = '';
		$result        = $this->feedzy_abstract->sanitize_attr( $sc, 'https://example.com/feed' );
		$this->assertStringContainsString( 'img/feedzy.svg', $result['default'] );

		// The filter can be overridden.
		add_filter(
			'feedzy_default_image',
			function ( $default_img, $feed_url ) {
				return 'https://example.com/custom-default.png';
			},
			99,
			2
		);
		$sc['default'] = '';
		$result        = $this->feedzy_abstract->sanitize_attr( $sc, 'https://example.com/feed' );
		$this->assertEquals( 'https://example.com/custom-default.png', $result['default'] );
		remove_all_filters( 'feedzy_default_image' );
	}

	/**
	 * Test get_short_code_attributes provides expected defaults.
	 *
	 * @access public
	 */
	public function test_get_short_code_attributes_defaults() {
		$sc = $this->feedzy_abstract->get_short_code_attributes( array() );

		$this->assertIsArray( $sc );
		$this->assertEquals( '', $sc['feeds'] );
		$this->assertEquals( '5', $sc['max'] );
		$this->assertEquals( 'yes', $sc['feed_title'] );
		$this->assertEquals( '_blank', $sc['target'] );
		$this->assertEquals( '12_hours', $sc['refresh'] );
		$this->assertEquals( 'auto', $sc['thumb'] );
		$this->assertEquals( 'auto', $sc['http'] );
		$this->assertEquals( 'no', $sc['lazy'] );
		$this->assertEquals( 'no', $sc['disable_default_style'] );
	}

	/**
	 * Test unsupported link targets fall back to the documented default.
	 *
	 * @access public
	 */
	public function test_get_short_code_attributes_rejects_unsupported_target() {
		$sc = $this->feedzy_abstract->get_short_code_attributes( array( 'target' => 'unsupported' ) );

		$this->assertEquals( '_blank', $sc['target'] );
	}

	/**
	 * Test get_short_code_attributes maps className to classname.
	 *
	 * @access public
	 */
	public function test_get_short_code_attributes_classname_mapping() {
		$sc = $this->feedzy_abstract->get_short_code_attributes( array( 'className' => 'my-class' ) );

		$this->assertArrayHasKey( 'classname', $sc );
		$this->assertEquals( 'my-class', $sc['classname'] );
	}

	/**
	 * Invalid filter return values should not crash shortcode attribute preparation.
	 *
	 * @access public
	 */
	public function test_get_short_code_attributes_ignores_null_filter_result() {
		add_filter( 'feedzy_get_short_code_attributes_filter', '__return_null' );

		$sc = $this->feedzy_abstract->get_short_code_attributes( array() );

		remove_filter( 'feedzy_get_short_code_attributes_filter', '__return_null' );

		$this->assertIsArray( $sc );
		$this->assertEquals( '5', $sc['max'] );
	}

	/**
	 * Invoke the private feed_nonce_action() method through reflection.
	 *
	 * @param string|array $feed_url The normalized feed url(s).
	 *
	 * @return string
	 */
	private function feed_nonce_action( $feed_url ) {
		$method = new ReflectionMethod( 'Feedzy_Rss_Feeds_Admin_Abstract', 'feed_nonce_action' );
		$method->setAccessible( true );

		return $method->invoke( $this->feedzy_abstract, $feed_url );
	}

	/**
	 * Test feed_nonce_action does not collapse multi-feed arrays to "Array".
	 *
	 * @access public
	 */
	public function test_feed_nonce_action_multi_feed_does_not_collapse_to_array() {
		$action = $this->feed_nonce_action( array( 'https://example.com/feed1', 'https://example.com/feed2' ) );

		$this->assertNotEquals( 'Array', $action );
	}

	/**
	 * Test feed_nonce_action produces distinct actions for distinct feed sets,
	 * so a nonce for one multi-feed shortcode does not validate for another.
	 *
	 * @access public
	 */
	public function test_feed_nonce_action_differs_between_feed_sets() {
		$action_a = $this->feed_nonce_action( array( 'https://example.com/a1', 'https://example.com/a2' ) );
		$action_b = $this->feed_nonce_action( array( 'https://example.com/b1', 'https://example.com/b2' ) );

		$this->assertNotEquals( $action_a, $action_b );
	}

	/**
	 * Test feed_nonce_action passes single-feed (scalar) urls through unchanged.
	 *
	 * @access public
	 */
	public function test_feed_nonce_action_single_feed_returns_scalar_unchanged() {
		$this->assertEquals( 'https://example.com/feed', $this->feed_nonce_action( 'https://example.com/feed' ) );
	}

	/**
	 * Test get_feed_title_filter falls back to an empty string for rss_url and
	 * rss_description when the feed provides none, instead of null.
	 *
	 * @access public
	 */
	public function test_get_feed_title_filter_null_safe_url_and_description() {
		$method = new ReflectionMethod( 'Feedzy_Rss_Feeds_Admin_Abstract', 'get_feed_title_filter' );
		$method->setAccessible( true );

		$feed = $this->getMockBuilder( 'stdClass' )
			->addMethods( array( 'get_permalink', 'get_description', 'get_title' ) )
			->getMock();
		$feed->method( 'get_permalink' )->willReturn( null );
		$feed->method( 'get_description' )->willReturn( null );
		$feed->method( 'get_title' )->willReturn( null );

		$result = $method->invoke( $this->feedzy_abstract, $feed, array( 'classname' => '' ), 'https://example.com/feed' );
		$this->assertSame( '', $result['rss_url'] );
		$this->assertSame( '', $result['rss_description'] );
		$this->assertSame( '', $result['rss_description'] );
	}

	/**
	 * Invoke a private method of the abstract class through reflection.
	 *
	 * @param string $name Method name.
	 * @param array  $args Arguments.
	 *
	 * @return mixed
	 */
	private function invoke_private( $name, array $args = array() ) {
		$method = new ReflectionMethod( 'Feedzy_Rss_Feeds_Admin_Abstract', $name );
		$method->setAccessible( true );

		return $method->invokeArgs( $this->feedzy_abstract, $args );
	}

	/**
	 * Load WordPress' SimplePie, the way create_simplepie_instance() does before
	 * the user agent is resolved.
	 *
	 * @return void
	 */
	private function load_simplepie() {
		if ( ! class_exists( 'SimplePie' ) ) {
			require_once ABSPATH . WPINC . '/class-simplepie.php';
		}
	}

	/**
	 * Test get_default_user_agent keeps the browser agent for Medium sources,
	 * for a single url and for an array of urls.
	 *
	 * @access public
	 */
	public function test_get_default_user_agent_medium_source_uses_browser_agent() {
		$this->assertSame(
			FEEDZY_USER_AGENT,
			$this->invoke_private( 'get_default_user_agent', array( 'https://medium.com/feed/@someone' ) )
		);

		$this->assertSame(
			FEEDZY_USER_AGENT,
			$this->invoke_private(
				'get_default_user_agent',
				array( array( 'https://example.com/feed', 'https://medium.com/feed/@someone' ) )
			)
		);
	}

	/**
	 * Test get_default_user_agent falls back to SimplePie's own agent for
	 * non-Medium sources, for a single url and for an array of urls.
	 *
	 * @access public
	 */
	public function test_get_default_user_agent_non_medium_uses_simplepie_agent() {
		$this->load_simplepie();

		$agent = $this->invoke_private( 'get_default_user_agent', array( 'https://example.com/feed' ) );

		$this->assertIsString( $agent );
		$this->assertNotEmpty( $agent );
		$this->assertNotSame( FEEDZY_USER_AGENT, $agent );

		$this->assertSame(
			$agent,
			$this->invoke_private(
				'get_default_user_agent',
				array( array( 'https://example.com/feed', 'https://example.org/feed' ) )
			)
		);
	}

	/**
	 * Test a lazy request built from a rendered placeholder is rendered and cached for that placeholder.
	 *
	 * @dataProvider lazy_placeholder_attributes
	 *
	 * @param array<string, mixed> $atts Shortcode or block attributes.
	 */
	public function test_lazy_request_from_rendered_placeholder_is_rendered_and_cached( $atts ) {
		$feedzy   = $this->lazy_feedzy();
		$response = $this->lazy_response( $feedzy, $this->placeholder_args( $feedzy, $atts ) );

		$this->assertTrue( $response['success'], wp_json_encode( $response ) );
		$this->assertStringContainsString( 'Feedzy lazy item', $response['data']['content'] );
		$this->assertStringContainsString( 'data-has_valid_cache="true"', $feedzy->feedzy_rss( $atts ) );
	}

	/**
	 * Test a lazy request only fills the cache of the placeholder that signed it.
	 */
	public function test_lazy_request_does_not_fill_the_cache_of_another_placeholder() {
		$feedzy = $this->lazy_feedzy();
		$signed = array(
			'feeds' => 'https://example.com/feed',
			'max'   => '2',
			'lazy'  => 'yes',
		);
		$other  = array_merge( $signed, array( 'max' => '3' ) );

		$this->assertTrue( $this->lazy_response( $feedzy, $this->placeholder_args( $feedzy, $signed ) )['success'] );

		$this->assertStringContainsString( 'data-has_valid_cache="true"', $feedzy->feedzy_rss( $signed ) );
		$this->assertStringNotContainsString( 'data-has_valid_cache="true"', $feedzy->feedzy_rss( $other ) );
	}

	/**
	 * Test line breaks are signed as the browser sends them: HTML turns CRLF and CR into LF.
	 */
	public function test_lazy_placeholder_signs_line_breaks_as_the_browser_sends_them() {
		$method = new ReflectionMethod( 'Feedzy_Rss_Feeds_Admin_Abstract', 'lazy_placeholder_attributes' );
		$method->setAccessible( true );

		$this->assertSame(
			array( 'error_empty' => "No\nnews\ntoday" ),
			$method->invoke( $this->feedzy_abstract, array( 'error_empty' => "No\r\nnews\rtoday" ) )
		);
	}

	/**
	 * Placeholders from a classic shortcode, from block attributes and from an entity-encoded value.
	 *
	 * @return array<string, array<int, array<string, mixed>>>
	 */
	public function lazy_placeholder_attributes() {
		return array(
			'shortcode' => array(
				array(
					'feeds' => 'https://example.com/feed',
					'max'   => '2',
					'lazy'  => 'yes',
				),
			),
			'block'     => array(
				array(
					'feeds'        => 'https://example.com/feed',
					'lazy'         => true,
					'aspectRatio'  => '16/9',
					'className'    => 'is-style-custom',
					'disableStyle' => false,
					'itemTitle'    => true,
				),
			),
			'entity'    => array(
				array(
					'feeds'       => 'https://example.com/feed',
					'error_empty' => 'No news from AT&amp;T',
					'lazy'        => 'yes',
				),
			),
		);
	}

	/**
	 * Test a lazy request that changes, shadows or retypes a signed attribute is rejected.
	 *
	 * @dataProvider tampered_lazy_requests
	 *
	 * @param callable(array<string, mixed>): mixed $tamper Changes the placeholder arguments.
	 */
	public function test_tampered_lazy_request_is_rejected( $tamper ) {
		$feedzy = $this->lazy_feedzy();
		$args   = $tamper(
			$this->placeholder_args(
				$feedzy,
				array(
					'feeds'       => 'https://example.com/feed',
					'max'         => '2',
					'error_empty' => 'No news?',
					'lazy'        => 'yes',
				)
			)
		);

		$response = $this->lazy_response( $feedzy, $args );

		$this->assertFalse( $response['success'] );
		$this->assertSame( 'Security check failed.', $response['data']['message'] );
	}

	/**
	 * Changes an attacker can make to the arguments of a signed placeholder.
	 *
	 * @return array<string, array<int, callable>>
	 */
	public function tampered_lazy_requests() {
		return array(
			'changed value'       => array(
				function ( $args ) {
					$args['max'] = '9';
					return $args;
				},
			),
			'uppercase duplicate' => array(
				function ( $args ) {
					$args['MAX'] = $args['max'];
					$args['max'] = '0';
					return $args;
				},
			),
			'uppercase copy'      => array(
				function ( $args ) {
					$args['MAX'] = $args['max'];
					return $args;
				},
			),
			'invalid UTF-8'       => array(
				function ( $args ) {
					$args['error_empty'] = "No news\xFF";
					return $args;
				},
			),
			'array value'         => array(
				function ( $args ) {
					$args['max'] = array( $args['max'] );
					return $args;
				},
			),
			'not an array'        => array(
				function ( $args ) {
					return http_build_query( $args );
				},
			),
		);
	}

	/**
	 * A Feedzy instance that can render lazy placeholders.
	 *
	 * @return Feedzy_Rss_Feeds_Admin_Abstract
	 */
	private function lazy_feedzy() {
		return new class extends Feedzy_Rss_Feeds_Admin_Abstract {
			protected $plugin_name = 'feedzy-rss-feeds';
			protected $version     = '1.0.0';
		};
	}

	/**
	 * Render a lazy placeholder and read its data attributes the way feedzy-lazy.js does.
	 *
	 * @param Feedzy_Rss_Feeds_Admin_Abstract $feedzy Feedzy instance.
	 * @param array<string, mixed>            $atts   Shortcode or block attributes.
	 *
	 * @return array<string, string>
	 */
	private function placeholder_args( $feedzy, $atts ) {
		$document = new DOMDocument();
		$document->loadHTML( '<html><head><meta charset="utf-8"></head><body>' . $feedzy->feedzy_rss( $atts ) . '</body></html>', LIBXML_NOERROR );

		$args = array();
		foreach ( $document->getElementsByTagName( 'div' )->item( 0 )->attributes as $attribute ) {
			if ( 0 === strpos( $attribute->name, 'data-' ) ) {
				$args[ substr( $attribute->name, 5 ) ] = $attribute->value;
			}
		}
		unset( $args['has_valid_cache'] );

		return $args;
	}

	/**
	 * Send a lazy request to the endpoint, serving a one-item feed, and decode its JSON response.
	 *
	 * @param Feedzy_Rss_Feeds_Admin_Abstract $feedzy Feedzy instance.
	 * @param mixed                           $args   Request arguments.
	 *
	 * @return array<string, mixed>
	 */
	private function lazy_response( $feedzy, $args ) {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter(
			'wp_die_ajax_handler',
			function () {
				return function () {
					throw new WPDieException();
				};
			}
		);
		add_filter(
			'pre_http_request',
			function ( $preempt, $request, $url ) {
				if ( 'https://example.com/feed' !== $url ) {
					return $preempt;
				}

				return array(
					'headers'  => array( 'content-type' => 'application/rss+xml' ),
					'body'     => '<?xml version="1.0"?><rss version="2.0"><channel><title>Feedzy lazy</title><link>https://example.com</link><description>Feed</description><item><title>Feedzy lazy item</title><link>https://example.com/item</link><description>Item</description><pubDate>Mon, 01 Jan 2024 10:00:00 +0000</pubDate></item></channel></rss>',
					'response' => array(
						'code'    => 200,
						'message' => 'OK',
					),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			3
		);

		ob_start();
		try {
			$feedzy->feedzy_lazy_load( array( 'args' => $args ) );
		} catch ( WPDieException $exception ) {
			unset( $exception );
		}

		return (array) json_decode( (string) ob_get_clean(), true );
	}
}
