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
	 * Assert that the rendered markup contains no element with an on* event
	 * handler attribute, i.e. nothing is executable once parsed as HTML.
	 *
	 * @access private
	 * @param string $html The rendered output.
	 * @return void
	 */
	private function assert_no_event_handler( $html ) {
		$doc = new DOMDocument();
		libxml_use_internal_errors( true );
		$doc->loadHTML( '<!DOCTYPE html><html><body>' . $html . '</body></html>' );
		libxml_clear_errors();

		foreach ( $doc->getElementsByTagName( '*' ) as $element ) {
			if ( null === $element->attributes ) {
				continue;
			}
			foreach ( $element->attributes as $attribute ) {
				$this->assertStringStartsNotWith(
					'on',
					strtolower( $attribute->name ),
					'Executable handler attribute found: ' . $attribute->name
				);
			}
		}
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

		$this->assert_no_event_handler( $output );
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
	 * The assembled output is sanitized with wp_kses_post(), so disallowed tags
	 * are dropped and no feed-injected event handler survives.
	 *
	 * @access public
	 * @return void
	 */
	public function test_output_is_sanitized() {
		$output = $this->render(
			$this->malicious_feed(),
			'<div><svg viewBox="0 0 1 1"></svg><form><input type="search"/></form><p>{{feedzy_title}}</p></div>'
		);

		$this->assertStringNotContainsString( '<svg', $output );
		$this->assertStringNotContainsString( '<form', $output );
		$this->assertStringNotContainsString( 'onerror=', $output );
	}

	/**
	 * An HTML-valued tag (description) placed in an attribute must be escaped
	 * for the attribute context so it cannot break out and add a handler.
	 *
	 * @access public
	 * @return void
	 */
	public function test_html_value_cannot_break_out_of_attribute() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>T</title>'
			. '<link>https://example.org/a</link>'
			. '<description>x&quot; onerror=&quot;alert(1)</description>'
			. '</item></channel></rss>';

		$output = $this->render(
			$feed,
			'<img src="https://example.org/i.png" alt="{{feedzy_description}}"/>'
		);

		$this->assert_no_event_handler( $output );
	}

	/**
	 * A literal ">" in an earlier quoted attribute must not make a later
	 * placeholder be treated as element content.
	 *
	 * @access public
	 * @return void
	 */
	public function test_gt_in_quoted_attribute_keeps_attribute_context() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">'
			. '<channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>T</title>'
			. '<link>https://example.org/a</link>'
			. '<content:encoded>x&quot; onerror=&quot;alert(1)</content:encoded>'
			. '</item></channel></rss>';

		$output = $this->render(
			$feed,
			'<img src="x" alt="1 > 0" title="{{feedzy_content}}"/>'
		);

		$this->assert_no_event_handler( $output );
	}

	/**
	 * A plain-text value in an unquoted attribute must not gain a second,
	 * executable attribute.
	 *
	 * @access public
	 * @return void
	 */
	public function test_unquoted_attribute_cannot_add_handler() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>x onerror=alert(1)</title>'
			. '<link>https://example.org/a</link>'
			. '</item></channel></rss>';

		$output = $this->render(
			$feed,
			'<img src="x" alt={{feedzy_title}}>'
		);

		$this->assert_no_event_handler( $output );
	}

	/**
	 * A quote inside an HTML comment must not let a later attribute placeholder
	 * inject a handler.
	 *
	 * @access public
	 * @return void
	 */
	public function test_comment_quote_does_not_enable_breakout() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">'
			. '<channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>T</title>'
			. '<link>https://example.org/a</link>'
			. '<content:encoded>x&quot; onerror=&quot;alert(1)</content:encoded>'
			. '</item></channel></rss>';

		$output = $this->render(
			$feed,
			'<!-- " --><img src="x" alt="1 > 0" title="{{feedzy_content}}"/>'
		);

		$this->assert_no_event_handler( $output );
	}

	/**
	 * A javascript: URL arriving through any magic tag must not survive in an
	 * href attribute.
	 *
	 * @access public
	 * @return void
	 */
	public function test_javascript_url_in_href_is_neutralized() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>javascript:alert(1)</title>'
			. '<link>https://example.org/a</link>'
			. '</item></channel></rss>';

		$output = $this->render(
			$feed,
			'<a href="{{feedzy_title}}">link</a>'
		);

		$this->assertStringNotContainsString( 'javascript:', $output );
	}

	/**
	 * An event-handler attribute in the template must be removed, so a value
	 * substituted inside it cannot execute.
	 *
	 * @access public
	 * @return void
	 */
	public function test_event_handler_attribute_is_removed() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. "<title>');alert(1);//</title>"
			. '<link>https://example.org/a</link>'
			. '</item></channel></rss>';

		$output = $this->render(
			$feed,
			'<a href="https://example.org/" onclick="console.log(\'{{feedzy_title}}\')">link</a>'
		);

		$this->assertStringNotContainsString( 'onclick', $output );
		$this->assertStringNotContainsString( 'alert(1)', $output );
	}

	/**
	 * An HTML-valued tag in element content must keep its allowed rich markup.
	 *
	 * @access public
	 * @return void
	 */
	public function test_html_value_keeps_rich_content() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">'
			. '<channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>T</title>'
			. '<link>https://example.org/a</link>'
			. '<description>Body</description>'
			. '<content:encoded>Rich &lt;strong&gt;bold&lt;/strong&gt;</content:encoded>'
			. '</item></channel></rss>';

		$output = $this->render( $feed, '<p>{{feedzy_content}}</p>' );

		$this->assertStringContainsString( '<strong>bold</strong>', $output );
	}

	/**
	 * A feed whose author has only an email (get_name() is null) must not
	 * abort Loop rendering when the template uses {{feedzy_author}}.
	 *
	 * @access public
	 * @return void
	 */
	public function test_email_only_author_does_not_abort_render() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>T</title>'
			. '<link>https://example.org/a</link>'
			. '<author>bob@example.com</author>'
			. '</item></channel></rss>';

		$output = $this->render( $feed, '<p>{{feedzy_author}}</p>' );

		$this->assertStringContainsString( 'feedzy-loop-columns', $output );
		$this->assertStringNotContainsString( '{{feedzy_author}}', $output );
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
