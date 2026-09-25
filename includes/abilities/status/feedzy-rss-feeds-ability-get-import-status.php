<?php
/**
 * Ability: feedzy/get-import-status
 *
 * Returns the last-run status for a Feedzy import job identified by "id".
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/status
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_Get_Import_Status
 */
class Feedzy_Rss_Feeds_Ability_Get_Import_Status extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'get-import-status';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'Get Import Status', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Return the last-run status for a Feedzy import job. Pass the job_id returned by feedzy/run-import to get the state and progress of that run; call again until state is no longer working.', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array<string, mixed>|null $input Input arguments, validated and sanitized according to `input_schema()`.
	 *
	 * @return array<string, mixed> Output data, validated and sanitized according to `output_schema()`.
	 */
	public function execute_callback( ?array $input = null ) {
		$run = null;
		if ( ! empty( $input['job_id'] ) ) {
			$run = Feedzy_Rss_Feeds_Ability_Helpers::decode_run_reference( (string) $input['job_id'] );
			if ( is_wp_error( $run ) ) {
				return Feedzy_Rss_Feeds_Ability_Helpers::from_wp_error( $run );
			}
			$input['id'] = $run['post_id'];
		}

		if ( empty( $input['id'] ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_invalid_input',
				sprintf(
					/* translators: %1$s and %2$s are the names of the input fields */
					__( 'Provide either "%1$s" or "%2$s".', 'feedzy-rss-feeds' ),
					'id',
					'job_id'
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

		$response = Feedzy_Rss_Feeds_Ability_Helpers::success(
			Feedzy_Rss_Feeds_Ability_Helpers::build_import_status( $post )
		);

		if ( null !== $run ) {
			$run_state                  = Feedzy_Rss_Feeds_Ability_Helpers::build_run_state( $post_id, $run['queued_at'], $run['max'] );
			$response['state']          = $run_state['state'];
			$response['progress']       = $run_state['progress'];
			$response['data']['run_id'] = $run_state['run_id'];
		}

		return $response;
	}

	/**
	 * {@inheritdoc}
	 *
	 * Applies the object-level check to the import job referenced by a run `job_id`.
	 *
	 * @param mixed $input Ability input.
	 *
	 * @return true|WP_Error
	 */
	public function check_permission( $input = null ) {
		if ( is_array( $input ) && ! empty( $input['job_id'] ) && is_string( $input['job_id'] ) ) {
			$run = Feedzy_Rss_Feeds_Ability_Helpers::decode_run_reference( $input['job_id'] );
			if ( ! is_wp_error( $run ) ) {
				$input['id'] = $run['post_id'];
			}
			unset( $input['job_id'] );
		}

		return parent::check_permission( $input );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function input_schema() {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'id'     => array(
					'type'        => 'integer',
					'description' => __( 'Numeric post ID of the import job. Required unless job_id is given.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
				),
				'job_id' => array(
					'type'        => 'string',
					'description' => __( 'Run reference returned by feedzy/run-import. When given, the response also reports the state and progress of that run.', 'feedzy-rss-feeds' ),
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
				'success'  => array( 'type' => 'boolean' ),
				'state'    => array(
					'type'        => 'string',
					'description' => __( 'State of the run referenced by job_id. Only when job_id is given.', 'feedzy-rss-feeds' ),
					'enum'        => array( 'working', 'completed', 'failed', 'cancelled' ),
				),
				'progress' => array(
					'type'        => 'object',
					'description' => __( 'Progress of the run referenced by job_id. total is 0 while the number of items is unknown.', 'feedzy-rss-feeds' ),
					'properties'  => array(
						'current' => array( 'type' => 'integer' ),
						'total'   => array( 'type' => 'integer' ),
						'message' => array( 'type' => 'string' ),
					),
				),
				'data'     => array(
					'type'       => 'object',
					'properties' => array(
						'run_id'           => array(
							'type'        => 'integer',
							'description' => __( 'Run identifier accepted by feedzy/list-imported-items. Only when job_id is given; 0 until the run starts.', 'feedzy-rss-feeds' ),
						),
						'job_id'           => array( 'type' => 'integer' ),
						'job_title'        => array( 'type' => 'string' ),
						'job_status'       => array( 'type' => 'string' ),
						'last_run'         => array( 'type' => array( 'string', 'null' ) ),
						'last_run_found'   => array( 'type' => 'integer' ),
						'last_run_total'   => array( 'type' => 'integer' ),
						'last_duplicates'  => array( 'type' => 'integer' ),
						'cumulative_total' => array( 'type' => 'integer' ),
						'errors'           => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
						'per_feed'         => array(
							'type'        => 'array',
							'description' => __( 'Per-URL breakdown for multi-feed jobs.', 'feedzy-rss-feeds' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'url'    => array( 'type' => 'string' ),
									'errors' => array(
										'type'  => 'array',
										'items' => array( 'type' => 'string' ),
									),
								),
							),
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
			'readonly'    => true,
			'destructive' => false,
			'idempotent'  => true,
		);
		return $meta;
	}
}
