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
     * @var   array<string, string|int|bool>
     */
    private $default_data = array(
        'first_import_run_datetime'     => '',
        'imports_runs'                  => 0,
        'first_import_created_datetime' => '',
        'can_track_first_usage'         => false,
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
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize usage tracking option.
     *
     * @since 5.0.7
     */
    private function init() {
        if ( false === get_option(self::OPTION_NAME) ) {
            add_option(self::OPTION_NAME, $this->default_data);
        }
    }

    /**
     * Get usage data with defaults merged.
     *
     * @since  5.0.7
     * @return array<string, string|int|bool>
     */
    public function get_usage_data() {
        $data = get_option( self::OPTION_NAME, array() );
        return wp_parse_args( $data, $this->default_data );
    }

    /**
     * Update usage data.
     *
     * @since  5.0.7
     * @param  array<string, string|int|bool> $new_data Data to merge.
     * @return bool
     */
    public function update_usage_data( $new_data ) {
        $current_data = $this->get_usage_data();
        $updated_data = array_merge( $current_data, $new_data );
        return update_option(self::OPTION_NAME, $updated_data);
    }

    /**
     * Track RSS feed import runs.
     *
     * @since 5.0.7
     */
    public function track_rss_import() {
        $data = $this->get_usage_data();

        if ( PHP_INT_MAX <= $data['imports_runs'] ) {
            return;
        }

        $update_data = array();

        $update_data['imports_runs'] = $data['imports_runs'] + 1;

        if ( $data['can_track_first_usage'] && empty( $data['first_import_run_datetime'] ) ) {
            $update_data['first_import_run_datetime'] = current_time('mysql');
        }

        $this->update_usage_data( $update_data );
    }

    /**
     * Track first import creation timestamp.
     *
     * @since 5.0.7
     */
    public function track_import_creation() {
        $data = $this->get_usage_data();

        if ( $data['can_track_first_usage'] && ! empty( $data['first_import_created_datetime'] ) ) {
            return;
        }

        $this->update_usage_data( array( 'first_import_created_datetime' => current_time('mysql') ) );
    }

    /**
     * Delete usage data option.
     *
     * @since  5.0.7
     * @return bool
     */
    public function delete_usage_data() {
        return delete_option(self::OPTION_NAME);
    }

    /**
     * Get formatted usage statistics with calculated fields.
     *
     * @since  5.0.7
     * @return array<string, string|int>
     */
    public function get_usage_stats() {
        $data = $this->get_usage_data();

        $stats = array(
            'import_runs' => $data['imports_runs'],
        );

        if ( ! $data['can_track_first_usage'] ) {
            return $data;
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
}
