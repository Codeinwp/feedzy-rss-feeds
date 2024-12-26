<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Visualizer
 */
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( class_exists( '\Yoast\PHPUnitPolyfills\Autoload' ) === false ) {
	require_once dirname( dirname( __FILE__ ) ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
}

if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}
/**
 * The path to the main file of the plugin to test.
 */
define( 'WP_USE_THEMES', false );
define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );
define( 'TI_UNIT_TESTING', true );

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';
/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/feedzy-rss-feed.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
activate_plugin( 'feedzy-rss-feeds/feedzy-rss-feed.php' );
global $current_user;
$current_user = new WP_User( 1 );
$current_user->set_role( 'administrator' );
wp_update_user(
	array(
		'ID'         => 1,
		'first_name' => 'Admin',
		'last_name'  => 'User',
	)
);
