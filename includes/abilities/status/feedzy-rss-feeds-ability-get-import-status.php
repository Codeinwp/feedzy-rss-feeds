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
		return __( 'Return the last-run status for a Feedzy import job.', 'feedzy-rss-feeds' );
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

		return Feedzy_Rss_Feeds_Ability_Helpers::success(
			Feedzy_Rss_Feeds_Ability_Helpers::build_import_status( $post )
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function input_schema() {
		return array(
			'type'                 => 'object',
			'required'             => array( 'id' ),
			'properties'           => array(
				'id' => array(
					'type'    => 'integer',
					'minimum' => 1,
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
				'data'    => array(
					'type'       => 'object',
					'properties' => array(
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
