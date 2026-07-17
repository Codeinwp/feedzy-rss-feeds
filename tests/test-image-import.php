<?php
/**
 * WordPress unit test plugin.
 *
 * @package     feedzy-rss-feeds
 * @subpackage  Tests
 * @copyright   Copyright (c) 2024, Bogdan Preda
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.4.6
 */
class Test_Image_Import extends WP_UnitTestCase {

	/**
	 * End-to-end import of a feed whose items have no images: the bundled SVG default
	 * must not be sideloaded, and the configured global fallback thumbnail must be used.
	 * See https://github.com/Codeinwp/feedzy-rss-feeds/issues/1277.
	 */
	public function test_import_without_item_images_uses_fallback_thumbnail() {
		// Serve a two-item, image-less feed for any HTTP request, recording requested URLs.
		$requested_urls = array();
		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) use ( &$requested_urls ) {
				$requested_urls[] = $url;
				return array(
					'headers'  => array( 'content-type' => 'application/rss+xml' ),
					'body'     => '<?xml version="1.0" encoding="UTF-8"?>
						<rss version="2.0"><channel><title>No Image Feed</title><link>http://feedzy.test</link><description>t</description>
							<item><title>Imageless one</title><link>http://feedzy.test/1</link><description>Plain text.</description><guid>fz-1</guid></item>
							<item><title>Imageless two</title><link>http://feedzy.test/2</link><description>Also plain.</description><guid>fz-2</guid></item>
						</channel></rss>',
					'response' => array( 'code' => 200, 'message' => 'OK' ),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			3
		);

		// Configure a global fallback thumbnail.
		$fallback_id = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/2004-07-22-DSC_0007.jpg' );
		$settings    = get_option( 'feedzy-settings', array() );
		$settings['general']['default-thumbnail-id'] = $fallback_id;
		update_option( 'feedzy-settings', $settings );

		// Create the import job.
		$job_id = wp_insert_post(
			array(
				'post_title'  => 'No image import',
				'post_type'   => 'feedzy_imports',
				'post_status' => 'publish',
			)
		);
		foreach ( array(
			'source'                    => 'http://feedzy.test/feed.xml',
			'import_post_title'         => '[#item_title]',
			'import_post_date'          => '[#item_date]',
			'import_post_content'       => '[#item_content]',
			'import_post_featured_img'  => '[#item_image]',
			'import_post_type'          => 'post',
			'import_post_status'        => 'publish',
			'import_use_external_image' => 'no',
		) as $key => $value ) {
			update_post_meta( $job_id, $key, $value );
		}

		$attachments_before = count( get_posts( array( 'post_type' => 'attachment', 'numberposts' => -1 ) ) );

		// Fresh instance so free_settings picks up the option; run outside wp-cron/AJAX,
		// like WP-CLI based cron runners (the path that leaked the SVG).
		$import = new Feedzy_Rss_Feeds_Import( 'feedzy-rss-feeds', '1.2.0' );
		$import->run_cron( 100, $job_id );

		$created = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1 ) );
		$this->assertCount( 2, $created, 'Both feed items should be imported' );

		foreach ( $created as $post ) {
			$this->assertEquals( $fallback_id, get_post_thumbnail_id( $post->ID ), 'Imported post must use the fallback thumbnail' );
		}

		// No sideload happened: the bundled SVG (or anything else) was not uploaded.
		$attachments_after = count( get_posts( array( 'post_type' => 'attachment', 'numberposts' => -1 ) ) );
		$this->assertEquals( $attachments_before, $attachments_after, 'Import must not upload any image to the Media Library' );

		$this->assertEmpty( get_post_meta( $job_id, 'import_errors', true ), 'Import must finish without errors' );

		// No fallback image (bundled SVG or configured attachment) may be fetched over
		// HTTP during the import: the only allowed request is the feed itself.
		$non_feed_requests = array_filter(
			$requested_urls,
			function ( $url ) {
				return 0 !== strpos( $url, 'http://feedzy.test/' );
			}
		);
		$this->assertEmpty( $non_feed_requests, 'Import must not download any image for imageless items: ' . implode( ', ', $non_feed_requests ) );
	}

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
		$import_info = array();
		$arguments = array( 'a random string', 0, 'Post Title', &$import_info, array() );
		$response = $try_save_featured_image->invokeArgs( $feedzy, $arguments );

		$this->assertFalse( $response );

		// Check that error was logged for invalid URL
		$logger = Feedzy_Rss_Feeds_Log::get_instance();
		$recent_logs = $logger->get_recent_logs( 5, 'error' );
		$this->assertNotEmpty( $recent_logs );
		
		// Find the error log for invalid image URL
		$found_error = false;
		foreach ( $recent_logs as $log ) {
			if ( strpos( $log['message'], 'Invalid image URL' ) !== false && strpos( $log['message'], 'a random string' ) !== false ) {
				$found_error = true;
				break;
			}
		}
		$this->assertTrue( $found_error, 'Expected error log for invalid image URL not found' );

		// For the next test, we will use a valid URL, but the image does not exist. We will check that the error is logged and is the expected one.
		add_filter( 'themeisle_log_event', function( $product, $message, $type, $file, $line ) {
			if ( $type === 'error' ) {
				$this->assertTrue( strpos( $message, 'Unable to download file' ) !== false );
			}
		}, 10, 5 );

		$import_info = array();
		$arguments = array( 'https://example.com/path_to_image/image.jpeg', 0, 'Post Title', &$import_info, array() );
		$response = $try_save_featured_image->invokeArgs( $feedzy, $arguments );

		// expected response is false because the image does not exist, but the URL is valid so no errors should be logged for URL validation
		$this->assertFalse( $response );

		$import_info = array();
		$arguments = array( 'https://example.com/path_to_image/image w space in name.jpeg', 0, 'Post Title', &$import_info, array() );
		$response = $try_save_featured_image->invokeArgs( $feedzy, $arguments );

		// expected response is false because the image does not exist, but the URL is valid so no errors should be logged for URL validation
		$this->assertFalse( $response );
	}

	public function test_import_image_special_characters() {
		$feedzy = new Feedzy_Rss_Feeds_Import( 'feedzy-rss-feeds', '1.2.0' );

		$reflector = new ReflectionClass( $feedzy );
		$try_save_featured_image = $reflector->getMethod( 'try_save_featured_image' );
		$try_save_featured_image->setAccessible( true );

		$import_info = array();

		$arguments = array( 'https://example.com/path_to_image/çöp.jpg?itok=ZYU_ihPB', 0, 'Post Title', &$import_info, array() );
		$response = $try_save_featured_image->invokeArgs( $feedzy, $arguments );

		// expected response is false because the image does not exist, but the URL is valid so no $import_errors should be set.
		$this->assertFalse( $response );
		$this->assertTrue( empty( $import_errors ) );

	}
}
