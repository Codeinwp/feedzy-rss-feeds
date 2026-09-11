<?php
/**
 * Tests for Feedzy_Rss_Feeds_Gutenberg_Block::feedzy_sanitize_feeds().
 *
 * @since      5.2.9
 *
 * @package    feedzy-rss-feeds
 */
class Test_Gutenberg_Block_Sanitize extends WP_UnitTestCase {

	/**
	 * Instance of the class being tested.
	 *
	 * @var Feedzy_Rss_Feeds_Gutenberg_Block
	 */
	private $block;

	/**
	 * Set up test environment.
	 *
	 * @access public
	 */
	public function setUp(): void {
		parent::setUp();

		$this->block = Feedzy_Rss_Feeds_Gutenberg_Block::get_instance();
	}

	/**
	 * A scalar string url should be validated/escaped directly instead of
	 * triggering a TypeError from count().
	 *
	 * @access public
	 */
	public function test_sanitize_feeds_with_string_input() {
		$result = $this->block->feedzy_sanitize_feeds( 'https://example.org/feed' );

		$this->assertEquals( 'https://example.org/feed', $result );
	}

	/**
	 * An invalid scalar string url should not validate and should return an empty string.
	 *
	 * @access public
	 */
	public function test_sanitize_feeds_with_invalid_string_input() {
		$result = $this->block->feedzy_sanitize_feeds( 'not-a-url' );

		$this->assertEquals( '', $result );
	}

	/**
	 * A single-item array should still return a single validated url string.
	 *
	 * @access public
	 */
	public function test_sanitize_feeds_with_single_item_array() {
		$result = $this->block->feedzy_sanitize_feeds( array( 'https://example.org/feed' ) );

		$this->assertEquals( 'https://example.org/feed', $result );
	}

	/**
	 * A multi-item array should return an array of validated urls.
	 *
	 * @access public
	 */
	public function test_sanitize_feeds_with_multiple_item_array() {
		$result = $this->block->feedzy_sanitize_feeds(
			array( 'https://example.org/feed1', 'https://example.org/feed2', 'not-a-url' )
		);

		$this->assertEquals( array( 'https://example.org/feed1', 'https://example.org/feed2' ), $result );
	}
}
