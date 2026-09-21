<?php
/**
 * Ability: feedzy/create-import
 *
 * Creates a new Feedzy import job with specified settings and templates.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/imports
 * @since      3.2.0
 */

declare( strict_types = 1 );

/**
 * Class Feedzy_Rss_Feeds_Ability_Create_Import
 */
class Feedzy_Rss_Feeds_Ability_Create_Import extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'create-import';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'Create Import Job', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Create a new Feedzy import job with specified settings and templates.', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array<string, mixed>|null $input Input arguments, validated and sanitized according to `input_schema()`.
	 *
	 * @return array<string, mixed> Output data, validated and sanitized according to `output_schema()`.
	 */
	public function execute_callback( ?array $input = null ) {
		if ( empty( $input['title'] ) || empty( $input['source'] ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_invalid_input',
				__( '"title" and "source" are required to create an import job.', 'feedzy-rss-feeds' )
			);
		}

		foreach ( array( 'validate_edition', 'validate_author' ) as $check ) {
			$valid = Feedzy_Rss_Feeds_Ability_Helpers::$check( (array) $input );
			if ( is_wp_error( $valid ) ) {
				return Feedzy_Rss_Feeds_Ability_Helpers::from_wp_error( $valid );
			}
		}

		$title  = sanitize_text_field( (string) $input['title'] );
		$status = in_array( $input['status'] ?? 'publish', array( 'publish', 'draft' ), true )
			? ( $input['status'] ?? 'publish' )
			: 'publish';

		$post_id = wp_insert_post(
			array(
				'post_title'  => $title,
				'post_type'   => 'feedzy_imports',
				'post_status' => $status,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::from_wp_error( $post_id );
		}

		$meta_input = $this->extract_meta( $input );
		$meta_input = Feedzy_Rss_Feeds_Ability_Helpers::resolve_ai_actions( $meta_input );
		$meta_input = $this->apply_job_defaults( $meta_input );

		Feedzy_Rss_Feeds_Ability_Helpers::save_import_meta( $post_id, $meta_input );

		$source_type = Feedzy_Rss_Feeds_Ability_Helpers::detect_source_type(
			$meta_input['source'] ?? ''
		);
		update_post_meta( $post_id, '__feedzy_source_type', $source_type );

		if ( 'publish' === $status ) {
			$this->schedule_import_cron( $post_id );
		}

		$post = get_post( $post_id );

		if ( class_exists( 'Feedzy_Rss_Feeds_Log' ) ) {
			Feedzy_Rss_Feeds_Log::info(
				'Ability feedzy/create-import: created job ' . $title,
				array(
					'post_id'     => $post_id,
					'source_type' => $source_type,
				)
			);
		}

		return Feedzy_Rss_Feeds_Ability_Helpers::success(
			Feedzy_Rss_Feeds_Ability_Helpers::shape_import( $post )
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function input_schema() {
		return array(
			'type'                 => 'object',
			'required'             => array( 'title', 'source' ),
			'additionalProperties' => false,
			'properties'           => array_merge(
				array(
					'title'  => array(
						'type'        => 'string',
						'description' => __( 'Import Title', 'feedzy-rss-feeds' ),
						'minLength'   => 1,
					),
					'status' => array(
						'type'    => 'string',
						'enum'    => array( 'publish', 'draft' ),
						'default' => 'publish',
					),
				),
				self::import_meta_schema()
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
			'idempotent'  => false,
		);
		return $meta;
	}

	/**
	 * Schedule the import cron job for a given import post ID, if not already scheduled.
	 *
	 * @param  int $post_id The ID of the newly created import job post.
	 *
	 * @return void
	 */
	private function schedule_import_cron( int $post_id ): void {
		if ( ! class_exists( 'Feedzy_Rss_Feeds_Util_Scheduler' ) ) {
			return;
		}

		$free_settings   = get_option( 'feedzy-settings', array() );
		$global_schedule = ! empty( $free_settings['general']['fz_cron_schedule'] )
			? $free_settings['general']['fz_cron_schedule']
			: 'daily';

		if ( false === Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'feedzy_cron' ) ) {
			Feedzy_Rss_Feeds_Util_Scheduler::schedule_event(
				time() + 10,
				$global_schedule,
				'feedzy_cron'
			);
		}

		$job_schedule = get_post_meta( $post_id, 'fz_cron_schedule', true );
		if (
			! empty( $job_schedule ) &&
			false === Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'feedzy_cron', array( 100, $post_id ) )
		) {
			Feedzy_Rss_Feeds_Util_Scheduler::schedule_event(
				time() + 10,
				$job_schedule,
				'feedzy_cron',
				array( 100, $post_id )
			);
		}
	}

	/**
	 * Extract only the import meta fields from raw input.
	 *
	 * @param  array<string, mixed> $input Raw input array.
	 *
	 * @return array<string, mixed>
	 */
	private function extract_meta( array $input ) {
		$non_meta = array( 'title', 'status' );
		return array_diff_key( $input, array_flip( $non_meta ) );
	}

	/**
	 * Apply default values to import meta fields when not supplied by the caller.
	 *
	 * @param  array<string, mixed> $meta Caller-supplied meta fields.
	 *
	 * @return array<string, mixed> Meta with defaults applied.
	 */
	private function apply_job_defaults( array $meta ) {
		$defaults = array(
			'import_post_title'         => '[#item_title]',
			'import_post_content'       => '[#item_content]',
			'import_post_featured_img'  => '[#item_image]',
			'import_post_date'          => '[#item_date]',
			'import_post_type'          => 'post',
			'import_post_status'        => 'publish',
			'import_remove_duplicates'  => 'yes',
			'import_remove_html'        => 'no',
			'import_use_external_image' => 'no',
			'import_feed_limit'         => 10,
		);

		foreach ( $defaults as $key => $default_value ) {
			if ( empty( $meta[ $key ] ) ) {
				$meta[ $key ] = $default_value;
			}
		}

		return $meta;
	}

	/**
	 * JSON Schema properties for all import meta fields, shared with update-import.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function import_meta_schema() {
		return array(
			'source'                       => array(
				'type'        => 'string',
				'description' => __( 'RSS Feed sources (comma separated URLs or Feed Groups slug).', 'feedzy-rss-feeds' ),
			),
			'import_post_type'             => array(
				'type'        => 'string',
				'description' => __( 'Post Type', 'feedzy-rss-feeds' ),
				'default'     => 'post',
			),
			'import_post_status'           => array(
				'type'    => 'string',
				'enum'    => array( 'publish', 'draft', 'pending', 'private' ),
				'default' => 'publish',
			),
			'import_post_term'             => array(
				'type'        => 'string',
				'description' => __( 'Taxonomy term(s) for imported posts.', 'feedzy-rss-feeds' ),
			),
			'import_post_author'           => array(
				'type'        => 'string',
				'description' => __( 'Author username for imported posts.', 'feedzy-rss-feeds' ),
			),
			'import_post_title'            => array(
				'type'        => 'string',
				'description' => __( 'Magic-tag template for the imported post title.', 'feedzy-rss-feeds' ),
			),
			'import_post_content'          => array(
				'type'        => 'string',
				'description' => __( 'Magic-tag template for the imported post content.', 'feedzy-rss-feeds' ),
			),
			'import_post_excerpt'          => array(
				'type'        => 'string',
				'description' => __( 'Magic-tag template for the imported post excerpt.', 'feedzy-rss-feeds' ),
			),
			'import_post_featured_img'     => array(
				'type'        => 'string',
				'description' => __( 'Magic-tag template for the featured image.', 'feedzy-rss-feeds' ),
			),
			'import_post_date'             => array(
				'type'        => 'string',
				'description' => __( 'Date source for imported posts.', 'feedzy-rss-feeds' ),
			),
			'import_feed_limit'            => array(
				'type'        => 'integer',
				'description' => __( 'Set the number of feed items to import per run.', 'feedzy-rss-feeds' ),
				'minimum'     => 1,
				'maximum'     => 9999,
			),
			'import_remove_duplicates'     => array(
				'type'    => 'string',
				'enum'    => array( 'yes', 'no' ),
				'default' => 'yes',
			),
			'import_use_external_image'    => array(
				'type'    => 'string',
				'enum'    => array( 'yes', 'no' ),
				'default' => 'no',
			),
			'import_remove_html'           => array(
				'type'    => 'string',
				'enum'    => array( 'yes', 'no' ),
				'default' => 'no',
			),
			'import_order'                 => array(
				'type'        => 'string',
				'description' => __( 'Feed Order', 'feedzy-rss-feeds' ),
			),
			'inc_key'                      => array(
				'type'        => 'string',
				'description' => __( 'Comma-separated include keywords. Only items matching these are imported.', 'feedzy-rss-feeds' ),
			),
			'exc_key'                      => array(
				'type'        => 'string',
				'description' => __( 'Comma-separated exclude keywords. Items matching these are skipped.', 'feedzy-rss-feeds' ),
			),
			'inc_on'                       => array(
				'type'        => 'string',
				'description' => __( 'Where to apply the include filter.', 'feedzy-rss-feeds' ),
			),
			'exc_on'                       => array(
				'type'        => 'string',
				'description' => __( 'Where to apply the exclude filter.', 'feedzy-rss-feeds' ),
			),
			'from_datetime'                => array(
				'type'        => 'string',
				'description' => __( 'Only import items published after this datetime.', 'feedzy-rss-feeds' ),
			),
			'to_datetime'                  => array(
				'type'        => 'string',
				'description' => __( 'Only import items published before this datetime.', 'feedzy-rss-feeds' ),
			),
			'fz_cron_schedule'             => array(
				'type'        => 'string',
				'description' => __( 'Per-job cron schedule override. Leave empty to use the global schedule.', 'feedzy-rss-feeds' ),
			),
			'import_auto_translation'      => array(
				'type'        => 'string',
				'description' => __( 'Translate content automatically on import.', 'feedzy-rss-feeds' ),
				'enum'        => array( 'yes', 'no' ),
			),
			'import_auto_translation_lang' => array(
				'type'        => 'string',
				'description' => __( 'Target Language', 'feedzy-rss-feeds' ),
			),
			'language'                     => array(
				'type'        => 'string',
				'description' => __( 'Content Language after import', 'feedzy-rss-feeds' ),
			),
			'mark_duplicate_tag'           => array(
				'type'        => 'string',
				'description' => __( 'Define a custom duplication key for identifying unique feed items when importing content. By default, items are considered unique based on their title and URL. Enter one or multiple magic tags.', 'feedzy-rss-feeds' ) . ' (Pro)',
			),
			'import_link_author_admin'     => array(
				'type'        => 'string',
				'description' => __( 'The source author will appear in the Dashboard', 'feedzy-rss-feeds' ) . ' (Pro)',
				'enum'        => array( 'yes', 'no' ),
			),
			'import_link_author_public'    => array(
				'type'        => 'string',
				'description' => __( 'The source author will appear in Archive Pages', 'feedzy-rss-feeds' ) . ' (Pro)',
				'enum'        => array( 'yes', 'no' ),
			),
			'default_thumbnail_id'         => array(
				'type'        => 'string',
				'description' => __( 'Comma-separated attachment IDs used as fallback featured image (Pro).', 'feedzy-rss-feeds' ),
			),
			'import_feed_delete_days'      => array(
				'type'        => 'integer',
				'description' => __( 'Delete the posts created for this import after a number of days', 'feedzy-rss-feeds' ) . '. ' . __( 'Helpful if you want to remove stale or old items automatically. Choose 0, and the imported items will not be automatically deleted.', 'feedzy-rss-feeds' ) . ' (Pro)',
				'minimum'     => 0,
				'maximum'     => 9999,
			),
			'custom_fields'                => array(
				'type'                 => 'object',
				'description'          => __( 'Custom fields added to each imported post, as meta key => value or magic-tag template (Pro, Business plan).', 'feedzy-rss-feeds' ),
				'additionalProperties' => array( 'type' => 'string' ),
			),
			'title_action'                 => array(
				'description' => __( 'Action(s) to apply to the post title.', 'feedzy-rss-feeds' ) . ' ' . __( 'Pass a single action object or an array of actions.', 'feedzy-rss-feeds' ),
				'oneOf'       => array(
					array(
						'type'                 => 'object',
						'properties'           => self::action_descriptor_schema(),
						'required'             => array( 'type' ),
						'additionalProperties' => false,
					),
					array(
						'type'     => 'array',
						'items'    => array(
							'type'                 => 'object',
							'properties'           => self::action_descriptor_schema(),
							'required'             => array( 'type' ),
							'additionalProperties' => false,
						),
						'minItems' => 1,
					),
				),
			),
			'content_action'               => array(
				'description' => __( 'Action(s) to apply to the post content.', 'feedzy-rss-feeds' ) . ' ' . __( 'Pass a single action object or an array of actions.', 'feedzy-rss-feeds' ),
				'oneOf'       => array(
					array(
						'type'                 => 'object',
						'properties'           => self::action_descriptor_schema(),
						'required'             => array( 'type' ),
						'additionalProperties' => false,
					),
					array(
						'type'     => 'array',
						'items'    => array(
							'type'                 => 'object',
							'properties'           => self::action_descriptor_schema(),
							'required'             => array( 'type' ),
							'additionalProperties' => false,
						),
						'minItems' => 1,
					),
				),
			),
			'excerpt_action'               => array(
				'description' => __( 'Action(s) to apply to the post excerpt.', 'feedzy-rss-feeds' ) . ' ' . __( 'Pass a single action object or an array of actions.', 'feedzy-rss-feeds' ),
				'oneOf'       => array(
					array(
						'type'                 => 'object',
						'properties'           => self::action_descriptor_schema(),
						'required'             => array( 'type' ),
						'additionalProperties' => false,
					),
					array(
						'type'     => 'array',
						'items'    => array(
							'type'                 => 'object',
							'properties'           => self::action_descriptor_schema(),
							'required'             => array( 'type' ),
							'additionalProperties' => false,
						),
						'minItems' => 1,
					),
				),
			),
			'featured_img_action'          => array(
				'description' => __( 'Action(s) to apply to the featured image field.', 'feedzy-rss-feeds' ) . ' ' . __( 'Pass a single action object or an array of actions.', 'feedzy-rss-feeds' ),
				'oneOf'       => array(
					array(
						'type'                 => 'object',
						'properties'           => self::action_descriptor_schema(),
						'required'             => array( 'type' ),
						'additionalProperties' => false,
					),
					array(
						'type'     => 'array',
						'items'    => array(
							'type'                 => 'object',
							'properties'           => self::action_descriptor_schema(),
							'required'             => array( 'type' ),
							'additionalProperties' => false,
						),
						'minItems' => 1,
					),
				),
			),
		);
	}

	/**
	 * JSON Schema for action descriptor objects used in title_action, content_action, etc.
	 *
	 * @return array<string, mixed>
	 */
	public static function action_descriptor_schema() {
		return array(
			'type'         => array(
				'type'        => 'string',
				'description' => __( 'Action id.', 'feedzy-rss-feeds' ),
				'enum'        => array( 'chat_gpt_rewrite', 'fz_summarize', 'fz_image', 'trim', 'search_replace', 'fz_translate', 'fz_paraphrase', 'modify_links', 'spinnerchief', 'wordAI', 'custom_html' ),
			),
			'prompt'       => array(
				'type'        => 'string',
				'description' => sprintf(
					// translators: %1$s is the action type, %2$s is the placeholder for magic tags, %3$s is the action type again.
					__( 'Prompt template for %1$s. Use {%2$s} placeholder only for %3$s action.', 'feedzy-rss-feeds' ),
					'chat_gpt_rewrite / fz_image',
					'content',
					'chat_gpt_rewrite'
				),
			),
			'provider'     => array(
				'type'        => 'string',
				'description' => sprintf(
					// translators: %s is magic tags.
					__( 'AI provider slug. Applies to %s.', 'feedzy-rss-feeds' ),
					'chat_gpt_rewrite / fz_summarize'
				),
				'default'     => 'openai',
			),
			'model'        => array(
				'type'        => 'string',
				'description' => sprintf(
					// translators: %s is magic tags.
					__( 'AI model override. Applies to %s.', 'feedzy-rss-feeds' ),
					'chat_gpt_rewrite / fz_summarize'
				),
			),
			'only_missing' => array(
				'type'        => 'boolean',
				'description' => 'fz_image: ' . __( 'Only generate the featured image if it\'s missing in the source XML RSS Feed.', 'feedzy-rss-feeds' ),
				'default'     => true,
			),
			'length'       => array(
				'type'        => 'integer',
				'description' => 'trim: ' . __( 'Maximum number of words to keep.', 'feedzy-rss-feeds' ),
				'minimum'     => 1,
			),
			'search'       => array(
				'type'        => 'string',
				'description' => 'search_replace: ' . __( 'Text (or pattern) to find.', 'feedzy-rss-feeds' ),
			),
			'replace'      => array(
				'type'        => 'string',
				'description' => 'search_replace: ' . __( 'Replace with', 'feedzy-rss-feeds' ),
			),
			'mode'         => array(
				'type'        => 'string',
				'description' => 'search_replace: ' . __( 'Match mode. One of: text (default), wildcard, regex.', 'feedzy-rss-feeds' ),
				'enum'        => array( 'text', 'wildcard', 'regex' ),
				'default'     => 'text',
			),
			'lang'         => array(
				'type'        => 'string',
				'description' => __( 'Target Language', 'feedzy-rss-feeds' ),
			),
			'remove_links' => array(
				'type'        => 'boolean',
				'description' => 'modify_links: ' . __( 'Remove links from the content?', 'feedzy-rss-feeds' ),
			),
			'target'       => array(
				'type'        => 'string',
				'description' => 'modify_links: ' . __( 'Target attribute for links.', 'feedzy-rss-feeds' ),
			),
			'follow'       => array(
				'type'        => 'string',
				'description' => 'modify_links: ' . sprintf(
					// translators: %s is the default value for the nofollow parameter.
					__( 'Set nofollow on links. Default: %s.', 'feedzy-rss-feeds' ),
					'yes'
				),
				'enum'        => array( 'yes', 'no' ),
			),
		);
	}
}
