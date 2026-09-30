<?php
/**
 * Abilities API stubs (WordPress 6.9+).
 *
 * Lets PHPStan resolve the Abilities API until the bundled WordPress stubs include it.
 *
 * @package feedzy-rss-feeds
 */

if ( ! class_exists( 'WP_Ability' ) ) {
	/**
	 * Stub of the core WP_Ability class.
	 */
	class WP_Ability {

		/**
		 * Constructor.
		 *
		 * @param string               $name The ability name.
		 * @param array<string, mixed> $args The ability arguments.
		 */
		public function __construct( $name, $args ) {}
	}
}
