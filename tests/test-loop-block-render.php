<?php
/**
 * Tests for Feedzy_Rss_Feeds_Loop_Block::render_callback() output sanitization.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Loop_Block_Render extends WP_UnitTestCase {

	/**
	 * Feed body returned by the mocked HTTP layer.
	 *
	 * @var string
	 */
	private $feed_body = '';

	/**
	 * Set up test environment.
	 *
	 * @access public
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Short-circuit at priority 1 so the mock runs before Feedzy's
		// request validator and before any real network access.
		add_filter( 'pre_http_request', array( $this, 'mock_http_request' ), 1, 3 );

		// The block singleton registers the magic-tag filter in its constructor
		// at bootstrap; the WP test suite restores hooks after each test, so
		// re-ensure it here rather than relying on that one-time registration.
		$block = Feedzy_Rss_Feeds_Loop_Block::get_instance();
		if ( false === has_filter( 'feedzy_loop_item', array( $block, 'apply_magic_tags' ) ) ) {
			add_filter( 'feedzy_loop_item', array( $block, 'apply_magic_tags' ), 10, 3 );
		}
	}

	/**
	 * Tear down test environment.
	 *
	 * @access public
	 * @return void
	 */
	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_http_request' ), 1 );
		$this->purge_feed_cache();
		parent::tearDown();
	}

	/**
	 * Serve the crafted feed body for the fixture URL only.
	 *
	 * @param false|array<string, mixed>|WP_Error $preempt Preemptive return value.
	 * @param array<string, mixed>                $args HTTP request arguments.
	 * @param string                              $url The request URL.
	 * @return false|array<string, mixed>
	 */
	public function mock_http_request( $preempt, $args, $url ) {
		if ( false === strpos( $url, 'feedzy-loop-fixture' ) ) {
			return $preempt;
		}

		return array(
			'headers'  => array( 'content-type' => 'application/rss+xml; charset=UTF-8' ),
			'body'     => $this->feed_body,
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => '',
		);
	}

	/**
	 * Remove SimplePie's on-disk feed cache between tests.
	 *
	 * @access private
	 * @return void
	 */
	private function purge_feed_cache() {
		$dir = wp_upload_dir()['basedir'] . '/simplepie';
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$files = glob( $dir . '/*.spc' );
		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				unlink( $file );
			}
		}
	}

	/**
	 * Build a feed URL that is unique per test so no feed cache is reused.
	 *
	 * @access private
	 * @return string
	 */
	private function build_feed_url() {
		return 'https://example.org/feedzy-loop-fixture/' . md5( $this->getName() . uniqid() ) . '.xml';
	}

	/**
	 * Render a Loop block for a given feed body and inner template.
	 *
	 * @access private
	 * @param string $feed_body The RSS feed XML to serve.
	 * @param string $template The inner block template containing magic tags.
	 * @return string The rendered block HTML.
	 */
	private function render( $feed_body, $template ) {
		$this->feed_body = $feed_body;
		$this->purge_feed_cache();

		$attributes = array(
			'feed'  => array(
				'type'   => 'url',
				'source' => array( $this->build_feed_url() ),
			),
			'query' => array(
				'max'     => 1,
				'sort'    => 'default',
				'refresh' => '1_hours',
			),
		);

		return Feedzy_Rss_Feeds_Loop_Block::get_instance()->render_callback( $attributes, $template );
	}

	/**
	 * Build an RSS feed whose values carry XSS payloads.
	 *
	 * @access private
	 * @return string
	 */
	private function malicious_feed() {
		return '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0" xmlns:feedzy="' . FEEDZY_FEED_CUSTOM_TAG_NAMESPACE . '">'
			. '<channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>&lt;img src=x onerror=alert(1)&gt;Headline</title>'
			. '<link>javascript:alert(2)</link>'
			. '<description>Body text</description>'
			. '<feedzy:parent-source>&lt;svg onload=alert(3)&gt;</feedzy:parent-source>'
			. '</item></channel></rss>';
	}

	/**
	 * The feed title must not reach output as an executable event handler,
	 * whether placed in text or inside an attribute.
	 *
	 * @access public
	 * @return void
	 */
	public function test_feed_title_markup_is_not_executable() {
		$output = $this->render(
			$this->malicious_feed(),
			'<p>{{feedzy_title}}</p><figure><img src="https://example.org/i.png" alt="{{feedzy_title}}"/></figure>'
		);

		$this->assertStringNotContainsString( 'onerror=', $output );
	}

	/**
	 * A javascript: feed link must not survive as an href.
	 *
	 * @access public
	 * @return void
	 */
	public function test_feed_url_rejects_javascript_scheme() {
		$output = $this->render(
			$this->malicious_feed(),
			'<p><a href="{{feedzy_url}}">go</a></p>'
		);

		$this->assertStringNotContainsString( 'javascript:', $output );
	}

	/**
	 * The feed source must not inject raw markup such as <svg onload>.
	 *
	 * @access public
	 * @return void
	 */
	public function test_feed_source_markup_is_stripped() {
		$output = $this->render(
			$this->malicious_feed(),
			'<p>{{feedzy_source}}</p>'
		);

		$this->assertStringNotContainsString( 'onload=', $output );
		$this->assertStringNotContainsString( '<svg', $output );
	}

	/**
	 * Escaping feed values must not strip markup the template author placed
	 * directly in the Loop block (e.g. an SVG icon or a search form).
	 *
	 * @access public
	 * @return void
	 */
	public function test_template_markup_is_preserved() {
		$output = $this->render(
			$this->malicious_feed(),
			'<div><svg viewBox="0 0 1 1"></svg><form><input type="search"/></form><p>{{feedzy_title}}</p></div>'
		);

		$this->assertStringContainsString( '<svg', $output );
		$this->assertStringContainsString( '<form', $output );
		$this->assertStringContainsString( 'type="search"', $output );
		$this->assertStringNotContainsString( 'onerror=', $output );
	}

	/**
	 * Sanitization must keep legitimate feed content intact.
	 *
	 * @access public
	 * @return void
	 */
	public function test_benign_content_is_preserved() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>Weekly News</title>'
			. '<link>https://example.org/article</link>'
			. '<description>Body text</description>'
			. '</item></channel></rss>';

		$output = $this->render(
			$feed,
			'<p>{{feedzy_title}}</p><p><a href="{{feedzy_url}}">read</a></p>'
		);

		$this->assertStringContainsString( 'Weekly News', $output );
		$this->assertStringContainsString( 'https://example.org/article', $output );
	}
}
