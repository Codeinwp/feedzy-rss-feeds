<!--
Layout For Tutorial Page of Feedzy RSS Feeds

@since    ?
@package  feedzy-rss-feeds
-->
<?php
	$is_pro = feedzy_is_pro();
?>
		<div class="fz-feature">
			<div class="fz-feature-inner">
				<div class="fz-feature-features">
					<h2>Welcome to Feedzy!</h2>
					<p>Feedzy helps you aggregate unlimited RSS feeds and automatically publish them on your site within a few minutes.</p>
					<p>With this version, you can already:</p>

					<ul style="list-style: disc; list-style-position: inside;">
						<li>Import an unlimited number of feeds</li>
						<li>Automatically create posts from your feeds (feed to post)</li>
						<li>Easily display RSS feeds (shortcodes, gutenberg blocks etc.)</li>
						<li>Import images</li>
						<li>Organize feeds into categories</li>
						<?php if ( $is_pro ) { ?>
						<li>Filter feeds based on keywords</li>
							<?php if ( true === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) { ?>
						<li>WordAI and SpinnerChief integration</li>
						<?php } ?>
						<li>Add affiliate links and referral parameters</li>
						<li>Automatically delete posts after X days</li>
						<?php } ?>
					</ul>

				<?php if ( ! $is_pro ) { ?>
					<p>We have many more features and offer email & chat support if you purchase our <a href="<?php echo esc_url( FEEDZY_UPSELL_LINK ); ?>" target="_blank">Pro Version</a>.</p>
				<?php } ?>

				<p>Ready to begin? Let's <a href="<?php echo esc_url( add_query_arg( 'post_type', 'feedzy_imports', admin_url( 'post-new.php' ) ) ); ?>">import a post</a> or <a href="<?php echo esc_url( add_query_arg( 'post_type', 'feedzy_categories', admin_url( 'post-new.php' ) ) ); ?>" target="_blank">create a category</a>!

				</div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="clear"></div>

		<div class="fz-feature">
			<div class="fz-feature-inner">
				<div class="fz-feature-features">
					<h2>Documentation</h2>
					<p>Please view our documentation page to get started <a href="https://docs.themeisle.com/article/658-feedzy-rss-feeds" target="_blank">here</a>. Here are a few more threads you could find useful:</p>

					<ul style="list-style: disc; list-style-position: inside;">
						<li><a target="_blank" href="https://docs.themeisle.com/article/1154-how-to-use-feed-to-post-feature-in-feedzy">How to use feed to post</a></li>
						<li><a target="_blank" href="https://docs.themeisle.com/article/1130-how-to-use-feedzy-with-a-shortcode">Shortcodes</a></li>

						<?php if ( $is_pro ) { ?>
							<?php if ( true === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) { ?>
							<li><a target="_blank" href="https://docs.themeisle.com/article/746-how-to-use-wordai-to-rephrase-rss-content-in-feedzy">Rephrase RSS content automatically</a></li>
							<?php } ?>
							<li><a target="_blank" href="https://docs.themeisle.com/article/715-feedzy-how-to-add-affiliate-referrals-to-feed-urls">Add affiliate links automatically</a></li>
							<li><a target="_blank" href="https://docs.themeisle.com/article/841-how-to-add-canonical-tags-for-imported-posts">Add canonical tags to imported posts</a></li>
						<?php } ?>

						<li><a target="_blank" href="https://docs.themeisle.com/article/1155-feedzy-troubleshooting-guide">Troubleshooting Guide</a></li>
						<li><a target="_blank" href="https://docs.themeisle.com/article/942-in-feedzy-how-do-i">Customizing Feedzy</a></li>
					</ul>
					<p></p>
				</div>
			</div>
		</div>
		<div class="clear"></div>

		<a name="shortcode"></a>
		<div class="fz-feature">
			<div class="fz-feature-inner">
				<div class="fz-feature-features">
					<h2>Shortcode</h2>
					<p>Show feed items using the <code>[feedzy-rss]</code>shortcode in a few easy steps.</p>
					<p>You can view our documentation about shortcodes <a href="https://docs.themeisle.com/article/1130-how-to-use-feedzy-with-a-shortcode" target="_blank">here</a></p>

					<?php if ( $is_pro ) : ?>
						<h3>Shortcode templates to suit your style</h3>
						<p><a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#examples" target="_blank">Three beautiful templates</a> are available for you to choose the style which best fits your design. We even have support for complex media such as <a href="https://demo.themeisle.com/feedzy-rss-feeds/audio-feed-template/" target="_blank">audio playback</a> and <a href="https://docs.themeisle.com/article/1113-how-to-get-videos-from-youtube-with-feedzy" target="_blank">YouTube videos</a>.</p>
						<p>If you like to roll your own templates, <a href="https://docs.themeisle.com/article/1162-feedzy-custom-templates" target="_blank">template tags</a> are available to help you do just that!</p>
					<?php endif; ?>

				</div>
				<div class="fz-feature-image">
					<iframe width="600" height="300" src="https://www.youtube.com/embed/GEFAY2IxxEc?start=84" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="clear"></div>

		<a name="import"></a>
		<div class="fz-feature">
			<div class="fz-feature-inner">
				<div class="fz-feature-features">
					<h2>Feed to Post</h2>
					<p>Convert feed items into WordPress Posts, Pages, or any custom post type in a few easy steps.</p>
					<p>You can view our documentation <a href="https://docs.themeisle.com/article/742-how-to-import-posts-from-feeds-in-feedzy" target="_blank">here</a></p>
				</div>
				<div class="fz-feature-image">
					<iframe width="600" height="300" src="https://www.youtube.com/embed/Fzx5W_PfQsQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="clear"></div>

		<?php if ( true === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) { ?>

		<div class="fz-feature">
			<div class="fz-feature-inner">
				<div class="fz-feature-features">
					<h2>Integration with SpinnerChief & WordAI</h2>
					<p>Through WordAI integration, Feedzy will give you unlimited new content. Your SpinnerChief or WordAI subscriptions (not included) integrate seamlessly with Feedzy, so you won't ever have to worry about duplicate content - or Google penalties - again. <a href="https://docs.themeisle.com/article/746-how-to-use-wordai-to-rephrase-rss-content-in-feedzy" target="_blank">Check this out here.</a></p>
				</div>
				<div class="fz-feature-image">
					<img src="<?php echo esc_url( FEEDZY_ABSURL ); ?>/img/feedzy-rss-feeds-wordai.jpg" alt="WordAi integration">
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="clear"></div>
		<?php } ?>


		<div class="fz-feature">
			<div class="fz-feature-inner">
				<div class="fz-feature-features fz-feature-centered">
					<h2>Grow your WordPress business with Feedzy today.</h2>
					<?php
					if ( ! $is_pro || false === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) {
						?>
					<div class="header-btns">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=more' ) ); ?>" class="buy-now">
						<span class="dashicons dashicons-cart"></span> Click here to see the additional features in <?php echo $is_pro ? 'the higher version' : 'Feedzy Pro'; ?></a>
					</div>
						<?php
					}
					?>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="clear"></div>
