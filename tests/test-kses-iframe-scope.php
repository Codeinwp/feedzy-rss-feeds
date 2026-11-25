<?php
/**
 * Tests that Feedzy allows iframes only while it sanitizes import content.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Kses_Iframe_Scope extends WP_UnitTestCase {

	/**
	 * Iframe markup used as attacker input and as an import template.
	 */
	const IFRAME = '<iframe src="https://www.youtube.com/embed/abc" width="560" height="315"></iframe>';

	/**
	 * Serve the fixture feed instead of a live request.
	 *
	 * @access public
	 */
	public function set_up(): void {
		parent::set_up();

		add_filter( 'pre_http_request', array( $this, 'mock_feed_request' ), 10, 3 );
	}

	/**
	 * Restore the HTTP layer, the request data and the current user.
	 *
	 * @access public
	 */
	public function tear_down(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_feed_request' ), 10 );
		$_POST = array();
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
	 * KSES contexts that clean untrusted input.
	 *
	 * @return array<string, array{0: string}>
	 */
	public function untrusted_contexts(): array {
		return array(
			'comment'      => array( 'pre_comment_content' ),
			'user bio'     => array( 'pre_user_description' ),
			'post title'   => array( 'title_save_pre' ),
			'post content' => array( 'post' ),
		);
	}

	/**
	 * Core KSES strips iframes from untrusted input with Feedzy active.
	 *
	 * @dataProvider untrusted_contexts
	 *
	 * @param string $context KSES context.
	 */
	public function test_core_kses_strips_iframe( string $context ): void {
		$this->assertStringNotContainsString( '<iframe', wp_kses( self::IFRAME, $context ) );
	}

	/**
	 * An unauthenticated comment cannot store an iframe.
	 */
	public function test_unauthenticated_comment_strips_iframe(): void {
		wp_set_current_user( 0 );
		$post_id = self::factory()->post->create();

		$comment_id = wp_new_comment(
			array(
				'comment_post_ID'      => $post_id,
				'comment_author'       => 'Visitor',
				'comment_author_email' => 'visitor@example.org',
				'comment_author_url'   => '',
				'comment_content'      => 'Hi ' . self::IFRAME,
			)
		);

		$this->assertIsInt( $comment_id );
		$this->assertStringNotContainsString( '<iframe', get_comment( $comment_id )->comment_content );
	}

	/**
	 * Saving an import keeps the template iframe and leaves other contexts closed.
	 */
	public function test_saved_import_template_keeps_iframe(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$import_id = self::factory()->post->create(
			array(
				'post_type'   => 'feedzy_imports',
				'post_author' => $admin_id,
			)
		);

		$_POST = array(
			'feedzy_post_nonce' => wp_create_nonce( 'feedzy_post_nonce' ),
			'post_type'         => 'feedzy_imports',
			'feedzy_meta_data'  => array(
				'import_post_type'    => 'post',
				'import_post_content' => '<iframe src="[#item_custom_media:url]" width="560"></iframe>[#item_content]<iframe src="[#item_custom_media:thumb]" height="315">',
			),
		);
		do_action( 'save_post_feedzy_imports', $import_id, get_post( $import_id ) );

		$template = stripslashes( urldecode( (string) get_post_meta( $import_id, 'import_post_content', true ) ) );
		$this->assertStringContainsString( '<iframe src="[#item_custom_media:url]" width="560"></iframe>', $template );
		$this->assertStringContainsString( '<iframe src="[#item_custom_media:thumb]" height="315">', $template );
		$this->assertStringNotContainsString( '<iframe', wp_kses( self::IFRAME, 'pre_comment_content' ) );
	}

	/**
	 * A cron import keeps the template iframe and leaves other contexts closed.
	 */
	public function test_cron_import_keeps_template_iframe(): void {
		wp_set_current_user( 0 );
		$this->assertNotFalse( has_filter( 'content_save_pre', 'wp_filter_post_kses' ), 'Core kses must be on, as for a cron run.' );

		$import_id = self::factory()->post->create(
			array(
				'post_type'   => 'feedzy_imports',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $import_id, 'source', 'https://example.org/feedzy-fixture/iframe-' . uniqid() . '.xml' );
		update_post_meta( $import_id, 'import_post_type', 'post' );
		update_post_meta( $import_id, 'import_post_status', 'publish' );
		update_post_meta( $import_id, 'import_post_title', '[#item_title]' );
		update_post_meta( $import_id, 'import_post_content', self::IFRAME . '[#item_content]' );
		update_post_meta( $import_id, 'import_feed_limit', 10 );

		$import = new Feedzy_Rss_Feeds_Import( 'feedzy-rss-feeds', Feedzy_Rss_Feeds::get_version() );
		$method = new ReflectionMethod( $import, 'run_job_logic' );
		$method->setAccessible( true );
		$method->invoke( $import, get_post( $import_id ), 10 );

		$posts = get_posts( array( 'post_type' => 'post', 'numberposts' => -1 ) );
		$this->assertNotEmpty( $posts );
		foreach ( $posts as $post ) {
			$this->assertStringContainsString( '<iframe src="https://www.youtube.com/embed/abc"', $post->post_content );
		}
		$this->assertStringNotContainsString( '<iframe', wp_kses( self::IFRAME, 'pre_comment_content' ) );
	}
}
