<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://themeisle.com
 * @since             3.0.0
 * @package           Feedzy_Rss_Feeds
 *
 * @wordpress-plugin
 * Plugin Name:       Feedzy RSS Feeds
 * Plugin URI:        http://themeisle.com/plugins/feedzy-rss-feeds/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           3.0.0
 * Author:            Themeisle
 * Author URI:        http://themeisle.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       feedzy_rss_translate
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-feedzy-rss-feeds-activator.php
 */
function activate_feedzy_rss_feeds() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-feedzy-rss-feeds-activator.php';
	Feedzy_Rss_Feeds_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-feedzy-rss-feeds-deactivator.php
 */
function deactivate_feedzy_rss_feeds() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-feedzy-rss-feeds-deactivator.php';
	Feedzy_Rss_Feeds_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_feedzy_rss_feeds' );
register_deactivation_hook( __FILE__, 'deactivate_feedzy_rss_feeds' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-feedzy-rss-feeds.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_feedzy_rss_feeds() {

	$plugin = new Feedzy_Rss_Feeds();
	$plugin->run();

}
run_feedzy_rss_feeds();
