<?php
/**
 * Ability: feedzy/update-import
 *
 * Update a Feedzy import job identified by "id" with new configuration parameters.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/imports
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_Update_Import
 */
class Feedzy_Rss_Feeds_Ability_Update_Import extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'update-import';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'Update Import Job', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Update a Feedzy import job.', 'feedzy-rss-feeds' );
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

		foreach ( array( 'validate_edition', 'validate_author', 'validate_terms' ) as $check ) {
			$valid = Feedzy_Rss_Feeds_Ability_Helpers::$check( (array) $input );
			if ( is_wp_error( $valid ) ) {
				return Feedzy_Rss_Feeds_Ability_Helpers::from_wp_error( $valid );
			}
		}

		$update_data = array( 'ID' => $post_id );

		if ( ! empty( $input['title'] ) ) {
			$update_data['post_title'] = sanitize_text_field( (string) $input['title'] );
		}

		if ( ! empty( $input['status'] ) && in_array( $input['status'], array( 'publish', 'draft' ), true ) ) {
			$update_data['post_status'] = $input['status'];
		}

		if ( count( $update_data ) > 1 ) {
			$result = wp_update_post( $update_data, true );
			if ( is_wp_error( $result ) ) {
				return Feedzy_Rss_Feeds_Ability_Helpers::from_wp_error( $result );
			}
		}

		// Persist changed meta fields.
		$non_meta   = array( 'id', 'title', 'status' );
		$meta_input = array_diff_key( $input, array_flip( $non_meta ) );
		$meta_input = Feedzy_Rss_Feeds_Ability_Helpers::resolve_ai_actions( $meta_input, $post_id );
		if ( ! empty( $meta_input ) ) {
			Feedzy_Rss_Feeds_Ability_Helpers::save_import_meta( $post_id, $meta_input );
		}

		// Clear any existing cron schedule so it is recreated with the new settings.
		if ( class_exists( 'Feedzy_Rss_Feeds_Util_Scheduler' ) ) {
			Feedzy_Rss_Feeds_Util_Scheduler::clear_scheduled_hook( 'feedzy_cron', array( 100, $post_id ) );
		}

		$updated_post = get_post( $post_id );

		if ( class_exists( 'Feedzy_Rss_Feeds_Log' ) ) {
			Feedzy_Rss_Feeds_Log::info(
				'Ability feedzy/update-import: updated job ID ' . $post_id,
				array( 'post_id' => $post_id )
			);
		}

		return Feedzy_Rss_Feeds_Ability_Helpers::success(
			Feedzy_Rss_Feeds_Ability_Helpers::shape_import( $updated_post )
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function input_schema() {
		return array(
			'type'                 => 'object',
			'required'             => array( 'id' ),
			'additionalProperties' => false,
			'properties'           => array_merge(
				array(
					'id'     => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'title'  => array(
						'type'      => 'string',
						'minLength' => 1,
					),
					'status' => array(
						'type' => 'string',
						'enum' => array( 'publish', 'draft' ),
					),
				),
				Feedzy_Rss_Feeds_Ability_Create_Import::import_meta_schema()
			),
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
				'data'    => array( 'type' => 'object' ),
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
			'idempotent'  => true,
		);
		return $meta;
	}
}
