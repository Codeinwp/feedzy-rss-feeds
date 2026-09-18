<?php
/**
 * Ability: feedzy/list-imports
 *
 * Returns a list of existing Feedzy import jobs with their configuration details.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/imports
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_List_Imports
 */
class Feedzy_Rss_Feeds_Ability_List_Imports extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'list-imports';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'List Import Jobs', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Returns a list of existing Feedzy import jobs with their configuration details.', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array<string, mixed>|null $input Input arguments, validated and sanitized according to `input_schema()`.
	 *
	 * @return array<string, mixed> Output data, validated and sanitized according to `output_schema()`.
	 */
	public function execute_callback( ?array $input = null ) {
		$page     = max( 1, (int) ( $input['page'] ?? 1 ) );
		$per_page = min( 100, max( 1, (int) ( $input['per_page'] ?? 20 ) ) );
		$search   = sanitize_text_field( (string) ( $input['search'] ?? '' ) );
		$status   = in_array( $input['status'] ?? 'any', array( 'publish', 'draft', 'any' ), true )
			? ( $input['status'] ?? 'any' )
			: 'any';

		$args = array(
			'post_type'              => 'feedzy_imports',
			'post_status'            => $status,
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
		$imports = array_map(
			array( 'Feedzy_Rss_Feeds_Ability_Helpers', 'shape_import' ),
			$query->posts
		);

		return Feedzy_Rss_Feeds_Ability_Helpers::success(
			array(
				'total'    => $total,
				'pages'    => $pages,
				'page'     => $page,
				'per_page' => $per_page,
				'imports'  => $imports,
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
					'type'    => 'integer',
					'minimum' => 1,
					'default' => 1,
				),
				'per_page' => array(
					'type'    => 'integer',
					'minimum' => 1,
					'maximum' => 100,
					'default' => 20,
				),
				'search'   => array(
					'type'        => 'string',
					'description' => __( 'Filter import jobs by title.', 'feedzy-rss-feeds' ),
				),
				'status'   => array(
					'type'        => 'string',
					'description' => __( 'Filter by post status.', 'feedzy-rss-feeds' ),
					'enum'        => array( 'publish', 'draft', 'any' ),
					'default'     => 'any',
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
						'total'    => array( 'type' => 'integer' ),
						'pages'    => array( 'type' => 'integer' ),
						'page'     => array( 'type' => 'integer' ),
						'per_page' => array( 'type' => 'integer' ),
						'imports'  => array( 'type' => 'array' ),
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
