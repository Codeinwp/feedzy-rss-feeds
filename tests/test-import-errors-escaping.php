<?php
/**
 * Tests for escaping of stored import errors on the Imports list.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Import_Errors_Escaping extends WP_UnitTestCase {

	private const PAYLOAD = '<img src=x onerror="alert(1)">';

	/**
	 * Import class under test.
	 *
	 * @var Feedzy_Rss_Feeds_Import
	 */
	private $import;

	/**
	 * Import job the column is rendered for.
	 *
	 * @var int
	 */
	private $import_id;

	/**
	 * Global post saved before each test.
	 *
	 * @var WP_Post|null
	 */
	private $original_post;

	/**
	 * Create an import job and make it the current post.
	 *
	 * @access public
	 */
	public function set_up(): void {
		parent::set_up();

		$this->original_post = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;

		$this->import    = new Feedzy_Rss_Feeds_Import( 'feedzy-rss-feeds', Feedzy_Rss_Feeds::get_version() );
		$this->import_id = self::factory()->post->create(
			array(
				'post_type'   => 'feedzy_imports',
				'post_status' => 'publish',
			)
		);

		$GLOBALS['post'] = get_post( $this->import_id );
	}

	/**
	 * Restore the global post.
	 *
	 * @access public
	 */
	public function tear_down(): void {
		$GLOBALS['post'] = $this->original_post;

		parent::tear_down();
	}

	/**
	 * A feed-supplied value in a stored error renders as text, not markup.
	 */
	public function test_stored_error_list_is_escaped(): void {
		update_post_meta( $this->import_id, 'import_errors', array( 'Invalid image URL: ' . self::PAYLOAD ) );

		$html = $this->render_last_run_column();

		$this->assertStringNotContainsString( '<img', $html );
		$this->assertStringContainsString( 'Invalid image URL: &lt;img src=x onerror=&quot;alert(1)&quot;&gt;', $html );
		$this->assertStringContainsString( 'feedzy-errors-dialog', $html );
		$this->assertStringContainsString( '<i class="dashicons dashicons-warning"></i>', $html );
	}

	/**
	 * A legacy single-string error is escaped too.
	 */
	public function test_stored_error_string_is_escaped(): void {
		update_post_meta( $this->import_id, 'import_errors', 'Invalid image URL: ' . self::PAYLOAD );

		$html = $this->render_last_run_column();

		$this->assertStringNotContainsString( '<img', $html );
		$this->assertStringContainsString( '&lt;img', $html );
	}

	/**
	 * Plain messages and existing entities display unchanged.
	 */
	public function test_plain_error_is_not_double_encoded(): void {
		update_post_meta( $this->import_id, 'import_errors', array( 'Title & Content are both empty.', 'Feed &amp; source' ) );

		$html = $this->render_last_run_column();

		$this->assertStringContainsString( 'Title &amp; Content are both empty.', $html );
		$this->assertStringContainsString( 'Feed &amp; source', $html );
		$this->assertStringNotContainsString( '&amp;amp;', $html );
	}

	/**
	 * Markup added through the pro error filter keeps safe tags and loses script.
	 */
	public function test_filtered_errors_strip_scripting(): void {
		add_filter(
			'feedzy_run_status_errors',
			static function (): string {
				return '<div class="feedzy-error feedzy-api-error"><br><strong>Openai</strong>: <a href="https://example.com/docs">Quota</a> ' . self::PAYLOAD . '</div>';
			}
		);

		$html = $this->render_last_run_column();

		$this->assertStringNotContainsString( 'onerror', $html );
		$this->assertStringContainsString( '<strong>Openai</strong>', $html );
		$this->assertStringContainsString( '<a href="https://example.com/docs">Quota</a>', $html );
		$this->assertStringContainsString( '<i class="dashicons dashicons-warning"></i>', $html );
	}

	/**
	 * Render the Imports list "Last Run" column for the test job.
	 *
	 * @return string The column HTML.
	 */
	private function render_last_run_column(): string {
		ob_start();
		$this->import->manage_feedzy_import_columns( 'feedzy-last_run', $this->import_id );

		return (string) ob_get_clean();
	}
}
