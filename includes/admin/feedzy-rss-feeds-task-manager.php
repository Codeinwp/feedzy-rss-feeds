<?php
/**
 * Register and manage scheduled tasks for Feedzy RSS Feeds.
 *
 * @package Feedzy_Rss_Feeds_Task_Manager
 * @version 5.1.0
 */

/**
 * Class Feedzy_Rss_Feeds_Task_Manager.
 */
class Feedzy_Rss_Feeds_Task_Manager {

	/**
	 * Register actions for scheduled tasks.
	 *
	 * @since 5.1.0
	 * @return void
	 */
	public function register_actions() {
		add_action(
			'feedzy_send_error_report',
			array( $this, 'send_error_report' )
		);
		
		add_action(
			'feedzy_cleanup_logs',
			function () {
				Feedzy_Rss_Feeds_Log::get_instance()->should_clean_logs();
			} 
		);

		add_action(
			'init',
			function () {
				$this->schedule_weekly_tasks();
				$this->schedule_daily_tasks();
			}
		);
	}

	/**
	 * Schedule weekly tasks.
	 *
	 * @since 5.1.0
	 * @return void
	 */
	public function schedule_weekly_tasks() {
		$log_instance = Feedzy_Rss_Feeds_Log::get_instance();
		
		if (
			! $log_instance->can_send_email() ||
			false !== Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'task_feedzy_send_error_report' )
		) {
			return;
		}
			
		Feedzy_Rss_Feeds_Util_Scheduler::schedule_event( time(), 'weekly', 'task_feedzy_send_error_report' );
	}

	/**
	 * Schedule daily tasks.
	 *
	 * @since 5.1.0
	 * @return void
	 */
	public function schedule_daily_tasks() {
		if (
			false !== Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'task_feedzy_cleanup_logs' )
		) {
			return;
		}

		Feedzy_Rss_Feeds_Util_Scheduler::schedule_event( time(), 'twicedaily', 'task_feedzy_cleanup_logs' );
	}

	/**
	 * Send error report email.
	 *
	 * @since 5.1.0
	 * @return void.
	 */
	public function send_error_report() {
		$log_instance = Feedzy_Rss_Feeds_Log::get_instance();
		
		if ( ! $log_instance->can_send_email() ) {
			return;
		}

		if ( ! $log_instance->has_reportable_data() ) {
			return;
		}

		$logs_entries = $log_instance->get_error_logs_for_email( 50 );
		$stats        = $log_instance->get_log_statistics();

		$message = $this->get_email_report_content( $logs_entries, $stats );
		
		$subject = sprintf( 'Feedzy RSS Feeds Log Report for %s', get_bloginfo( 'name' ) );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

        // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
		if ( wp_mail( $log_instance->get_email_address(), $subject, $message, $headers ) ) {
			$log_instance->reset_log_stats();
		}
	}

	/**
	 * Get the email report content using the layout template.
	 *
	 * @since 5.1.0
	 * @param array<int, array<string, mixed>> $logs_entries The log entries to include in the report.
	 * @param array<string, mixed>             $stats The log statistics.
	 * @return string The rendered email content.
	 */
	private function get_email_report_content( $logs_entries, $stats ) {
		// Prepare variables for the template
		$site_name      = get_bloginfo( 'name' );
		$generated_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

		// Start output buffering to capture the template output
		ob_start();
		
		// Include the email template
		include FEEDZY_ABSPATH . '/includes/layouts/feedzy-email-report.php';
		
		// Get the buffered content and clean the buffer
		$content = ob_get_clean();
		
		return $content;
	}
}
