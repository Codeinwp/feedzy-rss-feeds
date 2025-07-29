<?php
/**
 * Track the usage of the plugin.
 *
 * @since      5.0.8
 *
 * @package    feedzy-rss-feeds
 */

class Test_Plugin_Usage extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var Feedzy_Rss_Feeds_Usage
	 */
	private $usage;

	/**
	 * Set up test environment.
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Clean up any existing option data
		delete_option( Feedzy_Rss_Feeds_Usage::OPTION_NAME );
		
		// Reset singleton instance for clean testing
		$reflection = new ReflectionClass( Feedzy_Rss_Feeds_Usage::class );
		$instance_property = $reflection->getProperty( 'instance' );
		$instance_property->setAccessible( true );
		$instance_property->setValue( null, null );
		
		$this->usage = Feedzy_Rss_Feeds_Usage::get_instance();
	}

	/**
	 * Clean up after tests.
	 */
	public function tearDown(): void {
		delete_option( Feedzy_Rss_Feeds_Usage::OPTION_NAME );
		parent::tearDown();
	}

	/**
	 * Test singleton pattern implementation.
	 */
	public function test_singleton_pattern() {
		$instance1 = Feedzy_Rss_Feeds_Usage::get_instance();
		$instance2 = Feedzy_Rss_Feeds_Usage::get_instance();
		
		$this->assertSame( $instance1, $instance2 );
		$this->assertInstanceOf( Feedzy_Rss_Feeds_Usage::class, $instance1 );
	}

	/**
	 * Test initial data structure and defaults.
	 */
	public function test_initial_data_structure() {
		$data = $this->usage->get_usage_data();
		
		$this->assertIsArray( $data );
		$this->assertEquals( '', $data['first_import_run_datetime'] );
		$this->assertEquals( '', $data['first_import_created_datetime'] );
		$this->assertEquals( 0, $data['import_count'] );
		$this->assertEquals( 0, $data['plugin_age_on_first_import_created'] );
		$this->assertFalse( $data['can_track_first_usage'] );
		$this->assertIsArray( $data['imports_per_week'] );
		$this->assertEmpty( $data['imports_per_week'] );
	}

	/**
	 * Test updating usage data.
	 */
	public function test_update_usage_data() {
		$new_data = array(
			'import_count' => 5,
			'can_track_first_usage' => true,
		);
		
		$result = $this->usage->update_usage_data( $new_data );
		$this->assertTrue( $result );
		
		$updated_data = $this->usage->get_usage_data();
		$this->assertEquals( 5, $updated_data['import_count'] );
		$this->assertTrue( $updated_data['can_track_first_usage'] );
		
		// Other fields should remain unchanged
		$this->assertEquals( '', $updated_data['first_import_run_datetime'] );
		$this->assertEquals( '', $updated_data['first_import_created_datetime'] );
	}

	/**
	 * Test RSS import tracking.
	 */
	public function test_track_rss_import() {
		$initial_data = $this->usage->get_usage_data();
		$this->assertEquals( 0, $initial_data['import_count'] );
		
		$this->usage->track_rss_import();
		
		$updated_data = $this->usage->get_usage_data();
		$this->assertEquals( 1, $updated_data['import_count'] );
		
		// Track another import
		$this->usage->track_rss_import();
		$updated_data = $this->usage->get_usage_data();
		$this->assertEquals( 2, $updated_data['import_count'] );
	}

	/**
	 * Test RSS import tracking with first import datetime recording.
	 */
	public function test_track_rss_import_with_first_import() {
		// Enable tracking
		$this->usage->update_usage_data( array( 'can_track_first_usage' => true ) );
		
		$this->usage->track_rss_import();
		
		$data = $this->usage->get_usage_data();
		$this->assertEquals( 1, $data['import_count'] );
		$this->assertNotEmpty( $data['first_import_run_datetime'] );
		$this->assertMatchesRegularExpression( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $data['first_import_run_datetime'] );
		
		// Second import should not update first_import_run_datetime
		$first_datetime = $data['first_import_run_datetime'];
		sleep( 1 );
		$this->usage->track_rss_import();
		
		$updated_data = $this->usage->get_usage_data();
		$this->assertEquals( 2, $updated_data['import_count'] );
		$this->assertEquals( $first_datetime, $updated_data['first_import_run_datetime'] );
	}

	/**
	 * Test import count overflow protection.
	 */
	public function test_track_rss_import_overflow_protection() {
		$this->usage->update_usage_data( array( 'import_count' => PHP_INT_MAX ) );
		
		$this->usage->track_rss_import();
		
		$data = $this->usage->get_usage_data();
		$this->assertEquals( PHP_INT_MAX, $data['import_count'] );
	}

	/**
	 * Test import creation tracking.
	 */
	public function test_track_import_creation() {
		// Enable tracking
		$this->usage->update_usage_data( array( 'can_track_first_usage' => true ) );
		
		$this->usage->track_import_creation();
		
		$data = $this->usage->get_usage_data();
		$this->assertNotEmpty( $data['first_import_created_datetime'] );
		$this->assertMatchesRegularExpression( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $data['first_import_created_datetime'] );
		
		// Second call should not update the datetime
		$first_datetime = $data['first_import_created_datetime'];
		sleep( 1 );
		$this->usage->track_import_creation();
		
		$updated_data = $this->usage->get_usage_data();
		$this->assertEquals( $first_datetime, $updated_data['first_import_created_datetime'] );
	}

	/**
	 * Test import creation tracking when tracking is disabled.
	 */
	public function test_track_import_creation_tracking_disabled() {
		$this->usage->track_import_creation();
		
		$data = $this->usage->get_usage_data();
		$this->assertNotEmpty( $data['first_import_created_datetime'] );
	}

	/**
	 * Test per-week import recording.
	 */
	public function test_record_import_per_week() {
		$this->usage->record_import_per_week();
		
		$data = $this->usage->get_usage_data();
		$this->assertNotEmpty( $data['imports_per_week'] );
		
		$datetime = current_datetime();
		$expected_key = $datetime->format( 'o-\WW' );
		$this->assertArrayHasKey( $expected_key, $data['imports_per_week'] );
		$this->assertEquals( 1, $data['imports_per_week'][ $expected_key ] );
		
		// Record another import in the same week
		$this->usage->record_import_per_week();
		
		$updated_data = $this->usage->get_usage_data();
		$this->assertEquals( 2, $updated_data['imports_per_week'][ $expected_key ] );
	}

	/**
	 * Test removing old import records.
	 */
	public function test_remove_old_import_records() {
		$current_datetime = current_datetime();
		$old_datetime = ( clone $current_datetime )->modify( '-2 years' );
		
		$imports_per_week = array(
			$current_datetime->format( 'o-\WW' ) => 5,
			$old_datetime->format( 'o-\WW' ) => 3,
		);
		
		$filtered = $this->usage->remove_old_import_records( $imports_per_week, $current_datetime );
		
		$this->assertArrayHasKey( $current_datetime->format( 'o-\WW' ), $filtered );
		$this->assertArrayNotHasKey( $old_datetime->format( 'o-\WW' ), $filtered );
		$this->assertEquals( 5, $filtered[ $current_datetime->format( 'o-\WW' ) ] );
	}

	/**
	 * Test usage statistics generation.
	 */
	public function test_get_usage_stats() {
		$this->usage->update_usage_data( array(
			'import_count' => 10,
			'can_track_first_usage' => true,
			'first_import_run_datetime' => '2023-01-15 10:30:00',
			'first_import_created_datetime' => '2023-01-10 09:00:00',
		) );
		
		$stats = $this->usage->get_usage_stats();
		
		$this->assertIsArray( $stats );
		$this->assertEquals( 10, $stats['import_count'] );
		$this->assertEquals( '2023-01-15 10:30:00', $stats['first_import_run_datetime'] );
		$this->assertEquals( '2023-01-10 09:00:00', $stats['first_import_created_datetime'] );
		$this->assertArrayHasKey( 'time_between_first_import_created_and_run', $stats );
		$this->assertEquals( 437400, $stats['time_between_first_import_created_and_run'] ); // 5 days, 1.5 hours in seconds
		$this->assertIsArray( $stats['imports_per_week'] );
	}

	/**
	 * Test usage statistics with formatted imports per week.
	 */
	public function test_get_usage_stats_with_imports_per_week() {
		$imports_per_week = array(
			'2023-W01' => 5,
			'2023-W02' => 3,
			'2024-W03' => 7,
		);
		
		$this->usage->update_usage_data( array(
			'import_count' => 15,
			'imports_per_week' => $imports_per_week,
		) );
		
		$stats = $this->usage->get_usage_stats();
		
		$this->assertCount( 3, $stats['imports_per_week'] );
		
		// Check first entry
		$first_import = $stats['imports_per_week'][0];
		$this->assertEquals( 2023, $first_import['year'] );
		$this->assertEquals( 1, $first_import['week'] );
		$this->assertEquals( 5, $first_import['count'] );
		
		// Verify chronological ordering
		$this->assertTrue( $stats['imports_per_week'][0]['year'] <= $stats['imports_per_week'][1]['year'] );
		$this->assertTrue( $stats['imports_per_week'][2]['year'] >= $stats['imports_per_week'][1]['year'] );
	}

	/**
	 * Test usage statistics when tracking is disabled.
	 */
	public function test_get_usage_stats_tracking_disabled() {
		$this->usage->update_usage_data( array(
			'import_count' => 5,
			'can_track_first_usage' => false,
		) );
		
		$stats = $this->usage->get_usage_stats();
		
		$this->assertEquals( 5, $stats['import_count'] );
		$this->assertArrayNotHasKey( 'first_import_run_datetime', $stats );
		$this->assertArrayNotHasKey( 'first_import_created_datetime', $stats );
		$this->assertArrayNotHasKey( 'time_between_first_import_created_and_run', $stats );
	}

	/**
	 * Test deleting usage data.
	 */
	public function test_delete_usage_data() {
		$this->usage->update_usage_data( array( 'import_count' => 5 ) );
		
		// Verify data exists
		$data = $this->usage->get_usage_data();
		$this->assertEquals( 5, $data['import_count'] );
		
		// Delete data
		$result = $this->usage->delete_usage_data();
		$this->assertTrue( $result );
		
		// Verify data is reset to defaults
		$data = $this->usage->get_usage_data();
		$this->assertEquals( 0, $data['import_count'] );
	}

	/**
	 * Test new user detection.
	 */
	public function test_is_new_user() {
		// Test when no install time is set
		delete_option( 'feedzy_rss_feeds_install' );
		$this->assertTrue( $this->usage->is_new_user() );
		
		// Test with recent install time
		update_option( 'feedzy_rss_feeds_install', time() - HOUR_IN_SECONDS );
		$this->assertTrue( $this->usage->is_new_user() );
		
		// Test with old install time
		update_option( 'feedzy_rss_feeds_install', time() - ( 2 * DAY_IN_SECONDS ) );
		$this->assertFalse( $this->usage->is_new_user() );
		
		// Test with non-numeric install time
		update_option( 'feedzy_rss_feeds_install', 'invalid' );
		$this->assertTrue( $this->usage->is_new_user() );
	}

	/**
	 * Test edge case with invalid datetime format in imports per week.
	 */
	public function test_get_usage_stats_with_invalid_datetime_format() {
		$imports_per_week = array(
			'2023-W01' => 5,
			'invalid-format' => 3,
			'2023-W02' => 7,
		);
		
		$this->usage->update_usage_data( array(
			'imports_per_week' => $imports_per_week,
		) );
		
		$stats = $this->usage->get_usage_stats();
		
		// Should only include valid formats
		$this->assertCount( 2, $stats['imports_per_week'] );
	}

	/**
	 * Test time calculation edge cases.
	 */
	public function test_time_calculation_edge_cases() {
		// Test when import run time is before creation time (shouldn't happen but good to test)
		$this->usage->update_usage_data( array(
			'can_track_first_usage' => true,
			'first_import_run_datetime' => '2023-01-10 09:00:00',
			'first_import_created_datetime' => '2023-01-15 10:30:00',
		) );
		
		$stats = $this->usage->get_usage_stats();
		$this->assertArrayNotHasKey( 'time_between_first_import_created_and_run', $stats );
		
		// Test with invalid datetime strings
		$this->usage->update_usage_data( array(
			'first_import_run_datetime' => 'invalid-date',
			'first_import_created_datetime' => '2023-01-10 09:00:00',
		) );
		
		$stats = $this->usage->get_usage_stats();
		$this->assertArrayNotHasKey( 'time_between_first_import_created_and_run', $stats );
	}
}
