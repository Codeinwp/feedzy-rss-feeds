<?php
/**
 * WordPress unit test plugin.
 *
 * @package     feedzy-rss-feeds-pro
 * @subpackage  Tests
 * @copyright   Copyright (c) 2024, Bogdan Preda
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.4.6
 */
class Test_Image_Import extends WP_UnitTestCase {

	/**
	 * Test that the image import allows valid image URLs and logs errors for invalid ones.
	 * Test introduced to cover this issue https://github.com/Codeinwp/feedzy-rss-feeds/issues/917.
	 * @since 4.4.6
	 */
	public function test_image_import_url() {
		$feedzy = new Feedzy_Rss_Feeds_Import( 'feedzy-rss-feeds', '1.2.0' );

		$reflector = new ReflectionClass( $feedzy );
		$try_save_featured_image = $reflector->getMethod( 'try_save_featured_image' );
		$try_save_featured_image->setAccessible( true );

		// Check that NON-IMAGE URL returns invalid
		$import_errors = array();
		$import_info = array();
		$arguments = array( 'a random string', 0, 'Post Title', &$import_errors, &$import_info, array() );
		$response = $try_save_featured_image->invokeArgs( $feedzy, $arguments );

		$this->assertFalse( $response );

		$this->assertTrue( count( $import_errors ) > 0 );
		$this->assertEquals( 'Invalid Featured Image URL: a random string', $import_errors[0] );


		// For the next test, we will use a valid URL, but the image does not exist. We will check that the error is logged and is the expected one.
		add_filter( 'themeisle_log_event', function( $product, $message, $type, $file, $line ) {
			if ( $type === 'error' ) {
				$this->assertTrue( strpos( $message, 'Unable to download file' ) !== false );
			}
		}, 10, 5 );

		$import_errors = array();
		$import_info = array();
		$arguments = array( 'https://example.com/path_to_image/image.jpeg', 0, 'Post Title', &$import_errors, &$import_info, array() );
		$response = $try_save_featured_image->invokeArgs( $feedzy, $arguments );

		// expected response is false because the image does not exist, but the URL is valid so no $import_errors should be set.
		$this->assertFalse( $response );
		$this->assertTrue( empty( $import_errors ) );

		$import_errors = array();
		$import_info = array();
		$arguments = array( 'https://example.com/path_to_image/image w space in name.jpeg', 0, 'Post Title', &$import_errors, &$import_info, array() );
		$response = $try_save_featured_image->invokeArgs( $feedzy, $arguments );

		// expected response is false because the image does not exist, but the URL is valid so no $import_errors should be set.
		$this->assertFalse( $response );
		$this->assertTrue( empty( $import_errors ) );
	}
}
