<?php
/**
 * Ability: feedzy/update-import-template
 *
 * Update map content settings for an existing import job.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities/imports
 * @since      3.2.0
 */

declare( strict_types = 1 );

/**
 * Class Feedzy_Rss_Feeds_Ability_Update_Import_Template
 */
class Feedzy_Rss_Feeds_Ability_Update_Import_Template extends Feedzy_Rss_Feeds_Ability {

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_name() {
		return 'update-import-template';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_label() {
		return __( 'Update Import Template', 'feedzy-rss-feeds' );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_ability_description() {
		return __( 'Update map content settings for an existing import job.', 'feedzy-rss-feeds' );
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
		$post    = get_post( $post_id );

		if ( ! $post || 'feedzy_imports' !== $post->post_type ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_not_found',
				/* translators: %d: import job ID */
				sprintf( __( 'Import job with ID %d was not found.', 'feedzy-rss-feeds' ), $post_id )
			);
		}

		foreach ( array( 'validate_edition', 'validate_author' ) as $check ) {
			$valid = Feedzy_Rss_Feeds_Ability_Helpers::$check( (array) $input );
			if ( is_wp_error( $valid ) ) {
				return Feedzy_Rss_Feeds_Ability_Helpers::from_wp_error( $valid );
			}
		}

		$template_keys = array(
			'import_post_type',
			'import_post_status',
			'import_post_term',
			'import_post_author',
			'import_post_title',
			'import_post_content',
			'import_post_excerpt',
			'import_post_featured_img',
			'import_post_date',
			'default_thumbnail_id',
			'import_remove_html',
			'import_use_external_image',
			'import_link_author_admin',
			'import_link_author_public',
			'custom_fields',
		);

		$all_input  = Feedzy_Rss_Feeds_Ability_Helpers::resolve_ai_actions( (array) $input, $post_id );
		$meta_input = array_intersect_key( $all_input, array_flip( $template_keys ) );

		if ( empty( $meta_input ) ) {
			return Feedzy_Rss_Feeds_Ability_Helpers::error(
				'feedzy_invalid_input',
				__( 'No template fields were provided to update.', 'feedzy-rss-feeds' )
			);
		}

		Feedzy_Rss_Feeds_Ability_Helpers::save_import_meta( $post_id, $meta_input );

		$updated_post = get_post( $post_id );

		if ( class_exists( 'Feedzy_Rss_Feeds_Log' ) ) {
			Feedzy_Rss_Feeds_Log::info(
				'Ability feedzy/update-import-template: updated template for job ID ' . $post_id,
				array(
					'post_id' => $post_id,
					'fields'  => array_keys( $meta_input ),
				)
			);
		}

		return Feedzy_Rss_Feeds_Ability_Helpers::success(
			Feedzy_Rss_Feeds_Ability_Helpers::shape_import( $updated_post )
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function input_schema() {
		return array(
			'type'                 => 'object',
			'required'             => array( 'id' ),
			'additionalProperties' => false,
			'properties'           => array(
				'id'                        => array(
					'type'    => 'integer',
					'minimum' => 1,
				),
				'import_post_type'          => array(
					'type'        => 'string',
					'description' => __( 'Post Type', 'feedzy-rss-feeds' ),
				),
				'import_post_status'        => array(
					'type' => 'string',
					'enum' => array( 'publish', 'draft', 'pending', 'private' ),
				),
				'import_post_term'          => array(
					'type'        => 'string',
					'description' => __( 'Taxonomy term(s) for imported posts.', 'feedzy-rss-feeds' ),
				),
				'import_post_author'        => array(
					'type'        => 'string',
					'description' => __( 'Author username for imported posts.', 'feedzy-rss-feeds' ),
				),
				'import_post_title'         => array(
					'type'        => 'string',
					'description' => __( 'Magic-tag template for the imported post title.', 'feedzy-rss-feeds' ),
				),
				'import_post_content'       => array(
					'type'        => 'string',
					'description' => __( 'Magic-tag template for the imported post content.', 'feedzy-rss-feeds' ),
				),
				'import_post_excerpt'       => array(
					'type'        => 'string',
					'description' => __( 'Magic-tag template for the imported post excerpt.', 'feedzy-rss-feeds' ),
				),
				'import_post_featured_img'  => array(
					'type'        => 'string',
					'description' => __( 'Magic-tag template for the featured image.', 'feedzy-rss-feeds' ),
				),
				'import_post_date'          => array(
					'type'        => 'string',
					'description' => __( 'Date source for imported posts.', 'feedzy-rss-feeds' ),
				),
				'default_thumbnail_id'      => array(
					'type'        => 'integer',
					'description' => __( 'Attachment ID to use as the fallback featured image.', 'feedzy-rss-feeds' ),
					'minimum'     => 0,
				),
				'import_link_author_admin'  => array(
					'type'        => 'string',
					'description' => __( 'The source author will appear in the Dashboard', 'feedzy-rss-feeds' ) . ' (Pro)',
					'enum'        => array( 'yes', 'no' ),
				),
				'import_link_author_public' => array(
					'type'        => 'string',
					'description' => __( 'The source author will appear in Archive Pages', 'feedzy-rss-feeds' ) . ' (Pro)',
					'enum'        => array( 'yes', 'no' ),
				),
				'custom_fields'             => array(
					'type'                 => 'object',
					'description'          => __( 'Custom fields added to each imported post, as meta key => value or magic-tag template (Pro, Business plan).', 'feedzy-rss-feeds' ),
					'additionalProperties' => array( 'type' => 'string' ),
				),
				'title_action'              => array(
					'description' => __( 'Action(s) to apply to the post title.', 'feedzy-rss-feeds' ) . ' ' . __( 'Pass a single action object or an array of actions.', 'feedzy-rss-feeds' ),
					'oneOf'       => array(
						array(
							'type'                 => 'object',
							'properties'           => Feedzy_Rss_Feeds_Ability_Create_Import::action_descriptor_schema(),
							'required'             => array( 'type' ),
							'additionalProperties' => false,
						),
						array(
							'type'     => 'array',
							'items'    => array(
								'type'                 => 'object',
								'properties'           => Feedzy_Rss_Feeds_Ability_Create_Import::action_descriptor_schema(),
								'required'             => array( 'type' ),
								'additionalProperties' => false,
							),
							'minItems' => 1,
						),
					),
				),
				'content_action'            => array(
					'description' => __( 'Action(s) to apply to the post content.', 'feedzy-rss-feeds' ) . ' ' . __( 'Pass a single action object or an array of actions.', 'feedzy-rss-feeds' ),
					'oneOf'       => array(
						array(
							'type'                 => 'object',
							'properties'           => Feedzy_Rss_Feeds_Ability_Create_Import::action_descriptor_schema(),
							'required'             => array( 'type' ),
							'additionalProperties' => false,
						),
						array(
							'type'     => 'array',
							'items'    => array(
								'type'                 => 'object',
								'properties'           => Feedzy_Rss_Feeds_Ability_Create_Import::action_descriptor_schema(),
								'required'             => array( 'type' ),
								'additionalProperties' => false,
							),
							'minItems' => 1,
						),
					),
				),
				'excerpt_action'            => array(
					'description' => __( 'Action(s) to apply to the post excerpt.', 'feedzy-rss-feeds' ) . ' ' . __( 'Pass a single action object or an array of actions.', 'feedzy-rss-feeds' ),
					'oneOf'       => array(
						array(
							'type'                 => 'object',
							'properties'           => Feedzy_Rss_Feeds_Ability_Create_Import::action_descriptor_schema(),
							'required'             => array( 'type' ),
							'additionalProperties' => false,
						),
						array(
							'type'     => 'array',
							'items'    => array(
								'type'                 => 'object',
								'properties'           => Feedzy_Rss_Feeds_Ability_Create_Import::action_descriptor_schema(),
								'required'             => array( 'type' ),
								'additionalProperties' => false,
							),
							'minItems' => 1,
						),
					),
				),
				'featured_img_action'       => array(
					'description' => __( 'Action(s) to apply to the featured image field.', 'feedzy-rss-feeds' ) . ' ' . __( 'Pass a single action object or an array of actions.', 'feedzy-rss-feeds' ),
					'oneOf'       => array(
						array(
							'type'                 => 'object',
							'properties'           => Feedzy_Rss_Feeds_Ability_Create_Import::action_descriptor_schema(),
							'required'             => array( 'type' ),
							'additionalProperties' => false,
						),
						array(
							'type'     => 'array',
							'items'    => array(
								'type'                 => 'object',
								'properties'           => Feedzy_Rss_Feeds_Ability_Create_Import::action_descriptor_schema(),
								'required'             => array( 'type' ),
								'additionalProperties' => false,
							),
							'minItems' => 1,
						),
					),
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
