<?php
/**
 * Tests for helper methods of the Feedzy_Rss_Feeds_Import class.
 *
 * @since      5.1.0
 *
 * @package    feedzy-rss-feeds
 */
class Test_Import_Helpers extends WP_UnitTestCase {

	/**
	 * Instance of the import class.
	 *
	 * @var Feedzy_Rss_Feeds_Import
	 */
	private $import;

	/**
	 * Set up test environment.
	 *
	 * @access public
	 */
	public function setUp(): void {
		parent::setUp();
		$this->import = new Feedzy_Rss_Feeds_Import( 'feedzy-rss-feeds', Feedzy_Rss_Feeds::get_version() );
	}

	/**
	 * Test is_duplicate_post returns false when key or value is missing.
	 *
	 * @access public
	 */
	public function test_is_duplicate_post_empty_key_or_value_returns_false() {
		$this->assertFalse( $this->import->is_duplicate_post( 'post', '', 'some-value' ) );
		$this->assertFalse( $this->import->is_duplicate_post( 'post', 'some_key', '' ) );
		$this->assertFalse( $this->import->is_duplicate_post() );
	}

	/**
	 * Test is_duplicate_post finds an existing post by meta key/value.
	 *
	 * @access public
	 */
	public function test_is_duplicate_post_finds_existing_post() {
		$post_id = $this->factory->post->create(
			array(
				'post_title'  => 'Imported item',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $post_id, 'feedzy_item_url', 'https://example.com/imported-item' );

		$result = $this->import->is_duplicate_post( 'post', 'feedzy_item_url', 'https://example.com/imported-item' );

		$this->assertIsArray( $result );
		$this->assertContains( $post_id, $result );
	}

	/**
	 * Test is_duplicate_post returns an empty array when no post matches.
	 *
	 * @access public
	 */
	public function test_is_duplicate_post_no_match_returns_empty_array() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, 'feedzy_item_url', 'https://example.com/a' );

		$result = $this->import->is_duplicate_post( 'post', 'feedzy_item_url', 'https://example.com/b' );

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test is_duplicate_post respects the post type argument.
	 *
	 * @access public
	 */
	public function test_is_duplicate_post_respects_post_type() {
		$page_id = $this->factory->post->create(
			array(
				'post_type' => 'page',
			)
		);
		update_post_meta( $page_id, 'feedzy_item_url', 'https://example.com/page-item' );

		// Searching in "post" does not find the page.
		$result = $this->import->is_duplicate_post( 'post', 'feedzy_item_url', 'https://example.com/page-item' );
		$this->assertEmpty( $result );

		// Searching in "page" finds it.
		$result = $this->import->is_duplicate_post( 'page', 'feedzy_item_url', 'https://example.com/page-item' );
		$this->assertContains( $page_id, $result );
	}

	/**
	 * Test is_duplicate_post supports a LIKE meta compare.
	 *
	 * @access public
	 */
	public function test_is_duplicate_post_like_compare() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, 'mark_duplicate', 'John Doe - Example Feed' );

		$result = $this->import->is_duplicate_post( 'post', 'mark_duplicate', 'John Doe', 'LIKE' );
		$this->assertContains( $post_id, $result );

		// Exact compare does not match the partial value.
		$result = $this->import->is_duplicate_post( 'post', 'mark_duplicate', 'John Doe', '=' );
		$this->assertEmpty( $result );
	}

	/**
	 * Test feedzy_import_trim_tags converts a comma separated list into space separated words.
	 *
	 * @access public
	 */
	public function test_feedzy_import_trim_tags_basic() {
		$this->assertEquals( 'one two three', $this->import->feedzy_import_trim_tags( 'one, two,three' ) );
		$this->assertEquals( 'single', $this->import->feedzy_import_trim_tags( 'single' ) );
		$this->assertEquals( 'a b', $this->import->feedzy_import_trim_tags( '  a  ,  b  ' ) );
	}

	/**
	 * Test feedzy_import_trim_tags leaves empty and non string values untouched.
	 *
	 * @access public
	 */
	public function test_feedzy_import_trim_tags_edge_cases() {
		$this->assertEquals( '', $this->import->feedzy_import_trim_tags( '' ) );
		$this->assertEquals( '', $this->import->feedzy_import_trim_tags() );

		$array_value = array( 'not', 'a', 'string' );
		$this->assertEquals( $array_value, $this->import->feedzy_import_trim_tags( $array_value ) );
	}

	/**
	 * Test feedzy_wp_kses_allowed_html adds the iframe tag and disables span.
	 *
	 * @access public
	 */
	public function test_feedzy_wp_kses_allowed_html() {
		$context = array(
			'span' => array( 'class' => true ),
		);

		$result = $this->import->feedzy_wp_kses_allowed_html( $context );

		$this->assertArrayHasKey( 'iframe', $result );
		$this->assertTrue( $result['iframe']['src'] );
		$this->assertTrue( $result['iframe']['allowfullscreen'] );
		$this->assertTrue( $result['span']['disabled'] );

		// An existing iframe definition is not overwritten.
		$context = array(
			'iframe' => array( 'src' => false ),
		);
		$result  = $this->import->feedzy_wp_kses_allowed_html( $context );
		$this->assertFalse( $result['iframe']['src'] );
	}

	/**
	 * Test magic_tags_post_excerpt exposes the free tags and marks pro tags as disabled.
	 *
	 * @access public
	 */
	public function test_magic_tags_post_excerpt() {
		$tags = $this->import->magic_tags_post_excerpt( array() );

		$this->assertArrayHasKey( 'item_title', $tags );
		$this->assertArrayHasKey( 'item_content', $tags );
		$this->assertArrayHasKey( 'item_description', $tags );

		// The free version marks translated tags as disabled.
		$this->assertFalse( feedzy_is_pro(), 'These tests run against the free plugin' );
		$this->assertArrayHasKey( 'translated_title:disabled', $tags );
		$this->assertArrayHasKey( 'translated_content:disabled', $tags );
		$this->assertArrayHasKey( 'translated_description:disabled', $tags );
	}
}
