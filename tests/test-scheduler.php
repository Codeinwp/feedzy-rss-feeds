<?php
/**
 * Tests for the Feedzy_Rss_Feeds_Util_Scheduler utility class.
 *
 * These tests exercise the WP-Cron backed code paths (the free plugin
 * does not bundle Action Scheduler).
 *
 * @since      5.1.0
 *
 * @package    feedzy-rss-feeds
 */
class Test_Scheduler extends WP_UnitTestCase {

	/**
	 * Hook name used in the tests.
	 *
	 * @var string
	 */
	private $hook = 'feedzy_test_scheduler_hook';

	/**
	 * Clean up any events scheduled during a test.
	 *
	 * @access public
	 */
	public function tearDown(): void {
		wp_clear_scheduled_hook( $this->hook );
		wp_clear_scheduled_hook( $this->hook, array( 'job-1' ) );
		parent::tearDown();
	}

	/**
	 * Test is_scheduled returns false for an unknown hook.
	 *
	 * @access public
	 */
	public function test_is_scheduled_unknown_hook_is_falsy() {
		$this->assertEmpty( Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'feedzy_hook_never_scheduled' ) );
	}

	/**
	 * Test schedule_event schedules a recurring event that is_scheduled can find.
	 *
	 * @access public
	 */
	public function test_schedule_event_and_is_scheduled() {
		$timestamp = time() + MINUTE_IN_SECONDS;

		$result = Feedzy_Rss_Feeds_Util_Scheduler::schedule_event( $timestamp, 'hourly', $this->hook );
		$this->assertNotFalse( $result );
		$this->assertFalse( is_wp_error( $result ) );

		$next = Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( $this->hook );
		$this->assertNotEmpty( $next );

		// When backed by WP-Cron, the returned value is the scheduled timestamp.
		if ( is_int( $next ) && ! function_exists( 'as_next_scheduled_action' ) ) {
			$this->assertEquals( $timestamp, $next );
		}

		// The recurrence is stored on the cron event.
		$event = wp_get_scheduled_event( $this->hook );
		$this->assertNotFalse( $event );
		$this->assertEquals( 'hourly', $event->schedule );
	}

	/**
	 * Test scheduled events are matched by their arguments.
	 *
	 * @access public
	 */
	public function test_schedule_event_with_args() {
		$timestamp = time() + MINUTE_IN_SECONDS;

		Feedzy_Rss_Feeds_Util_Scheduler::schedule_event( $timestamp, 'daily', $this->hook, array( 'job-1' ) );

		// Found with the matching args.
		$this->assertNotEmpty( Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( $this->hook, array( 'job-1' ) ) );

		// Not found with different args.
		$this->assertEmpty( Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( $this->hook, array( 'job-2' ) ) );
	}

	/**
	 * Test clear_scheduled_hook removes the scheduled event.
	 *
	 * @access public
	 */
	public function test_clear_scheduled_hook() {
		$timestamp = time() + MINUTE_IN_SECONDS;

		Feedzy_Rss_Feeds_Util_Scheduler::schedule_event( $timestamp, 'hourly', $this->hook );
		$this->assertNotEmpty( Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( $this->hook ) );

		Feedzy_Rss_Feeds_Util_Scheduler::clear_scheduled_hook( $this->hook );
		$this->assertEmpty( Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( $this->hook ) );
	}

	/**
	 * Test schedule_event with an unknown recurrence fails gracefully.
	 *
	 * @access public
	 */
	public function test_schedule_event_unknown_recurrence() {
		$timestamp = time() + MINUTE_IN_SECONDS;

		$result = Feedzy_Rss_Feeds_Util_Scheduler::schedule_event( $timestamp, 'every_never', $this->hook );

		// WP-Cron rejects unknown schedules (false or WP_Error depending on WP version).
		$this->assertTrue( false === $result || is_wp_error( $result ) );
		$this->assertEmpty( Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( $this->hook ) );
	}
}
