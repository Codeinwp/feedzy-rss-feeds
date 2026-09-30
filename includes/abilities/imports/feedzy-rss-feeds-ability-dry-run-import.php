<?php
/**
 * Ability: feedzy/dry-run-import
 *
 * Performs a dry run of a Feedzy import job, returning a preview of items that would be imported without actually importing them. Useful for testing and debugging import configurations.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/imports
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_Dry_Run_Import
 */
class Feedzy_Rss_Feeds_Ability_Dry_Run_Import extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'dry-run-import';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'Dry Run Import', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Performs a dry run of a Feedzy import job, returning the feed items with the decision the import would take for each (import, duplicate, filtered) without importing anything.', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array<string, mixed>|null $input Input arguments, validated and sanitized according to `input_schema()`.
	 *
	 * @return array<string, mixed> Output data, validated and sanitized according to `output_schema()`.
	 */
	public function execute_callback( ?array $input = null ) {
		$source = $this->resolve_source( $input );

		if ( is_wp_error( $source ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::from_wp_error( $source );
		}

		$job     = ! empty( $input['job_id'] ) && empty( $input['source'] ) ? get_post( (int) $input['job_id'] ) : null;
		$limit   = min( 50, max( 1, (int) ( $input['limit'] ?? 5 ) ) );
		$inc_key = sanitize_text_field( (string) ( $input['inc_key'] ?? '' ) );
		$exc_key = sanitize_text_field( (string) ( $input['exc_key'] ?? '' ) );

		$data = array(
			'source'    => $source,
			'limit'     => $limit,
			'persisted' => false,
		);

		if ( ! isset( $input['include_html'] ) || ! empty( $input['include_html'] ) ) {
			$shortcode = sprintf(
				'feedzy-rss feeds="%s" max="%d" feed_title=no meta=no summary=no thumb=no error_empty="" keywords_inc="%s" _dryrun_="yes"',
				esc_attr( $source ),
				$limit,
				esc_attr( $inc_key )
			);

			if ( function_exists( 'feedzy_is_pro' ) && feedzy_is_pro() ) {
				$shortcode .= sprintf(
					' keywords_exc="%s" keywords_ban="%s"',
					esc_attr( $exc_key ),
					esc_attr( $exc_key )
				);
			}

			$data['preview_html'] = do_shortcode( '[' . $shortcode . ']' );
		}

		$preview = $this->build_structured_preview( $source, $job instanceof WP_Post ? $job : null, $limit, $inc_key, $exc_key );
		if ( is_wp_error( $preview ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::from_wp_error( $preview );
		}

		return Feedzy_Rss_Feeds_Ability_Helpers::success( array_merge( $data, $preview ) );
	}

	/**
	 * Build the structured preview: which feed items the import would pick up, which
	 * ones the filters or the duplicate checks would leave out, and where they would go.
	 *
	 * Nothing is persisted and no content transformation (AI, translation, spinning) is executed.
	 *
	 * @param  string       $source  Feed URL(s) or feed group slug.
	 * @param  WP_Post|null $job     The import job, when previewing an existing one.
	 * @param  int          $limit   Maximum number of items the run would process.
	 * @param  string       $inc_key Include keywords, used when no job is given.
	 * @param  string       $exc_key Exclude keywords, used when no job is given.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	private function build_structured_preview( string $source, $job, int $limit, string $inc_key, string $exc_key ) {
		if ( ! class_exists( 'Feedzy_Rss_Feeds_Import' ) || ! class_exists( 'Feedzy_Rss_Feeds' ) ) {
			return new WP_Error( 'feedzy_dependency_missing', __( 'The Feedzy import feature is not available.', 'feedzy-rss-feeds' ) );
		}

		$job_id = $job ? $job->ID : 0;

		if ( $job ) {
			$filters = get_post_meta( $job_id, 'filter_conditions', true );
			$legacy  = array(
				'keywords_inc'    => get_post_meta( $job_id, 'inc_key', true ),
				'keywords_exc'    => get_post_meta( $job_id, 'exc_key', true ),
				'keywords_inc_on' => get_post_meta( $job_id, 'inc_on', true ),
				'keywords_exc_on' => get_post_meta( $job_id, 'exc_on', true ),
				'from_datetime'   => get_post_meta( $job_id, 'from_datetime', true ),
				'to_datetime'     => get_post_meta( $job_id, 'to_datetime', true ),
			);
		} else {
			$filters = '';
			$legacy  = array(
				'keywords_inc'    => $inc_key,
				'keywords_exc'    => $exc_key,
				'keywords_inc_on' => 'title',
				'keywords_exc_on' => 'title',
			);
		}

		if ( empty( $filters ) ) {
			$filters = apply_filters( 'feedzy_filter_conditions_migration', $legacy );
		}

		// Same options the import runner uses to read the feed.
		$options = array(
			'feeds'         => $source,
			'max'           => 50,
			'feed_title'    => 'no',
			'target'        => '_blank',
			'title'         => '',
			'meta'          => 'yes',
			'summary'       => 'yes',
			'summarylength' => '',
			'thumb'         => 'auto',
			'default'       => '',
			'size'          => '250',
			'columns'       => 1,
			'offset'        => 0,
			'multiple_meta' => 'no',
			'refresh'       => '55_mins',
			'filters'       => is_string( $filters ) ? $filters : '',
			'sort'          => $job ? get_post_meta( $job_id, 'import_order', true ) : '',
		);
		if ( $job ) {
			$options = apply_filters( 'feedzy_shortcode_options', $options, $job );
		}

		$admin              = Feedzy_Rss_Feeds::instance()->get_admin();
		$options            = $admin->sanitize_attr( $options, $source );
		$options['max']     = 50;
		$options['__jobID'] = $job_id;

		$importer       = new Feedzy_Rss_Feeds_Import( Feedzy_Rss_Feeds::get_plugin_name(), Feedzy_Rss_Feeds::get_version() );
		$import_content = $job ? (string) get_post_meta( $job_id, 'import_post_content', true ) : '[#item_content]';

		$matching = $importer->get_job_feed( $options, $import_content );
		if ( is_wp_error( $matching ) ) {
			return $matching;
		}
		$matching = is_array( $matching ) ? array_values( $matching ) : array();

		$all = $matching;
		if ( ! empty( $options['filters'] ) ) {
			$unfiltered            = $options;
			$unfiltered['filters'] = '';
			$all                   = $importer->get_job_feed( $unfiltered, $import_content );
			$all                   = is_array( $all ) ? array_values( $all ) : $matching;
		}

		$matching_keys = array();
		foreach ( $matching as $position => $item ) {
			$matching_keys[ $this->item_key( $item ) ] = $position;
		}

		// Duplicate bookkeeping, read-only.
		$imported_old      = $job ? get_post_meta( $job_id, 'imported_items', true ) : array();
		$imported_old      = is_array( $imported_old ) ? $imported_old : array();
		$imported_new      = $job ? get_post_meta( $job_id, 'imported_items_hash', true ) : array();
		$imported_new      = is_array( $imported_new ) ? $imported_new : array();
		$use_new_hash      = empty( $imported_old );
		$remove_duplicates = $job && 'yes' === get_post_meta( $job_id, 'import_remove_duplicates', true );
		$post_type         = $job ? (string) get_post_meta( $job_id, 'import_post_type', true ) : 'post';
		$post_type         = '' !== $post_type ? $post_type : 'post';
		$duplicate_tag     = $job ? (string) get_post_meta( $job_id, 'mark_duplicate_tag', true ) : '';
		$custom_key_in_use = '' !== $duplicate_tag && function_exists( 'feedzy_is_pro' ) && feedzy_is_pro();

		$items   = array();
		$summary = array(
			'found'        => count( $all ),
			'would_import' => 0,
			'duplicates'   => 0,
			'filtered'     => 0,
			'over_limit'   => 0,
		);

		foreach ( $all as $item ) {
			$key      = $this->item_key( $item );
			$row      = $this->shape_item( $item );
			$decision = 'import';
			$reason   = '';

			if ( ! isset( $matching_keys[ $key ] ) ) {
				$decision = 'filtered';
				$reason   = 'filter_conditions';
				++$summary['filtered'];
			} elseif ( $matching_keys[ $key ] >= $limit ) {
				$decision = 'over_limit';
				$reason   = 'item_limit_reached';
				++$summary['over_limit'];
			} elseif ( $job ) {
				$item_id   = isset( $item['item_id'] ) ? $item['item_id'] : '';
				$item_hash = $use_new_hash ? $item_id : hash( 'sha256', $item['item_url'] . '_' . $item['item_date'] );
				if ( in_array( $item_hash, $use_new_hash ? $imported_new : $imported_old, true ) ) {
					$decision = 'duplicate';
					$reason   = 'already_imported_by_job';
					++$summary['duplicates'];
				} elseif ( $remove_duplicates && ! $custom_key_in_use ) {
					$existing = $importer->is_duplicate_post( $post_type, 'feedzy_item_url', esc_url_raw( $item['item_url'] ) );
					if ( ! empty( $existing ) ) {
						$reason                   = 'replaces_existing_post';
						$row['existing_post_ids'] = array_map( 'intval', (array) $existing );
					}
				}
			}

			if ( 'import' === $decision ) {
				++$summary['would_import'];
			}

			$row['decision'] = $decision;
			$row['reason']   = $reason;
			$items[]         = $row;
		}

		return array(
			'items'   => $items,
			'summary' => $summary,
			'target'  => $this->describe_target( $job, $post_type, $custom_key_in_use ? $duplicate_tag : 'item_url' ),
			'notes'   => array(
				__( 'Content transformations (AI, translation, paraphrasing, magic-tag actions) are listed under target.pending_actions but are not executed in a dry run.', 'feedzy-rss-feeds' ),
			),
		);
	}

	/**
	 * Stable key for a feed item.
	 *
	 * @param  array<string, mixed> $item Feed item array.
	 *
	 * @return string
	 */
	private function item_key( array $item ): string {
		if ( ! empty( $item['item_id'] ) && is_string( $item['item_id'] ) ) {
			return $item['item_id'];
		}
		return isset( $item['item_unique_hash'] ) ? (string) $item['item_unique_hash'] : md5( (string) wp_json_encode( array( $item['item_url'] ?? '', $item['item_title'] ?? '' ) ) );
	}

	/**
	 * Shape a feed item for output.
	 *
	 * @param  array<string, mixed> $item Feed item array.
	 *
	 * @return array<string, mixed>
	 */
	private function shape_item( array $item ) {
		$author = '';
		if ( ! empty( $item['item_author'] ) ) {
			if ( is_string( $item['item_author'] ) ) {
				$author = $item['item_author'];
			} elseif ( is_object( $item['item_author'] ) && method_exists( $item['item_author'], 'get_name' ) ) {
				$author = (string) $item['item_author']->get_name();
			}
		}

		$date = isset( $item['item_date'] ) && is_numeric( $item['item_date'] ) ? gmdate( 'c', (int) $item['item_date'] ) : '';

		return array(
			'title'      => wp_strip_all_tags( (string) ( $item['item_title'] ?? '' ) ),
			'url'        => esc_url_raw( (string) ( $item['item_url'] ?? '' ) ),
			'date'       => $date,
			'author'     => sanitize_text_field( $author ),
			'categories' => sanitize_text_field( (string) ( $item['item_categories'] ?? '' ) ),
			'image'      => esc_url_raw( (string) ( $item['item_img_path'] ?? '' ) ),
			'excerpt'    => wp_trim_words( wp_strip_all_tags( (string) ( $item['item_description'] ?? '' ) ), 40 ),
			'feed_url'   => esc_url_raw( (string) ( $item['feed_url'] ?? '' ) ),
		);
	}

	/**
	 * Describe where the items would be imported and what would be applied to them.
	 *
	 * @param  WP_Post|null $job           The import job.
	 * @param  string       $post_type     Target post type.
	 * @param  string       $duplicate_key Key used to detect duplicates.
	 *
	 * @return array<string, mixed>
	 */
	private function describe_target( $job, string $post_type, string $duplicate_key ) {
		if ( ! $job ) {
			return array(
				'post_type' => $post_type,
			);
		}

		$terms = array();
		foreach ( array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $job->ID, 'import_post_term', true ) ) ) ) as $token ) {
			$parts   = explode( '_', $token );
			$term_id = (int) array_pop( $parts );
			$term    = $term_id > 0 ? get_term( $term_id, implode( '_', $parts ) ) : null;
			$terms[] = $term instanceof WP_Term
				? array(
					'taxonomy' => $term->taxonomy,
					'term_id'  => $term->term_id,
					'name'     => $term->name,
				)
				: array( 'tag' => $token );
		}

		$pending = array();
		foreach ( Feedzy_Rss_Feeds_Ability_Helpers::tagify_fields() as $field ) {
			$actions = Feedzy_Rss_Feeds_Ability_Helpers::decode_tagify_actions( (string) get_post_meta( $job->ID, $field, true ) );
			$ids     = array_values( array_filter( array_column( $actions, 'id' ) ) );
			if ( ! empty( $ids ) ) {
				$pending[ $field ] = $ids;
			}
		}

		$author_id = (int) get_post_meta( $job->ID, 'import_post_author', true );

		return array(
			'post_type'         => $post_type,
			'post_status'       => (string) get_post_meta( $job->ID, 'import_post_status', true ),
			'post_author'       => $author_id > 0 ? $author_id : (int) $job->post_author,
			'terms'             => $terms,
			'remove_duplicates' => 'yes' === get_post_meta( $job->ID, 'import_remove_duplicates', true ),
			'duplicate_key'     => $duplicate_key,
			'pending_actions'   => $pending,
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function input_schema() {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'source'       => array(
					'type'        => 'string',
					'description' => __( 'RSS Feed sources (comma separated URLs or Feed Groups slug).', 'feedzy-rss-feeds' ),
				),
				'job_id'       => array(
					'type'        => 'integer',
					'description' => __( 'ID of the import job.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
				),
				'limit'        => array(
					'type'        => 'integer',
					'description' => __( 'Maximum number of feed items to preview.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
					'maximum'     => 50,
					'default'     => 5,
				),
				'inc_key'      => array(
					'type'        => 'string',
					'description' => __( 'Comma-separated include keywords; only matching items are shown.', 'feedzy-rss-feeds' ),
				),
				'include_html' => array(
					'type'        => 'boolean',
					'description' => __( 'Also return the rendered HTML preview. Default true.', 'feedzy-rss-feeds' ),
					'default'     => true,
				),
				'exc_key'      => array(
					'type'        => 'string',
					'description' => __( 'Comma-separated exclude keywords; matching items are hidden.', 'feedzy-rss-feeds' ),
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
						'preview_html' => array(
							'type'        => 'string',
							'description' => __( 'Rendered HTML preview of matching feed items.', 'feedzy-rss-feeds' ),
						),
						'items'        => array(
							'type'        => 'array',
							'description' => __( 'Feed items with the decision the import would take: import, duplicate, filtered or over_limit.', 'feedzy-rss-feeds' ),
							'items'       => array( 'type' => 'object' ),
						),
						'summary'      => array( 'type' => 'object' ),
						'target'       => array( 'type' => 'object' ),
						'notes'        => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
						'source'       => array( 'type' => 'string' ),
						'limit'        => array( 'type' => 'integer' ),
						'persisted'    => array(
							'type'        => 'boolean',
							'description' => __( 'Always false for dry runs - nothing was saved.', 'feedzy-rss-feeds' ),
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

	/**
	 * Resolve the feed source string from `source` or `job_id`.
	 *
	 * @param  array<string, mixed>|null $input Input array.
	 *
	 * @return string|WP_Error
	 */
	private function resolve_source( ?array $input ) {
		if ( ! empty( $input['source'] ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::sanitize_url_or_slug( (string) $input['source'] );
		}

		if ( ! empty( $input['job_id'] ) ) {
			$post_id = (int) $input['job_id'];
			$post    = get_post( $post_id );
			if ( ! $post || 'feedzy_imports' !== $post->post_type ) {
				return new WP_Error(
					'feedzy_not_found',
					/* translators: %d: import job ID */
					sprintf( __( 'Import job with ID %d was not found.', 'feedzy-rss-feeds' ), $post_id )
				);
			}
			$source = get_post_meta( $post_id, 'source', true );
			if ( empty( $source ) ) {
				return new WP_Error(
					'feedzy_no_source',
					__( 'No Source Configured', 'feedzy-rss-feeds' )
				);
			}
			return $source;
		}

		return new WP_Error(
			'feedzy_invalid_input',
			sprintf(
				/* translators: %s is the name of the required input field(s) */
				__( 'Provide either "%1$s" or "%2$s".', 'feedzy-rss-feeds' ),
				'source',
				'job_id'
			)
		);
	}
}
