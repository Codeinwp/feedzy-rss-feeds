<?php
/**
 * Ability: feedzy/delete-feed-source
 *
 * Deletes a Feedzy feed source group by its numeric ID. Returns the deleted feed source ID on success.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/feed-sources
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_Delete_Feed_Source
 */
class Feedzy_Rss_Feeds_Ability_Delete_Feed_Source extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'delete-feed-source';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'Delete Feed Source', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Deletes a Feedzy feed source group. Returns the deleted feed source ID on success.', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array<string, mixed> $input Input arguments, validated and sanitized according to `input_schema()`.
	 *
	 * @return array<string, mixed> Output data, validated and sanitized according to `output_schema()`.
	 */
	public function execute_callback( ?array $input = null ) {
		$input = (array) $input;
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

		$post_type = get_post_type_object( 'feedzy_categories' );
		$cap       = ( $post_type && isset( $post_type->cap->delete_post ) ) ? $post_type->cap->delete_post : 'edit_posts';
		if ( ! current_user_can( $cap ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_forbidden',
				/* translators: %d: post ID */
				sprintf( __( 'You do not have permission to delete feed source with ID %d.', 'feedzy-rss-feeds' ), $post_id ),
				array( 'status' => 403 )
			);
		}

		$post = get_post( $post_id );

		if ( ! $post || 'feedzy_categories' !== $post->post_type ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'Feed source with ID %d was not found.', 'feedzy-rss-feeds' ), $post_id )
			);
		}

		$force          = ! empty( $input['force'] );
		$trash_disabled = ( defined( 'EMPTY_TRASH_DAYS' ) && 0 === (int) EMPTY_TRASH_DAYS );

		if ( $force ) {
			$result  = wp_delete_post( $post_id, true );
			$outcome = 'deleted';
			$success = (bool) $result;
			$trashed = false;
		} else {
			$result  = wp_trash_post( $post_id );
			$success = (bool) $result;
			$trashed = $success && ! $trash_disabled;
			$outcome = $trashed ? 'trashed' : 'deleted';
		}

		if ( ! $success ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_delete_failed',
				/* translators: %d: post ID */
				sprintf( __( 'Failed to delete feed source with ID %d.', 'feedzy-rss-feeds' ), $post_id )
			);
		}

		if ( class_exists( 'Feedzy_Rss_Feeds_Log' ) ) {
			Feedzy_Rss_Feeds_Log::info(
				'Ability feedzy/delete-feed-source: ' . $outcome . ' group ID ' . $post_id,
				array(
					'post_id'        => $post_id,
					'force'          => $force,
					'trash_disabled' => $trash_disabled,
				)
			);
		}

		return Feedzy_Rss_Feeds_Ability_Helpers::success(
			array(
				'deleted' => true,
				'trashed' => $trashed,
				'force'   => $force,
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
				'id'    => array(
					'type'        => 'integer',
					'description' => __( 'Numeric post ID of the feed source group to delete.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
				),
				'force' => array(
					'type'        => 'boolean',
					'description' => __( 'When true, permanently delete. When false or omitted, the feed source is moved to the trash.', 'feedzy-rss-feeds' ),
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
				'data'    => array(
					'type'       => 'object',
					'properties' => array(
						'deleted' => array( 'type' => 'boolean' ),
						'trashed' => array( 'type' => 'boolean' ),
						'force'   => array( 'type' => 'boolean' ),
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
