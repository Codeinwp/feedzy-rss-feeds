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

		$output = Feedzy_Rss_Feeds_Loop_Block::get_instance()->render_callback( $attributes, $template );

		// The Loop wrapper is only emitted once items are fetched and substituted,
		// so this guards against a feed that silently failed to render.
		$this->assertStringContainsString( 'feedzy-loop-columns-', $output );

		return $output;
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
	 * Only feed values are escaped, so child-block markup placed in the template
	 * (SVG icon, search form) survives while the feed payload stays inert.
	 *
	 * @access public
	 * @return void
	 */
	public function test_child_block_markup_is_preserved() {
		$output = $this->render(
			$this->malicious_feed(),
			'<div><svg viewBox="0 0 1 1"><path d="M0 0h1v1z"/></svg><form><input type="search"/></form><p>{{feedzy_title}}</p></div>'
		);

		$this->assertStringContainsString( '<svg viewBox="0 0 1 1">', $output );
		$this->assertStringContainsString( '<path d="M0 0h1v1z"', $output );
		$this->assertStringContainsString( '<input type="search"', $output );
		$this->assert_no_event_handler( $output );
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
	 * A feed value substituted into an event-handler attribute is dropped, so
	 * the attacker payload cannot reach the JavaScript context.
	 *
	 * @access public
	 * @return void
	 */
	public function test_event_handler_substitution_is_rejected() {
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

		$this->assertStringNotContainsString( 'alert(1)', $output );
		$this->assertStringContainsString( "console.log('')", $output );
	}

	/**
	 * A feed value substituted into a style attribute is dropped.
	 *
	 * @access public
	 * @return void
	 */
	public function test_style_attribute_substitution_is_rejected() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>red;background:url(https://evil.example/x)</title>'
			. '<link>https://example.org/a</link>'
			. '</item></channel></rss>';

		$output = $this->render(
			$feed,
			'<span style="color:{{feedzy_title}}">x</span>'
		);

		$this->assertStringContainsString( 'style="color:"', $output );
		$this->assertStringNotContainsString( 'background:', $output );
	}

	/**
	 * A feed value substituted in attribute-name position cannot introduce a
	 * second attribute (only name-legal characters survive).
	 *
	 * @access public
	 * @return void
	 */
	public function test_attribute_name_position_cannot_inject() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>x onclick=alert(1)</title>'
			. '<link>https://example.org/a</link>'
			. '</item></channel></rss>';

		$output = $this->render(
			$feed,
			'<span {{feedzy_title}}>x</span>'
		);

		$this->assert_no_event_handler( $output );
	}

	/**
	 * A benign title in a quoted attribute keeps its full text and adds no
	 * extra attribute.
	 *
	 * @access public
	 * @return void
	 */
	public function test_quoted_attribute_keeps_full_value() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>She said "hi" &amp; left</title>'
			. '<link>https://example.org/a</link>'
			. '</item></channel></rss>';

		$output = $this->render( $feed, '<img src="https://example.org/i.png" alt="{{feedzy_title}}"/>' );

		$doc = new DOMDocument();
		libxml_use_internal_errors( true );
		$doc->loadHTML( '<!DOCTYPE html><html><body>' . $output . '</body></html>' );
		libxml_clear_errors();
		$img = $doc->getElementsByTagName( 'img' )->item( 0 );

		$this->assertInstanceOf( 'DOMElement', $img );
		$this->assertSame( 'She said "hi" & left', $img->getAttribute( 'alt' ) );
		$this->assertSame( 2, $img->attributes->length );
	}

	/**
	 * A value in a single-quoted attribute cannot break out of the quotes.
	 *
	 * @access public
	 * @return void
	 */
	public function test_single_quoted_attribute_cannot_break_out() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. "<title>x' onerror='alert(1)</title>"
			. '<link>https://example.org/a</link>'
			. '</item></channel></rss>';

		$output = $this->render( $feed, "<img src='x' alt='{{feedzy_title}}'>" );

		$this->assert_no_event_handler( $output );
	}

	/**
	 * An uppercase event-handler attribute is matched case-insensitively and its
	 * substituted value is rejected.
	 *
	 * @access public
	 * @return void
	 */
	public function test_uppercase_event_handler_is_rejected() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>x);alert(1)</title>'
			. '<link>https://example.org/a</link>'
			. '</item></channel></rss>';

		$output = $this->render( $feed, '<a ONCLICK="f({{feedzy_title}})">x</a>' );

		$this->assertStringNotContainsString( 'alert(1)', $output );
	}

	/**
	 * A data: URL in an href attribute is rejected by esc_url().
	 *
	 * @access public
	 * @return void
	 */
	public function test_data_url_in_href_is_rejected() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>data:text/html,hello</title>'
			. '<link>https://example.org/a</link>'
			. '</item></channel></rss>';

		$output = $this->render( $feed, '<a href="{{feedzy_title}}">x</a>' );

		$this->assertStringNotContainsString( 'data:text/html', $output );
	}

	/**
	 * A src attribute is treated as a URL regardless of the magic tag key.
	 *
	 * @access public
	 * @return void
	 */
	public function test_src_attribute_rejects_unsafe_scheme() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>javascript:alert(1)</title>'
			. '<link>https://example.org/a</link>'
			. '</item></channel></rss>';

		$output = $this->render( $feed, '<img src="{{feedzy_title}}">' );

		$this->assertStringNotContainsString( 'javascript:', $output );
	}

	/**
	 * A value inside a <script> body cannot close the element or execute: it is
	 * reduced to inert text, with no extra </script> and no handler.
	 *
	 * @access public
	 * @return void
	 */
	public function test_script_body_is_not_exploitable() {
		$feed = '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0"><channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>'
			. '<title>&lt;/script&gt;&lt;img src=x onerror=alert(1)&gt;</title>'
			. '<link>https://example.org/a</link>'
			. '</item></channel></rss>';

		$output = $this->render( $feed, '<script>var a = "{{feedzy_title}}";</script>' );

		$this->assertSame( 1, substr_count( strtolower( $output ), '</script>' ) );
		$this->assertStringNotContainsString( 'onerror=', $output );
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
