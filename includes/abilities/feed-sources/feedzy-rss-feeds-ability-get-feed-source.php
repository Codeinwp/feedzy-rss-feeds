<?php
/**
 * Ability: feedzy/get-feed-source
 *
 * Returns a single Feedzy feed source group by its numeric ID.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/feed-sources
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_Get_Feed_Source
 */
class Feedzy_Rss_Feeds_Ability_Get_Feed_Source extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'get-feed-source';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'Get Feed Source', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Returns a single Feedzy feed source group.', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array<string, mixed>|null $input Input arguments, validated and sanitized according to `input_schema()`.
	 *
	 * @return array<string, mixed> Output data, validated and sanitized according to `output_schema()`.
	 */
	public function execute_callback( ?array $input = null ) {
		$post = $this->resolve_post( $input );

		if ( is_wp_error( $post ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::from_wp_error( $post );
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
			'properties'           => array(
				'id'   => array(
					'type'        => 'integer',
					'description' => __( 'Numeric post ID of the feed source group.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
				),
				'name' => array(
					'type'        => 'string',
					'description' => __( 'Post slug of the feed source group.', 'feedzy-rss-feeds' ),
					'minLength'   => 1,
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
			'readonly'    => true,
			'destructive' => false,
			'idempotent'  => true,
		);
		return $meta;
	}

	/**
	 * Resolve the target post from `id` or `name`.
	 *
	 * @param  array<string, mixed>|null $input Input array.
	 *
	 * @return WP_Post|WP_Error
	 */
	private function resolve_post( ?array $input ) {
		if ( ! empty( $input['id'] ) ) {
			$post = get_post( (int) $input['id'] );
			if ( ! $post || 'feedzy_categories' !== $post->post_type ) {
				return new WP_Error(
					'feedzy_not_found',
					/* translators: %d: feed source ID */
					sprintf( __( 'Feed source with ID %d was not found.', 'feedzy-rss-feeds' ), (int) $input['id'] )
				);
			}
			return $post;
		}

		if ( ! empty( $input['name'] ) ) {
			$posts = get_posts(
				array(
					'post_type'              => 'feedzy_categories',
					'name'                   => sanitize_text_field( $input['name'] ),
					'posts_per_page'         => 1,
					'post_status'            => array( 'publish', 'draft' ),
					'no_found_rows'          => true,
					'update_post_meta_cache' => true,
					'update_post_term_cache' => false,
				)
			);
			if ( empty( $posts ) ) {
				return new WP_Error(
					'feedzy_not_found',
					/* translators: %s: feed source name */
					sprintf( __( 'Feed source "%s" was not found.', 'feedzy-rss-feeds' ), esc_html( $input['name'] ) )
				);
			}
			return $posts[0];
		}

		return new WP_Error(
			'feedzy_invalid_input',
			sprintf(
				/* translators: %s is the name of the required input field */
				__( 'Provide either "%1$s" or "%2$s" to identify the feed source.', 'feedzy-rss-feeds' ),
				'id',
				'name'
			)
		);
	}
}
