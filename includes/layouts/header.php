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
