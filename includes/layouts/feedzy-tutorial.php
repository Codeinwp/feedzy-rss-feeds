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
				<iframe width="600" height="300" src="https://www.youtube.com/embed/GEFAY2IxxEc?start=84" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		</div>

		<?php if ( class_exists( 'Feedzy_Rss_Feeds_Pro' ) ) { ?>
		<div class="fz-feature">
			<div class="fz-feature-features">
				<h2>Shortcode templates to suit your style</h2>
				<p><a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#examples" target="_blank">Three beautiful templates</a> are available for you to choose the style which best fits your design. We even have support for complex media such as <a href="https://demo.themeisle.com/feedzy-rss-feeds/audio-feed-template/" target="_blank">audio playback</a> and <a href="https://docs.themeisle.com/article/1113-how-to-get-videos-from-youtube-with-feedzy" target="_blank">YouTube videos</a>.</p>
				<p>If you like to roll your own templates, <a href="https://docs.themeisle.com/article/1162-feedzy-custom-templates" target="_blank">template tags</a> are available to help you do just that!</p>
			</div>
			<div class="fz-feature-image">
				<img src="<?php echo FEEDZY_ABSURL; ?>/img/features-templates.jpg" alt="Feed templates">
			</div>
		</div>
		<?php } ?>

		<?php if ( class_exists( 'Feedzy_Rss_Feeds_Pro' ) ) { ?>
		<a name="import"></a>
		<div class="fz-feature">
			<div class="fz-feature-features">
			<h2>Feed to Post</h2>
				<p>Convert feed items into WordPress Posts, Pages, or any custom post type in a few easy steps.</p>
				<p>You can view our documentation <a href="https://docs.themeisle.com/article/742-how-to-import-posts-from-feeds-in-feedzy" target="_blank">here</a></p>		
			</div>
			<div class="fz-feature-image">
				<iframe width="600" height="300" src="https://www.youtube.com/embed/Fzx5W_PfQsQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		</div>
		<?php } ?>

		<?php if ( true === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) { ?>
		<div class="fz-feature">
			<div class="fz-feature-features">
				<h2>Integration with SpinnerChief & WordAI</h2>
				<p>Through WordAI integration, Feedzy will give you unlimited new content. Your SpinnerChief or WordAI subscriptions (not included) integrate seamlessly with Feedzy, so you won't ever have to worry about duplicate content - or Google penalties - again. <a href="https://docs.themeisle.com/article/746-how-to-use-wordai-to-rephrase-rss-content-in-feedzy" target="_blank">Check this out here.</a></p>
			</div>
			<div class="fz-feature-image">
				<img src="<?php echo FEEDZY_ABSURL; ?>/img/feedzy-rss-feeds-wordai.jpg" alt="WordAi integration">
			</div>
		</div>
		<?php } ?>

		<div class="fz-feature">
		<div class="fz-feature-features fz-feature-centered">
			<h2>Grow your WordPress business with Feedzy today.</h4>
		<div class="header-btns">
			<?php
			$show_more = ! class_exists( 'Feedzy_Rss_Feeds_Pro' ) || false === apply_filters( 'feedzy_is_license_of_type', false, 'agency' );

			if ( $show_more ) {
				?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=more' ) ); ?>" class="buy-now"><span
			class="dashicons dashicons-cart"></span> Click here to see the additional features in Feedzy Pro</a>
				<?php
			}
			?>
		</div>

	</div><!-- .fz-features-content -->

</div>
