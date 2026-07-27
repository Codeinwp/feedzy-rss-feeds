<?php
/**
 * Plugin Name: Feedzy E2E Log Seeder
 * Description: Test-only REST endpoint that appends raw log entries to the Feedzy log file, so e2e tests can seed entries written by older plugin versions.
 *
 * @package feedzy-rss-feeds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'feedzy-e2e/v1',
			'/legacy-log',
			array(
				'methods'             => 'POST',
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'callback'            => function ( WP_REST_Request $request ) {
					if ( ! class_exists( 'Feedzy_Rss_Feeds_Log' ) ) {
						return new WP_Error( 'feedzy_missing', 'Feedzy logger is not available.', array( 'status' => 500 ) );
					}

					// Entries written before #1278 carried only `job_id` and `source` in their context.
					$entry = array(
						'timestamp' => gmdate( 'c' ),
						'level'     => 'error',
						'message'   => (string) ( $request->get_param( 'message' ) ? $request->get_param( 'message' ) : 'Legacy log entry seeded for e2e' ),
						'context'   => array(
							'job_id' => 123,
							'source' => 'https://example.com/legacy-feed.xml',
						),
					);

					Feedzy_Rss_Feeds_Log::get_instance()->append_log_to_file( $entry );

					return rest_ensure_response( array( 'seeded' => true ) );
				},
			)
		);
	}
);
