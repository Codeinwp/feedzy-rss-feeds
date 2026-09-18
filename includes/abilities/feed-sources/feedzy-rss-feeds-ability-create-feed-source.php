<?php
/**
 * Ability: feedzy/create-feed-source
 *
 * Creates a new Feedzy feed source group with a given title and array of feed URLs.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/feed-sources
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_Create_Feed_Source
 */
class Feedzy_Rss_Feeds_Ability_Create_Feed_Source extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'create-feed-source';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'Create Feed Source', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Creates a new Feedzy feed source group with a given title and array of feed URLs.', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array<string, mixed>|null $input Input arguments, validated and sanitized according to `input_schema()`.
	 */
	public function execute_callback( ?array $input = null ) {
		$post_type = get_post_type_object( 'feedzy_categories' );
		$cap       = ( $post_type && isset( $post_type->cap->create_posts ) ) ? $post_type->cap->create_posts : 'edit_posts';
		if ( ! current_user_can( $cap ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_forbidden',
				__( 'You do not have permission to create a feed source.', 'feedzy-rss-feeds' ),
				array( 'status' => 403 )
			);
		}

		if ( empty( $input['title'] ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_invalid_input',
				sprintf(
					/* translators: %s is the name of the required input field */
					__( '"%s" is required.', 'feedzy-rss-feeds' ),
					'title'
				)
			);
		}

		if ( empty( $input['feeds'] ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_invalid_input',
				sprintf(
					/* translators: %s is the name of the required input field */
					__( '"%s" is required.', 'feedzy-rss-feeds' ),
					'feeds'
				)
			);
		}

		$title  = sanitize_text_field( (string) $input['title'] );
		$status = in_array( $input['status'] ?? 'publish', array( 'publish', 'draft' ), true )
			? ( $input['status'] ?? 'publish' )
			: 'publish';

		$feeds = array_filter(
			array_map( 'esc_url_raw', (array) $input['feeds'] )
		);

		if ( empty( $feeds ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_invalid_input',
				__( 'No valid feed URLs were provided.', 'feedzy-rss-feeds' )
			);
		}

		$post_id = wp_insert_post(
			array(
				'post_title'  => $title,
				'post_type'   => 'feedzy_categories',
				'post_status' => $status,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::from_wp_error( $post_id );
		}

		update_post_meta( $post_id, 'feedzy_category_feed', implode( "\n", $feeds ) );

		$post = get_post( $post_id );

		if ( class_exists( 'Feedzy_Rss_Feeds_Log' ) ) {
			Feedzy_Rss_Feeds_Log::info(
				'Ability feedzy/create-feed-source: created group ' . $title,
				array(
					'post_id' => $post_id,
					'feeds'   => $feeds,
				)
			);
		}

		return Feedzy_Rss_Feeds_Ability_Helpers::success(
			Feedzy_Rss_Feeds_Ability_Helpers::shape_feed_source( $post )
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function input_schema() {
		return array(
			'type'                 => 'object',
			'required'             => array( 'title', 'feeds' ),
			'properties'           => array(
				'title'  => array(
					'type'        => 'string',
					'description' => __( 'Human-readable title for the feed source group.', 'feedzy-rss-feeds' ),
					'minLength'   => 1,
				),
				'feeds'  => array(
					'type'        => 'array',
					'description' => __( 'One or more RSS/Atom feed URLs to include in this group.', 'feedzy-rss-feeds' ),
					'items'       => array(
						'type'   => 'string',
						'format' => 'uri',
					),
					'minItems'    => 1,
				),
				'status' => array(
					'type'        => 'string',
					'description' => __( 'Post status for the group.', 'feedzy-rss-feeds' ),
					'enum'        => array( 'publish', 'draft' ),
					'default'     => 'publish',
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
			'idempotent'  => false,
		);
		return $meta;
	}
}
