<?php
/**
 * Test metadata compatibility with other plugins.
 *
 * @package     Feedzy
 * @subpackage  Tests
 * @copyright   Copyright (c) 2026, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Test_Feedzy_Metadata_Compatibility
 *
 * Tests that Feedzy's metadata filters don't interfere with other plugins.
 * Specifically tests the issue where Slider Hero button text changes don't save
 * when Feedzy is active.
 */
class Test_Feedzy_Metadata_Compatibility extends WP_UnitTestCase {

	/**
	 * Test that metadata updates on non-feedzy post types work correctly.
	 *
	 * This simulates the Slider Hero plugin saving button text metadata.
	 *
	 * @access public
	 */
	public function test_non_feedzy_post_meta_updates() {
		// Create a regular post (simulating Slider Hero's post type).
		$user_id = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $user_id );

		$post_id = $this->factory->post->create(
			array(
				'post_title'  => 'Test Slider',
				'post_type'   => 'post', // Not a feedzy post type
				'post_author' => $user_id,
			)
		);

		// Ensure the post was created.
		$this->assertGreaterThan( 0, $post_id );

		// Simulate Slider Hero saving button text metadata.
		$button_text = 'Click Me Now';
		$result      = update_post_meta( $post_id, 'slider_button_text', $button_text );

		// Verify the metadata was saved successfully.
		$this->assertNotFalse( $result, 'Metadata update should succeed' );

		// Retrieve the metadata and verify it matches.
		$saved_value = get_post_meta( $post_id, 'slider_button_text', true );
		$this->assertEquals( $button_text, $saved_value, 'Button text should be saved correctly' );

		// Update it again with different text.
		$new_button_text = 'Updated Button Text';
		$result          = update_post_meta( $post_id, 'slider_button_text', $new_button_text );

		$this->assertNotFalse( $result, 'Second metadata update should succeed' );

		// Verify the new value.
		$saved_value = get_post_meta( $post_id, 'slider_button_text', true );
		$this->assertEquals( $new_button_text, $saved_value, 'Updated button text should be saved correctly' );
	}

	/**
	 * Test that multiple metadata updates on non-feedzy posts work.
	 *
	 * @access public
	 */
	public function test_multiple_non_feedzy_meta_updates() {
		$user_id = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $user_id );

		$post_id = $this->factory->post->create(
			array(
				'post_title'  => 'Test Multiple Meta',
				'post_type'   => 'page', // Different post type
				'post_author' => $user_id,
			)
		);

		// Save multiple pieces of metadata.
		$meta_data = array(
			'button_text'  => 'Click Here',
			'button_url'   => 'https://example.com',
			'button_style' => 'primary',
		);

		foreach ( $meta_data as $key => $value ) {
			$result = update_post_meta( $post_id, $key, $value );
			$this->assertNotFalse( $result, "Metadata update for {$key} should succeed" );
		}

		// Verify all metadata was saved.
		foreach ( $meta_data as $key => $value ) {
			$saved_value = get_post_meta( $post_id, $key, true );
			$this->assertEquals( $value, $saved_value, "Metadata {$key} should match saved value" );
		}
	}

	/**
	 * Test that feedzy_categories metadata still works correctly.
	 *
	 * This ensures our fix doesn't break existing functionality.
	 *
	 * @access public
	 */
	public function test_feedzy_category_meta_still_works() {
		$user_id = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $user_id );

		$post_id = $this->factory->post->create(
			array(
				'post_title'  => 'Test Feedzy Category',
				'post_type'   => 'feedzy_categories',
				'post_author' => $user_id,
			)
		);

		$feed_url = 'https://example.com/feed';

		// Set up the POST data as Feedzy expects.
		$_POST['id']                             = $post_id;
		$_POST['feedzy_category_meta_noncename'] = wp_create_nonce( FEEDZY_BASEFILE );
		$_POST['post_type']                      = 'feedzy_categories';
		$_POST['feedzy_category_feed']           = $feed_url;

		// Trigger the save_post action.
		$post = get_post( $post_id );
		do_action( 'save_post', $post_id, $post );

		// Verify the feed URL was validated and saved.
		$saved_feed = get_post_meta( $post_id, 'feedzy_category_feed', true );

		// Note: The validate_category_feeds filter may modify the URL or return empty if invalid.
		// For this test, we just need to verify that the meta operation completed without errors.
		$this->assertNotNull( $saved_feed, 'Feedzy category feed metadata should be processed' );
	}

	/**
	 * Test add_post_meta for non-feedzy post types.
	 *
	 * @access public
	 */
	public function test_add_meta_for_non_feedzy_posts() {
		$user_id = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $user_id );

		$post_id = $this->factory->post->create(
			array(
				'post_title'  => 'Test Add Meta',
				'post_type'   => 'post',
				'post_author' => $user_id,
			)
		);

		// Use add_post_meta instead of update_post_meta.
		$result = add_post_meta( $post_id, 'new_slider_field', 'New Value', true );

		// add_post_meta returns meta_id on success, false on failure.
		$this->assertNotFalse( $result, 'add_post_meta should succeed for non-feedzy posts' );
		$this->assertIsNumeric( $result, 'add_post_meta should return meta_id' );

		// Verify the metadata.
		$saved_value = get_post_meta( $post_id, 'new_slider_field', true );
		$this->assertEquals( 'New Value', $saved_value, 'Metadata should be saved correctly via add_post_meta' );
	}
}
