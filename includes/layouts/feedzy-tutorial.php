<!--
Layout For Tutorial Page of Feedzy RSS Feeds

@since    ?
@package  feedzy-rss-feeds
-->
<div id="fz-features">

	<div class="fz-features-content">
	
		<a name="shortcode"></a>
		<div class="fz-feature">
			<div class="fz-feature-features">
			<h2>Shortcode</h2>
				<p>Show feed items using the <code>[feedzy-rss-feeds]</code>shortcode in a few easy steps.</p>
				<p>You can view our documentation <a href="https://docs.themeisle.com/article/658-feedzy-rss-feeds" target="_blank">here</a></p>
			</div>
			<div class="fz-feature-image">
				<iframe width="603" height="508" src="https://www.youtube.com/embed/GEFAY2IxxEc?start=84" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		</div>

		<?php if ( class_exists( 'Feedzy_Rss_Feeds_Pro' ) ) { ?>
		<a name="import"></a>
		<div class="fz-feature">
			<div class="fz-feature-features">
			<h2>Feed to Post</h2>
				<p>Convert feed items into WordPress Posts, Pages, or any custom post type in a few easy steps.</p>
				<p>You can view our documentation <a href="https://docs.themeisle.com/article/742-how-to-import-posts-from-feeds-in-feedzy" target="_blank">here</a></p>		
			</div>
			<div class="fz-feature-image">
				<iframe width="603" height="508" src="https://www.youtube.com/embed/Fzx5W_PfQsQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		</div>
		<?php } ?>

		<div class="fz-feature">
		<div class="fz-feature-features fz-feature-centered">
			<h2>Grow your WordPress business with Feedzy today.</h4>
		<div class="header-btns">
			<?php
			if ( ! defined( 'FEEDZY_PRO_ABSURL' ) ) :
				?>
			<a target="_blank" href="<?php echo FEEDZY_UPSELL_LINK; ?>" class="buy-now"><span
			class="dashicons dashicons-cart"></span> Get Feedzy Pro</a>
				<?php
			endif;
			?>
		</div>

	</div><!-- .fz-features-content -->

</div>
