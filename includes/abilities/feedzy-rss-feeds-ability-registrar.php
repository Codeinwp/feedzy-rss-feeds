<?php
/**
 * Feedzy Abilities Registrar.
 *
 * Registers the Feedzy ability category on `wp_abilities_api_categories_init`
 * and all individual abilities on `wp_abilities_api_init`.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_Registrar
 */
class Feedzy_Rss_Feeds_Ability_Registrar {

	/**
	 * Ability category slug registered under the WP Abilities API.
	 *
	 * @var   string
	 */
	const CATEGORY_SLUG = 'feedzy';

	/**
	 * Construct the registrar and attach it to the relevant hooks.
	 *
	 * Runs after the default priority so that the abilities declared here replace
	 * the copies registered by Feedzy Pro versions that still ship them.
	 */
	public function __construct() {
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ), 20 );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ), 20 );
	}

	/**
	 * Register the `feedzy` ability category.
	 *
	 * @return void
	 */
	public function register_category() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		if ( function_exists( 'wp_has_ability_category' ) && wp_has_ability_category( self::CATEGORY_SLUG ) ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY_SLUG,
			array(
				'label'       => __( 'Feedzy RSS Feeds', 'feedzy-rss-feeds' ),
				'description' => __( 'Abilities for managing Feedzy RSS feed sources, import jobs, and import monitoring.', 'feedzy-rss-feeds' ),
			)
		);
	}

	/**
	 * Require all ability class files, then register each one.
	 *
	 * @return void
	 */
	public function register_abilities() {
		if ( ! function_exists( 'wp_register_ability' ) || ! class_exists( 'WP_Ability' ) ) {
			return;
		}

		foreach ( $this->get_ability_classes() as $class ) {
			$name = self::CATEGORY_SLUG . '/' . $class::get_ability_name();

			if ( function_exists( 'wp_has_ability' ) && wp_has_ability( $name ) ) {
				if ( ! function_exists( 'wp_unregister_ability' ) ) {
					continue;
				}
				wp_unregister_ability( $name );
			}

			wp_register_ability(
				$name,
				array(
					'label'         => $class::get_ability_label(),
					'description'   => $class::get_ability_description(),
					'ability_class' => $class,
				)
			);
		}
	}

	/**
	 * Load the ability files found in the immediate subdirectories of the
	 * abilities directory and return their class names.
	 *
	 * @return string[]
	 */
	private function get_ability_classes() {
		require_once __DIR__ . '/feedzy-rss-feeds-ability-helpers.php';
		require_once __DIR__ . '/feedzy-rss-feeds-ability.php';

		$files = glob( __DIR__ . '/*/*.php' );
		if ( empty( $files ) ) {
			return array();
		}

		$classes = array();
		foreach ( $files as $file ) {
			require_once $file;

			$class = str_replace( '-', '_', ucwords( basename( $file, '.php' ), '-' ) );
			if ( class_exists( $class, false ) && is_a( $class, 'Feedzy_Rss_Feeds_Ability', true ) ) {
				$classes[] = $class;
			}
		}

		return $classes;
	}
}
