<?php
/**
 * Tests for the import screen's "Preview Import" (dry run) ordering.
 *
 * All HTTP requests are intercepted via the pre_http_request filter
 * and served from a local XML fixture whose items are not in date order.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Import_Preview_Order extends WP_Ajax_UnitTestCase {

	/**
	 * Fixture body.
	 *
	 * @var string
	 */
	private $fixture = '';

	/**
	 * The sort attribute the shortcode pipeline received.
	 *
	 * @var string|null
	 */
	private $captured_sort = null;

	/**
	 * Set up test environment.
	 *
	 * @access public
	 */
	public function setUp(): void {
		parent::setUp();

		$this->fixture       = file_get_contents( dirname( __FILE__ ) . '/fixtures/unsorted-feed.xml' );
		$this->captured_sort = null;

		$this->purge_simplepie_file_cache();

		add_filter( 'pre_http_request', array( $this, 'mock_http_request' ), 10, 3 );
		add_filter( 'feedzy_get_feed_array', array( $this, 'capture_sort' ), 8, 2 );
	}

	/**
	 * Clean up after tests.
	 *
	 * @access public
	 */
	public function tearDown(): void {
		remove_filter( 'feedzy_get_feed_array', array( $this, 'capture_sort' ), 8 );
		remove_filter( 'pre_http_request', array( $this, 'mock_http_request' ), 10 );
		parent::tearDown();
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
	 * Intercept all HTTP requests and serve the fixture.
	 *
	 * @param false|array $preempt Whether to preempt the request.
	 * @param array       $args Request args.
	 * @param string      $url Request url.
	 *
	 * @return false|array|WP_Error
	 */
	public function mock_http_request( $preempt, $args, $url ) {
		if ( false === strpos( $url, 'feedzy-fixture' ) ) {
			// Fail loudly if anything tries to reach a real host.
			return new WP_Error( 'network_disabled', 'Live HTTP requests are not allowed in this test: ' . $url );
		}

		return array(
			'headers'  => array(
				'content-type' => 'application/rss+xml; charset=UTF-8',
			),
			'body'     => $this->fixture,
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => '',
		);
	}

	/**
	 * Record the sort attribute the preview shortcode was rendered with.
	 *
	 * @param array $feed_items The feed items.
	 * @param array $sc The shortcode attributes.
	 *
	 * @return array
	 */
	public function capture_sort( $feed_items, $sc ) {
		$this->captured_sort = isset( $sc['sort'] ) ? $sc['sort'] : null;
		return $feed_items;
	}

	/**
	 * Run the dry run for a campaign with the given feed order.
	 *
	 * @param string $import_order The import_order meta value.
	 *
	 * @return string The preview markup.
	 */
	private function preview_for_order( $import_order ) {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// A unique url per case keeps the feed out of any cache shared between cases.
		$url = 'https://example.org/feedzy-fixture/unsorted-' . md5( $this->getName() . $import_order . uniqid() ) . '.xml';

		// The real screen posts every form input; the first one absorbs the http:// that
		// sanitize_url() prepends to the field string, so keep one ahead of the meta data.
		$fields = array(
			'post_title'       => 'Preview order campaign',
			'feedzy_meta_data' => array(
				'source'            => $url,
				'import_feed_limit' => 10,
				'import_order'      => $import_order,
			),
		);

		$_POST['security']    = wp_create_nonce( FEEDZY_BASEFILE );
		$_POST['fields']      = http_build_query( $fields );
		$_REQUEST['security'] = $_POST['security'];

		$import = new Feedzy_Rss_Feeds_Import( 'feedzy-rss-feeds', '1.2.0' );
		$method = new ReflectionMethod( $import, 'dry_run' );
		$method->setAccessible( true );

		// The ajax die handler collects the response from the output buffer.
		ob_start();
		try {
			$method->invoke( $import );
			ob_end_clean();
		} catch ( WPAjaxDieContinueException $e ) {
			// wp_send_json_success() ends the request; the response is in _last_response.
		}

		$response = json_decode( $this->_last_response, true );
		$this->assertTrue( isset( $response['success'] ) && $response['success'], 'The dry run should succeed' );

		return $response['data']['output'];
	}

	/**
	 * The order in which the fixture titles appear in the preview markup.
	 *
	 * @param string $output The preview markup.
	 *
	 * @return array
	 */
	private function title_order( $output ) {
		$titles = array(
			'Newest Item Of All',
			'Second Newest Item',
			'Third Newest Item',
			'Second Oldest Item',
			'Oldest Item Of All',
		);

		$positions = array();
		foreach ( $titles as $title ) {
			$at = strpos( $output, $title );
			$this->assertNotFalse( $at, 'The preview should contain "' . $title . '"' );
			$positions[ $title ] = $at;
		}
		asort( $positions );

		return array_keys( $positions );
	}

	/**
	 * "Latest items first" previews the feed newest first.
	 *
	 * @access public
	 */
	public function test_preview_sorts_newest_first() {
		$order = $this->title_order( $this->preview_for_order( 'date_desc' ) );

		$this->assertSame( 'date_desc', $this->captured_sort );
		$this->assertSame(
			array(
				'Newest Item Of All',
				'Second Newest Item',
				'Third Newest Item',
				'Second Oldest Item',
				'Oldest Item Of All',
			),
			$order
		);
	}

	/**
	 * "Oldest items first" previews the feed oldest first.
	 *
	 * @access public
	 */
	public function test_preview_sorts_oldest_first() {
		$order = $this->title_order( $this->preview_for_order( 'date_asc' ) );

		$this->assertSame( 'date_asc', $this->captured_sort );
		$this->assertSame(
			array(
				'Oldest Item Of All',
				'Second Oldest Item',
				'Third Newest Item',
				'Second Newest Item',
				'Newest Item Of All',
			),
			$order
		);
	}

	/**
	 * "Original feed order" previews the feed in the order the source publishes it.
	 *
	 * @access public
	 */
	public function test_preview_keeps_original_feed_order() {
		$order = $this->title_order( $this->preview_for_order( '' ) );

		$this->assertSame( '', $this->captured_sort );
		$this->assertSame(
			array(
				'Third Newest Item',
				'Oldest Item Of All',
				'Newest Item Of All',
				'Second Newest Item',
				'Second Oldest Item',
			),
			$order
		);
	}

	/**
	 * An unknown order token falls back to the original feed order.
	 *
	 * @access public
	 */
	public function test_preview_rejects_unknown_order() {
		$order = $this->title_order( $this->preview_for_order( 'title_asc' ) );

		$this->assertSame( '', $this->captured_sort );
		$this->assertSame(
			array(
				'Third Newest Item',
				'Oldest Item Of All',
				'Newest Item Of All',
				'Second Newest Item',
				'Second Oldest Item',
			),
			$order
		);
	}
}
