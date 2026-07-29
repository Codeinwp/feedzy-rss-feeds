<?php
/**
 * Tests for the pure helper functions in includes/feedzy-rss-feeds-feed-tweaks.php.
 *
 * @since      5.1.0
 *
 * @package    feedzy-rss-feeds
 */
class Test_Feed_Tweaks extends WP_UnitTestCase {

	/**
	 * Test feedzy_filter_custom_pattern with a single keyword.
	 *
	 * @access public
	 */
	public function test_filter_custom_pattern_single_keyword() {
		$this->assertEquals( '(?=.*news)', feedzy_filter_custom_pattern( 'news' ) );
	}

	/**
	 * Test feedzy_filter_custom_pattern with comma separated keywords (OR logic).
	 *
	 * @access public
	 */
	public function test_filter_custom_pattern_comma_is_or() {
		$this->assertEquals( '(?=.*alpha)|(?=.*beta)', feedzy_filter_custom_pattern( 'alpha,beta' ) );
		// Keywords are trimmed.
		$this->assertEquals( '(?=.*alpha)|(?=.*beta)', feedzy_filter_custom_pattern( ' alpha , beta ' ) );
	}

	/**
	 * Test feedzy_filter_custom_pattern with plus separated keywords (AND logic).
	 *
	 * @access public
	 */
	public function test_filter_custom_pattern_plus_is_and() {
		$this->assertEquals( '(?=.*alpha)(?=.*beta)', feedzy_filter_custom_pattern( 'alpha+beta' ) );
		// Mixed AND/OR.
		$this->assertEquals( '(?=.*a)(?=.*b)|(?=.*c)', feedzy_filter_custom_pattern( 'a+b,c' ) );
	}

	/**
	 * Test feedzy_filter_custom_pattern with empty input.
	 *
	 * @access public
	 */
	public function test_filter_custom_pattern_empty_input() {
		$this->assertEquals( '', feedzy_filter_custom_pattern( '' ) );
		$this->assertEquals( '', feedzy_filter_custom_pattern() );
	}

	/**
	 * Test the generated pattern actually works as a regular expression.
	 *
	 * @access public
	 */
	public function test_filter_custom_pattern_matches_strings() {
		$pattern = feedzy_filter_custom_pattern( 'space+rocks,banana' );

		$this->assertSame( 1, preg_match( '/' . $pattern . '/i', 'Space Rocks Discovered' ) );
		$this->assertSame( 1, preg_match( '/' . $pattern . '/i', 'I like banana bread' ) );
		$this->assertSame( 0, preg_match( '/' . $pattern . '/i', 'Space only, no minerals' ) );
	}

	/**
	 * Test feedzy_minimize_css collapses whitespace and strips separators.
	 *
	 * @access public
	 */
	public function test_minimize_css_whitespace_and_separators() {
		$css = ".a {\n\tcolor: red;\n\tmargin: 0px;\n}";

		$minified = feedzy_minimize_css( $css );

		$this->assertStringNotContainsString( "\n", $minified );
		$this->assertStringNotContainsString( "\t", $minified );
		// Trailing semicolon before } is removed, 0px shortened to 0.
		$this->assertEquals( '.a{color:red;margin:0}', $minified );
	}

	/**
	 * Test feedzy_minimize_css removes normal comments but keeps preserved ones.
	 *
	 * @access public
	 */
	public function test_minimize_css_comments() {
		$css = '/* remove me */ .a { color: blue; } /*! keep me */';

		$minified = feedzy_minimize_css( $css );

		$this->assertStringNotContainsString( 'remove me', $minified );
		$this->assertStringContainsString( 'keep me', $minified );
	}

	/**
	 * Test feedzy_minimize_css shortens hex colors and leading zeros.
	 *
	 * @access public
	 */
	public function test_minimize_css_hex_and_zero_values() {
		$this->assertEquals( '.a{color:#fff}', feedzy_minimize_css( '.a { color: #ffffff; }' ) );
		$this->assertEquals( '.a{margin:.5px}', feedzy_minimize_css( '.a { margin: 0.5px; }' ) );
		$this->assertEquals( '.a{margin:0}', feedzy_minimize_css( '.a { margin: 0 0 0 0; }' ) );
	}

	/**
	 * Test feedzy_minimize_css with empty input.
	 *
	 * @access public
	 */
	public function test_minimize_css_empty_input() {
		$this->assertEquals( '', feedzy_minimize_css( '' ) );
	}

	/**
	 * Test feedzy_default_css returns minified css scoped to the default class.
	 *
	 * @access public
	 */
	public function test_default_css_default_class() {
		$css = feedzy_default_css();

		$this->assertStringContainsString( '.feedzy-rss', $css );
		$this->assertStringContainsString( 'list-style:none', $css );
	}

	/**
	 * Test feedzy_default_css replaces the class with a custom suffix.
	 *
	 * @access public
	 */
	public function test_default_css_custom_suffix() {
		$css = feedzy_default_css( 'my-custom-scope' );

		$this->assertStringContainsString( '.my-custom-scope', $css );
		$this->assertStringNotContainsString( 'feedzy-rss', $css );
	}

	/**
	 * Test feedzy_custom_tag_escape sanitizes disallowed markup.
	 *
	 * @access public
	 */
	public function test_custom_tag_escape_sanitizes_html() {
		// Script tags are stripped by wp_kses.
		$result = feedzy_custom_tag_escape( '<p class="x">Hello</p><script>alert(1)</script>' );
		$this->assertStringContainsString( '<p class="x">Hello</p>', $result );
		$this->assertStringNotContainsString( '<script>', $result );

		// Allowed markup is preserved.
		$result = feedzy_custom_tag_escape( '<a href="https://example.com">link</a>' );
		$this->assertEquals( '<a href="https://example.com">link</a>', $result );

		// Empty content stays empty.
		$this->assertEquals( '', feedzy_custom_tag_escape( '' ) );
	}

	/**
	 * Test feedzy_is_pro returns false in the free plugin.
	 *
	 * @access public
	 */
	public function test_feedzy_is_pro_is_false_in_free_plugin() {
		$this->assertFalse( feedzy_is_pro() );
		// Even when skipping the license check, FEEDZY_PRO_ABSPATH is not defined here.
		$this->assertFalse( feedzy_is_pro( false ) );
	}

	/**
	 * Test the widget refresh option helpers expose the documented cache keys.
	 *
	 * @access public
	 */
	public function test_widget_refresh_options() {
		$expected_keys = array( '1_hours', '3_hours', '12_hours', '1_days', '3_days', '15_days' );

		$elementor = feedzy_elementor_widget_refresh_options();
		$classic   = feedzy_classic_widget_refresh_options();

		$this->assertEquals( $expected_keys, array_keys( $elementor ) );
		$this->assertEquals( $expected_keys, array_keys( $classic ) );

		// Classic options carry label/value pairs.
		$this->assertEquals( '12_hours', $classic['12_hours']['value'] );
		$this->assertArrayHasKey( 'label', $classic['12_hours'] );
	}

	/**
	 * Test escape_html_to_tag leaves plain text untouched.
	 *
	 * @access public
	 */
	public function test_escape_html_to_tag_plain_text() {
		$this->assertEquals( 'Hello world', escape_html_to_tag( 'Hello world' ) );
	}

	/**
	 * Test escape_html_to_tag converts html tags into tagify custom_html values.
	 *
	 * @access public
	 */
	public function test_escape_html_to_tag_converts_html() {
		$result = escape_html_to_tag( 'Intro <strong>Hi</strong>' );

		// The raw html is removed from the base content.
		$this->assertStringNotContainsString( '<strong>Hi</strong>', $result );
		$this->assertStringContainsString( 'Intro', $result );
		// ... and converted into an encoded custom_html tag payload.
		$this->assertStringContainsString( 'custom_html', $result );
	}

	/**
	 * Test escape_html_to_tag preserves already converted tagify blocks.
	 *
	 * @access public
	 */
	public function test_escape_html_to_tag_preserves_tag_blocks() {
		$block   = '[[{"value":"abc"}]]';
		$content = 'Before ' . $block . ' after';

		$this->assertEquals( $content, escape_html_to_tag( $content ) );
	}
}
