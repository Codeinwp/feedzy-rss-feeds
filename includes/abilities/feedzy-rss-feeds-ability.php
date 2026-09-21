<?php
/**
 * Abstract base class for all Feedzy abilities.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities
 */

if ( ! class_exists( 'WP_Ability' ) ) {
	return;
}

/**
 * Class Feedzy_Rss_Feeds_Ability
 */
abstract class Feedzy_Rss_Feeds_Ability extends WP_Ability {

	/**
	 * Constructor.
	 * 
	 * @param string               $name The ability namespace.
	 * @param array<string, mixed> $properties Additional properties for this ability.
	 * @return void
	 */
	public function __construct( $name, $properties = array() ) {
		$merged_meta = array_replace_recursive(
			$this->meta(),
			isset( $properties['meta'] ) ? $properties['meta'] : array()
		);

		parent::__construct(
			$name,
			array(
				'label'               => $properties['label'] ?? '',
				'description'         => $properties['description'] ?? '',
				'category'            => $this->category(),
				'input_schema'        => $this->input_schema(),
				'output_schema'       => $this->output_schema(),
				'execute_callback'    => array( $this, 'execute_callback' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'meta'                => $merged_meta,
			)
		);
	}

	/**
	 * Get ability namespace.
	 *
	 * @return string
	 */
	abstract public static function get_ability_name();

	/**
	 * Get ability label.
	 *
	 * @return string
	 */
	abstract public static function get_ability_label();

	/**
	 * Get ability description.
	 *
	 * @return string
	 */
	abstract public static function get_ability_description();

	/**
	 * Get input schema for this ability.
	 *
	 * @return array<string, mixed> JSON Schema for validating and sanitizing input.
	 */
	abstract protected function input_schema();

	/**
	 * Get output schema for this ability.
	 *
	 * @return array<string, mixed> JSON Schema for validating and sanitizing output.
	 */
	abstract protected function output_schema();

	/**
	 * Execute callback for this ability.
	 *
	 * @param array<string, mixed>|null $input Input arguments, validated and sanitized according to `input_schema()`.
	 * @return array<string, mixed>|null Output data, validated and sanitized according to `output_schema()`.
	 */
	abstract public function execute_callback( ?array $input = null );

	/**
	 * Meta configuration for this ability.
	 *
	 * @return array<string, mixed>
	 */
	protected function meta() {
		return array(
			'show_in_rest' => true,
		);
	}

	/**
	 * Category slug shared by every Feedzy ability.
	 *
	 * @return string
	 */
	protected function category() {
		return Feedzy_Rss_Feeds_Ability_Registrar::CATEGORY_SLUG;
	}

	/**
	 * Capability checked against the Feedzy post referenced by the input.
	 *
	 * Mirrors the object-level check the import screen applies before acting on a job.
	 *
	 * @return string
	 */
	protected function object_capability() {
		return 'edit_post';
	}

	/**
	 * Default permission callback: delegate to the canonical Feedzy gate, then
	 * apply the object-level check when the input references a Feedzy post.
	 *
	 * @param mixed $input Ability input.
	 *
	 * @return true|WP_Error
	 */
	public function check_permission( $input = null ) {
		$allowed = Feedzy_Rss_Feeds_Ability_Helpers::permission_or_error();
		if ( true !== $allowed ) {
			return $allowed;
		}

		if ( ! is_array( $input ) ) {
			return true;
		}

		foreach ( array( 'id', 'job_id' ) as $key ) {
			if ( empty( $input[ $key ] ) || ! is_numeric( $input[ $key ] ) ) {
				continue;
			}
			$post_id = (int) $input[ $key ];
			if ( ! in_array( get_post_type( $post_id ), array( 'feedzy_imports', 'feedzy_categories' ), true ) ) {
				continue;
			}
			if ( ! current_user_can( $this->object_capability(), $post_id ) ) {
				return new WP_Error(
					'feedzy_forbidden',
					__( 'You do not have permission to do this.', 'feedzy-rss-feeds' ),
					array( 'status' => 403 )
				);
			}
		}

		return true;
	}
}
