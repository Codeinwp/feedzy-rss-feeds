<?php
/**
 * Tests for sanitizing feed-derived data stored on imported featured images.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Import_Attachment_Sanitize extends WP_UnitTestCase {

	/**
	 * Importer under test.
	 *
	 * @var Feedzy_Rss_Feeds_Import
	 */
	private $import;

	/**
	 * Attachments created by a test, deleted with their files in tear_down().
	 *
	 * @var list<int>
	 */
	private $attachment_ids = array();

	/**
	 * Run as an administrator so kses is removed, as a manual "Run Now" does.
	 *
	 * @access public
	 */
	public function set_up(): void {
		parent::set_up();

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->import = new Feedzy_Rss_Feeds_Import( 'feedzy-rss-feeds', '1.2.0' );
	}

	/**
	 * Delete created attachment files and restore the anonymous user.
	 *
	 * @access public
	 */
	public function tear_down(): void {
		foreach ( $this->attachment_ids as $attachment_id ) {
			wp_delete_attachment( $attachment_id, true );
		}
		$this->attachment_ids = array();
		wp_set_current_user( 0 );

		parent::tear_down();
	}

	/**
	 * A feed item title with markup is stored as a plain attachment title.
	 */
	public function test_featured_image_title_strips_executable_markup(): void {
		$this->assertFalse( has_filter( 'title_save_pre', 'wp_filter_kses' ), 'Core kses must be off, as for an administrator running the import.' );

		$attachment = $this->save_featured_image( '<svg onload=alert(document.domain)>Breaking news' );

		$this->assertSame( 'Breaking news', $attachment->post_title );
	}

	/**
	 * Importing the same feed title again reuses the existing attachment.
	 */
	public function test_featured_image_reuses_attachment_for_same_feed_title(): void {
		$title = '<svg onload=alert(document.domain)>Breaking news';
		$first = $this->save_featured_image( $title );

		$post_id = self::factory()->post->create();
		$result  = false;

		$this->assertTrue( $this->import->try_reuse_existing_featured_image( $result, $title, $post_id ) );
		$this->assertSame( $first->ID, (int) get_post_thumbnail_id( $post_id ) );
	}

	/**
	 * Save a generated JPEG as a new post's featured image and return the attachment.
	 *
	 * @param string $title Feed item title passed to the importer.
	 *
	 * @return WP_Post
	 */
	private function save_featured_image( string $title ): WP_Post {
		$post_id = self::factory()->post->create();
		$method  = new ReflectionMethod( $this->import, 'try_save_featured_image' );
		$method->setAccessible( true );

		$import_info = array();
		$this->assertNotFalse( $method->invokeArgs( $this->import, array( 'data:image/jpeg;base64,' . base64_encode( $this->jpeg_bytes() ), $post_id, $title, &$import_info ) ) );

		$attachment_id          = (int) get_post_thumbnail_id( $post_id );
		$this->attachment_ids[] = $attachment_id;

		$attachment = get_post( $attachment_id );
		$this->assertInstanceOf( WP_Post::class, $attachment );

		return $attachment;
	}

	/**
	 * Build a small JPEG.
	 *
	 * @return string JPEG bytes.
	 */
	private function jpeg_bytes(): string {
		ob_start();
		imagejpeg( imagecreatetruecolor( 8, 8 ) );

		return (string) ob_get_clean();
	}
}
