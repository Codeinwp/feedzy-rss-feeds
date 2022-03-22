<!--
Layout For Tutorial Page of Feedzy RSS Feeds

@since    ?
@package  feedzy-rss-feeds
-->
<?php
	$is_pro = feedzy_is_pro();
?>
		<div class="support-box-list">
			<ul>
				<li>
					<div class="support-box welcome-box">
						<h3 class="h3">Welcome to Feedzy!</h3>
						<p>With Feedzy Lite you can Import feeds, Organize them into categories, create posts from your feeds, add feeds in Gutenberg & Elementor.</p>
						<p>While Feedzy Pro adds more powerful features like Keyword Filtering, importing Feeds to post, referral parameters and much more. </p>
						<a href="<?php echo esc_url( add_query_arg( 'post_type', 'feedzy_imports', admin_url( 'post-new.php' ) ) ); ?>" class="btn btn-outline-primary" target="_blank">Import your first feed </a>
					</div>
				</li>
				<li>
					<div class="support-box quick-links-box">
						<h3 class="h3">Quick Links</h3>
						<p>New here? Learn how to use Feedzy by following our tips and tricks:</p>
						<div class="quick-link-list">
							<ul>
								<li><a href="https://docs.themeisle.com/category/712-feedzy" target="blank">General Guide</a></li>
								<li><a href="https://docs.themeisle.com/article/1154-how-to-use-feed-to-post-feature-in-feedzy" target="blank">How to use Feed to post</a></li>
								<li><a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#widget" target="blank">Feedzy Widget</a></li>
							</ul>
							<ul>
								<li><a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#troubleshooting" target="blank">Troubleshooting Guide</a></li>
								<li><a href="https://docs.themeisle.com/article/540-what-actions-and-filters-are-available-in-feedzy" target="blank">Customizing Feedzy</a></li>
								<li><a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#categories" target="blank">Organize feeds in Categories</a></li>
							</ul>
						</div>
					</div>
				</li>
				<li>
					<div class="support-box">
						<h3 class="h3">Shortcode<?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
						<p>Display feed items using [feedzy-rss] shortcode. The advantage of this approach is that it can be used with any WordPress theme or page builder.</p>
						<a href="https://docs.themeisle.com/article/1130-how-to-use-feedzy-with-a-shortcode/" class="btn btn-outline-primary" target="blank">Learn more</a>
					</div>
				</li>
				<li>
					<div class="support-box">
						<h3 class="h3">Elementor and Gutenberg integration </h3>
						<p>Feedzy is compatible with most popular page builders, so you can easily configure your feed imports directly in Elementor & Gutenberg.</p>
						<a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#gutenberg" class="btn btn-outline-primary" target="blank">Learn more</a>
					</div>
				</li>
				<li>
					<div class="support-box">
						<h3 class="h3">Referral Links<?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
						<p>Add referral parameters, and Feedzy will automatically configure affiliate links for each item in the feed.</p>
						<a href="https://docs.themeisle.com/article/715-feedzy-how-to-add-affiliate-referrals-to-feed-urls" class="btn btn-outline-primary" target="blank">Learn more</a>
					</div>
				</li>
				<li>
					<div class="support-box">
						<h3 class="h3">Keyword Filtering<?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
						<p>Filter feed items, and Display or Exclude items if the title or content contains specific keyword(s). </p>
						<a href="https://themeisle.com/plugins/feedzy-rss-feeds/#reasons" class="btn btn-outline-primary" target="blank">Learn more</a>
					</div>
				</li>
			</ul>
			<div class="cta">
				<a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation" class="btn btn-ghost" target="blank">View all Feedzy features</a>
			</div>
		</div>
