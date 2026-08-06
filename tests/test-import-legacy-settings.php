<?php
/**
 * Tests for the legacy Auto-Delete / Delete image import settings.
 *
 * @package     feedzy-rss-feeds
 * @subpackage  Tests
 */
class Test_Feedzy_Import_Legacy_Settings extends WP_UnitTestCase {

	/**
	 * The import instance under test.
	 *
	 * @var Feedzy_Rss_Feeds_Import
	 */
	private $import;

	/**
	 * Sets up the test methods.
	 */
	public function setUp(): void {
		parent::setUp();

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );

		$this->import = new Feedzy_Rss_Feeds_Import( 'feedzy-rss-feeds', Feedzy_Rss_Feeds::get_version() );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
	}

	/**
	 * Renders the import metabox for a given import job.
	 *
	 * @param int $import_id The import job ID.
	 *
	 * @return string The rendered markup.
	 */
	private function render_metabox( $import_id ) {
		global $post, $pagenow;

		$previous_post    = $post;
		$previous_pagenow = $pagenow;

		$post    = get_post( $import_id );
		$pagenow = 'post.php';

		ob_start();
		$this->import->feedzy_import_feed_options();
		$markup = ob_get_clean();

		$post    = $previous_post;
		$pagenow = $previous_pagenow;

		return $markup;
	}

	/**
	 * Creates an import job.
	 *
	 * @return int The import job ID.
	 */
	private function create_import() {
		return $this->factory->post->create(
			array(
				'post_type'   => 'feedzy_imports',
				'post_status' => 'publish',
			)
		);
	}

	/**
	 * An import job without the legacy meta does not render either setting.
	 */
	public function test_legacy_settings_are_hidden_for_new_imports() {
		$markup = $this->render_metabox( $this->create_import() );

		$this->assertStringNotContainsString( 'feedzy_meta_data[import_feed_delete_days]', $markup );
		$this->assertStringNotContainsString( 'feedzy_meta_data[import_feed_delete_media]', $markup );
	}

	/**
	 * An import job that already stores `import_feed_delete_days` still renders the Auto-Delete setting.
	 */
	public function test_delete_days_is_shown_when_previously_saved() {
		$import_id = $this->create_import();
		update_post_meta( $import_id, 'import_feed_delete_days', '15' );

		$markup = $this->render_metabox( $import_id );

		$this->assertStringContainsString( 'feedzy_meta_data[import_feed_delete_days]', $markup );
		$this->assertStringContainsString( 'value="15"', $markup );
		$this->assertStringNotContainsString( 'feedzy_meta_data[import_feed_delete_media]', $markup );
	}

	/**
	 * An import job that already stores `import_feed_delete_media` still renders the Delete image setting.
	 */
	public function test_delete_media_is_shown_when_previously_saved() {
		$import_id = $this->create_import();
		update_post_meta( $import_id, 'import_feed_delete_media', 'yes' );

		$markup = $this->render_metabox( $import_id );

		$this->assertStringContainsString( 'feedzy_meta_data[import_feed_delete_media]', $markup );
		$this->assertStringNotContainsString( 'feedzy_meta_data[import_feed_delete_days]', $markup );
	}

	/**
	 * The disabled state is stored as `no`, which still counts as previously configured.
	 */
	public function test_delete_media_is_shown_when_saved_as_no() {
		$import_id = $this->create_import();
		update_post_meta( $import_id, 'import_feed_delete_media', 'no' );

		$markup = $this->render_metabox( $import_id );

		$this->assertStringContainsString( 'feedzy_meta_data[import_feed_delete_media]', $markup );
	}

	/**
	 * Saving an import that never had the legacy media setting must not create it.
	 */
	public function test_saving_does_not_create_delete_media_meta_for_new_imports() {
		$import_id = $this->create_import();
		$post      = get_post( $import_id );

		$_POST['feedzy_category_meta_noncename'] = wp_create_nonce( FEEDZY_BASEFILE );
		$_POST['feedzy_post_nonce']              = wp_create_nonce( 'feedzy_post_nonce' );
		$_POST['post_type']                      = 'feedzy_imports';
		$_POST['feedzy_meta_data']               = array(
			'source'             => 'http://example.com/feed',
			'import_post_type'   => 'post',
			'import_post_status' => 'publish',
			'import_post_title'  => '[#item_title]',
		);
		$_POST['custom_vars_key']                = array();
		$_POST['custom_vars_value']              = array();

		do_action( 'save_post_feedzy_imports', $import_id, $post );

		$this->assertFalse( metadata_exists( 'post', $import_id, 'import_feed_delete_media' ) );
	}

	/**
	 * Unchecking the toggle on an import that already stores the setting keeps persisting `no`.
	 */
	public function test_saving_keeps_delete_media_meta_for_existing_imports() {
		$import_id = $this->create_import();
		update_post_meta( $import_id, 'import_feed_delete_media', 'yes' );
		$post = get_post( $import_id );

		$_POST['feedzy_category_meta_noncename'] = wp_create_nonce( FEEDZY_BASEFILE );
		$_POST['feedzy_post_nonce']              = wp_create_nonce( 'feedzy_post_nonce' );
		$_POST['post_type']                      = 'feedzy_imports';
		$_POST['feedzy_meta_data']               = array(
			'source'             => 'http://example.com/feed',
			'import_post_type'   => 'post',
			'import_post_status' => 'publish',
			'import_post_title'  => '[#item_title]',
		);
		$_POST['custom_vars_key']                = array();
		$_POST['custom_vars_value']              = array();

		do_action( 'save_post_feedzy_imports', $import_id, $post );

		$this->assertSame( 'no', get_post_meta( $import_id, 'import_feed_delete_media', true ) );
	}
}
