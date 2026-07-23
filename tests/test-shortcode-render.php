<?php
/**
 * End-to-end tests for the [feedzy-rss] shortcode render pipeline
 * using a mocked (network-free) feed.
 *
 * All HTTP requests are intercepted via the pre_http_request filter
 * and served from local XML fixtures.
 *
 * @since      5.1.0
 *
 * @package    feedzy-rss-feeds
 */
class Test_Shortcode_Render extends WP_UnitTestCase {

	/**
	 * Fixture bodies, keyed by url substring.
	 *
	 * @var array
	 */
	private $fixtures = array();

	/**
	 * Urls requested during the test (proof that the mock was used).
	 *
	 * @var array
	 */
	private $requested_urls = array();

	/**
	 * Set up test environment.
	 *
	 * @access public
	 */
	public function setUp(): void {
		parent::setUp();
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );

		$this->fixtures = array(
			'sample-feed' => file_get_contents( dirname( __FILE__ ) . '/fixtures/sample-feed.xml' ),
			'empty-feed'  => file_get_contents( dirname( __FILE__ ) . '/fixtures/empty-feed.xml' ),
		);

		$this->requested_urls = array();

		// SimplePie may persist feed caches as .spc files in a "wp_transient"
		// directory under the working directory. Purge it so every test always
		// goes through the mocked HTTP layer instead of a stale filesystem cache.
		$this->purge_simplepie_file_cache();

		add_filter( 'pre_http_request', array( $this, 'mock_http_request' ), 10, 3 );
	}

	/**
	 * Remove any SimplePie filesystem cache left behind by previous runs.
	 *
	 * @access private
	 */
	private function purge_simplepie_file_cache() {
		$dirs = array(
			dirname( dirname( __FILE__ ) ) . '/wp_transient',
			getcwd() . '/wp_transient',
		);
		foreach ( array_unique( $dirs ) as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}
			$files = glob( $dir . '/*.spc' );
			if ( is_array( $files ) ) {
				foreach ( $files as $file ) {
					unlink( $file );
				}
			}
		}
	}

	/**
	 * Clean up after tests.
	 *
	 * @access public
	 */
	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_http_request' ), 10 );
		parent::tearDown();
	}

	/**
	 * Intercept all HTTP requests to tests.example.com and serve fixtures.
	 *
	 * @param false|array $preempt Whether to preempt the request.
	 * @param array       $args Request args.
	 * @param string      $url Request url.
	 *
	 * @return false|array
	 */
	public function mock_http_request( $preempt, $args, $url ) {
		if ( false === strpos( $url, 'feedzy-fixture' ) ) {
			// Fail loudly if anything tries to reach a real host.
			return new WP_Error( 'network_disabled', 'Live HTTP requests are not allowed in this test: ' . $url );
		}

		$this->requested_urls[] = $url;

		$body = $this->fixtures['sample-feed'];
		if ( false !== strpos( $url, 'empty-feed' ) ) {
			$body = $this->fixtures['empty-feed'];
		}

		return array(
			'headers'  => array(
				'content-type' => 'application/rss+xml; charset=UTF-8',
			),
			'body'     => $body,
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => '',
		);
	}

	/**
	 * Build a shortcode string with a unique feed url per test.
	 *
	 * @param string $extra Extra shortcode attributes.
	 * @param string $feed_slug The fixture feed slug used in the url.
	 *
	 * @return string
	 */
	private function build_shortcode( $extra = '', $feed_slug = 'sample-feed' ) {
		// A unique per-process component keeps urls out of any feed cache
		// shared between php processes/runs.
		static $run_id = null;
		if ( null === $run_id ) {
			$run_id = uniqid();
		}

		// The url uses the test site host (example.org) so wp_http_validate_url()
		// accepts it without doing a DNS lookup; the request itself is mocked.
		$url = 'https://example.org/feedzy-fixture/' . $feed_slug . '-' . $run_id . '-' . md5( $this->getName() . $extra ) . '.xml';

		return '[feedzy-rss feeds="' . $url . '" disable_default_style="yes" ' . $extra . ']';
	}

	/**
	 * Test the shortcode is registered.
	 *
	 * @access public
	 */
	public function test_shortcode_is_registered() {
		$this->assertTrue( shortcode_exists( 'feedzy-rss' ) );
	}

	/**
	 * Test the shortcode renders all mocked feed item titles and links.
	 *
	 * @access public
	 */
	public function test_shortcode_renders_mocked_feed_items() {
		$output = do_shortcode( $this->build_shortcode( 'max="10"' ) );

		$this->assertNotEmpty( $this->requested_urls, 'The mocked HTTP layer should have been used' );

		// Container is rendered.
		$this->assertStringContainsString( 'feedzy-rss', $output );

		// All 5 item titles are rendered.
		$this->assertStringContainsString( 'Alpha Article About Space Exploration', $output );
		$this->assertStringContainsString( 'Beta Bananas Are Great', $output );
		$this->assertStringContainsString( 'Gamma Space Telescope News', $output );
		$this->assertStringContainsString( 'Delta Cooking Recipes', $output );
		$this->assertStringContainsString( 'Epsilon Space Rocks Discovered', $output );

		// Item links point to the fixture permalinks.
		$this->assertStringContainsString( 'https://tests.example.com/alpha', $output );
		$this->assertStringContainsString( 'https://tests.example.com/epsilon', $output );

		// Item descriptions are rendered by default (summary=yes).
		$this->assertStringContainsString( 'Alpha unique description content.', $output );
	}

	/**
	 * Test the shortcode respects the max attribute.
	 *
	 * @access public
	 */
	public function test_shortcode_respects_max() {
		$output = do_shortcode( $this->build_shortcode( 'max="2"' ) );

		$this->assertEquals( 2, substr_count( $output, '<span class="title">' ), 'Only 2 items should be rendered' );

		// Feed is date-ordered so the 2 newest items are shown.
		$this->assertStringContainsString( 'Alpha Article About Space Exploration', $output );
		$this->assertStringContainsString( 'Beta Bananas Are Great', $output );
		$this->assertStringNotContainsString( 'Gamma Space Telescope News', $output );
		$this->assertStringNotContainsString( 'Epsilon Space Rocks Discovered', $output );
	}

	/**
	 * Test the shortcode filters items by keywords_title.
	 *
	 * @access public
	 */
	public function test_shortcode_filters_by_keywords_title() {
		$output = do_shortcode( $this->build_shortcode( 'max="10" keywords_title="space"' ) );

		// Only the 3 items containing "space" in the title are kept.
		$this->assertEquals( 3, substr_count( $output, '<span class="title">' ) );
		$this->assertStringContainsString( 'Alpha Article About Space Exploration', $output );
		$this->assertStringContainsString( 'Gamma Space Telescope News', $output );
		$this->assertStringContainsString( 'Epsilon Space Rocks Discovered', $output );
		$this->assertStringNotContainsString( 'Beta Bananas Are Great', $output );
		$this->assertStringNotContainsString( 'Delta Cooking Recipes', $output );
	}

	/**
	 * Test the feed title header rendering can be toggled.
	 *
	 * @access public
	 */
	public function test_shortcode_feed_title_toggle() {
		$output = do_shortcode( $this->build_shortcode( 'feed_title="yes"' ) );
		$this->assertStringContainsString( 'rss_header', $output );
		$this->assertStringContainsString( 'Feedzy Fixture Feed', $output );

		$output = do_shortcode( $this->build_shortcode( 'feed_title="no"' ) );
		$this->assertStringNotContainsString( 'rss_header', $output );
		$this->assertStringNotContainsString( 'Feedzy Fixture Feed', $output );
	}

	/**
	 * Test the target attribute is applied to the item anchors.
	 *
	 * @access public
	 */
	public function test_shortcode_target_attribute() {
		$output = do_shortcode( $this->build_shortcode( 'max="1" target="_self"' ) );
		$this->assertStringContainsString( 'target="_self"', $output );

		// Default is _blank.
		$output = do_shortcode( $this->build_shortcode( 'max="1"' ) );
		$this->assertStringContainsString( 'target="_blank"', $output );
	}

	/**
	 * Test the summary attribute toggles item descriptions.
	 *
	 * @access public
	 */
	public function test_shortcode_summary_toggle() {
		$output = do_shortcode( $this->build_shortcode( 'max="1" summary="no"' ) );
		$this->assertStringContainsString( 'Alpha Article About Space Exploration', $output );
		$this->assertStringNotContainsString( 'Alpha unique description content.', $output );

		$output = do_shortcode( $this->build_shortcode( 'max="1" summary="yes"' ) );
		$this->assertStringContainsString( 'Alpha unique description content.', $output );
	}

	/**
	 * Test a feed without items renders the error_empty message.
	 *
	 * @access public
	 */
	public function test_shortcode_empty_feed_shows_error_empty() {
		$output = do_shortcode( $this->build_shortcode( 'error_empty="No stories found here"', 'empty-feed' ) );

		$this->assertStringContainsString( 'No stories found here', $output );
		$this->assertEquals( 0, substr_count( $output, '<span class="title">' ) );
	}

	/**
	 * Test the shortcode with no feeds attribute returns the original (empty) content.
	 *
	 * @access public
	 */
	public function test_shortcode_without_feeds_returns_empty() {
		// An empty feeds attribute falls back to the most recent feedzy_categories
		// post, so remove the "News sites" demo group created on activation
		// (the deletion is rolled back after the test).
		$news_group_id = get_option( '_feedzy_news_group_id' );
		if ( $news_group_id ) {
			wp_delete_post( $news_group_id, true );
			delete_option( '_feedzy_news_group_id' );
		}

		$output = do_shortcode( '[feedzy-rss feeds="" disable_default_style="yes"]' );

		$this->assertEquals( '', $output );
		$this->assertEmpty( $this->requested_urls, 'No HTTP request should be made without a feed url' );
	}
}
