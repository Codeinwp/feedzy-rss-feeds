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
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
 * @author     Themeisle <friends@themeisle.com>
 */
class Feedzy_Rss_Feeds_Usage {

    /**
     * Option name in wp_options table.
     */
    const OPTION_NAME = 'feedzy_usage';

    /**
     * The single instance of the class.
     *
     * @var Feedzy_Rss_Feeds_Usage|null
     */
    private static $instance = null;

    /**
     * Default usage data structure.
     *
     * @var array<string, string|int>
     */
    private $default_data = array(
        'first_import_run_datetime'     => '',
        'imports_runs'                  => 0,
        'first_import_created_datetime' => '',
        'can_track_first_usage'         => false,
    );

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Prevent cloning of the instance.
     */
    public function __clone() {}

    /**
     * Prevent unserialization of the instance.
     */
    public function __wakeup() {}

    /**
     * Get the single instance of the class.
     *
     * @return Feedzy_Rss_Feeds_Usage
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the usage tracking.
     * Creates the option if it doesn't exist.
     */
    private function init() {
        if ( false === get_option(self::OPTION_NAME) ) {
            add_option(self::OPTION_NAME, $this->default_data);
        }
    }

    /**
     * Get all usage data.
     *
     * @return array<string, string|int> Usage data array.
     */
    public function get_usage_data() {
        $data = get_option( self::OPTION_NAME, array() );
        return wp_parse_args( $data, $this->default_data );
    }

    /**
     * Update usage data.
     *
     * @param array<string, string|int> $new_data Data to update.
     * @return bool True if the option was updated, false otherwise.
     */
    public function update_usage_data( $new_data ) {
        $current_data = $this->get_usage_data();
        $updated_data = array_merge( $current_data, $new_data );
        return update_option(self::OPTION_NAME, $updated_data);
    }

    /**
     * Track RSS feed import.
     * Sets first import timestamp if it's the first import, always increments counter.
     *
     * @return void
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
     * Track settings page creation.
     * Sets first settings page timestamp if it's the first page, always increments counter.
     *
     * @return void
     */
    public function track_import_creation() {
        $data = $this->get_usage_data();

        if ( $data['can_track_first_usage'] && ! empty( $data['first_import_created_datetime'] ) ) {
            return;
        }

        $this->update_usage_data( array( 'first_import_created_datetime' => current_time('mysql') ) );
    }

    /**
     * Delete the usage data option.
     * Useful for plugin uninstall.
     *
     * @return bool True if the option was deleted, false otherwise.
     */
    public function delete_usage_data() {
        return delete_option(self::OPTION_NAME);
    }

    /**
     * Get usage statistics in a formatted array.
     *
     * @return array<string, string|int> Formatted usage statistics.
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
     * Check if the user is new to track the first usage.
     *
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
