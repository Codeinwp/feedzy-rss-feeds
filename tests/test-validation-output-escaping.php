<?php
/**
 * Tests for text rendering in validation and preview output.
 *
 * @package feedzy-rss-feeds
 */
class Test_Validation_Output_Escaping extends WP_UnitTestCase {

	/**
	 * Configure JSON responses to return instead of exiting.
	 */
	public function set_up(): void {
		parent::set_up();

		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', array( $this, 'get_die_handler' ), 1 );
	}

	/**
	 * Restore request data and filters after each test.
	 */
	public function tear_down(): void {
		remove_filter( 'feedzy_is_license_of_type', '__return_true' );
		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_die_handler' ), 1 );
		$_POST = array();

		parent::tear_down();
	}

	/**
	 * Return the test JSON die handler.
	 *
	 * @return callable
	 */
	public function get_die_handler(): callable {
		return array( $this, 'stop_json_response' );
	}

	/**
	 * Let wp_send_json() return without ending the test process.
	 *
	 * @param string $message Die message.
	 */
	public function stop_json_response( $message ): void {
	}

	/**
	 * Feed title entities are decoded before the client receives them.
	 *
	 * @dataProvider encoded_feed_title_provider
	 *
	 * @param string $encoded_title Encoded title returned by the feed parser.
	 * @param string $expected_title Plain title expected in the response.
	 */
	public function test_feed_title_entities_are_decoded_before_json( string $encoded_title, string $expected_title ): void {
		$feed = new class( $encoded_title ) {
			private $title;

			public function __construct( string $title ) {
				$this->title = $title;
			}

			public function get_item_quantity(): int {
				return 1;
			}

			public function get_title(): string {
				return $this->title;
			}

			public function error() {
				return null;
			}
		};

		$admin = new class( $feed ) extends Feedzy_Rss_Feeds_Admin {
			private $feed;

			public function __construct( $feed ) {
				parent::__construct( 'feedzy-rss-feeds', Feedzy_Rss_Feeds::get_version() );
				$this->feed = $feed;
			}

			public function fetch_feed( $feed_url, $cache = '12_hours', $sc = '' ) {
				return $this->feed;
			}
		};

		$_POST['nonce']    = wp_create_nonce( FEEDZY_BASEFILE );
		$_POST['feed_url'] = 'https://example.com/feed';

		ob_start();
		$admin->validate_feed();

		$output   = ob_get_clean();
		$response = json_decode( $output, true );
		$this->assertIsArray( $response, 'Expected a JSON response, got: ' . $output );
		$result   = $response['data']['results'][0];

		$this->assertTrue( $response['success'] );
		$this->assertSame( $expected_title, $result['title'] );
		$this->assertStringStartsWith( $expected_title, $result['message'] );
		$this->assertStringNotContainsString( '&amp;', $result['message'] );
	}

	/**
	 * Encoded feed titles and their expected response values.
	 *
	 * @return array<string, array{string, string}>
	 */
	public function encoded_feed_title_provider(): array {
		return array(
			'formatting-like title' => array( '&lt;b&gt;News&lt;/b&gt; &amp; Updates', '<b>News</b> & Updates' ),
			'literal less-than'     => array( 'C++ &lt;3 Rust', 'C++ <3 Rust' ),
		);
	}

	/**
	 * Full-content errors in the dry-run preview render as text.
	 */
	public function test_dry_run_full_content_error_is_escaped(): void {
		add_filter( 'feedzy_is_license_of_type', '__return_true' );

		$admin  = new class() extends Feedzy_Rss_Feeds_Admin_Abstract {
			protected $plugin_name = 'feedzy-rss-feeds';
		};
		$method = new ReflectionMethod( $admin, 'get_dry_run_results' );
		$method->setAccessible( true );
		$output = $method->invoke(
			$admin,
			array( '_dry_run_tags_' => 'item_full_content' ),
			array( 'full_content_error' => '<b>Remote detail</b>' )
		);

		$this->assertStringNotContainsString( '<b>', $output );
		$this->assertStringContainsString( '&lt;b&gt;Remote detail&lt;/b&gt;', $output );
		$this->assertStringContainsString( '<i class="dashicons dashicons-no-alt fail"></i>', $output );
	}
}
