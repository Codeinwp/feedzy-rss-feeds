<?php
/**
 * Header file for admin pages
 *
 * @package     Feedzy
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
?>
<div class="fz-features-header">
	<p class="logo">Feedzy RSS Feeds</p>
	<span class="slogan">by <a
				href="https://themeisle.com/">ThemeIsle</a></span>
	<div class="header-btns">
		<?php
		if ( ! defined( 'FEEDZY_PRO_ABSURL' ) ) :
			?>

			<a target="_blank" href="<?php echo FEEDZY_UPSELL_LINK; ?>" class="buy-now"><span
						class="dashicons dashicons-cart"></span> Upgrade</a>
			<?php
		endif;
		?>
	</div>
</div><!-- .fz-features-header -->
