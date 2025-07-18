<?php
/**
 * Feedzy Constants.
 * 
 * Adds Feedzy constants for PHPStan to use.
 * 
 * @package feedzy-rss-feeds
 */

define( 'FEEDZY_BASEFILE', __FILE__ );
define( 'FEEDZY_ABSURL', plugins_url( '/', __FILE__ ) );
define( 'FEEDZY_BASENAME', plugin_basename( __FILE__ ) );
define( 'FEEDZY_ABSPATH', __DIR__ );
define( 'FEEDZY_DIRNAME', basename( FEEDZY_ABSPATH ) );
define( 'FEEDZY_UPSELL_LINK', 'https://themeisle.com/plugins/feedzy-rss-feeds/upgrade/' );
define( 'FEEDZY_SUBSCRIBE_API', 'https://api.themeisle.com/tracking/subscribe' );
define( 'FEEDZY_NAME', 'Feedzy RSS Feeds' );
define( 'FEEDZY_USER_AGENT', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36' );
define( 'FEEDZY_ALLOW_HTTPS', true );
define( 'FEEDZY_REST_VERSION', '1' );
// to redirect all themeisle_log_event to error log.
define( 'FEEDZY_LOCAL_DEBUG', false );
define( 'FEEDZY_FEED_CUSTOM_TAG_NAMESPACE', 'http://feedzy.themeisle.com' );
define( 'FEEDZY_PRO_VERSION', '1.2.3' );

// always make this true before testing
// also used in gutenberg.
define( 'FEEDZY_DISABLE_CACHE_FOR_TESTING', false );
