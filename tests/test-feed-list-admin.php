<?php
/**
 * Feed list validation and wizard block integration tests.
 *
 * @package feedzy-rss-feeds
 */
class Test_Feed_List_Admin extends WP_UnitTestCase {

	private $feeds;

	public function set_up(): void {
		parent::set_up();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$token       = wp_generate_uuid4();
		$this->feeds = array(
			'https://93.184.215.14/feedzy-fixture/one?test=' . $token . '&limit=2',
			'https://93.184.215.14/feedzy-fixture/two?test=' . $token . '&limit=2',
		);
		add_filter( 'pre_http_request', array( $this, 'mock_feed_request' ), 10, 3 );
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', array( $this, 'get_die_handler' ) );
	}

	public function tear_down(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_feed_request' ), 10 );
		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_die_handler' ) );
		$_POST    = array();
		$_REQUEST = array();
		wp_set_current_user( 0 );
		parent::tear_down();
	}

	public function mock_feed_request( $preempt, $args, $url ) {
		if ( ! in_array( $url, $this->feeds, true ) ) {
			return new WP_Error( 'invalid_feed', 'Not a fixture feed.' );
		}
		return array(
			'headers'  => array( 'content-type' => 'application/rss+xml; charset=UTF-8' ),
			'body'     => file_get_contents( __DIR__ . '/fixtures/sample-feed.xml' ),
			'response' => array( 'code' => 200, 'message' => 'OK' ),
			'cookies'  => array(),
			'filename' => '',
		);
	}

	public function get_die_handler(): callable {
		return static function ( $message ) {
			throw new WPDieException( (string) $message );
		};
	}

	private function ajax_request( $action, $nonce_action, $payload ) {
		$_POST    = wp_slash( array_merge( $payload, array( 'security' => wp_create_nonce( $nonce_action ) ) ) );
		$_REQUEST = $_POST;
		ob_start();
		try {
			do_action( 'wp_ajax_' . $action );
		} catch ( WPDieException $e ) {
			unset( $e );
		}
		$response = json_decode( ob_get_clean(), true );
		$this->assertIsArray( $response );
		return $response;
	}

	public function feed_separators(): array {
		return array(
			'line feeds'       => array( "\n" ),
			'blank lines'      => array( "\n\n" ),
			'windows lines'    => array( "\r\n" ),
			'carriage returns' => array( "\r" ),
			'commas'           => array( ', ' ),
		);
	}

	/**
	 * @dataProvider feed_separators
	 *
	 * @param string $separator Feed separator.
	 */
	public function test_ajax_validates_each_feed_separately( string $separator ): void {
		$input    = array_merge( $this->feeds, array( 'https://93.184.215.14/feedzy-fixture/invalid' ) );
		$response = $this->ajax_request(
			'feedzy_categories',
			FEEDZY_NAME,
			array( '_action' => 'validate_feeds_group', 'feeds' => implode( $separator, $input ) )
		);
		$this->assertTrue( $response['success'] );
		$this->assertSame( $this->feeds, $response['data']['valid'] );
		$this->assertSame( array( $input[2] ), array_values( $response['data']['invalid'] ) );
		$single = $this->ajax_request(
			'feedzy_categories',
			FEEDZY_NAME,
			array( '_action' => 'validate_feeds_group', 'feeds' => $this->feeds[0] )
		);
		$this->assertSame( array( $this->feeds[0] ), $single['data']['valid'] );
		$this->assertSame( array(), $single['data']['invalid'] );
	}

	public function feed_counts(): array {
		return array( 'single feed' => array( 1 ), 'multiple feeds' => array( 2 ) );
	}

	/**
	 * @dataProvider feed_counts
	 *
	 * @param int $count Number of feeds.
	 */
	public function test_wizard_draft_contains_separate_block_sources( int $count ): void {
		$feeds    = array_slice( $this->feeds, 0, $count );
		$payload  = array( 'step' => 'step_2', 'feed' => implode( ',', $feeds ), 'integrate_with' => 'page_builder' );
		$response = $this->ajax_request(
			'feedzy_wizard_step_process',
			FEEDZY_BASEFILE,
			$payload
		);
		$this->assertSame( 1, $response['status'] );
		$wizard = get_option( 'feedzy_wizard_data' );
		$this->assertSame( implode( ',', $feeds ), $wizard['feed'] );
		$page = get_post( $wizard['page_id'] );
		$this->assertSame( 'draft', $page->post_status );
		$blocks = parse_blocks( $page->post_content );
		$this->assertSame( 'feedzy-rss-feeds/loop', $blocks[0]['blockName'] );
		$this->assertSame( array( 'type' => 'url', 'source' => $feeds ), $blocks[0]['attrs']['feed'] );
		$this->assertSame( 'core/group', $blocks[0]['innerBlocks'][0]['blockName'] );
		$this->assertCount( 4, $blocks[0]['innerBlocks'][0]['innerBlocks'] );
		$updated = $this->ajax_request( 'feedzy_wizard_step_process', FEEDZY_BASEFILE, $payload );
		$this->assertSame( 1, $updated['status'] );
		$this->assertSame( $page->ID, get_option( 'feedzy_wizard_data' )['page_id'] );
		$blocks = parse_blocks( get_post_field( 'post_content', $page->ID ) );
		$this->assertSame( $feeds, $blocks[0]['attrs']['feed']['source'] );
	}
}
