<?php
/**
 * Header file for admin pages
 *
 * @package     Feedzy
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// phpcs:disable WordPress.Security.NonceVerification
$page_title = __( 'Settings', 'feedzy-rss-feeds' );
if ( isset( $_GET['page'] ) && 'feedzy-support' === $_GET['page'] ) {
	$page_title = __( 'Dashboard', 'feedzy-rss-feeds' );
} elseif ( isset( $_GET['page'] ) && 'feedzy-integration' === $_GET['page'] ) {
	$page_title = __( 'Integration', 'feedzy-rss-feeds' );
}

/**
 * Filters the page title shown in the Feedzy admin header.
 *
 * Allows Pro (or any extension) to supply a custom page title for new pages
 * that are added under the Feedzy menu without modifying this file.
 *
 * @since 5.1.0
 *
 * @param string $page_title The resolved page title.
 */
$page_title = apply_filters( 'feedzy_admin_page_title', $page_title );
?>
<div class="feedzy-header">
	<div class="feedzy-container">
		<div class="page-title h1"><?php echo esc_html( $page_title ); ?></div>
		<div class="feedzy-logo">
			<div class="feedzy-version"><?php echo esc_html( Feedzy_Rss_Feeds::get_version() ); ?></div>
			<div class="feedzy-logo-icon"><img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/feedzy.svg' ); ?>" width="60" height="60" alt=""></div>
		</div>
	</div>
</div>
