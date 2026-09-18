<?php
/**
 * Ability: feedzy/get-feed-health
 *
 * Return an aggregated health report across all Feedzy import jobs.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/status
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_Get_Feed_Health
 */
class Feedzy_Rss_Feeds_Ability_Get_Feed_Health extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'get-feed-health';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'Get Feed Health', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Return an aggregated health report across all Feedzy import jobs.', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array<string, mixed>|null $input Input arguments, validated and sanitized according to `input_schema()`.
	 *
	 * @return array<string, mixed> Output data, validated and sanitized according to `output_schema()`.
	 */
	public function execute_callback( ?array $input = null ) {
		$include_inactive = ! empty( $input['include_inactive'] );
		$post_status      = $include_inactive ? array( 'publish', 'draft' ) : 'publish';

		$jobs_limit = isset( $input['jobs_limit'] ) ? (int) $input['jobs_limit'] : 100;
		if ( $jobs_limit < 1 ) {
			$jobs_limit = 1;
		}
		if ( $jobs_limit > 500 ) {
			$jobs_limit = 500;
		}

		$total_jobs       = 0;
		$active_jobs      = 0;
		$total_imported   = 0;
		$jobs_with_errors = 0;
		$last_run_ts      = 0;
		$job_rollups      = array();

		$batch_size = 100;
		$page       = 1;

		do {
			$jobs = get_posts(
				array(
					'post_type'              => 'feedzy_imports',
					'post_status'            => $post_status,
					'posts_per_page'         => $batch_size,
					'paged'                  => $page,
					'orderby'                => 'ID',
					'order'                  => 'ASC',
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
				)
			);

			$batch_count = count( $jobs );

			foreach ( $jobs as $job ) {
				$status_data = Feedzy_Rss_Feeds_Ability_Helpers::build_import_status( $job );

				$cumulative     = (int) $status_data['cumulative_total'];
				$has_errors     = ! empty( $status_data['errors'] );
				$last_run       = $status_data['last_run'];
				$last_run_epoch = 0;
				if ( $last_run ) {
					$last_run_epoch = strtotime( $last_run );
					if ( $last_run_epoch > $last_run_ts ) {
						$last_run_ts = $last_run_epoch;
					}
				}

				++$total_jobs;

				if ( 'publish' === $job->post_status ) {
					++$active_jobs;
				}

				$total_imported += $cumulative;

				if ( $has_errors ) {
					++$jobs_with_errors;
				}

				if ( count( $job_rollups ) < $jobs_limit ) {
					$job_rollups[] = array(
						'id'               => $job->ID,
						'title'            => esc_html( $job->post_title ),
						'status'           => $job->post_status,
						'last_run'         => $last_run,
						'cumulative_total' => $cumulative,
						'has_errors'       => $has_errors,
					);
				}
			}

			++$page;
		} while ( $batch_count === $batch_size );

		$error_rate = $total_jobs > 0
			? round( ( $jobs_with_errors / $total_jobs ) * 100, 2 )
			: 0.0;

		return Feedzy_Rss_Feeds_Ability_Helpers::success(
			array(
				'total_jobs'       => $total_jobs,
				'active_jobs'      => $active_jobs,
				'total_imported'   => $total_imported,
				'jobs_with_errors' => $jobs_with_errors,
				'error_rate'       => $error_rate,
				'last_run'         => $last_run_ts > 0 ? gmdate( 'c', $last_run_ts ) : null,
				'jobs'             => $job_rollups,
				'jobs_returned'    => count( $job_rollups ),
				'truncated'        => $total_jobs > count( $job_rollups ),
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
				'include_inactive' => array(
					'type'        => 'boolean',
					'description' => sprintf(
						// translators: %s is the name of the post status.
						__( 'Include %s jobs in the report. Default false.', 'feedzy-rss-feeds' ),
						'inactive'
					),
					'default'     => false,
				),
				'jobs_limit'       => array(
					'type'        => 'integer',
					'description' => __( 'Maximum number of per-job rollups to include in the response. Default 100, max 500.', 'feedzy-rss-feeds' ),
					'minimum'     => 1,
					'maximum'     => 500,
					'default'     => 100,
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
						'total_jobs'       => array( 'type' => 'integer' ),
						'active_jobs'      => array( 'type' => 'integer' ),
						'total_imported'   => array( 'type' => 'integer' ),
						'jobs_with_errors' => array( 'type' => 'integer' ),
						'error_rate'       => array(
							'type'        => 'number',
							'description' => __( 'Percentage of jobs that have errors.', 'feedzy-rss-feeds' ),
						),
						'last_run'         => array( 'type' => array( 'string', 'null' ) ),
						'jobs'             => array(
							'type'        => 'array',
							'items'       => array( 'type' => 'object' ),
							'description' => __( 'Per-job rollups, capped at jobs_limit. See `truncated` to detect whether more jobs exist.', 'feedzy-rss-feeds' ),
						),
						'jobs_returned'    => array(
							'type'        => 'integer',
							'description' => __( 'Number of per-job rollups actually included in the `jobs` array.', 'feedzy-rss-feeds' ),
						),
						'truncated'        => array(
							'type'        => 'boolean',
							'description' => __( 'True when more jobs exist on the site than were returned in the `jobs` array.', 'feedzy-rss-feeds' ),
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
