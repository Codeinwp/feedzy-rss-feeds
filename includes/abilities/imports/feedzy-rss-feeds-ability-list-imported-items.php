<?php
/**
 * Ability: feedzy/list-imported-items
 *
 * Lists the posts created by an import job, with the feed item each one came from.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/imports
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_List_Imported_Items
 */
class Feedzy_Rss_Feeds_Ability_List_Imported_Items extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'list-imported-items';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'List Imported Items', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Lists the posts created by a Feedzy import job, with the source feed item URL, author and the run that imported each one.', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array<string, mixed>|null $input Input arguments, validated and sanitized according to `input_schema()`.
	 *
	 * @return array<string, mixed>|WP_Error Output data, validated and sanitized according to `output_schema()`.
	 */
	public function execute_callback( ?array $input = null ) {
		$job_id = isset( $input['job_id'] ) ? (int) $input['job_id'] : 0;
		$job    = $job_id > 0 ? get_post( $job_id ) : null;

		if ( ! $job || 'feedzy_imports' !== $job->post_type ) {
			return new WP_Error(
				'feedzy_not_found',
				/* translators: %d: import job ID */
				sprintf( __( 'Import job with ID %d was not found.', 'feedzy-rss-feeds' ), $job_id ),
				array( 'status' => 404 )
			);
		}

		$page     = max( 1, (int) ( $input['page'] ?? 1 ) );
		$per_page = min( 100, max( 1, (int) ( $input['per_page'] ?? 20 ) ) );
		$statuses = array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' );
		$status   = isset( $input['status'] ) && in_array( $input['status'], $statuses, true ) ? $input['status'] : 'any';

		$post_type = (string) get_post_meta( $job_id, 'import_post_type', true );
		if ( '' === $post_type || ! post_type_exists( $post_type ) ) {
			$post_type = 'any';
		}

		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$meta_query = array(
			array(
				'key'   => 'feedzy_job',
				'value' => $job_id,
			),
		);

		if ( ! empty( $input['run_id'] ) ) {
			$meta_query[] = array(
				'key'   => 'feedzy_job_time',
				'value' => (int) $input['run_id'],
			);
		}

		$query = new WP_Query(
			array(
				'post_type'              => $post_type,
				'post_status'            => $status,
				'posts_per_page'         => $per_page,
				'paged'                  => $page,
				'orderby'                => 'date',
				'order'                  => 'DESC',
				'no_found_rows'          => false,
				'update_post_term_cache' => false,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'             => $meta_query,
			)
		);

		$items = array();
		foreach ( $query->posts as $post ) {
			if ( ! $post instanceof WP_Post || ! current_user_can( 'read_post', $post->ID ) ) {
				continue;
			}

			$run_time = (int) get_post_meta( $post->ID, 'feedzy_job_time', true );

			$items[] = array(
				'id'             => $post->ID,
				'title'          => wp_strip_all_tags( $post->post_title ),
				'post_type'      => $post->post_type,
				'status'         => $post->post_status,
				'date'           => $post->post_date_gmt,
				'link'           => (string) get_permalink( $post ),
				'item_url'       => esc_url_raw( (string) get_post_meta( $post->ID, 'feedzy_item_url', true ) ),
				'item_author'    => (string) get_post_meta( $post->ID, 'feedzy_item_author', true ),
				'external_image' => esc_url_raw( (string) get_post_meta( $post->ID, 'feedzy_item_external_url', true ) ),
				'run_id'         => $run_time,
				'imported_at'    => $run_time > 0 ? gmdate( 'c', $run_time ) : null,
				'has_thumbnail'  => has_post_thumbnail( $post ),
			);
		}

		$total = (int) $query->found_posts;

		return Feedzy_Rss_Feeds_Ability_Helpers::success(
			array(
				'job_id'      => $job_id,
				'last_run_id' => (int) get_post_meta( $job_id, 'last_run_id', true ),
				'total'       => $total,
				'pages'       => (int) ceil( $total / $per_page ),
				'page'        => $page,
				'per_page'    => $per_page,
				'items'       => $items,
			)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function input_schema() {
		return array(
			'type'                 => 'object',
			'required'             => array( 'job_id' ),
			'additionalProperties' => false,
			'properties'           => array(
				'job_id'   => array(
					'type'        => 'integer',
					'description' => __( 'ID of the import job.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
				),
				'status'   => array(
					'type'        => 'string',
					'description' => __( 'Only return imported posts with this status.', 'feedzy-rss-feeds' ),
					'enum'        => array( 'any', 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
					'default'     => 'any',
				),
				'run_id'   => array(
					'type'        => 'integer',
					'description' => __( 'Only return posts imported by this run (the run timestamp, see last_run_id).', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
				),
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
				'data'    => array(
					'type'       => 'object',
					'properties' => array(
						'job_id'      => array( 'type' => 'integer' ),
						'last_run_id' => array( 'type' => 'integer' ),
						'total'       => array( 'type' => 'integer' ),
						'pages'       => array( 'type' => 'integer' ),
						'page'        => array( 'type' => 'integer' ),
						'per_page'    => array( 'type' => 'integer' ),
						'items'       => array(
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
