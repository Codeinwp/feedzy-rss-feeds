<?php
/**
 * WordPress unit test plugin.
 *
 * @package     feedzy-rss-feeds-pro
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Bogdan Preda
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */
class Test_Post_Access extends WP_UnitTestCase {

	/**
	 * Utility method to generate a random 5 char string.
	 *
	 * @since   3.0.12
	 * @access  private
	 * @return string
	 */
	private function get_rand_name() {
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$result     = '';
		for ( $i = 0; $i < 5; $i ++ ) {
			$result .= $characters[ mt_rand( 0, 61 ) ];
		}

		return $result;
	}

	public function test_custom_post_access() {
		$random_name = $this->get_rand_name();
		$admin_id     = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $admin_id );
		$p = $this->factory->post->create_and_get(
			array(
				'post_title'  => $random_name,
				'post_type'   => 'feedzy_categories',
				'post_author' => $admin_id,
			)
		);
		do_action( 'save_post', $p->ID, $p );
		$this->assertEquals( $p->post_title, $random_name );
		$this->assertEquals( $p->post_type, 'feedzy_categories' );

		$this->assertTrue( feedzy_current_user_can() );
		$this->assertTrue( current_user_can( 'edit_post', $p->ID ) );


		$contributor_id = $this->factory->user->create(
			array(
				'role' => 'contributor',
			)
		);
		wp_set_current_user( $contributor_id );

		$this->assertFalse( feedzy_current_user_can() );
		$this->assertFalse( current_user_can( 'edit_post', $p->ID ) );

	}

	public function test_contributor_user_with_errors() {
		$feedzy = new Feedzy_Rss_Feeds_Admin('feedzy', 'latest');
		$contributor_id = $this->factory->user->create(
			array(
				'role' => 'contributor',
			)
		);
		wp_set_current_user( $contributor_id );
		$post_id = $this->factory->post->create( array( 'post_author' => get_current_user_id() ) );
		$GLOBALS['post'] = get_post( $post_id );
		// Mock feed object and errors.
		$feed = (object) array( 'multifeed_url' => array( 'http://example.com/feed' ) );
		$errors = array( 'Error 1', 'Error 2' );


		$actual_output = $feedzy->feedzy_default_error_notice( $errors, $feed, 'http://example.com/feed' );

		$this->assertEquals( '', $actual_output );
	}
}
