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
		update_post_meta( $category->ID, 'feedzy_category_feed', 'https://example.com/feed-a, https://example.com/feed-b' );

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
		update_post_meta( $category->ID, 'feedzy_category_feed', "https://example.com/one.xml,\nhttps://example.com/two.xml" );

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
	 * Test get_short_code_attributes maps className to classname.
	 *
	 * @access public
	 */
	public function test_get_short_code_attributes_classname_mapping() {
		$sc = $this->feedzy_abstract->get_short_code_attributes( array( 'className' => 'my-class' ) );

		$this->assertArrayHasKey( 'classname', $sc );
		$this->assertEquals( 'my-class', $sc['classname'] );
	}
}
