<?php
/**
 * Plugin Name: Feedzy E2E Hide Support Hub
 * Description: Hides the SDK Support Hub on requests with the feedzy_e2e_hide_support_hub query argument, so e2e tests can check screens without its Help panel.
 *
 * @package feedzy-rss-feeds
 */

add_filter(
	'themeisle_sdk_hide_support_hub',
	function ( $hide ) {
		return $hide || isset( $_GET['feedzy_e2e_hide_support_hub'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
);
