<!--
Layout For Upsell Page of Feedzy RSS Feeds

@since    3.0.12
@package  feedzy-rss-feeds
-->

<div id="fz-features">

	<div class="fz-features-content">
	
	<?php if ( false === apply_filters( 'feedzy_is_license_of_type', false, 'pro' ) ) { ?>
		<div class="fz-feature">
			<div class="fz-feature-features fz-feature-centered">
				<h2>Boost your Business with Pro Content</h4>
					<h4>Aggregate unlimited RSS feeds in your posts, pages or custom content types</h3>
						<br><hr><br>
						<h4>Upgrade to Feedzy Pro to experience powerful benefits:</h4>
						<h3>Add content on up to 30 of your own websites, automatically build affiliate links, integrate with WordAI and even add live product pricing feeds.</h3>
						<div class="header-btns">
							<a target="_blank" href="<?php echo FEEDZY_UPSELL_LINK; ?>" class="buy-now"><span class="dashicons dashicons-cart"></span> Upgrade to Pro now</a>
						</div>
			</div>
		</div>
		<div class="fz-feature">
			<div class="fz-feature-features">
			<h2>Unlimited Content</h2>
				<p>With Pro, collect RSS feeds from an unlimited number of sources and bring them to up to 30 of your own WordPress sites.</p>
				<p>Feedzy will automatically filter each feed item and place it wherever you want in your site. Easy to install and ready to go.</p>
				<h2>Feed to Post</h2>
				<p>Convert feed items into WordPress Posts, Pages, or any custom post type in a few easy steps.</p>
			</div>
			<div class="fz-feature-image">
				<img src="<?php echo FEEDZY_ABSURL; ?>/img/features-feed-to-post.jpg" alt="Feed to post">
			</div>
		</div>
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
		<div class="fz-feature">
			<div class="fz-feature-features">
				<h2>Grow your business</h2>
				<p>Feature affiliate links on your site with Feedzy. Pro automatically includes your referral/affiliate ID on feed links. You can even import prices from product sources to create extra value for your readers.</p>
			</div>
			<div class="fz-feature-image">
				<img src="<?php echo FEEDZY_ABSURL; ?>/img/features-affiliate-ready.jpg" alt="Protect your Brand">
			</div>
		</div>
		<div class="fz-feature">
			<div class="fz-feature-features">
				<h2>Protect your Brand</h2>
				<p>Take control of your content: blacklist specific keywords to show only the content you want to display on your site.</p>
			</div>
			<div class="fz-feature-image">
				<img src="<?php echo FEEDZY_ABSURL; ?>/img/Protect-your-Brand.jpg" alt="Affiliate ready">
			</div>
		</div>
		<?php } ?>
		<?php if ( true === apply_filters( 'feedzy_is_license_of_type', false, 'pro' ) && false === apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ) { ?>
			<div class="fz-feature">
				<div class="fz-feature-features">
				<h2>Unlimited Content</h2>
					<p>With Pro, collect RSS feeds from an unlimited number of sources and bring them to up to 30 of your own WordPress sites.</p>
					<p>Feedzy will automatically filter each feed item and place it wherever you want in your site. Easy to install and ready to go.</p>
					<h2>Feed to Post</h2>
					<p>Convert feed items along with their <b>full content</b> into WordPress Posts, Pages, or any custom post type in a few easy steps.</p>
				</div>
				<div class="fz-feature-image">
					<img src="<?php echo FEEDZY_ABSURL; ?>/img/features-feed-to-post.jpg" alt="Feed to post">
				</div>
			</div>
		<?php } ?>
		<?php if ( false === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) { ?>
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
			<div class="fz-feature-features">
				<h2>World-class support</h2>
				<p>We’re proud to serve over 10,000 happy customers and provide unlimited support/updates for the duration of your subscription. If you need help, our customer service and developer teams are on-hand to offer personalized, priority assistance to Pro customers.</p>
			</div>
			<div class="fz-feature-image">
				<img src="<?php echo FEEDZY_ABSURL; ?>/img/World-class-support.jpg" alt="World Class Support">
			</div>
		</div>

		<div class="fz-feature">
		<div class="fz-feature-features fz-feature-centered">
			<h2>Grow your WordPress business with Feedzy today.</h4>
		<div class="header-btns">
			<?php if ( false === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) { ?>
			<a target="_blank" href="<?php echo FEEDZY_UPSELL_LINK; ?>" class="buy-now"><span
			class="dashicons dashicons-cart"></span> Get Feedzy Pro</a>
			<?php } ?>
		</div>

	</div><!-- .fz-features-content -->

</div>
