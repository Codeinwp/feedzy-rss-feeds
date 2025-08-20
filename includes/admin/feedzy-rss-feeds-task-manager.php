<?php
/**
 * Register and manage scheduled tasks for Feedzy RSS Feeds.
 *
 * @package Feedzy_Rss_Feeds_Task_Manager
 */

/**
 * Class Feedzy_Rss_Feeds_Task_Manager.
 * 
 * @since 5.1.0
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
			'task_feedzy_send_error_report',
			array( $this, 'send_error_report' )
		);

		add_action(
			'update_option_feedzy-settings',
			array( $this, 'maybe_reschedule_email_report' ),
			10,
			2
		);
		
		add_action(
			'task_feedzy_cleanup_logs',
			array( $this, 'check_and_clean_logs' )
		);

		add_action(
			'init',
			function () {
				$this->schedule_tasks();
			}
		);
	}

	/**
	 * Schedule weekly tasks.
	 *
	 * @since 5.1.0
	 * @return void
	 */
	public function schedule_tasks() {
		if (
			false === Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'task_feedzy_cleanup_logs' )
		) {
			Feedzy_Rss_Feeds_Util_Scheduler::schedule_event( time(), 'hourly', 'task_feedzy_cleanup_logs' );
		}
		$this->schedule_email_report();
	}

	/**
	 * Schedule daily tasks.
	 *
	 * @since 5.1.0
	 * @return void
	 */
	public function schedule_email_report() {
		$log_instance = Feedzy_Rss_Feeds_Log::get_instance();
		
		if (
			! $log_instance->can_send_email() ||
			false !== Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'task_feedzy_send_error_report' )
		) {
			return;
		}

		$frequency = $log_instance->email_frequency;
		if ( ! in_array( $frequency, array( 'daily', 'weekly' ), true ) ) {
			$frequency = 'weekly';
		}

		Feedzy_Rss_Feeds_Util_Scheduler::schedule_event( time(), $frequency, 'task_feedzy_send_error_report' );
	}

	/**
	 * When feedzy settings are updated, ensure the error report schedule matches the new frequency.
	 *
	 * @since 5.1.0
	 * @param array<string, mixed> $old_value Previous option value.
	 * @param array<string, mixed> $value     New option value.
	 * @return void
	 */
	public function maybe_reschedule_email_report( $old_value, $value ) {
		if ( ! isset( $value['logs'], $value['logs']['send_email_report'] ) || ! boolval( $value['logs']['send_email_report'] ) ) {
			Feedzy_Rss_Feeds_Util_Scheduler::clear_scheduled_hook( 'task_feedzy_send_error_report' );
			return;
		}

		$old_freq = isset( $old_value['logs']['email_frequency'] ) ? sanitize_text_field( $old_value['logs']['email_frequency'] ) : '';
		$new_freq = isset( $value['logs']['email_frequency'] ) ? sanitize_text_field( $value['logs']['email_frequency'] ) : '';

		if ( $old_freq === $new_freq ) {
			return;
		}
		
		Feedzy_Rss_Feeds_Util_Scheduler::clear_scheduled_hook( 'task_feedzy_send_error_report' );
		
		if ( ! in_array( $new_freq, array( 'daily', 'weekly' ), true ) ) {
			$new_freq = 'weekly';
		}
		
		$send_reports = ! empty( $value['logs']['send_email_report'] );
		$to_email     = isset( $value['logs']['email'] ) ? sanitize_email( $value['logs']['email'] ) : '';

		if ( $send_reports && ! empty( $to_email ) ) {
			Feedzy_Rss_Feeds_Util_Scheduler::schedule_event( time(), $new_freq, 'task_feedzy_send_error_report' );
		}
	}

	/**
	 * Check and clean logs if necessary.
	 * 
	 * @since 5.1.0
	 * @return void
	 */
	public function check_and_clean_logs() {
		Feedzy_Rss_Feeds_Log::get_instance()->should_clean_logs();
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
