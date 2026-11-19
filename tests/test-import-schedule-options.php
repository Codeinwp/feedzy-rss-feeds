<?php
/**
 * Tests for the import schedule dropdowns.
 *
 * @package     feedzy-rss-feeds
 * @subpackage  Tests
 */
class Test_Import_Schedule_Options extends WP_UnitTestCase {

	/**
	 * The import instance under test.
	 *
	 * @var Feedzy_Rss_Feeds_Import
	 */
	private $import;

	/**
	 * Sets up the import instance and an administrator.
	 */
	public function set_up(): void {
		parent::set_up();

		$this->import = new Feedzy_Rss_Feeds_Import( 'feedzy-rss-feeds', Feedzy_Rss_Feeds::get_version() );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	/**
	 * Removes the test schedule.
	 */
	public function tear_down(): void {
		remove_filter( 'cron_schedules', array( $this, 'add_daily_duplicate_schedule' ) );

		parent::tear_down();
	}

	/**
	 * Registers a schedule with the same interval as `daily`.
	 *
	 * @param array<string, array{interval: int, display: string}> $schedules Registered schedules.
	 *
	 * @return array<string, array{interval: int, display: string}>
	 */
	public function add_daily_duplicate_schedule( array $schedules ): array {
		$schedules['feedzy_test_every_day'] = array(
			'interval' => DAY_IN_SECONDS,
			'display'  => 'Every Day',
		);

		return $schedules;
	}

	/**
	 * A saved schedule that is no longer registered stays selected.
	 */
	public function test_unregistered_saved_schedule_stays_selected(): void {
		$markup = $this->render_schedule_select( 'feedzy_test_gone' );

		$this->assertSame( array( 'feedzy_test_gone' ), $this->selected_slugs( $markup ) );
		$this->assertStringContainsString( 'feedzy_test_gone (unavailable)', $markup );
	}

	/**
	 * A saved schedule sharing its interval with an earlier one is still listed and selected.
	 */
	public function test_saved_schedule_with_duplicate_interval_stays_selected(): void {
		add_filter( 'cron_schedules', array( $this, 'add_daily_duplicate_schedule' ) );

		$markup = $this->render_schedule_select( 'feedzy_test_every_day' );

		$this->assertSame( array( 'feedzy_test_every_day' ), $this->selected_slugs( $markup ) );
		$this->assertStringNotContainsString( '(unavailable)', $markup );
	}

	/**
	 * A registered saved schedule is selected once, with no extra option.
	 */
	public function test_registered_saved_schedule_is_selected_once(): void {
		$markup = $this->render_schedule_select( 'weekly' );

		$this->assertSame( array( 'weekly' ), $this->selected_slugs( $markup ) );
		$this->assertSame( 1, substr_count( $markup, 'value="weekly"' ) );
		$this->assertStringNotContainsString( '(unavailable)', $markup );
	}

	/**
	 * The global schedule list keeps hourly first and appends an unregistered saved schedule.
	 */
	public function test_global_schedule_options_keep_unregistered_saved_schedule(): void {
		$options = feedzy_get_schedule_options( 'feedzy_test_gone', 'hourly' );
		$slugs   = array_keys( $options );

		$this->assertSame( 'hourly', reset( $slugs ) );
		$this->assertSame( 'feedzy_test_gone', end( $slugs ) );
		$this->assertSame( 'feedzy_test_gone (unavailable)', $options['feedzy_test_gone'] );
	}

	/**
	 * Without a saved schedule, no extra option is added.
	 */
	public function test_no_saved_schedule_adds_no_option(): void {
		$options = feedzy_get_schedule_options( '', 'daily' );

		$this->assertSame( 'daily', array_keys( $options )[0] );
		$this->assertSame( array(), array_diff_key( $options, wp_get_schedules() ) );
	}

	/**
	 * Renders the import metabox with the given saved schedule and returns its schedule select.
	 *
	 * @param string $saved_schedule Saved schedule slug.
	 */
	private function render_schedule_select( string $saved_schedule ): string {
		global $post, $pagenow;

		$previous_post    = $post;
		$previous_pagenow = $pagenow;

		$import_id = self::factory()->post->create(
			array(
				'post_type'   => 'feedzy_imports',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $import_id, 'fz_cron_schedule', $saved_schedule );

		$post    = get_post( $import_id );
		$pagenow = 'post.php';

		ob_start();
		$this->import->feedzy_import_feed_options();
		$markup = (string) ob_get_clean();

		$post    = $previous_post;
		$pagenow = $previous_pagenow;

		$this->assertSame( 1, preg_match( '#<select id="fz-event-schedule".*?</select>#s', $markup, $matches ) );

		return $matches[0];
	}

	/**
	 * Returns the slugs of the selected options in a select's markup.
	 *
	 * @param string $markup Select markup.
	 *
	 * @return list<string>
	 */
	private function selected_slugs( string $markup ): array {
		preg_match_all( '#value="([^"]*)"\s*selected=#', $markup, $matches );

		return $matches[1];
	}
}
