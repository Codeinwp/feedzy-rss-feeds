<?php
/**
 * Tests for Feedzy_Rss_Feeds_Loop_Block::render_callback() output escaping.
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
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Priority 1 runs before Feedzy's request validator and any network access.
		add_filter( 'pre_http_request', array( $this, 'mock_http_request' ), 1, 3 );

		$block = Feedzy_Rss_Feeds_Loop_Block::get_instance();
		if ( false === has_filter( 'feedzy_loop_item', array( $block, 'apply_magic_tags' ) ) ) {
			add_filter( 'feedzy_loop_item', array( $block, 'apply_magic_tags' ), 10, 3 );
		}
	}

	/**
	 * Tear down test environment.
	 *
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
	 * @return false|array<string, mixed>|WP_Error|array{headers: array{content-type: string}, body: string, response: array{code: 200, message: 'OK'}, cookies: array{}, filename: ''}
	 */
	public function mock_http_request( $preempt, array $args, string $url ) {
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
	 * @return void
	 */
	private function purge_feed_cache(): void {
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
	 * Render a Loop block for a given feed body and inner template.
	 *
	 * @param string $feed_body The RSS feed XML to serve.
	 * @param string $template The inner block template containing magic tags.
	 * @return string The rendered block HTML.
	 */
	private function render( string $feed_body, string $template ): string {
		$this->feed_body = $feed_body;
		$this->purge_feed_cache();

		$attributes = array(
			'feed'  => array(
				'type'   => 'url',
				'source' => array( 'https://example.org/feedzy-loop-fixture/' . md5( $this->getName() . uniqid() ) . '.xml' ),
			),
			'query' => array(
				'max'     => 1,
				'sort'    => 'default',
				'refresh' => '1_hours',
			),
		);

		$output = Feedzy_Rss_Feeds_Loop_Block::get_instance()->render_callback( $attributes, $template );

		// The wrapper is only emitted once items were fetched and substituted.
		$this->assertStringContainsString( 'feedzy-loop-columns-', $output );

		return $output;
	}

	/**
	 * Parse rendered markup into a DOM document.
	 *
	 * @param string $html The rendered output.
	 * @return DOMDocument
	 */
	private function parse( string $html ): DOMDocument {
		$doc = new DOMDocument();
		libxml_use_internal_errors( true );
		$doc->loadHTML( '<!DOCTYPE html><html><body>' . $html . '</body></html>' );
		libxml_clear_errors();

		return $doc;
	}

	/**
	 * Assert no element carries an on* handler or srcdoc attribute.
	 *
	 * @param string $html The rendered output.
	 * @return void
	 */
	private function assert_not_executable( string $html ): void {
		foreach ( $this->parse( $html )->getElementsByTagName( '*' ) as $element ) {
			if ( null === $element->attributes ) {
				continue;
			}
			foreach ( $element->attributes as $attribute ) {
				$name = strtolower( $attribute->name );
				$this->assertStringStartsNotWith( 'on', $name, 'Executable handler attribute found: ' . $name );
				$this->assertNotSame( 'srcdoc', $name );
			}
		}
	}

	/**
	 * Build a one-item RSS feed.
	 *
	 * @param string $item_xml The inner XML of the item.
	 * @return string
	 */
	private function feed( string $item_xml ): string {
		return '<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:feedzy="' . FEEDZY_FEED_CUSTOM_TAG_NAMESPACE . '">'
			. '<channel><title>Fixture</title><link>https://example.org/</link><description>d</description>'
			. '<item>' . $item_xml . '</item>'
			. '</channel></rss>';
	}

	/**
	 * Build a feed whose title carries the given text.
	 *
	 * @param string $title The raw title XML.
	 * @return string
	 */
	private function feed_with_title( string $title ): string {
		return $this->feed( '<title>' . $title . '</title><link>https://example.org/a</link>' );
	}

	/**
	 * Build a feed whose content:encoded carries the given text.
	 *
	 * @param string $content The raw content XML.
	 * @return string
	 */
	private function feed_with_content( string $content ): string {
		return $this->feed( '<title>T</title><link>https://example.org/a</link><content:encoded>' . $content . '</content:encoded>' );
	}

	/**
	 * Entity-encoded title markup must not become an executable element.
	 *
	 * @return void
	 */
	public function test_feed_title_markup_is_not_executable(): void {
		$output = $this->render(
			$this->feed_with_title( '&lt;img src=x onerror=alert(1)&gt;Headline' ),
			'<p>{{feedzy_title}}</p><figure><img src="https://example.org/i.png" alt="{{feedzy_title}}"/></figure>'
		);

		$this->assert_not_executable( $output );
		$this->assertStringContainsString( 'Headline', $output );
	}

	/**
	 * A javascript: feed link must not survive as an href.
	 *
	 * @return void
	 */
	public function test_feed_url_rejects_javascript_scheme(): void {
		$output = $this->render(
			$this->feed( '<title>T</title><link>javascript:alert(2)</link>' ),
			'<p><a href="{{feedzy_url}}">go</a></p>'
		);

		$this->assertStringNotContainsString( 'javascript:', $output );
	}

	/**
	 * The feed source must not inject raw markup.
	 *
	 * @return void
	 */
	public function test_feed_source_markup_is_stripped(): void {
		$output = $this->render(
			$this->feed( '<title>T</title><link>https://example.org/a</link><feedzy:parent-source>&lt;svg onload=alert(3)&gt;</feedzy:parent-source>' ),
			'<p>{{feedzy_source}}</p>'
		);

		$this->assertStringNotContainsString( '<svg', $output );
		$this->assert_not_executable( $output );
	}

	/**
	 * Child-block markup in the template (SVG icon, search form, embed iframe)
	 * survives while the feed payload stays inert.
	 *
	 * @return void
	 */
	public function test_child_block_markup_is_preserved(): void {
		$output = $this->render(
			$this->feed_with_title( '&lt;img src=x onerror=alert(1)&gt;' ),
			'<div><svg viewBox="0 0 1 1"><path d="M0 0h1v1z"/></svg><form role="search"><input type="search" name="s"/></form>'
			. '<iframe src="https://www.youtube.com/embed/x" allowfullscreen></iframe><p>{{feedzy_title}}</p></div>'
		);

		$doc    = $this->parse( $output );
		$svg    = $doc->getElementsByTagName( 'svg' )->item( 0 );
		$input  = $doc->getElementsByTagName( 'input' )->item( 0 );
		$iframe = $doc->getElementsByTagName( 'iframe' )->item( 0 );

		$this->assertInstanceOf( 'DOMElement', $svg );
		$this->assertSame( '0 0 1 1', $svg->getAttribute( 'viewbox' ) );
		$this->assertSame( 1, $doc->getElementsByTagName( 'path' )->length );
		$this->assertSame( 1, $doc->getElementsByTagName( 'form' )->length );
		$this->assertInstanceOf( 'DOMElement', $input );
		$this->assertSame( 'search', $input->getAttribute( 'type' ) );
		$this->assertInstanceOf( 'DOMElement', $iframe );
		$this->assertSame( 'https://www.youtube.com/embed/x', $iframe->getAttribute( 'src' ) );
		$this->assert_not_executable( $output );
	}

	/**
	 * An HTML-valued tag placed in an attribute cannot break out and add a handler.
	 *
	 * @return void
	 */
	public function test_html_value_cannot_break_out_of_attribute(): void {
		$output = $this->render(
			$this->feed( '<title>T</title><link>https://example.org/a</link><description>x&quot; onerror=&quot;alert(1)</description>' ),
			'<img src="https://example.org/i.png" alt="{{feedzy_description}}"/>'
		);

		$this->assert_not_executable( $output );
	}

	/**
	 * A ">" in an earlier quoted attribute does not change how a later value is treated.
	 *
	 * @return void
	 */
	public function test_gt_in_quoted_attribute_keeps_attribute_context(): void {
		$output = $this->render(
			$this->feed_with_content( 'x&quot; onerror=&quot;alert(1)' ),
			'<img src="x" alt="1 > 0" title="{{feedzy_content}}"/>'
		);

		$img = $this->parse( $output )->getElementsByTagName( 'img' )->item( 0 );

		$this->assertInstanceOf( 'DOMElement', $img );
		$this->assertSame( 'x', $img->getAttribute( 'src' ) );
		$this->assertSame( '1 > 0', $img->getAttribute( 'alt' ) );
		$this->assertSame( 'x" onerror="alert(1)', $img->getAttribute( 'title' ) );
		$this->assertSame( 3, $img->attributes->length );
		$this->assertSame( '', trim( wp_strip_all_tags( $output ) ) );
		$this->assert_not_executable( $output );
	}

	/**
	 * A ">" in a quoted attribute keeps the tag intact even without magic tags.
	 *
	 * @return void
	 */
	public function test_gt_in_quoted_attribute_without_magic_tag_is_preserved(): void {
		$output = $this->render(
			$this->feed_with_title( 'T' ),
			'<figure><img src="https://example.org/i.png" alt=\'Price > 5 < 9\'/></figure>'
		);

		$img = $this->parse( $output )->getElementsByTagName( 'img' )->item( 0 );

		$this->assertInstanceOf( 'DOMElement', $img );
		$this->assertSame( 'https://example.org/i.png', $img->getAttribute( 'src' ) );
		$this->assertSame( 'Price > 5 < 9', $img->getAttribute( 'alt' ) );
		$this->assertSame( '', trim( wp_strip_all_tags( $output ) ) );
	}

	/**
	 * A quote inside an HTML comment does not enable an attribute breakout.
	 *
	 * @return void
	 */
	public function test_comment_quote_does_not_enable_breakout(): void {
		$output = $this->render(
			$this->feed_with_content( 'x&quot; onerror=&quot;alert(1)' ),
			'<!-- " --><img src="x" alt="1 > 0" title="{{feedzy_content}}"/>'
		);

		$this->assert_not_executable( $output );
	}

	/**
	 * Magic tags inside a comment cannot end the comment early or add an element,
	 * and URL values there are plain text.
	 *
	 * @return void
	 */
	public function test_comment_value_cannot_close_comment(): void {
		$output = $this->render(
			$this->feed( '<title>--&gt;&lt;img src=x onerror=alert(1)&gt;</title><link>https://example.org/a</link>' ),
			'<!-- {{feedzy_title}} {{feedzy_url}} --><p>x</p>'
		);

		$this->assertSame( 1, substr_count( $output, '<!--' ) );
		$this->assertSame( 1, substr_count( $output, '-->' ) );
		$this->assertSame( 1, preg_match( '/<!--([^<]*)--><p>x<\/p>/', $output, $comment ) );
		$this->assertStringContainsString( 'https://example.org/a', $comment[1] );

		$doc = $this->parse( $output );
		$this->assertSame( 0, $doc->getElementsByTagName( 'img' )->length );
		$this->assertSame( 1, $doc->getElementsByTagName( 'p' )->length );
	}

	/**
	 * Denied style and script elements are removed with their body, so the
	 * source never shows as text.
	 *
	 * @return void
	 */
	public function test_style_and_script_bodies_are_removed(): void {
		$output = $this->render(
			$this->feed_with_title( 'Headline' ),
			'<style>.feedzy-x{color:red}</style><script>var feedzyX = 1;</script><p class="feedzy-x">{{feedzy_title}}</p>'
		);

		$this->assertStringNotContainsString( '.feedzy-x{color:red}', $output );
		$this->assertStringNotContainsString( 'feedzyX', $output );
		$this->assertStringContainsString( '<p class="feedzy-x">Headline</p>', $output );
	}

	/**
	 * A style tag inside a comment is not treated as a raw-text element.
	 *
	 * @return void
	 */
	public function test_style_inside_comment_keeps_following_markup(): void {
		$output = $this->render( $this->feed_with_title( 'Headline' ), '<!-- <style> --><p>{{feedzy_title}}</p>' );

		$this->assertStringContainsString( '<p>Headline</p>', $output );
		$this->assertStringNotContainsString( '&lt;!--', $output );
	}

	/**
	 * A comment opener inside a style or script body does not swallow the rest
	 * of the template.
	 *
	 * @return void
	 */
	public function test_comment_opener_in_raw_text_body_keeps_following_markup(): void {
		$output = $this->render(
			$this->feed_with_title( 'Headline' ),
			'<style>.x::before{content:"<!--"}</style><p>{{feedzy_title}}</p><script>var a = "<!--";</script><p>{{feedzy_title}}</p>'
		);

		$this->assertSame( 2, substr_count( $output, '<p>Headline</p>' ) );
		$this->assertStringNotContainsString( '<!--', $output );
		$this->assertStringNotContainsString( 'content:', $output );
	}

	/**
	 * Templates where the HTML tokenizer does not read "<" as markup, with
	 * markup around each construct that must still render.
	 *
	 * @return array<string, array{string, string}> Template and expected markup.
	 */
	public function special_markup_provider(): array {
		return array(
			'textarea with unclosed comment' => array( '<textarea>a <!-- b</textarea><p>{{feedzy_title}}</p>', '</textarea><p>Headline</p>' ),
			'title with unclosed comment'    => array( '<title>a <!-- b</title><p>{{feedzy_title}}</p>', '</title><p>Headline</p>' ),
			'xmp with unclosed comment'      => array( '<xmp>a <!-- b</xmp><p>{{feedzy_title}}</p>', '</xmp><p>Headline</p>' ),
			'iframe body with comment'       => array( '<iframe src="https://example.org/"><!-- </iframe><p>{{feedzy_title}}</p>', '</iframe><p>Headline</p>' ),
			'noscript body with comment'     => array( '<noscript><!-- </noscript><p>{{feedzy_title}}</p>', '</noscript><p>Headline</p>' ),
			'unclosed textarea'              => array( '<p>{{feedzy_title}}</p><textarea>a <!-- b', '<p>Headline</p><textarea>a &lt;!-- b</textarea>' ),
			'comment closed by --!>'         => array( '<!-- a --!><p>{{feedzy_title}}</p>', '<!-- a --><p>Headline</p>' ),
			'empty comment <!-->'            => array( '<!--><p>{{feedzy_title}}</p>', '<p>Headline</p>' ),
			'empty comment <!--->'           => array( '<!---><p>{{feedzy_title}}</p>', '<p>Headline</p>' ),
			'unclosed comment at end'        => array( '<p>{{feedzy_title}}</p><!-- tail', '<p>Headline</p><!-- tail-->' ),
			'CDATA bogus comment'            => array( '<![CDATA[<!--]]><p>{{feedzy_title}}</p>', '<p>Headline</p>' ),
			'processing instruction'         => array( '<?x <!--?><p>{{feedzy_title}}</p>', '<p>Headline</p>' ),
			'end tag attribute value'        => array( '<span>x</span title="<!--"><p>{{feedzy_title}}</p>', '</span><p>Headline</p>' ),
			'plaintext to end of template'   => array( '<p>{{feedzy_title}}</p><plaintext>a <!-- b', '<p>Headline</p><plaintext>a &lt;!-- b' ),
			'plaintext body is text'         => array( '<p>{{feedzy_title}}</p><plaintext>a <b>c</b> <!-- d -->', '<p>Headline</p><plaintext>a &lt;b&gt;c&lt;/b&gt; &lt;!-- d --&gt;' ),
			'end tag bogus comment'          => array( '</3 <textarea><p>{{feedzy_title}}</p>', '<p>Headline</p>' ),
		);
	}

	/**
	 * Raw-text, text-only and comment-like constructs end where browsers end
	 * them, and the markup around each construct must still render.
	 *
	 * @dataProvider special_markup_provider
	 *
	 * @param string $template The inner block template.
	 * @param string $expected Markup that must appear in the output.
	 * @return void
	 */
	public function test_special_markup_keeps_following_markup( string $template, string $expected ): void {
		$output = $this->render( $this->feed_with_title( 'Headline' ), $template );

		$this->assertSame( 1, substr_count( $output, '<p>Headline</p>' ) );
		$this->assertStringContainsString( $expected, $output );
		$this->assertStringNotContainsString( '&lt;p&gt;', $output );
	}

	/**
	 * A rich feed value inside a noscript body is inserted as plain text.
	 *
	 * @return void
	 */
	public function test_noscript_value_is_plain_text(): void {
		$output = $this->render(
			$this->feed_with_content( 'Hi &lt;b&gt;x&lt;/b&gt;' ),
			'<noscript><p>{{feedzy_content}}</p></noscript>'
		);

		$this->assertStringContainsString( '<noscript><p>Hi x</p></noscript>', $output );
	}

	/**
	 * A rich feed value inside a textarea is plain text, so it cannot close the
	 * textarea and add markup after it.
	 *
	 * @return void
	 */
	public function test_rich_value_cannot_close_textarea(): void {
		$output = $this->render(
			$this->feed_with_content( 'Hi &lt;textarea&gt;x&lt;/textarea&gt;&lt;img src=&quot;https://attacker.example/p.png&quot;&gt;' ),
			'<textarea>{{feedzy_content}}</textarea>'
		);

		$this->assertSame( 1, substr_count( $output, '</textarea>' ) );
		$this->assertStringNotContainsString( 'attacker.example', $output );
		$this->assertStringContainsString( '<textarea>Hi x</textarea>', $output );
	}

	/**
	 * Add a `true` element entry to the post allowlist, as some plugins do.
	 *
	 * @param array<string, mixed> $tags The allowed HTML.
	 * @param string               $context The KSES context.
	 * @return array<string, mixed>
	 */
	public function allow_mark_as_true( array $tags, string $context ): array {
		if ( 'post' === $context ) {
			$tags['mark'] = true;
		}
		return $tags;
	}

	/**
	 * Render with a `true` mark entry in the post allowlist.
	 *
	 * @param string $template The inner block template.
	 * @return string The rendered block HTML.
	 */
	private function render_with_true_mark_entry( string $template ): string {
		add_filter( 'wp_kses_allowed_html', array( $this, 'allow_mark_as_true' ), 10, 2 );

		try {
			return $this->render( $this->feed_with_title( 'Headline' ), $template );
		} finally {
			remove_filter( 'wp_kses_allowed_html', array( $this, 'allow_mark_as_true' ), 10 );
		}
	}

	/**
	 * A `true` element entry used by the template does not abort rendering.
	 *
	 * @return void
	 */
	public function test_true_allowlist_entry_does_not_abort_render(): void {
		$output = $this->render_with_true_mark_entry( '<p><mark>{{feedzy_title}}</mark></p>' );

		$this->assertStringContainsString( '<mark>Headline</mark>', $output );
	}

	/**
	 * A `true` element entry the template does not use does not abort rendering.
	 *
	 * @return void
	 */
	public function test_unused_true_allowlist_entry_does_not_abort_render(): void {
		$output = $this->render_with_true_mark_entry( '<p>{{feedzy_title}}</p>' );

		$this->assertStringContainsString( '<p>Headline</p>', $output );
	}

	/**
	 * A plain-text value in an unquoted attribute cannot add a handler.
	 *
	 * @return void
	 */
	public function test_unquoted_attribute_cannot_add_handler(): void {
		$output = $this->render( $this->feed_with_title( 'x onerror=alert(1)' ), '<img src="x" alt={{feedzy_title}}>' );

		$this->assert_not_executable( $output );
	}

	/**
	 * A value in a single-quoted attribute cannot break out of the quotes.
	 *
	 * @return void
	 */
	public function test_single_quoted_attribute_cannot_break_out(): void {
		$output = $this->render( $this->feed_with_title( "x' onerror='alert(1)" ), "<img src='x' alt='{{feedzy_title}}'>" );

		$this->assert_not_executable( $output );
	}

	/**
	 * A value in attribute-name position cannot introduce a handler.
	 *
	 * @return void
	 */
	public function test_attribute_name_position_cannot_inject(): void {
		$output = $this->render( $this->feed_with_title( 'x onclick=alert(1)' ), '<span {{feedzy_title}}>x</span>' );

		$this->assert_not_executable( $output );
	}

	/**
	 * Feed values forming both an attribute name and its value cannot build a handler.
	 *
	 * @return void
	 */
	public function test_dynamic_attribute_name_cannot_form_handler(): void {
		$output = $this->render(
			$this->feed( '<title>onerror</title><link>https://example.org/a</link><author>alert(1)</author>' ),
			'<img src="x" {{feedzy_title}}="{{feedzy_author}}">'
		);

		$this->assert_not_executable( $output );
	}

	/**
	 * A javascript: value from a text tag must not survive in an href.
	 *
	 * @return void
	 */
	public function test_javascript_url_in_href_is_neutralized(): void {
		$output = $this->render( $this->feed_with_title( 'javascript:alert(1)' ), '<a href="{{feedzy_title}}">link</a>' );

		$this->assertStringNotContainsString( 'javascript:', $output );
	}

	/**
	 * An apostrophe in an earlier unquoted value does not let a javascript: href through.
	 *
	 * @return void
	 */
	public function test_apostrophe_in_unquoted_value_keeps_href_safe(): void {
		$output = $this->render(
			$this->feed_with_title( 'javascript:alert(1)' ),
			"<span data-label=don't>x</span><a href=\"{{feedzy_title}}\">link</a>"
		);

		$this->assertStringNotContainsString( 'javascript:', $output );
	}

	/**
	 * A data: URL in an href attribute is rejected.
	 *
	 * @return void
	 */
	public function test_data_url_in_href_is_rejected(): void {
		$output = $this->render( $this->feed_with_title( 'data:text/html,hello' ), '<a href="{{feedzy_title}}">x</a>' );

		$this->assertStringNotContainsString( 'data:text/html', $output );
	}

	/**
	 * A src attribute rejects unsafe schemes regardless of the magic tag.
	 *
	 * @return void
	 */
	public function test_src_attribute_rejects_unsafe_scheme(): void {
		$output = $this->render( $this->feed_with_title( 'javascript:alert(1)' ), '<img src="{{feedzy_title}}">' );

		$this->assertStringNotContainsString( 'javascript:', $output );
	}

	/**
	 * A feed value substituted into an event-handler attribute never reaches the page.
	 *
	 * @return void
	 */
	public function test_event_handler_substitution_is_rejected(): void {
		$output = $this->render(
			$this->feed_with_title( "');alert(1);//" ),
			'<a href="https://example.org/" onclick="console.log(\'{{feedzy_title}}\')">link</a>'
		);

		$this->assertStringNotContainsString( 'alert(1)', $output );
		$this->assert_not_executable( $output );
	}

	/**
	 * Uppercase and spaced event-handler attributes are rejected too.
	 *
	 * @return void
	 */
	public function test_uppercase_and_spaced_handlers_are_rejected(): void {
		$output = $this->render(
			$this->feed_with_title( 'x);alert(1)' ),
			'<a ONCLICK="f({{feedzy_title}})">x</a><button onclick = "{{feedzy_title}}">b</button>'
		);

		$this->assertStringNotContainsString( 'alert(1)', $output );
		$this->assert_not_executable( $output );
	}

	/**
	 * A feed value in an iframe srcdoc never reaches the page; the iframe stays.
	 *
	 * @return void
	 */
	public function test_srcdoc_substitution_is_rejected(): void {
		$output = $this->render(
			$this->feed_with_title( '&lt;img src=x onerror=alert(1)&gt;' ),
			'<iframe src="https://example.org/" srcdoc="{{feedzy_title}}"></iframe>'
		);

		$this->assertSame( 1, $this->parse( $output )->getElementsByTagName( 'iframe' )->length );
		$this->assert_not_executable( $output );
	}

	/**
	 * A value inside a script body cannot run: script elements are removed.
	 *
	 * @return void
	 */
	public function test_script_body_is_not_exploitable(): void {
		$output = $this->render(
			$this->feed_with_title( '&lt;/script&gt;&lt;img src=x onerror=alert(1)&gt;' ),
			'<script>var a = "{{feedzy_title}}";</script>'
		);

		$this->assertStringNotContainsString( '<script', strtolower( $output ) );
		$this->assert_not_executable( $output );
	}

	/**
	 * A javascript: value must not survive in an SVG xlink:href.
	 *
	 * @return void
	 */
	public function test_svg_xlink_href_rejects_javascript_scheme(): void {
		$output = $this->render(
			$this->feed_with_title( 'javascript:alert(1)' ),
			'<svg><a xlink:href="{{feedzy_title}}">link</a></svg>'
		);

		$this->assertStringNotContainsString( 'javascript:', $output );
	}

	/**
	 * SVG animation elements are removed, so they cannot rewrite an href.
	 *
	 * @return void
	 */
	public function test_svg_animation_elements_are_removed(): void {
		$output = $this->render(
			$this->feed_with_title( 'javascript:alert(1)' ),
			'<svg><a><set attributeName="href" to="{{feedzy_title}}"/><animate attributeName="href" values="{{feedzy_title}}"/><text y="20">link</text></a></svg>'
		);

		$doc = $this->parse( $output );

		$this->assertSame( 0, $doc->getElementsByTagName( 'set' )->length );
		$this->assertSame( 0, $doc->getElementsByTagName( 'animate' )->length );
		$this->assertSame( 1, $doc->getElementsByTagName( 'text' )->length );
		$this->assertStringNotContainsString( 'javascript:', $output );
	}

	/**
	 * An HTML-valued tag in a quoted attribute keeps its full text and cannot
	 * add another allowed attribute.
	 *
	 * @return void
	 */
	public function test_html_value_in_attribute_keeps_full_value(): void {
		$output = $this->render(
			$this->feed_with_content( 'x&quot; src=&quot;https://attacker.example/pixel' ),
			'<img alt="{{feedzy_content}}">'
		);

		$img = $this->parse( $output )->getElementsByTagName( 'img' )->item( 0 );

		$this->assertInstanceOf( 'DOMElement', $img );
		$this->assertSame( 'x" src="https://attacker.example/pixel', $img->getAttribute( 'alt' ) );
		$this->assertSame( 1, $img->attributes->length );
	}

	/**
	 * Feed URLs in an SVG link and a CSS background keep working.
	 *
	 * @return void
	 */
	public function test_url_in_svg_link_and_style_is_preserved(): void {
		$output = $this->render(
			$this->feed( '<title>T</title><link>https://example.org/a.png</link>' ),
			'<svg><use xlink:href="{{feedzy_url}}"/></svg><div style="background-image:url({{feedzy_url}})">x</div>'
		);

		$this->assertStringContainsString( 'xlink:href="https://example.org/a.png"', $output );
		$this->assertStringContainsString( 'background-image:url(https://example.org/a.png)', $output );
	}

	/**
	 * A benign title in a quoted attribute keeps its full text and adds no attribute.
	 *
	 * @return void
	 */
	public function test_quoted_attribute_keeps_full_value(): void {
		$output = $this->render(
			$this->feed_with_title( 'She said "hi" &amp; left' ),
			'<img src="https://example.org/i.png" alt="{{feedzy_title}}"/>'
		);

		$img = $this->parse( $output )->getElementsByTagName( 'img' )->item( 0 );

		$this->assertInstanceOf( 'DOMElement', $img );
		$this->assertSame( 'She said "hi" & left', $img->getAttribute( 'alt' ) );
		$this->assertSame( 2, $img->attributes->length );
	}

	/**
	 * An HTML-valued tag in element content keeps its allowed rich markup.
	 *
	 * @return void
	 */
	public function test_html_value_keeps_rich_content(): void {
		$output = $this->render( $this->feed_with_content( 'Rich &lt;strong&gt;bold&lt;/strong&gt;' ), '<p>{{feedzy_content}}</p>' );

		$this->assertStringContainsString( '<strong>bold</strong>', $output );
	}

	/**
	 * An email-only author must not abort rendering.
	 *
	 * @return void
	 */
	public function test_email_only_author_does_not_abort_render(): void {
		$output = $this->render(
			$this->feed( '<title>T</title><link>https://example.org/a</link><author>bob@example.com</author>' ),
			'<p>{{feedzy_author}}</p>'
		);

		$this->assertStringNotContainsString( '{{feedzy_author}}', $output );
	}

	/**
	 * Legitimate feed content stays intact.
	 *
	 * @return void
	 */
	public function test_benign_content_is_preserved(): void {
		$output = $this->render(
			$this->feed( '<title>Weekly News</title><link>https://example.org/article</link><description>Body text</description>' ),
			'<p>{{feedzy_title}}</p><p><a href="{{feedzy_url}}">read</a></p><p>{{feedzy_description}}</p>'
		);

		$this->assertStringContainsString( 'Weekly News', $output );
		$this->assertStringContainsString( 'href="https://example.org/article"', $output );
		$this->assertStringContainsString( 'Body text', $output );
	}
}
