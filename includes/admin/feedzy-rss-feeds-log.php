<?php
/**
 * Add logging functionality to Feedzy RSS Feeds.
 *
 * @package Feedzy_Rss_Feeds_Log
 * @version 5.1.0
 */

/**
 * Class Feedzy_Rss_Feeds_Log.
 *
 * A JSON logger for WordPress.
 */
class Feedzy_Rss_Feeds_Log {

	/**
	 * Option key for storing log statistics.
	 *
	 * @var string Option key for storing log statistics.
	 */
	const STATS_OPTION_KEY = 'feedzy_log_stats'; 
	
	/**
	 * Debug level.
	 *
	 * @var int Debug level.
	 */
	const DEBUG = 100;

	/**
	 * Info level.
	 *
	 * @var int Info level.
	 */
	const INFO = 200;

	/**
	 * Warning level.
	 *
	 * @var int Warning level.
	 */
	const WARNING = 300;

	/**
	 * Error level.
	 *
	 * @var int Error level.
	 */
	const ERROR = 400;

	/**
	 * Ignore level.
	 *
	 * @var int Ignore level.
	 */
	const NONE = 500;

	/**
	 * Log file name.
	 *
	 * @var string Default log name.
	 */
	const FILE_NAME = 'feedzy';

	/**
	 * Log file extension.
	 *
	 * @var string Log file extension.
	 */
	const FILE_EXT = '.jsonl';

	/**
	 * Default max file size.
	 *
	 * @var int Default max file size in bytes (50MB).
	 */
	const DEFAULT_MAX_FILE_SIZE = 50 * 1024 * 1024;

	/**
	 * Default max files.
	 *
	 * @var int Default max number of log files.
	 */
	const DEFAULT_MAX_FILES = 3;

	/**
	 * Log levels.
	 *
	 * @var array<int, string> Log levels.
	 */
	private static $levels = array(
		self::DEBUG   => 'DEBUG',
		self::INFO    => 'INFO',
		self::WARNING => 'WARNING',
		self::ERROR   => 'ERROR',
	);

	const PRIORITIES_MAPPING = array(
		'debug'   => self::DEBUG,
		'info'    => self::INFO,
		'warning' => self::WARNING,
		'error'   => self::ERROR,
		'none'    => self::NONE,
	);

	/**
	 * The single instance of the class.
	 *
	 * @var ?self The single instance of the class.
	 */
	private static $instance = null;

	/**
	 * The path to the log file.
	 *
	 * @var string The path to the log file.
	 */
	private $filepath;

	/**
	 * The WordPress filesystem instance.
	 *
	 * @var \WP_Filesystem_Base|null The WordPress filesystem instance.
	 */
	private $filesystem;

	/**
	 * The context for the logger.
	 *
	 * @var array<string, mixed> The context for the logger.
	 */
	private $context = array();

	/**
	 * The minimum log level threshold for logging messages.
	 *
	 * @var int The minimum log level threshold.
	 */
	public $level_threshold = self::ERROR;

	/**
	 * Whether to retain error messages for import run errors meta.
	 *
	 * @var string[]
	 */
	private $error_messages_accumulator = array();

	/**
	 * Whether to retain error messages for import run errors meta.
	 *
	 * @var bool Whether to retain error messages.
	 */
	private $retain_error_messages = false;

	/**
	 * Whether email reports can be sent.
	 *
	 * @var bool Whether email reports can be sent.
	 */
	public $can_send_email = false;

	/**
	 * The email address to send reports to.
	 *
	 * @var string The email address to send reports to.
	 */
	public $to_email = '';

	/**
	 * Feedzy_Rss_Feeds_Logger constructor.
	 *
	 * @since 5.1.0
	 */
	public function __construct() {
		$this->init_filesystem();
		$this->setup_log_directory();
		$this->init_saved_settings();
	}

	/**
	 * Get the single instance of the class.
	 *
	 * @since 5.1.0
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the WordPress filesystem.
	 *
	 * @since 5.1.0
	 * @return void
	 */
	private function init_filesystem() {
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();
		$this->filesystem = $wp_filesystem;
	}

	/**
	 * Setup the log directory.
	 *
	 * @since 5.1.0
	 * @return void
	 */
	private function setup_log_directory() {
		$upload_dir = wp_upload_dir();
		$log_dir    = $upload_dir['basedir'] . '/feedzy-logs';

		if ( ! $this->filesystem->exists( $log_dir ) ) {
			$this->filesystem->mkdir( $log_dir, FS_CHMOD_DIR );
			$this->filesystem->put_contents( $log_dir . '/.htaccess', "Deny from all\n", FS_CHMOD_FILE );
			$this->filesystem->put_contents( $log_dir . '/index.php', "<?php // Silence is golden\n", FS_CHMOD_FILE );
		}

		$this->filepath = $this->get_log_file_path();
	}

	/**
	 * Initialize saved settings for logger.
	 *
	 * @since 5.1.0
	 * @return void
	 */
	private function init_saved_settings() {
		$feedzy_settings = get_option( 'feedzy-settings', array() );
		if ( ! isset( $feedzy_settings['logs'] ) ) {
			return;
		}

		if ( isset( $feedzy_settings['logs']['level'] ) && isset( self::PRIORITIES_MAPPING[ $feedzy_settings['logs']['level'] ] ) ) {
			$this->level_threshold = self::PRIORITIES_MAPPING[ $feedzy_settings['logs']['level'] ];
		}

		$this->can_send_email = isset( $feedzy_settings['logs']['send_email_report'] ) && $feedzy_settings['logs']['send_email_report'];
		$this->to_email       = isset( $feedzy_settings['logs']['email'] ) ? sanitize_email( $feedzy_settings['logs']['email'] ) : '';
	}

	/**
	 * Set the context for the logger.
	 *
	 * @since 5.1.0
	 * @param array<string, mixed> $context The context to set.
	 * @return self
	 */
	public function set_context( array $context ) {
		$this->context = $context;
		return $this;
	}

	/**
	 * Log a message.
	 *
	 * @since 5.1.0
	 * @param int                  $level The log level.
	 * @param string               $message The log message.
	 * @param array<string, mixed> $context The log context.
	 * @return void
	 */
	public static function log( $level, $message, array $context = array() ) {
		$instance = self::get_instance();
		$instance->write_log( $level, $message, $context );
	}

	/**
	 * Write log to file.
	 *
	 * @since 5.1.0
	 * @param int                  $level The log level.
	 * @param string               $message The log message.
	 * @param array<string, mixed> $context The log context.
	 * @return void
	 */
	private function write_log( $level, $message, array $context = array() ) {
		if ( self::ERROR === $level ) {
			$this->increment_log_stat( 'error_count' );
			$this->try_accumulate_error_message( $message );
		}
		
		if ( ! $this->filesystem ) {
			return;
		}

		if ( $this->level_threshold > $level ) {
			return;
		}

		$record = array(
			'timestamp' => gmdate( 'c' ),
			'level'     => isset( self::$levels[ $level ] ) ? self::$levels[ $level ] : 'UNKNOWN',
			'message'   => $message,
			'context'   => array_merge( $this->context, $context ),
		);

		if ( wp_doing_ajax() ) {
			$record['doing_ajax'] = true;
		}

		if ( wp_doing_cron() ) {
			$record['doing_cron'] = true;
		}
		
		// Write log entry
		$formatted = wp_json_encode( $record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;

		// Ensure the directory exists before writing.
		$log_dir = dirname( $this->filepath );
		if ( ! $this->filesystem->is_dir( $log_dir ) ) {
			$this->setup_log_directory();
		}

		error_log( $formatted, 3, $this->filepath );
	}

	/**
	 * Log a debug message.
	 *
	 * @since 5.1.0
	 * @param string               $message The log message.
	 * @param array<string, mixed> $context The log context.
	 * @return void
	 */
	public static function debug( $message, array $context = array() ) {
		self::log( self::DEBUG, $message, $context );
	}

	/**
	 * Log an info message.
	 *
	 * @since 5.1.0
	 * @param string               $message The log message.
	 * @param array<string, mixed> $context The log context.
	 * @return void
	 */
	public static function info( $message, array $context = array() ) {
		self::log( self::INFO, $message, $context );
	}

	/**
	 * Log a warning message.
	 *
	 * @since 5.1.0
	 * @param string               $message The log message.
	 * @param array<string, mixed> $context The log context.
	 * @return void
	 */
	public static function warning( $message, array $context = array() ) {
		self::log( self::WARNING, $message, $context );
	}

	/**
	 * Log an error message.
	 *
	 * @since 5.1.0
	 * @param string               $message The log message.
	 * @param array<string, mixed> $context The log context.
	 * @return void
	 */
	public static function error( $message, array $context = array() ) {
		self::log( self::ERROR, $message, $context );
	}

	/**
	 * Get all logs as raw entries.
	 *
	 * @since 5.1.0
	 * @return string|false The log entries.
	 */
	public function get_all_logs_raw() {
		if ( ! file_exists( $this->filepath ) ) {
			return false;
		}
		
		return $this->filesystem->get_contents( $this->filepath );
	}

	/**
	 * Get recent log entries.
	 *
	 * @since 5.1.0
	 * @param int         $limit The number of entries to return.
	 * @param string|null $level The log level to filter by.
	 * @return array<int, array<string, mixed>>|WP_Error
	 */
	public function get_recent_logs( $limit = 100, $level = null ) {
		if ( ! file_exists( $this->filepath ) ) {
			return array();
		}

		$lines = $this->get_last_lines( $limit * 2 ); // Read more lines to account for filtering.
		if ( is_wp_error( $lines ) ) {
			return array();
		}

		$logs = array();
		foreach ( $lines as $line ) {
			if ( empty( $line ) ) {
				continue;
			}

			$log = json_decode( $line, true );
			if ( $log && ( null === $level || ( isset( $log['level'] ) && $log['level'] === $level ) ) ) {
				$logs[] = $log;
				if ( count( $logs ) >= $limit ) {
					break;
				}
			}
		}

		return $logs;
	}

	/**
	 * Get the last N lines from the log file.
	 *
	 * @since 5.1.0
	 * @param int $num_lines The number of lines to return.
	 * @return array<string>|WP_Error The last N lines or error messages or WP_Error.
	 */
	public function get_last_lines( $num_lines = 50 ) {
		if ( $this->can_use_direct_file_access() ) {
			return $this->tail_file( $num_lines );
		}

		// Fallback to WP_Filesystem method (slower but works with all filesystem types).
		return $this->read_last_lines_wp_filesystem( $num_lines );
	}

	/**
	 * Check if direct file access is available.
	 *
	 * @since 5.1.0
	 * @return bool Whether direct file access is available.
	 */
	private function can_use_direct_file_access() {
		return 'direct' === get_filesystem_method();
	}

	/**
	 * Efficiently read the last N lines using direct file access.
	 *
	 * @since 5.1.0
	 * @param int $lines The number of lines to read.
	 * @return array<string>|WP_Error The last N lines.
	 */
	private function tail_file( $lines = 50 ) {
		if ( ! $this->log_file_exists() || ! $this->is_log_readable() ) {
			return new WP_Error( 'log_file_not_found' );
		}

		try {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			$handle = fopen( $this->filepath, 'r' );
			if ( ! $handle ) {
				return new WP_Error( 'unable_to_open_log_file' );
			}

			$line_counter = $lines;
			$pos          = -2;
			$beginning    = false;
			$text         = array();

			while ( $line_counter > 0 ) {
				$t = ' ';
				while ( "\n" !== $t ) {
					if ( -1 === fseek( $handle, $pos, SEEK_END ) ) {
						$beginning = true;
						break;
					}
					$t = fgetc( $handle );
					if ( false === $t ) {
						$beginning = true;
						break;
					}
					--$pos;
				}
				--$line_counter;
				if ( $beginning ) {
					rewind( $handle );
				}
				$line = fgets( $handle );
				if ( false !== $line ) {
					$text[] = $line;
				}
				if ( $beginning ) {
					break;
				}
			}
			fclose( $handle );
		
			return $text;
		} catch ( Throwable $e ) {
			return new WP_Error( 'error_reading_log_file', $e->getMessage() );
		}
	}

	/**
	 * Read the last N lines using WP_Filesystem (compatible with all filesystem types).
	 *
	 * @since 5.1.0
	 * @param int $lines_count The number of lines to read.
	 * @return array<string>|WP_Error The last N lines.
	 */
	private function read_last_lines_wp_filesystem( $lines_count ) {
		if ( ! $this->log_file_exists() || ! $this->is_log_readable() ) {
			return new WP_Error( 'log_file_not_found' );
		}

		$content = $this->filesystem->get_contents( $this->filepath );
		if ( false === $content ) {
			return new WP_Error( 'unable_to_read_log_file' );
		}

		$lines = explode( "\n", $content );
		$lines = array_filter(
			$lines,
			function ( $line ) {
				return '' !== trim( $line );
			}
		);

		return array_reverse( array_slice( $lines, -$lines_count ) );
	}

	/**
	 * Delete the log file if it is older than 14 days or if the size exceeds the maximum allowed size.
	 *
	 * @since 5.1.0
	 * @return bool
	 */
	public function should_clean_logs() {
		if ( ! file_exists( $this->filepath ) ) {
			return false;
		}

		$is_too_big = $this->get_log_file_size() > self::DEFAULT_MAX_FILE_SIZE;
		$is_too_old = filemtime( $this->filepath ) < strtotime( '-14 days' );

		if ( $is_too_big || $is_too_old ) {
			return $this->delete_log_file();
		}

		return false;
	}

	/**
	 * Static convenience methods.
	 *
	 * @since 5.1.0
	 * @param string            $name The method name.
	 * @param array<int, mixed> $arguments The method arguments.
	 * @return mixed
	 */
	public static function __callStatic( $name, $arguments ) {
		$instance = self::get_instance();
		if ( method_exists( $instance, $name ) ) {
			return call_user_func_array( array( $instance, $name ), $arguments );
		}
	}

	/**
	 * Register REST API endpoints for logs.
	 *
	 * @since 5.1.0
	 * @return void
	 */
	public function register_endpoints() {
		register_rest_route(
			'feedzy/v1',
			'/logs/download',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'export_logs_endpoint' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
		register_rest_route(
			'feedzy/v1',
			'/logs/delete',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'delete_log_file_endpoint' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * REST API endpoint to export logs.
	 *
	 * @since 5.1.0
	 * @param WP_REST_Request<array<string, mixed>> $request The REST request.
	 * @return WP_Error|void
	 */
	public function export_logs_endpoint( $request ) {
		if ( ! $request instanceof WP_REST_Request ) {
			return new WP_Error( 'invalid_request', '', array( 'status' => 400 ) );
		}

		if ( ! $this->log_file_exists() || ! $this->is_log_readable() ) {
			return new WP_Error( 'no_logs', '', array( 'status' => 404 ) );
		}

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=feedzy-log.jsonl' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Connection: Keep-Alive' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $this->filepath ) );

		readfile( $this->filepath );

		exit;
	}

	/**
	 * REST API endpoint to delete log file.
	 *
	 * @since 5.1.0
	 * @param WP_REST_Request<array<string, mixed>> $request The REST request.
	 * @return WP_Error|array<string, mixed>
	 */
	public function delete_log_file_endpoint( $request ) {
		if ( ! $request instanceof WP_REST_Request ) {
			return new WP_Error( 'invalid_request', '', array( 'status' => 400 ) );
		}

		if ( ! $this->log_file_exists() ) {
			return new WP_Error( 'no_logs', '', array( 'status' => 404 ) );
		}

		if ( $this->delete_log_file() ) {
			$this->reset_log_stats();
			return array( 'success' => true );
		}

		return new WP_Error( 'delete_failed', '', array( 'status' => 500 ) );
	}

	/**
	 * Get the size of the log file.
	 *
	 * @since 5.1.0
	 * @return bool|int
	 */
	public function get_log_file_size() {
		if ( ! file_exists( $this->filepath ) ) {
			return 0;
		}

		return $this->filesystem->size( $this->filepath );
	}

	/**
	 * Check if the log file is readable.
	 *
	 * @since 5.1.0
	 * @return bool Whether the log file is readable.
	 */
	public function is_log_readable() {
		if ( ! file_exists( $this->filepath ) ) {
			return false;
		}

		return $this->filesystem->is_readable( $this->filepath );
	}

	/**
	 * Check if the log file exists.
	 *
	 * @since 5.1.0
	 * @return bool Whether the log file exists.
	 */
	public function log_file_exists() {
		if ( ! file_exists( $this->filepath ) ) {
			return false;
		}

		return $this->filesystem->exists( $this->filepath );
	}

	/**
	 * Delete the log file.
	 *
	 * @since 5.1.0
	 * @return bool
	 */
	public function delete_log_file() {
		if ( file_exists( $this->filepath ) ) {
			return $this->filesystem->delete( $this->filepath );
		}
		return false;
	}

	/**
	 * Check if email reports can be sent.
	 *
	 * @since 5.1.0
	 * @return bool Whether email reports can be sent.
	 */
	public function can_send_email() {
		return $this->can_send_email && ! empty( $this->to_email );
	}

	/**
	 * Get the email address for reports.
	 *
	 * @since 5.1.0
	 * @return string The email address.
	 */
	public function get_email_address() {
		return $this->to_email;
	}

	/**
	 * Check if there are reportable logs or stats.
	 *
	 * @since 5.1.0
	 * @return bool Whether there are logs or stats to report.
	 */
	public function has_reportable_data() {
		$logs_entries = $this->get_recent_logs( 50, 'ERROR' );
		$stats        = get_option( self::STATS_OPTION_KEY, array() );

		return ( ! empty( $logs_entries ) && is_array( $logs_entries ) ) || ! empty( $stats );
	}

	/**
	 * Get error logs for email reports.
	 *
	 * @since 5.1.0
	 * @param int $limit The number of logs to retrieve.
	 * @return array<int, array<string, mixed>> The error log entries.
	 */
	public function get_error_logs_for_email( $limit = 50 ) {
		$recent_logs = $this->get_recent_logs( $limit, 'ERROR' );
		if ( is_wp_error( $recent_logs ) ) {
			return array();
		}
		return $recent_logs;
	}

	/**
	 * Get log statistics.
	 *
	 * @since 5.1.0
	 * @return array<string, mixed> The log statistics.
	 */
	public function get_log_statistics() {
		return get_option( self::STATS_OPTION_KEY, array() );
	}

	/**
	 * Increment a log statistic.
	 *
	 * @since 5.1.0
	 * @param string $stat_name The statistic name.
	 * @return void
	 */
	public function increment_log_stat( $stat_name ) {
		$stats = get_option( self::STATS_OPTION_KEY, array() );
		if ( ! isset( $stats[ $stat_name ] ) ) {
			$stats[ $stat_name ] = 0;
		}

		if ( ! isset( $stats['stats_since'] ) ) {
			$stats['stats_since'] = current_datetime()->format( DATE_ATOM );
		}

		++$stats[ $stat_name ];
		update_option( self::STATS_OPTION_KEY, $stats );
	}

	/**
	 * Reset log statistics.
	 *
	 * @since 5.1.0
	 * @return void
	 */
	public function reset_log_stats() {
		delete_option( self::STATS_OPTION_KEY );
	}

	/**
	 * Get the full path to the log file.
	 *
	 * @since 5.1.0
	 * @return string The full path to the log file.
	 */
	public function get_log_file_path() {
		$upload_dir = wp_upload_dir();
		$log_dir    = $upload_dir['basedir'] . '/feedzy-logs';
		return $log_dir . '/' . self::FILE_NAME . self::FILE_EXT;
	}

	/**
	 * Enable retention of error messages.
	 * 
	 * @return void
	 */
	public function enable_error_messages_retention() {
		$this->retain_error_messages = true;
	}

	/**
	 * Disable retention of error messages.
	 * 
	 * @return void
	 */
	public function disable_error_messages_retention() {
		$this->retain_error_messages      = false;
		$this->error_messages_accumulator = array();
	}

	/**
	 * Get the accumulated error messages.
	 * 
	 * @return string[]
	 */
	public function get_error_messages_accumulator() {
		return $this->error_messages_accumulator;
	}

	/**
	 * Add an error message to the accumulator.
	 * 
	 * @param string $message The error message to accumulate.
	 * @return void
	 */
	public function try_accumulate_error_message( $message ) {
		if ( ! $this->retain_error_messages ) {
			return;
		}

		if ( 200 <= count( $this->error_messages_accumulator ) ) {
			return;
		}

		$this->error_messages_accumulator[] = $message;
	}
}
