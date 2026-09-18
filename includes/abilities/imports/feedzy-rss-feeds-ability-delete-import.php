<?php
/**
 * Ability: feedzy/delete-import
 *
 * Deletes a Feedzy import job identified by "id". This action is irreversible and will remove the import job and all its configuration permanently from the database. Use with caution.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/imports
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_Delete_Import
 */
class Feedzy_Rss_Feeds_Ability_Delete_Import extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'delete-import';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'Delete Import Job', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Permanently deletes a Feedzy import job. This action is irreversible and will remove the import job and all its configuration permanently from the database.', 'feedzy-rss-feeds' );
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

		// Clear any cron hooks before deleting.
		if ( class_exists( 'Feedzy_Rss_Feeds_Util_Scheduler' ) ) {
			Feedzy_Rss_Feeds_Util_Scheduler::clear_scheduled_hook( 'feedzy_cron', array( 100, $post_id ) );
		}

		$deleted = wp_delete_post( $post_id, true );

		if ( ! $deleted ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_delete_failed',
				/* translators: %d: import job ID */
				sprintf( __( 'Failed to delete import job with ID %d.', 'feedzy-rss-feeds' ), $post_id )
			);
		}

		if ( class_exists( 'Feedzy_Rss_Feeds_Log' ) ) {
			Feedzy_Rss_Feeds_Log::info(
				'Ability feedzy/delete-import: deleted job ID ' . $post_id,
				array( 'post_id' => $post_id )
			);
		}

		return Feedzy_Rss_Feeds_Ability_Helpers::success(
			array(
				'deleted' => true,
				'id'      => $post_id,
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
				'id' => array(
					'type'        => 'integer',
					'description' => __( 'Numeric post ID of the import job to delete.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
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
						'deleted' => array( 'type' => 'boolean' ),
						'id'      => array( 'type' => 'integer' ),
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
			'destructive' => true,
			'idempotent'  => true,
		);
		return $meta;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function object_capability() {
		return 'delete_post';
	}
}
