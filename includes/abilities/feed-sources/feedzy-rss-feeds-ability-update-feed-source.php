<?php
/**
 * Ability: feedzy/update-feed-source
 *
 * Updates an existing Feedzy feed source group identified by "id".
 * Optionally update the "title", "feeds" array of URLs, or "status". Only supplied fields are changed. Returns the updated feed source object.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/feed-sources
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_Update_Feed_Source
 */
class Feedzy_Rss_Feeds_Ability_Update_Feed_Source extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'update-feed-source';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'Update Feed Source', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Updates an existing Feedzy feed source group. Only supplied fields are changed. Returns the updated feed source object.', 'feedzy-rss-feeds' );
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

		$post_type = get_post_type_object( 'feedzy_categories' );
		$cap       = ( $post_type && isset( $post_type->cap->edit_post ) ) ? $post_type->cap->edit_post : 'edit_posts';
		if ( ! current_user_can( $cap ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_forbidden',
				__( 'You do not have permission to do this.', 'feedzy-rss-feeds' ),
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

		if ( ! empty( $input['feeds'] ) ) {
			$feeds = array_filter(
				array_map( 'esc_url_raw', (array) $input['feeds'] )
			);
			if ( ! empty( $feeds ) ) {
				update_post_meta( $post_id, 'feedzy_category_feed', implode( "\n", $feeds ) );
			}
		}

		$updated_post = get_post( $post_id );

		if ( class_exists( 'Feedzy_Rss_Feeds_Log' ) ) {
			Feedzy_Rss_Feeds_Log::info(
				'Ability feedzy/update-feed-source: updated group ID ' . $post_id,
				array( 'post_id' => $post_id )
			);
		}

		return Feedzy_Rss_Feeds_Ability_Helpers::success(
			Feedzy_Rss_Feeds_Ability_Helpers::shape_feed_source( $updated_post )
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
				'id'     => array(
					'type'        => 'integer',
					'description' => __( 'Numeric post ID of the feed source group.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
				),
				'title'  => array(
					'type'        => 'string',
					'description' => __( 'Group Title', 'feedzy-rss-feeds' ),
					'minLength'   => 1,
				),
				'feeds'  => array(
					'type'        => 'array',
					'description' => __( 'Replacement list of RSS/Atom feed URLs.', 'feedzy-rss-feeds' ),
					'items'       => array(
						'type'   => 'string',
						'format' => 'uri',
					),
					'minItems'    => 1,
				),
				'status' => array(
					'type'        => 'string',
					'description' => __( 'Post status', 'feedzy-rss-feeds' ),
					'enum'        => array( 'publish', 'draft' ),
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
