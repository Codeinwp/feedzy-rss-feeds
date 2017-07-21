<?php

/**
 * WordPress unit test plugin.
 *
 * @package     Feedzy
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.10
 */
class Test_Feedzy extends WP_UnitTestCase {
	/**
	 * Check if we have SDK loaded.
	 */
	public function test_sdk_exists() {
		$this->assertTrue( class_exists( 'ThemeIsle_SDK_Loader' ) );
	}

	/**
	 * Test method to check Create | Update and Feed from Slug
	 *
	 * @since   3.0.12
	 * @access  public
	 */
	public function test_feedzy_category() {

		$random_name = $this->get_rand_name();
		$user_id     = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $user_id );
		$p = $this->factory->post->create_and_get(
			array(
				'post_title'  => $random_name,
				'post_type'   => 'feedzy_categories',
				'post_author' => $user_id,
			)
		);

		$urls = $this->get_two_rand_feeds();

		$_POST[ 'feedzy_categories' . '_noncename' ] = wp_create_nonce( FEEDZY_BASEFILE );
		$_POST['post_type']                          = 'feedzy_categories';
		$_POST['feedzy_category_feed']               = $urls;

		$this->assertClassHasStaticAttribute( 'instance', 'Feedzy_Rss_Feeds' );

		// Test Create Feedzy Category
		do_action( 'save_post', $p->ID, $p );
		$post_meta_urls = get_post_meta( $p->ID, 'feedzy_category_feed', true );
		$this->assertEquals( $p->post_title, $random_name );
		$this->assertEquals( $p->post_type, 'feedzy_categories' );
		$this->assertEquals( $urls, $post_meta_urls );

		// Test Update Feedzy Category
		$urls_changed                  = $this->get_two_rand_feeds();
		$_POST['feedzy_category_feed'] = $urls_changed;
		do_action( 'save_post', $p->ID, $p );
		$post_meta_urls = get_post_meta( $p->ID, 'feedzy_category_feed', true );
		$this->assertEquals( $urls_changed, $post_meta_urls );

		// Test Feed By Category slug
		$post           = get_post( $p->ID );
		$slug           = $post->post_name;
		$feed_from_slug = apply_filters( 'feedzy_process_feed_source', $slug );
		$this->assertEquals( str_replace( PHP_EOL, '', $urls_changed ), $feed_from_slug );

	}

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

	/**
	 * Utility method to generate a random feed urls.
	 *
	 * @since   3.0.12
	 * @access  private
	 * @return string
	 */
	private function get_two_rand_feeds() {
		$sections  = array( 'economics', 'china', 'europe', 'united-states', 'international', 'science-technology' );
		$feed_urls = '';
		$section   = $sections[ mt_rand( 0, 5 ) ];
		$feed_urls .= 'http://www.economist.com/sections/' . $section . '/rss.xml, ' . PHP_EOL;
		$section   = $sections[ mt_rand( 0, 5 ) ];
		$feed_urls .= 'http://www.economist.com/sections/' . $section . '/rss.xml' . PHP_EOL;

		return $feed_urls;
	}
}
