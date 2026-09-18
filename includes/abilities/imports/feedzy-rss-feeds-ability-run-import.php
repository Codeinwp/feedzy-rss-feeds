<?php
/**
 * Ability: feedzy/run-import
 *
 * Triggers a Feedzy import job immediately on demand.
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
		return __( 'Triggers a Feedzy import job immediately on demand.', 'feedzy-rss-feeds' );
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

		return Feedzy_Rss_Feeds_Ability_Helpers::success(
			array(
				'imported'       => $items_count,
				'import_success' => $import_success,
				'message'        => $message,
				'errors'         => array_values( $errors ),
			)
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
				'id'  => array(
					'type'        => 'integer',
					'description' => __( 'Numeric post ID of the import job to run.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
				),
				'max' => array(
					'type'        => 'integer',
					'description' => __( 'Maximum number of items to process in this run. Defaults to the job\'s own limit.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
					'maximum'     => 9999,
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
						'imported'       => array(
							'type'        => 'integer',
							'description' => __( 'Number of newly imported items.', 'feedzy-rss-feeds' ),
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
		return $meta;
	}
}
