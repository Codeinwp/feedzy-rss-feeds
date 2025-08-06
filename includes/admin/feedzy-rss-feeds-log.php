<?php
/**
 * Feedzy_Rss_Feeds_Log - A JSON Logger for WordPress
 *
 * This file provides a JSON logging system with a facade pattern
 * that saves logs to a temporary folder in WordPress.
 *
 * @package Feedzy_Rss_Feeds_Log
 * @version 1.0.0
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
	 * Critical level.
	 *
	 * @var int Critical level.
	 */
	const CRITICAL = 500;

	/**
	 * Default log name.
	 *
	 * @var string Default log name.
	 */
	const DEFAULT_LOG_NAME = 'feedzy';

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
		self::DEBUG    => 'DEBUG',
		self::INFO     => 'INFO',
		self::WARNING  => 'WARNING',
		self::ERROR    => 'ERROR',
		self::CRITICAL => 'CRITICAL',
	);

	const PRIORITIES_MAPPING = array(
		'debug'   => self::DEBUG,
		'info'    => self::INFO,
		'warning' => self::WARNING,
		'error'   => self::ERROR,
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

	public $level_threshold = self::ERROR;

	public $can_send_email = false;
	public $to_email       = '';

	/**
	 * Feedzy_Rss_Feeds_Logger constructor.
	 */
	public function __construct() {
		$this->init_filesystem();
		$this->setup_log_directory( self::DEFAULT_LOG_NAME );
		$this->init_saved_settings();
	}

	/**
	 * Get the single instance of the class.
	 *
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
	 * @param string $log_name The name of the log file.
	 * @return void
	 */
	private function setup_log_directory( $log_name ) {
		$upload_dir = wp_upload_dir();
		$log_dir    = $upload_dir['basedir'] . '/feedzy-logs';

		if ( ! $this->filesystem->exists( $log_dir ) ) {
			$this->filesystem->mkdir( $log_dir, FS_CHMOD_DIR );
			$this->filesystem->put_contents( $log_dir . '/.htaccess', "Deny from all\n", FS_CHMOD_FILE );
			$this->filesystem->put_contents( $log_dir . '/index.php', "<?php // Silence is golden\n", FS_CHMOD_FILE );
		}

		$this->filepath = $log_dir . '/' . $log_name . '.jsonl';
	}

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
	 * @param int                  $level The log level.
	 * @param string               $message The log message.
	 * @param array<string, mixed> $context The log context.
	 * @return void
	 */
	private function write_log( $level, $message, array $context = array() ) {
		if ( ! $this->filesystem ) {
			return;
		}

		if ( $this->level_threshold > $level ) {
			return;
		}

		if ( self::ERROR <= $level ) {
			$this->increment_log_stat( 'error_count' );
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
			$this->setup_log_directory( basename( $this->filepath, '.jsonl' ) );
		}

		error_log( $formatted, 3, $this->filepath );
	}

	/**
	 * Log a debug message.
	 *
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
	 * @param string               $message The log message.
	 * @param array<string, mixed> $context The log context.
	 * @return void
	 */
	public static function error( $message, array $context = array() ) {
		self::log( self::ERROR, $message, $context );
	}

	/**
	 * Log a critical message.
	 *
	 * @param string               $message The log message.
	 * @param array<string, mixed> $context The log context.
	 * @return void
	 */
	public static function critical( $message, array $context = array() ) {
		self::log( self::CRITICAL, $message, $context );
	}

	/**
	 * Get all logs as raw entries.
	 *
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
	 * @param int         $limit The number of entries to return.
	 * @param string|null $level The log level to filter by.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_recent_logs( $limit = 100, $level = null ) {
		if ( ! file_exists( $this->filepath ) ) {
			return array();
		}

		$lines = $this->read_last_lines( $this->filepath, $limit * 2 ); // Read more lines to account for filtering.
		if ( empty( $lines ) ) {
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
	 * Read the last N lines from a file efficiently.
	 *
	 * @param string $filepath Path to the file.
	 * @param int    $lines_count The number of lines to read.
	 * @param int    $buffer_size The buffer size.
	 * @return array<string> The last N lines.
	 */
	private function read_last_lines( $filepath, $lines_count, $buffer_size = 4096 ) {
		$file = @fopen( $filepath, 'rb' );
		if ( false === $file ) {
			return array();
		}

		fseek( $file, 0, SEEK_END );
		$pos   = ftell( $file );
		$lines = array();
		$chunk = '';

		while ( $pos > 0 && count( $lines ) < $lines_count ) {
			$seek = min( $pos, $buffer_size );
			$pos -= $seek;
			fseek( $file, $pos, SEEK_SET );

			$read_chunk = fread( $file, $seek );
			if ( ! is_string( $read_chunk ) ) {
				break;
			}
			$chunk = $read_chunk . $chunk;

			$new_lines = explode( "\n", $chunk );
			if ( count( $new_lines ) > 1 ) {
				$lines = array_merge( array_slice( $new_lines, 1 ), $lines );
				$chunk = $new_lines[0];
			}
		}

		if ( $pos === 0 && ! empty( $chunk ) ) {
			$lines = array_merge( explode( "\n", $chunk ), $lines );
		}

		fclose( $file );

		// Trim empty values and get the last $lines_count lines.
		return array_slice(
			array_filter(
				$lines,
				function ( $line ) {
					return '' !== trim( $line );
				}
			),
			- $lines_count
		);
	}

	/**
	 * Clean old log files.
	 *
	 * @param int $days The number of days to keep log files.
	 * @return int The number of deleted files.
	 */
	public function clean_old_logs( $days = 30 ) {
		$log_dir = dirname( $this->filepath );
		$files   = $this->filesystem->dirlist( $log_dir );

		if ( ! $files ) {
			return 0;
		}

		$deleted = 0;
		$cutoff  = strtotime( "-{$days} days" );
		$prefix  = basename( $this->filepath, '.jsonl' );

		foreach ( $files as $file => $info ) {
			if ( strpos( $file, $prefix ) === 0 && strpos( $file, '.jsonl' ) !== false ) {
				$file_path = $log_dir . '/' . $file;
				if (
					$this->filesystem->mtime( $file_path ) < $cutoff || 
					$info['size'] > self::DEFAULT_MAX_FILE_SIZE
				) {
					if ( $this->filesystem->delete( $file_path ) ) {
						++$deleted;
					}
				}
			}
		}

		return $deleted;
	}

	/**
	 * Static convenience methods.
	 *
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

	public function export_logs_endpoint( $request ) {

		if ( ! file_exists( $this->filepath ) ) {
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

	public function delete_log_file_endpoint( $request ) {
		if ( ! file_exists( $this->filepath ) ) {
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
	 * @return bool|int
	 */
	public function get_log_file_size() {
		if ( ! file_exists( $this->filepath ) ) {
			return 0;
		}

		return $this->filesystem->size( $this->filepath );
	}

	/**
	 * Delete the log file.
	 *
	 * @return bool
	 */
	public function delete_log_file() {
		if ( file_exists( $this->filepath ) ) {
			return $this->filesystem->delete( $this->filepath );
		}
		return false;
	}

	public function try_to_send_email_report() {
		if ( ! $this->can_send_email || empty( $this->to_email ) ) {
			return false;
		}
		
		$logs_entries = $this->get_recent_logs( 50, 'ERROR' );
		$stats        = get_option( self::STATS_OPTION_KEY, array() );

		if ( empty( $logs_entries ) && empty( $stats ) ) {
			return false;
		}

		$message = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
			body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
			.header { background-color: #f4f4f4; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
			.stats { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
			.log-entry { background-color: #f8f9fa; border-left: 4px solid #dc3545; padding: 10px; margin-bottom: 10px; }
			.timestamp { color: #6c757d; font-size: 0.9em; }
			.level { font-weight: bold; color: #dc3545; }
			.context { color: #6c757d; font-style: italic; font-size: 0.9em; margin-top: 5px; }
			</style></head><body>';

		$message .= '<div class="header">';
		$message .= '<h2>' . sprintf( __( 'Feedzy RSS Feeds Log Report for %s', 'feedzy-rss-feeds' ), get_bloginfo( 'name' ) ) . '</h2>';
		$message .= '<p>' . sprintf( __( 'Generated on %s', 'feedzy-rss-feeds' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ) . '</p>';
		$message .= '</div>';

		if ( ! empty( $stats ) && isset( $stats['error_count'] ) && $stats['error_count'] > 0 ) {
			$stats['stats_since'] = isset( $stats['stats_since'] ) ? $stats['stats_since'] : current_datetime()->format( DATE_ATOM );
			$message             .= '<div class="stats">';
			$message             .= '<h3>' . __( 'Error Statistics', 'feedzy-rss-feeds' ) . '</h3>';
			$message             .= '<p>' . sprintf(
				__( '<strong>Errors logged:</strong> %1$s (since %2$s)', 'feedzy-rss-feeds' ),
				$stats['error_count'],
				date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $stats['stats_since'] ) )
			) . '</p>';
			$message             .= '</div>';
		}

		$message .= '<h3>' . __( 'Recent Log Entries', 'feedzy-rss-feeds' ) . '</h3>';
		
		if ( empty( $logs_entries ) ) {
			$message .= '<p>' . __( 'No recent error entries found.', 'feedzy-rss-feeds' ) . '</p>';
		} else {
			foreach ( $logs_entries as $entry ) {
				$message .= '<div class="log-entry">';
				$message .= '<div class="timestamp">' . esc_html( $entry['timestamp'] ) . '</div>';
				$message .= '<div><span class="level">[' . esc_html( $entry['level'] ) . ']</span> ' . esc_html( $entry['message'] ) . '</div>';
				if ( ! empty( $entry['context'] ) ) {
					$message .= '<div class="context">Context: ' . esc_html( wp_json_encode( $entry['context'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) . '</div>';
				}
				$message .= '</div>';
			}
		}
		
		$message .= '</body></html>';
		
		$subject = sprintf( 'Feedzy RSS Feeds Log Report for %s', get_bloginfo( 'name' ) );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$email_send = wp_mail( $this->to_email, $subject, $message, $headers );

		if ( $email_send ) {
			// $this->reset_log_stats();
		}

		return $email_send;
	}

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

	public function reset_log_stats() {
		delete_option( self::STATS_OPTION_KEY );
	}
}
