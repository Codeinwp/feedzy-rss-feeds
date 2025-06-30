<?php
/**
 * The class that contains utility functions for scheduling tasks.
 *
 * @link       https://themeisle.com
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/util
 */

/**
 * The class that contains utility functions for scheduling tasks.
 *
 * Class that contains utility functions for scheduling tasks.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/util
 * @author     Themeisle <friends@themeisle.com>
 */
class Feedzy_Rss_Feeds_Util_Scheduler {

	/**
	 * Check if an action hook is scheduled.
	 *
	 * @param string $hook The hook to check.
	 * @param array  $args Optional. Arguments to pass to the hook.
	 *
	 * @return bool|int
	 */
	public static function is_scheduled( string $hook, array $args = array() ) {
		if ( function_exists( 'as_next_scheduled_action' ) ) {
			// For older versions of AS.
			return as_next_scheduled_action( $hook, $args );
		}
		if ( function_exists( 'as_has_scheduled_action' ) ) {
			return as_has_scheduled_action( $hook, $args );
		}

		return wp_next_scheduled( $hook, $args );
	}

	/**
	 * Clear scheduled hook.
	 *
	 * @param string $hook The name of the hook to clear.
	 * @param array  $args Optional. Arguments that were to be passed to the hook's callback function. Default empty array.
	 * @return mixed The scheduled action ID if a scheduled action was found, or null if no matching action found. If WP_Cron is used, on success an integer indicating number of events unscheduled, false or WP_Error if unscheduling one or more events fail.
	 */
	public static function clear_scheduled_hook( $hook, $args = array() ) {
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			return as_unschedule_all_actions( $hook, $args );
		}

		return wp_clear_scheduled_hook( $hook, $args );
	}

	/**
	 * Schedule an event.
	 *
	 * @param int    $time       The first time that the event will occur.
	 * @param string $recurrence How often the event should recur. See wp_get_schedules() for accepted values.
	 * @param string $hook       The name of the hook that will be triggered by the event.
	 * @param array  $args       Optional. Arguments to pass to the hook's callback function. Default empty array.
	 * @return integer|bool|WP_Error The action ID if Action Scheduler is used. True if event successfully scheduled, False or WP_Error on failure if WP Cron is used.
	 */
	public static function schedule_event( $time, $recurrence, $hook, $args = array() ) {
		if ( function_exists( 'as_schedule_recurring_action' ) ) {
			$schedules = wp_get_schedules();
			if ( isset( $schedules[ $recurrence ] ) ) {
				$interval = $schedules[ $recurrence ]['interval'];
				return as_schedule_recurring_action( $time, $interval, $hook, $args );
			}
		}

		return wp_schedule_event( $time, $recurrence, $hook, $args );
	}
}
