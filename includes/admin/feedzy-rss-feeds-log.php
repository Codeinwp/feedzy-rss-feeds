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
	 * Feedzy_Rss_Feeds_Logger constructor.
	 */
	public function __construct() {
		$this->init_filesystem();
		$this->setup_log_directory( self::DEFAULT_LOG_NAME );
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

		ray( 'Reading logs from: ' . $this->filepath, $level );

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
}
