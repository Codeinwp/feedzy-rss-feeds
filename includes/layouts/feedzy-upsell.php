<!--
Layout For Upsell Page of Feedzy RSS Feeds

@since    3.0.12
@package  feedzy-rss-feeds
-->

<?php
	$is_pro = feedzy_is_pro();
?>

<?php if ( ! $is_pro ) { ?>
<div class="fz-feature">
	<div class="fz-feature-inner">
		<div class="fz-feature-features">
			<h2>Boost your Business with Pro Content</h2>
			<h3>Upgrade to Feedzy Pro to experience powerful benefits</h3>
			<p>Aggregate unlimited RSS feeds in your posts, pages or custom content types</p>
			<p>Add content on up to 30 of your own websites, automatically build affiliate links, integrate with WordAI and even add live product pricing feeds.</p>
		</div>
		<div class="fz-feature-image">
			<div class="header-btns">
				<a target="_blank" href="<?php echo FEEDZY_UPSELL_LINK; ?>" class="buy-now"><span class="dashicons dashicons-cart"></span> Upgrade to Feedzy Pro now</a>
			</div>
		</div>
		<div class="clear"></div>
	</div>
</div>
<div class="clear"></div>

<div class="fz-feature">
	<div class="fz-feature-inner">
		<div class="fz-feature-features">
			<h2>Unlimited Content with Feed to Post</h2>
			<p>Convert feed items into WordPress Posts, Pages, or any custom post type in a few easy steps.</p>
			<p>With Pro, collect RSS feeds from an unlimited number of sources and bring them to up to all of your own WordPress sites.</p>
			<p>Feedzy will automatically filter each feed item and place it wherever you want in your site. Easy to install and ready to go.</p>
		</div>
		<div class="fz-feature-image">
			<img src="<?php echo FEEDZY_ABSURL; ?>/img/features-feed-to-post.jpg" alt="Feed to post">
		</div>
		<div class="clear"></div>
	</div>
</div>
<div class="clear"></div>

<div class="fz-feature">
	<div class="fz-feature-inner">
		<div class="fz-feature-features">
			<h2>Shortcode templates to suit your style</h2>
			<p><a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#examples" target="_blank">Three beautiful templates</a> are available for you to choose the style which best fits your design. We even have support for complex media such as <a href="https://demo.themeisle.com/feedzy-rss-feeds/audio-feed-template/" target="_blank">audio playback</a> and <a href="https://docs.themeisle.com/article/1113-how-to-get-videos-from-youtube-with-feedzy" target="_blank">YouTube videos</a>.</p>
			<p>If you like to roll your own templates, <a href="https://docs.themeisle.com/article/1162-feedzy-custom-templates" target="_blank">template tags</a> are available to help you do just that!</p>
		</div>
		<div class="fz-feature-image">
			<img src="<?php echo FEEDZY_ABSURL; ?>/img/features-templates.jpg" alt="Feed templates">
		</div>
		<div class="clear"></div>
	</div>
</div>
<div class="clear"></div>


<div class="fz-feature">
	<div class="fz-feature-inner">
		<div class="fz-feature-features">
			<h2>Grow your business</h2>
			<p>Feature affiliate links on your site with Feedzy. Pro automatically includes your referral/affiliate ID on feed links. You can even import prices from product sources to create extra value for your readers.</p>
		</div>
		<div class="fz-feature-image">
			<img src="<?php echo FEEDZY_ABSURL; ?>/img/features-affiliate-ready.jpg" alt="Protect your Brand">
		</div>
		<div class="clear"></div>
	</div>
</div>
<div class="clear"></div>

<div class="fz-feature">
	<div class="fz-feature-inner">
		<div class="fz-feature-features">
			<h2>Protect your Brand</h2>
			<p>Take control of your content: blacklist specific keywords to show only the content you want to display on your site.</p>
		</div>
		<div class="fz-feature-image">
			<img src="<?php echo FEEDZY_ABSURL; ?>/img/Protect-your-Brand.jpg" alt="Affiliate ready">
		</div>
		<div class="clear"></div>
	</div>
</div>
<div class="clear"></div>

<?php } ?>

<?php if ( $is_pro && false === apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ) { ?>

<div class="fz-feature">
	<div class="fz-feature-inner">
		<div class="fz-feature-features">
			<h2>Unlimited Content with Feed to Post</h2>
			<p>Convert feed items into WordPress Posts, Pages, or any custom post type in a few easy steps.</p>
			<p>With Pro, collect RSS feeds from an unlimited number of sources and bring them to up to all of your own WordPress sites.</p>
			<p>Feedzy will automatically filter each feed item and place it wherever you want in your site. Easy to install and ready to go.</p>
		</div>
		<div class="fz-feature-image">
			<img src="<?php echo FEEDZY_ABSURL; ?>/img/features-feed-to-post.jpg" alt="Feed to post">
		</div>
		<div class="clear"></div>
	</div>
</div>
<div class="clear"></div>

<?php } ?>

<?php if ( $is_pro && false === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) { ?>

<div class="fz-feature">
	<div class="fz-feature-inner">
		<div class="fz-feature-features">
			<h2>Integration with SpinnerChief & WordAI</h2>
			<p>Through WordAI integration, Feedzy will give you unlimited new content. Your SpinnerChief or WordAI subscriptions (not included) integrate seamlessly with Feedzy, so you won't ever have to worry about duplicate content - or Google penalties - again. <a href="https://docs.themeisle.com/article/746-how-to-use-wordai-to-rephrase-rss-content-in-feedzy" target="_blank">Check this out here.</a></p>
		</div>
		<div class="fz-feature-image">
			<img src="<?php echo FEEDZY_ABSURL; ?>/img/feedzy-rss-feeds-wordai.jpg" alt="WordAi integration">
		</div>
		<div class="clear"></div>
	</div>
</div>
<div class="clear"></div>

<?php } ?>

<?php if ( ! $is_pro ) { ?>

<div class="fz-feature">
	<div class="fz-feature-inner">
		<div class="fz-feature-features">
			<h2>World-class support</h2>
			<p>We're proud to serve over 10,000 happy customers and provide unlimited support/updates for the duration of your subscription. If you need help, our customer service and developer teams are on-hand to offer personalized, priority assistance to Pro customers.</p>
		</div>
		<div class="fz-feature-image">
			<img src="<?php echo FEEDZY_ABSURL; ?>/img/World-class-support.jpg" alt="World Class Support">
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
			if ( $is_pro || false === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) {
				?>
			<div class="header-btns">
				<a href="<?php echo FEEDZY_UPSELL_LINK; ?>" class="buy-now">
				<span class="dashicons dashicons-cart"></span> Upgrade <?php echo $is_pro ? 'your license to a higher plan' : 'to Feedzy Pro'; ?></a>
			</div>
				<?php
			}
			?>
		</div>
		<div class="clear"></div>
	</div>
</div>
<div class="clear"></div>
