<?php
/**
 * Tests for the logger.
 *
 * @since      5.1.0
 *
 * @package    feedzy-rss-feeds
 */

class Test_Logger extends WP_UnitTestCase {
	
	/**
	 * The logger instance.
	 *
	 * @var Feedzy_Rss_Feeds_Log
	 */
	private $logger;

	/**
	 * Test log file path.
	 *
	 * @var string
	 */
	private $test_log_path;

	/**
	 * Helper method for string contains assertion (PHPUnit compatibility).
	 *
	 * @param string $needle The substring to search for.
	 * @param string $haystack The string to search in.
	 * @param string $message Optional failure message.
	 */
	private function assertStringContains( $needle, $haystack, $message = '' ) {
		if ( method_exists( $this, 'assertStringContainsString' ) ) {
			$this->assertStringContainsString( $needle, $haystack, $message );
		} else {
			$this->assertContains( $needle, $haystack, $message );
		}
	}

	/**
	 * Helper method for type assertion (PHPUnit compatibility).
	 *
	 * @param string $type The expected type.
	 * @param mixed  $value The value to check.
	 * @param string $message Optional failure message.
	 */
	private function assertIsType( $type, $value, $message = '' ) {
		$method = 'assertIs' . ucfirst( $type );
		if ( method_exists( $this, $method ) ) {
			$this->$method( $value, $message );
		} else {
			$this->assertInternalType( $type, $value, $message );
		}
	}

	/**
	 * Reset the logger singleton to ensure fresh state per test.
	 */
	private function reset_logger_singleton() {
		if ( class_exists( 'Feedzy_Rss_Feeds_Log' ) ) {
			$ref  = new ReflectionClass( 'Feedzy_Rss_Feeds_Log' );
			if ( $ref->hasProperty( 'instance' ) ) {
				$prop = $ref->getProperty( 'instance' );
				$prop->setAccessible( true );
				$prop->setValue( null, null );
			}
		}
	}

	/**
	 * Set up test environment.
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Ensure fresh singleton for each test.
		$this->reset_logger_singleton();
		$this->logger = Feedzy_Rss_Feeds_Log::get_instance();
		
		// Use the class API to determine the log path.
		$this->test_log_path = $this->logger->get_log_file_path();
		
		// Clean up any existing log files
		if ( file_exists( $this->test_log_path ) ) {
			unlink( $this->test_log_path );
		}
		
		// Reset log statistics
		delete_option( Feedzy_Rss_Feeds_Log::STATS_OPTION_KEY );
		
		// Reset logger settings
		delete_option( 'feedzy-settings' );
	}

	/**
	 * Clean up after tests.
	 */
	public function tearDown(): void {
		// Clean up test files
		if ( file_exists( $this->test_log_path ) ) {
			unlink( $this->test_log_path );
		}
		
		// Reset options
		delete_option( Feedzy_Rss_Feeds_Log::STATS_OPTION_KEY );
		delete_option( 'feedzy-settings' );

		// Reset the singleton to avoid side effects between tests.
		$this->reset_logger_singleton();
		
		parent::tearDown();
	}

	/**
	 * Test logger singleton pattern.
	 */
	public function test_singleton_instance() {
		$instance1 = Feedzy_Rss_Feeds_Log::get_instance();
		$instance2 = Feedzy_Rss_Feeds_Log::get_instance();
		
		$this->assertSame( $instance1, $instance2, 'Logger should return the same instance (singleton)' );
		$this->assertInstanceOf( 'Feedzy_Rss_Feeds_Log', $instance1, 'Instance should be of correct class' );
	}

	/**
	 * Test log level constants.
	 */
	public function test_log_level_constants() {
		$this->assertEquals( 100, Feedzy_Rss_Feeds_Log::DEBUG );
		$this->assertEquals( 200, Feedzy_Rss_Feeds_Log::INFO );
		$this->assertEquals( 300, Feedzy_Rss_Feeds_Log::WARNING );
		$this->assertEquals( 400, Feedzy_Rss_Feeds_Log::ERROR );
		$this->assertEquals( 500, Feedzy_Rss_Feeds_Log::NONE );
	}

	/**
	 * Test debug logging.
	 */
	public function test_debug_logging() {
		// Set threshold to DEBUG to ensure debug messages are logged
		$this->logger->level_threshold = Feedzy_Rss_Feeds_Log::DEBUG;
		
		Feedzy_Rss_Feeds_Log::debug( 'Test debug message', array( 'test_data' => 'debug_value' ) );
		
		$this->assertTrue( file_exists( $this->test_log_path ), 'Log file should be created' );
		
		$logs = $this->logger->get_recent_logs( 1 );
		$this->assertNotEmpty( $logs, 'Should have log entries' );
		$this->assertEquals( 'debug', $logs[0]['level'] );
		$this->assertEquals( 'Test debug message', $logs[0]['message'] );
		$this->assertEquals( 'debug_value', $logs[0]['context']['test_data'] );
	}

	/**
	 * Test info logging.
	 */
	public function test_info_logging() {
		// Set threshold to INFO to ensure info messages are logged
		$this->logger->level_threshold = Feedzy_Rss_Feeds_Log::INFO;
		
		Feedzy_Rss_Feeds_Log::info( 'Test info message', array( 'test_data' => 'info_value' ) );
		
		$logs = $this->logger->get_recent_logs( 1 );
		$this->assertNotEmpty( $logs );
		$this->assertEquals( 'info', $logs[0]['level'] );
		$this->assertEquals( 'Test info message', $logs[0]['message'] );
	}

	/**
	 * Test warning logging.
	 */
	public function test_warning_logging() {
		// Set threshold to WARNING to ensure warning messages are logged
		$this->logger->level_threshold = Feedzy_Rss_Feeds_Log::WARNING;
		
		Feedzy_Rss_Feeds_Log::warning( 'Test warning message', array( 'test_data' => 'warning_value' ) );
		
		$logs = $this->logger->get_recent_logs( 1 );
		$this->assertNotEmpty( $logs );
		$this->assertEquals( 'warning', $logs[0]['level'] );
		$this->assertEquals( 'Test warning message', $logs[0]['message'] );
	}

	/**
	 * Test error logging.
	 */
	public function test_error_logging() {
		// Errors should always be logged regardless of threshold
		Feedzy_Rss_Feeds_Log::error( 'Test error message', array( 'test_data' => 'error_value' ) );
		
		$logs = $this->logger->get_recent_logs( 1 );
		$this->assertNotEmpty( $logs );
		$this->assertEquals( 'error', $logs[0]['level'] );
		$this->assertEquals( 'Test error message', $logs[0]['message'] );
		
		// Check that error count statistic is incremented
		$stats = $this->logger->get_log_statistics();
		$this->assertEquals( 1, $stats['error_count'] );
	}

	/**
	 * Test log level threshold filtering.
	 */
	public function test_log_level_threshold() {
		// Set threshold to ERROR - only errors should be logged
		$this->logger->level_threshold = Feedzy_Rss_Feeds_Log::ERROR;
		
		Feedzy_Rss_Feeds_Log::debug( 'Debug message' );
		Feedzy_Rss_Feeds_Log::info( 'Info message' );
		Feedzy_Rss_Feeds_Log::warning( 'Warning message' );
		Feedzy_Rss_Feeds_Log::error( 'Error message' );
		
		$logs = $this->logger->get_recent_logs( 10 );
		$this->assertCount( 1, $logs, 'Only error message should be logged' );
		$this->assertEquals( 'error', $logs[0]['level'] );
		$this->assertEquals( 'Error message', $logs[0]['message'] );
	}

	/**
	 * Test context setting.
	 */
	public function test_context_setting() {
		$context = array(
			'import_id' => 123,
			'feed_url' => 'https://example.com/feed.xml'
		);
		
		$this->logger->set_context( $context );
		
		// Set threshold to DEBUG to ensure message is logged
		$this->logger->level_threshold = Feedzy_Rss_Feeds_Log::DEBUG;
		Feedzy_Rss_Feeds_Log::debug( 'Test with context' );
		
		$logs = $this->logger->get_recent_logs( 1 );
		$this->assertEquals( 123, $logs[0]['context']['import_id'] );
		$this->assertEquals( 'https://example.com/feed.xml', $logs[0]['context']['feed_url'] );

		// New: origin should be auto-injected.
		$this->assertArrayHasKey( 'function', $logs[0]['context'] );
		$this->assertArrayHasKey( 'line', $logs[0]['context'] );
	}

	/**
	 * Test log entry structure.
	 */
	public function test_log_entry_structure() {
		$this->logger->level_threshold = Feedzy_Rss_Feeds_Log::DEBUG;
		
		Feedzy_Rss_Feeds_Log::debug( 'Test message', array( 'key' => 'value' ) );
		
		$logs = $this->logger->get_recent_logs( 1 );
		$log = $logs[0];
		
		$this->assertArrayHasKey( 'timestamp', $log );
		$this->assertArrayHasKey( 'level', $log );
		$this->assertArrayHasKey( 'message', $log );
		$this->assertArrayHasKey( 'context', $log );
		
		// Validate timestamp format (ISO 8601, allow Z or explicit offset)
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\+\d{2}:\d{2}|Z)$/', $log['timestamp'] );
	}

	/**
	 * Test getting recent logs with limit.
	 */
	public function test_get_recent_logs_with_limit() {
		$this->logger->level_threshold = Feedzy_Rss_Feeds_Log::DEBUG;
		
		// Log multiple messages
		for ( $i = 1; $i <= 5; $i++ ) {
			Feedzy_Rss_Feeds_Log::debug( "Message $i" );
			// Add small delay to ensure different timestamps
			usleep( 1000 );
		}
		
		$logs = $this->logger->get_recent_logs( 3 );
		$this->assertCount( 3, $logs, 'Should return only 3 recent logs' );
		
		// Should be in reverse chronological order (most recent first)
		$this->assertEquals( 'Message 5', $logs[0]['message'] );
		$this->assertEquals( 'Message 4', $logs[1]['message'] );
		$this->assertEquals( 'Message 3', $logs[2]['message'] );
	}

	/**
	 * Test getting logs filtered by level.
	 */
	public function test_get_recent_logs_filtered_by_level() {
		$this->logger->level_threshold = Feedzy_Rss_Feeds_Log::DEBUG;
		
		Feedzy_Rss_Feeds_Log::debug( 'Debug message' );
		Feedzy_Rss_Feeds_Log::info( 'Info message' );
		Feedzy_Rss_Feeds_Log::error( 'Error message' );
		
		$error_logs = $this->logger->get_recent_logs( 10, 'error' );
		$this->assertCount( 1, $error_logs );
		$this->assertEquals( 'error', $error_logs[0]['level'] );
		$this->assertEquals( 'Error message', $error_logs[0]['message'] );
	}

	/**
	 * Test log file operations.
	 */
	public function test_log_file_operations() {
		// Initially no log file should exist
		$this->assertFalse( $this->logger->log_file_exists() );
		$this->assertEquals( 0, $this->logger->get_log_file_size() );
		
		// Log something to create the file
		Feedzy_Rss_Feeds_Log::error( 'Test error' );
		
		$this->assertTrue( $this->logger->log_file_exists() );
		$this->assertTrue( $this->logger->is_log_readable() );
		$this->assertGreaterThan( 0, $this->logger->get_log_file_size() );
		
		// Test deleting log file
		$this->assertTrue( $this->logger->delete_log_file() );
		$this->assertFalse( $this->logger->log_file_exists() );
	}

	/**
	 * Test raw log content retrieval.
	 */
	public function test_get_all_logs_raw() {
		// No logs initially
		$this->assertFalse( $this->logger->get_all_logs_raw() );
		
		$this->logger->level_threshold = Feedzy_Rss_Feeds_Log::DEBUG;
		Feedzy_Rss_Feeds_Log::debug( 'Test message' );
		
		$raw_content = $this->logger->get_all_logs_raw();
		$this->assertIsType( 'string', $raw_content );
		$this->assertStringContains( 'Test message', $raw_content );
		$this->assertStringContains( '"level":"debug"', $raw_content );
	}

	/**
	 * Test log statistics.
	 */
	public function test_log_statistics() {
		// Initially no stats
		$stats = $this->logger->get_log_statistics();
		$this->assertEmpty( $stats );
		
		// Log some errors
		Feedzy_Rss_Feeds_Log::error( 'Error 1' );
		Feedzy_Rss_Feeds_Log::error( 'Error 2' );
		
		$stats = $this->logger->get_log_statistics();
		$this->assertEquals( 2, $stats['error_count'] );
		$this->assertArrayHasKey( 'stats_since', $stats );
		
		// Test resetting stats
		$this->logger->reset_log_stats();
		$stats = $this->logger->get_log_statistics();
		$this->assertEmpty( $stats );
	}

	/**
	 * Test error message accumulation.
	 */
	public function test_error_message_accumulation() {
		// Initially disabled
		$this->assertEmpty( $this->logger->get_error_messages_accumulator() );
		
		// Enable retention
		$this->logger->enable_error_messages_retention();
		
		Feedzy_Rss_Feeds_Log::error( 'First error' );
		Feedzy_Rss_Feeds_Log::error( 'Second error' );
		
		$messages = $this->logger->get_error_messages_accumulator();
		$this->assertContains( 'First error', $messages );
		$this->assertContains( 'Second error', $messages );
		
		// Disable retention
		$this->logger->disable_error_messages_retention();
		$this->assertEmpty( $this->logger->get_error_messages_accumulator() );
	}

	/**
	 * Test email configuration.
	 */
	public function test_email_configuration() {
		// Initially no email configured
		$this->assertFalse( $this->logger->can_send_email() );
		$this->assertEmpty( $this->logger->get_email_address() );
		
		// Configure email settings
		$settings = array(
			'logs' => array(
				'send_email_report' => true,
				'email' => 'test@example.com'
			)
		);
		update_option( 'feedzy-settings', $settings );
		
		// Recreate logger singleton to pick up new settings
		$this->reset_logger_singleton();
		$this->logger = Feedzy_Rss_Feeds_Log::get_instance();
		
		$this->assertTrue( $this->logger->can_send_email() );
		$this->assertEquals( 'test@example.com', $this->logger->get_email_address() );
	}

	/**
	 * Test reportable data detection.
	 */
	public function test_has_reportable_data() {
		// Initially no reportable data
		$this->assertFalse( $this->logger->has_reportable_data() );
		
		// Log an error
		Feedzy_Rss_Feeds_Log::error( 'Test error for reporting' );
		
		// Should now have reportable data
		$this->assertTrue( $this->logger->has_reportable_data() );
	}

	/**
	 * Test error logs for email.
	 */
	public function test_get_error_logs_for_email() {
		$this->logger->level_threshold = Feedzy_Rss_Feeds_Log::DEBUG;
		
		// Log different levels
		Feedzy_Rss_Feeds_Log::debug( 'Debug message' );
		Feedzy_Rss_Feeds_Log::info( 'Info message' );
		Feedzy_Rss_Feeds_Log::error( 'Error for email' );
		
		$error_logs = $this->logger->get_error_logs_for_email( 10 );
		$this->assertCount( 1, $error_logs );
		$this->assertEquals( 'error', $error_logs[0]['level'] );
		$this->assertEquals( 'Error for email', $error_logs[0]['message'] );
	}

	/**
	 * Test settings initialization.
	 */
	public function test_settings_initialization() {
		$settings = array(
			'logs' => array(
				'level' => 'debug',
				'send_email_report' => true,
				'email' => 'admin@example.com'
			)
		);
		update_option( 'feedzy-settings', $settings );
		
		// Create new logger instance to test initialization
		$this->reset_logger_singleton();
		$logger = Feedzy_Rss_Feeds_Log::get_instance();
		
		$this->assertEquals( Feedzy_Rss_Feeds_Log::DEBUG, $logger->level_threshold );
		$this->assertTrue( $logger->can_send_email );
		$this->assertEquals( 'admin@example.com', $logger->to_email );
	}

	/**
	 * Test invalid settings values.
	 */
	public function test_invalid_settings_values() {
		$settings = array(
			'logs' => array(
				'level' => 'invalid_level',
				'email' => 'invalid-email'
			)
		);
		update_option( 'feedzy-settings', $settings );
		
		$this->reset_logger_singleton();
		$logger = Feedzy_Rss_Feeds_Log::get_instance();
		
		// Should fallback to default ERROR level
		$this->assertEquals( Feedzy_Rss_Feeds_Log::ERROR, $logger->level_threshold );
		// Invalid email should be sanitized to empty
		$this->assertEmpty( $logger->to_email );
	}

	/**
	 * Test log cleaning based on file size and age.
	 */
	public function test_should_clean_logs() {
		// Create a log file
		Feedzy_Rss_Feeds_Log::error( 'Test error' );
		$this->assertTrue( $this->logger->log_file_exists() );

		$file_path = $this->logger->get_log_file_path();

		// Simulate an old log file by setting its modification time far in the past
		$this->assertTrue( file_exists( $file_path ), 'Log file should exist before aging it' );
		$old_time = time() - ( defined( 'YEAR_IN_SECONDS' ) ? YEAR_IN_SECONDS : 365 * 24 * 60 * 60 );
		$this->assertTrue( touch( $file_path, $old_time ), 'Should be able to update mtime to an old timestamp' );
		clearstatcache( true, $file_path );

		// Old file should be cleaned
		$this->logger->should_clean_logs();
		$this->assertFalse( $this->logger->log_file_exists(), 'Old log file should be cleaned (deleted)' );

		// Create a fresh small log again
		Feedzy_Rss_Feeds_Log::error( 'Recent small error' );
		$this->assertTrue( $this->logger->log_file_exists(), 'Recent log file should exist' );

		// File is small and recent, should not be cleaned
		$this->logger->should_clean_logs();
		$this->assertTrue( $this->logger->log_file_exists(), 'Recent small log should not be cleaned' );

		// Enlarge the log to exceed the max size and ensure it gets cleaned
		$fp = fopen( $file_path, 'r+' );
		$this->assertNotFalse( $fp, 'Should be able to open log file for resizing' );
		$target_size = Feedzy_Rss_Feeds_Log::DEFAULT_MAX_FILE_SIZE + 1;
		$this->assertTrue( ftruncate( $fp, $target_size ), 'Should be able to enlarge log file' );
		fclose( $fp );
		clearstatcache( true, $file_path );
		$this->assertGreaterThan( Feedzy_Rss_Feeds_Log::DEFAULT_MAX_FILE_SIZE, filesize( $file_path ), 'Log size should exceed max size' );

		$this->logger->should_clean_logs();
		$this->assertFalse( $this->logger->log_file_exists(), 'Oversized log file should be cleaned (deleted)' );
	}

	/**
	 * Test JSON formatting of log entries.
	 */
	public function test_json_log_format() {
		$this->logger->level_threshold = Feedzy_Rss_Feeds_Log::DEBUG;
		
		$context = array(
			'unicode' => 'Test with émojis 🚀',
			'special_chars' => 'Test with "quotes" and \backslashes\\'
		);
		
		Feedzy_Rss_Feeds_Log::debug( 'Test message', $context );
		
		$raw_content = $this->logger->get_all_logs_raw();
		$this->assertIsType( 'string', $raw_content );
		
		// Should be valid JSON
		$log_entry = json_decode( trim( $raw_content ), true );
		$this->assertNotNull( $log_entry );
		$this->assertEquals( 'Test with émojis 🚀', $log_entry['context']['unicode'] );
	}
}
