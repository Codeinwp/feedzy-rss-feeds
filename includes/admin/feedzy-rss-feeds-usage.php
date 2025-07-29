<?php
/**
 * Track the usage of the plugin.
 *
 * @link       https://themeisle.com
 * @since      5.0.7
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
 */

/**
 * Track the usage of the plugin.
 *
 * Implements Singleton pattern to track usage metrics for analytics.
 *
 * @since      5.0.7
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
 * @author     Themeisle <friends@themeisle.com>
 */
class Feedzy_Rss_Feeds_Usage {

	/**
	 * Option name in wp_options table.
	 *
	 * @since 5.0.7
	 * @var   string
	 */
	const OPTION_NAME = 'feedzy_usage';

	/**
	 * Singleton instance.
	 *
	 * @since 5.0.7
	 * @var   Feedzy_Rss_Feeds_Usage|null
	 */
	private static $instance = null;

	/**
	 * Default usage data structure.
	 *
	 * @since 5.0.7
	 * @var   array{ first_import_run_datetime: string, first_import_created_datetime: string, import_count: int, 'can_track_first_usage': bool, imports_per_week: array<string, int> }
	 */
	private $default_data = array(
		'first_import_run_datetime'          => '',
		'import_count'                       => 0,
		'plugin_age_on_first_import_created' => 0,
		'first_import_created_datetime'      => '',
		'can_track_first_usage'              => false,
		'imports_per_week'                   => [],
	);

	/**
	 * Initialize usage tracking.
	 *
	 * @since 5.0.7
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Prevent cloning.
	 *
	 * @since 5.0.7
	 */
	public function __clone() {}

	/**
	 * Prevent unserialization.
	 *
	 * @since 5.0.7
	 */
	public function __wakeup() {}

	/**
	 * Get singleton instance.
	 *
	 * @since  5.0.7
	 * @return Feedzy_Rss_Feeds_Usage
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize usage tracking option.
	 * 
	 * @return void
	 *
	 * @since 5.0.7
	 */
	private function init() {
		if ( false === get_option( self::OPTION_NAME ) ) {
			add_option( self::OPTION_NAME, $this->default_data );
		}
	}

	/**
	 * Get usage data with defaults merged.
	 *
	 * @since  5.0.7
	 * @return array{ first_import_run_datetime: string, first_import_created_datetime: string, import_count: int, can_track_first_usage: bool, imports_per_week: array<string, int> }
	 */
	public function get_usage_data() {
		$data = get_option( self::OPTION_NAME, array() );
		return wp_parse_args( $data, $this->default_data );
	}

	/**
	 * Update usage data.
	 *
	 * @since  5.0.7
	 * @param  array<string, string|int|bool|array<string, int>> $new_data Data to merge.
	 * @return bool
	 */
	public function update_usage_data( $new_data ) {
		$current_data = $this->get_usage_data();
		$updated_data = array_merge( $current_data, $new_data );
		return update_option( self::OPTION_NAME, $updated_data );
	}

	/**
	 * Track RSS feed import runs.
	 * 
	 * @return void
	 *
	 * @since 5.0.7
	 */
	public function track_rss_import() {
		$data = $this->get_usage_data();
		$this->record_import_per_week();

		if ( PHP_INT_MAX <= $data['import_count'] ) {
			return;
		}

		$update_data = array();

		$update_data['import_count'] = $data['import_count'] + 1;

		if ( $data['can_track_first_usage'] && empty( $data['first_import_run_datetime'] ) ) {
			$update_data['first_import_run_datetime'] = current_time( 'mysql' );
		}

		$this->update_usage_data( $update_data );
	}

	/**
	 * Track first import creation timestamp.
	 * 
	 * @return void
	 *
	 * @since 5.0.7
	 */
	public function track_import_creation() {
		$data = $this->get_usage_data();

		if ( $data['can_track_first_usage'] && ! empty( $data['first_import_created_datetime'] ) ) {
			return;
		}

		$this->update_usage_data( array( 'first_import_created_datetime' => current_time( 'mysql' ) ) );
	}

	/**
	 * Delete usage data option.
	 *
	 * @since  5.0.7
	 * @return bool
	 */
	public function delete_usage_data() {
		return delete_option( self::OPTION_NAME );
	}

	/**
	 * Get formatted usage statistics with calculated fields.
	 *
	 * @since  5.0.7
	 * @return array{ first_import_run_datetime?: string, first_import_created_datetime?: string, import_count: int, imports_per_week: array<array{year: int, week: int, count: int}>, time_between_first_import_created_and_run?: int }
	 */
	public function get_usage_stats() {
		$data = $this->get_usage_data();

		$stats = array(
			'import_count'     => $data['import_count'],
			'imports_per_week' => array(),
		);

		if ( ! empty( $data['imports_per_week'] ) ) {

			/**
			 * Format the import into friendly structure for MongoDB.
			 * 
			 * @var array<array{year: int, week: int, count: int}>
			 */
			$formatted_imports = array();

			foreach ( $data['imports_per_week'] as $key => $count ) {
				// Parse the ISO week format manually (e.g., "2025-W31")
				if ( ! preg_match( '/^(\d{4})-W(\d{1,2})$/', $key, $matches ) ) {
					continue;
				}

				$year = (int) $matches[1];
				$week = (int) $matches[2];

				$formatted_imports[] = array(
					'year'  => $year,
					'week'  => $week,
					'count' => (int) $count,
				);
			}

			// Sort in chronological order by year and week.
			usort(
				$formatted_imports,
				function ( $a, $b ) {
					if ( $a['year'] !== $b['year'] ) {
						return $a['year'] - $b['year'];
					}
					return $a['week'] - $b['week'];
				}
			);
			$stats['imports_per_week'] = $formatted_imports;
		}
		
		if ( ! $data['can_track_first_usage'] ) {
			return $stats;
		}

		$stats['first_import_run_datetime']     = ! empty( $data['first_import_run_datetime'] ) ? $data['first_import_run_datetime'] : 'Never';
		$stats['first_import_created_datetime'] = ! empty( $data['first_import_created_datetime'] ) ? $data['first_import_created_datetime'] : 'Never';

		// Calculate time between first import run and first import created if applicable.
		if (
			! empty( $data['first_import_run_datetime'] ) &&
			! empty( $data['first_import_created_datetime'] )
		) {
			$import_time   = strtotime( $data['first_import_run_datetime'] );
			$settings_time = strtotime( $data['first_import_created_datetime'] );
			if (
				( false !== $import_time && false !== $settings_time ) &&
				$settings_time <= $import_time
			) {
				$stats['time_between_first_import_created_and_run'] = $import_time - $settings_time;
			}
		}

		return $stats;
	}

	/**
	 * Record the import count per year week.
	 * 
	 * @return void
	 * 
	 * @since 5.0.8
	 */
	public function record_import_per_week() {
		$datetime = current_datetime();
		$key      = $datetime->format( 'o-\WW' );

		$imports_per_week = array();
		$data             = $this->get_usage_data();
		if ( is_array( $data['imports_per_week'] ) ) {
			$imports_per_week = $data['imports_per_week'];
		}

		if ( array_key_exists( $key, $imports_per_week ) ) {
			$imports_per_week[ $key ] += 1;
		} else {
			$imports_per_week[ $key ] = 1;
		}

		if ( 120 < count( $imports_per_week ) ) {
			$imports_per_week = $this->remove_old_import_records( $imports_per_week, $datetime );
		}

		$this->update_usage_data( array( 'imports_per_week' => $imports_per_week ) );
	}

	/**
	 * Check if user installed plugin within last day.
	 *
	 * @since  5.0.7
	 * @return bool
	 */
	public function is_new_user() {
		$install_time = get_option( 'feedzy_rss_feeds_install', false );

		if ( ! is_numeric( $install_time ) ) {
			return true;
		}

		return DAY_IN_SECONDS >= ( time() - $install_time );
	}

	/**
	 * Remove the records older than two years.
	 * 
	 * @param array<string, int> $imports_per_week The imports per week data.
	 * @param DateTimeImmutable  $datetime The current datetime.
	 * @return array<string, int>
	 */
	public function remove_old_import_records( $imports_per_week, $datetime ) {
		$cutoff_date_time = ( clone $datetime )->modify( '-2 years' );

		return array_filter(
			$imports_per_week,
			function ( $key ) use ( $cutoff_date_time ) {
				// Parse the ISO week format manually (e.g., "2025-W31")
				if ( ! preg_match( '/^(\d{4})-W(\d{1,2})$/', $key, $matches ) ) {
					return false;
				}

				$year = (int) $matches[1];
				$week = (int) $matches[2];

				// Create a datetime for the last day of that week (Sunday)
				$record_datetime = new DateTime();
				$record_datetime->setISODate( $year, $week, 7 );

				return $record_datetime >= $cutoff_date_time;
			},
			ARRAY_FILTER_USE_KEY 
		);
	}
}
