<?php
/**
 * Tests for sanitizing the imported post excerpt.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Import_Excerpt_Sanitize extends WP_UnitTestCase {

	/**
	 * Create an administrator so the import runs with kses removed, as a manual "Run Now" does.
	 *
	 * @access public
	 */
	public function set_up(): void {
		parent::set_up();

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		add_filter( 'pre_http_request', array( $this, 'mock_feed_request' ), 10, 3 );
	}

	/**
	 * Restore the HTTP layer and the kses filters removed for the administrator.
	 *
	 * @access public
	 */
	public function tear_down(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_feed_request' ), 10 );
		wp_set_current_user( 0 );

		parent::tear_down();
	}

	/**
	 * Serve the fixture feed instead of a live request.
	 *
	 * @param false|array<string, mixed>|WP_Error $preempt Whether to preempt the request.
	 * @param array<string, mixed>                $args    Request args.
	 * @param string                              $url     Request url.
	 *
	 * @return array{headers: array{content-type: string}, body: string, response: array{code: 200, message: 'OK'}, cookies: array{}, filename: ''}|WP_Error
	 */
	public function mock_feed_request( $preempt, array $args, string $url ) {
		if ( false === strpos( $url, 'feedzy-fixture' ) ) {
			return new WP_Error( 'network_disabled', 'Live HTTP requests are not allowed in this test: ' . $url );
		}

		return array(
			'headers'  => array( 'content-type' => 'application/rss+xml; charset=UTF-8' ),
			'body'     => (string) file_get_contents( __DIR__ . '/fixtures/excerpt-xss-feed.xml' ),
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => '',
		);
	}

	/**
	 * Feed markup routed into the excerpt is stored without executable HTML.
	 */
	public function test_imported_excerpt_strips_executable_markup(): void {
		$this->assertFalse( has_filter( 'excerpt_save_pre', 'wp_filter_post_kses' ), 'Core kses must be off, as for an administrator running the import.' );

		$excerpts = $this->run_import( '[#item_description] [#item_title]' );

		$this->assertArrayHasKey( 'Video Item', $excerpts );
		$this->assertArrayHasKey( 'Title Item', $excerpts );

		foreach ( $excerpts as $title => $excerpt ) {
			$this->assertDoesNotMatchRegularExpression( '/<(svg|script)\b/i', $excerpt, $title );
			$this->assertDoesNotMatchRegularExpression( '/\son[a-z]+\s*=/i', $excerpt, $title );
		}

		$this->assertStringContainsString( '<strong>Video</strong> description', $excerpts['Video Item'] );
		$this->assertStringContainsString( 'Title item description.', $excerpts['Title Item'] );
	}

	/**
	 * Run an import of the fixture feed with the given excerpt template.
	 *
	 * @param string $excerpt_template The import_post_excerpt template.
	 *
	 * @return array<string, string> Stored excerpts keyed by post title.
	 */
	private function run_import( string $excerpt_template ): array {
		$import_id = self::factory()->post->create(
			array(
				'post_type'   => 'feedzy_imports',
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
			)
		);

		update_post_meta( $import_id, 'source', 'https://example.org/feedzy-fixture/excerpt-xss-' . uniqid() . '.xml' );
		update_post_meta( $import_id, 'import_post_type', 'post' );
		update_post_meta( $import_id, 'import_post_status', 'publish' );
		update_post_meta( $import_id, 'import_post_title', '[#item_title]' );
		update_post_meta( $import_id, 'import_post_content', '[#item_content]' );
		update_post_meta( $import_id, 'import_post_excerpt', $excerpt_template );
		update_post_meta( $import_id, 'import_feed_limit', 10 );

		$import = new Feedzy_Rss_Feeds_Import( 'feedzy-rss-feeds', Feedzy_Rss_Feeds::get_version() );
		$method = new ReflectionMethod( $import, 'run_job_logic' );
		$method->setAccessible( true );
		$method->invoke( $import, get_post( $import_id ), 10 );

		$excerpts = array();
		foreach ( get_posts( array( 'post_type' => 'post', 'numberposts' => -1 ) ) as $post ) {
			$excerpts[ wp_strip_all_tags( $post->post_title ) ] = $post->post_excerpt;
		}

		return $excerpts;
	}
}
