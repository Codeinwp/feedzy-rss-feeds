<?php
/**
 * Ability: feedzy/run-import
 *
 * Queues a Feedzy import job to run in the background through the import cron hook.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/imports
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_Run_Import
 */
class Feedzy_Rss_Feeds_Ability_Run_Import extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'run-import';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'Run Import Job', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Queues a Feedzy import job to run now in the background (through the same cron hook the scheduled imports use) and returns a job_id. Poll feedzy/get-import-status with that job_id until state is completed or failed. Set wait to true to run the import inside this call instead.', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array<string, mixed>|null $input Input arguments, validated and sanitized according to `input_schema()`.
	 *
	 * @return array<string, mixed> Output data, validated and sanitized according to `output_schema()`.
	 */
	public function execute_callback( ?array $input = null ) {
		if ( empty( $input['id'] ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_invalid_input',
				sprintf(
					/* translators: %s is the name of the required input field */
					__( '"%s" is required.', 'feedzy-rss-feeds' ),
					'id'
				)
			);
		}

		$post_id = (int) $input['id'];
		$post    = get_post( $post_id );

		if ( ! $post || 'feedzy_imports' !== $post->post_type ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_not_found',
				/* translators: %d: import job ID */
				sprintf( __( 'Import job with ID %d was not found.', 'feedzy-rss-feeds' ), $post_id )
			);
		}

		if ( 'publish' !== $post->post_status ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_job_not_active',
				/* translators: %d: import job ID */
				sprintf(
					/* translators: %s is the post status, e.g. "draft" */
					__( 'Import job with ID %1$d is not active (current status: "%2$s").', 'feedzy-rss-feeds' ),
					$post_id,
					$post->post_status
				)
			);
		}

		$max = ! empty( $input['max'] ) ? (int) $input['max'] : (int) get_post_meta( $post_id, 'import_feed_limit', true );
		if ( $max < 1 ) {
			$max = 100;
		}

		if ( ! class_exists( 'Feedzy_Rss_Feeds_Import' ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_dependency_missing',
				__( 'Ensure that the free version of Feedzy RSS Feeds plugin is installed and activated.', 'feedzy-rss-feeds' )
			);
		}

		$queued_at = time();
		$job_id    = Feedzy_Rss_Feeds_Ability_Helpers::encode_run_reference( $post_id, $queued_at, $max );

		if ( empty( $input['wait'] ) ) {
			return $this->queue_run( $post_id, $max, $job_id );
		}

		$import_class = new Feedzy_Rss_Feeds_Import( Feedzy_Rss_Feeds::get_plugin_name(), Feedzy_Rss_Feeds::get_version() );

		if ( class_exists( 'Feedzy_Rss_Feeds_Log' ) ) {
			Feedzy_Rss_Feeds_Log::get_instance()->enable_error_messages_retention();
		}

		if ( ! method_exists( $import_class, 'run_cron' ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_dependency_outdated',
				__( 'Please update the free version of Feedzy RSS Feeds plugin to the latest version.', 'feedzy-rss-feeds' )
			);
		}
		$import_class->run_cron( $max, $post_id );

		$errors = array();
		if ( class_exists( 'Feedzy_Rss_Feeds_Log' ) ) {
			$errors = Feedzy_Rss_Feeds_Log::get_instance()->get_error_messages_accumulator();
			Feedzy_Rss_Feeds_Log::get_instance()->disable_error_messages_retention();
		}

		// Read post-run status to determine how many items were imported.
		$items_count = (int) get_post_meta( $post_id, 'imported_items_count', true );

		$import_success = empty( $errors );
		$message        = $import_success
			/* translators: %d: number of imported items */
			? sprintf( __( 'Import run completed. %d items imported.', 'feedzy-rss-feeds' ), $items_count )
			: __( 'Import run completed with errors.', 'feedzy-rss-feeds' );

		if ( class_exists( 'Feedzy_Rss_Feeds_Log' ) ) {
			Feedzy_Rss_Feeds_Log::info(
				'Ability feedzy/run-import: finished job ID ' . $post_id,
				array(
					'post_id'        => $post_id,
					'imported_count' => $items_count,
					'errors'         => $errors,
				)
			);
		}

		$response           = Feedzy_Rss_Feeds_Ability_Helpers::success(
			array(
				'queued'         => false,
				'run_id'         => (int) get_post_meta( $post_id, 'last_run_id', true ),
				'imported'       => $items_count,
				'import_success' => $import_success,
				'message'        => $message,
				'errors'         => array_values( $errors ),
			)
		);
		$response['job_id'] = $job_id;

		return $response;
	}

	/**
	 * Queue the run on the import cron hook, the same one the scheduled imports fire.
	 *
	 * @param  int    $post_id Import job ID.
	 * @param  int    $max     The import feed limit passed to the cron hook.
	 * @param  string $job_id  Run reference returned to the caller.
	 *
	 * @return array<string, mixed>
	 */
	private function queue_run( int $post_id, int $max, string $job_id ) {
		$args = array( $max, $post_id );

		if ( ! has_action( 'feedzy_cron' ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_dependency_missing',
				__( 'The Feedzy import feature is not available.', 'feedzy-rss-feeds' )
			);
		}

		if ( function_exists( 'as_schedule_single_action' ) ) {
			$scheduled = as_schedule_single_action( time(), 'feedzy_cron', $args );
		} else {
			$scheduled = wp_schedule_single_event( time(), 'feedzy_cron', $args, true );
		}

		// An event with the same arguments (the job's own schedule) that is due right away also runs this job.
		$already_due = ( empty( $scheduled ) || is_wp_error( $scheduled ) ) && false !== Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'feedzy_cron', $args );

		if ( ( empty( $scheduled ) || is_wp_error( $scheduled ) ) && ! $already_due ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_schedule_failed',
				is_wp_error( $scheduled ) ? $scheduled->get_error_message() : __( 'The import run could not be scheduled.', 'feedzy-rss-feeds' )
			);
		}

		if ( ! function_exists( 'as_schedule_single_action' ) ) {
			spawn_cron();
		}

		if ( class_exists( 'Feedzy_Rss_Feeds_Log' ) ) {
			Feedzy_Rss_Feeds_Log::info(
				'Ability feedzy/run-import: queued job ID ' . $post_id,
				array(
					'post_id' => $post_id,
				)
			);
		}

		$response           = Feedzy_Rss_Feeds_Ability_Helpers::success(
			array(
				'queued'  => true,
				'message' => __( 'Import run queued. Poll feedzy/get-import-status with the returned job_id.', 'feedzy-rss-feeds' ),
			)
		);
		$response['job_id'] = $job_id;

		return $response;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function input_schema() {
		return array(
			'type'                 => 'object',
			'required'             => array( 'id' ),
			'properties'           => array(
				'id'   => array(
					'type'        => 'integer',
					'description' => __( 'Numeric post ID of the import job to run.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
				),
				'max'  => array(
					'type'        => 'integer',
					'description' => __( 'Maximum number of items to process in this run. Defaults to the job\'s own limit.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
					'maximum'     => 9999,
				),
				'wait' => array(
					'type'        => 'boolean',
					'description' => __( 'Run the import inside this call instead of queueing it. Can take minutes. Default false.', 'feedzy-rss-feeds' ),
					'default'     => false,
				),
			),
			'additionalProperties' => false,
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function output_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'success' => array( 'type' => 'boolean' ),
				'job_id'  => array(
					'type'        => 'string',
					'description' => __( 'Reference of this run. Pass it to feedzy/get-import-status as job_id.', 'feedzy-rss-feeds' ),
				),
				'data'    => array(
					'type'       => 'object',
					'properties' => array(
						'queued'         => array(
							'type'        => 'boolean',
							'description' => __( 'True when the run was queued in the background; the remaining fields are then reported by feedzy/get-import-status.', 'feedzy-rss-feeds' ),
						),
						'run_id'         => array(
							'type'        => 'integer',
							'description' => __( 'Run identifier accepted by feedzy/list-imported-items. Only when wait is true.', 'feedzy-rss-feeds' ),
						),
						'imported'       => array(
							'type'        => 'integer',
							'description' => __( 'Number of newly imported items. Only when wait is true.', 'feedzy-rss-feeds' ),
						),
						'import_success' => array( 'type' => 'boolean' ),
						'message'        => array( 'type' => 'string' ),
						'errors'         => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
					),
				),
			),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function meta() {
		$meta                = parent::meta();
		$meta['annotations'] = array(
			'readonly'    => false,
			'destructive' => false,
			'idempotent'  => false,
		);
		$meta['task']        = array(
			'mode'           => 'poll',
			'status_ability' => 'feedzy/get-import-status',
		);
		return $meta;
	}
}
