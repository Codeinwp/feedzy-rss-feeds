<?php
/**
 * Ability: feedzy/list-feed-sources
 *
 * Returns a paginated list of Feedzy feed source groups (feedzy_categories).
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/feed-sources
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_List_Feed_Sources
 */
class Feedzy_Rss_Feeds_Ability_List_Feed_Sources extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'list-feed-sources';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'List Feed Sources', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Returns a paginated list of Feedzy feed source groups.', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array<string, mixed> $input Input arguments, validated and sanitized according to `input_schema()`.
	 *
	 * @return array<string, mixed> Output data, validated and sanitized according to `output_schema()`.
	 */
	public function execute_callback( ?array $input = null ) {
		$input    = (array) $input;
		$page     = max( 1, (int) ( $input['page'] ?? 1 ) );
		$per_page = min( 100, max( 1, (int) ( $input['per_page'] ?? 20 ) ) );
		$search   = sanitize_text_field( (string) ( $input['search'] ?? '' ) );

		$args = array(
			'post_type'              => 'feedzy_categories',
			'post_status'            => 'publish',
			'posts_per_page'         => $per_page,
			'paged'                  => $page,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => false,
			'update_post_term_cache' => false,
		);

		if ( '' !== $search ) {
			$args['s'] = $search;
		}

		$query   = new WP_Query( $args );
		$total   = (int) $query->found_posts;
		$pages   = (int) ceil( $total / $per_page );
		$sources = array_map(
			array( 'Feedzy_Rss_Feeds_Ability_Helpers', 'shape_feed_source' ),
			$query->posts
		);

		return Feedzy_Rss_Feeds_Ability_Helpers::success(
			array(
				'total'        => $total,
				'pages'        => $pages,
				'page'         => $page,
				'per_page'     => $per_page,
				'feed_sources' => $sources,
			)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function input_schema() {
		return array(
			'type'                 => array( 'object', 'null' ),
			'properties'           => array(
				'page'     => array(
					'type'        => 'integer',
					'description' => __( 'Page number.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
					'default'     => 1,
				),
				'per_page' => array(
					'type'        => 'integer',
					'description' => __( 'Number of results per page.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
					'maximum'     => 100,
					'default'     => 20,
				),
				'search'   => array(
					'type'        => 'string',
					'description' => __( 'Optional search string to filter feed source groups by title.', 'feedzy-rss-feeds' ),
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
						'total'        => array( 'type' => 'integer' ),
						'pages'        => array( 'type' => 'integer' ),
						'page'         => array( 'type' => 'integer' ),
						'per_page'     => array( 'type' => 'integer' ),
						'feed_sources' => array(
							'type'  => 'array',
							'items' => array( 'type' => 'object' ),
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
