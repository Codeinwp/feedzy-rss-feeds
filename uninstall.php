<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * @link       https://themeisle.com
 * @since      3.0.0
 *
 * @package    Feedzy_Rss_Feeds
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


// clean up after ourselves, that's a good plugin!
delete_option( 'feedzy_rss_feeds_logger_flag' );
delete_option( 'feedzy_logger_flag' );
delete_option( 'feedzy-settings' );
delete_option( 'feedzy-rss-feeds' );
delete_option( 'feedzy_fresh_install' );
delete_option( 'feedzy_wizard_data' );
delete_option( 'feedzy_usage' );

/**
 * Clear scheduled hook.
 *
 * @param string $hook The name of the hook to clear.
 * @param array  $args Optional. Arguments that were to be passed to the hook's callback function. Default empty array.
 * @return mixed The scheduled action ID if a scheduled action was found, or null if no matching action found. If WP_Cron is used, on success an integer indicating number of events unscheduled, false or WP_Error if unscheduling one or more events fail.
 */
function clear_scheduled_hook( $hook, $args = array() ) {
	if ( function_exists( 'as_unschedule_all_actions' ) ) {
		return as_unschedule_all_actions( $hook, $args );
	}

	return wp_clear_scheduled_hook( $hook, $args );
}

clear_scheduled_hook( 'feedzy_rss_feeds_log_activity' );

clear_scheduled_hook( 'feedzy_cron' );

clear_scheduled_hook( 'task_feedzy_cleanup_logs' );

clear_scheduled_hook( 'task_feedzy_send_error_report' );

// Remove import jobs based cron jobs.
$import_job_crons = get_posts(
	array(
		'post_type'   => 'feedzy_imports',
		'post_status' => 'publish',
		'numberposts' => 99,
		'fields'      => 'ids',
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query'  => array(
			'relation' => 'AND',
			array(
				'key'     => 'fz_cron_schedule',
				'compare' => 'EXISTS',
			),
		),
	)
);


if ( ! empty( $import_job_crons ) ) {

	foreach ( $import_job_crons as $job_id ) {
		$fz_cron_schedule = get_post_meta( $job_id, 'fz_cron_schedule', true );
		clear_scheduled_hook( 'feedzy_cron', array( 100, $job_id ) );
	}
}
