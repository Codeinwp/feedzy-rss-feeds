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
				<p>Collect the Best Content, Automatically Add It to Your WordPress Site. Grow your site with features like: </p>
				<ul>
					<li>- Display or Import content from RSS feeds.</li>
					<li>- Get full content from your RSS feeds.</li>
					<li>- Paraphrase content before import.</li>
					<li>- Automatically add referral links.</li>
					<li>- Pagebuilders integration.</li>
					<li>- Translate content automatically on import.</li>
				</ul>
				<br/>
				<a href="<?php echo esc_url( add_query_arg( 'post_type', 'feedzy_imports', admin_url( 'post-new.php' ) ) ); ?>"
				   class="btn btn-outline-primary" target="_blank">Import your first feed </a>
			</div>
		</li>
		<li>
			<div class="support-box quick-links-box">
				<h3 class="h3">Quick Links</h3>
				<p>New here? Learn how to use Feedzy by following our tips and tricks:</p>
				<div class="quick-link-list">
					<ul>
						<li><a href="https://docs.themeisle.com/category/712-feedzy" target="blank">General Guide</a>
						</li>
						<li><a href="https://docs.themeisle.com/article/1154-how-to-use-feed-to-post-feature-in-feedzy"
							   target="blank">How to use Feed to post</a></li>
						<li><a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#widget"
							   target="blank">Feedzy Widget</a></li>
					</ul>
					<ul>
						<li>
							<a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#troubleshooting"
							   target="blank">Troubleshooting Guide</a></li>
						<li>
							<a href="https://docs.themeisle.com/article/540-what-actions-and-filters-are-available-in-feedzy"
							   target="blank">Customizing Feedzy</a></li>
						<li><a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#categories"
							   target="blank">Organize feeds in Categories</a></li>
					</ul>
				</div>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3">Shortcode</h3>
				<p>Display feed items using [feedzy-rss] shortcode. The advantage of this approach is that it can be
					used with any WordPress theme or page builder.</p>
				<a href="https://bit.ly/3IxzOI1" class="btn btn-outline-primary" target="blank">Learn more</a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3">Pagebuilders integration</h3>
				<p>Feedzy is compatible with most popular page builders, so you can easily configure your feed imports
					directly in Elementor & Gutenberg.</p>
				<a href="https://bit.ly/3RuN3gA" class="btn btn-outline-primary" target="blank">Learn more</a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3">Referral Links<?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
				<p>Automatically add referral parameters, and Feedzy will automatically configure affiliate links for
					each item in the feed.</p>
				<a href="https://docs.themeisle.com/article/715-feedzy-how-to-add-affiliate-referrals-to-feed-urls"
				   class="btn btn-outline-primary" target="blank">Learn more</a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3">Full text
					import<?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
				<p>Get full content of posts/articles from your RSS feeds.</p>
				<a href="https://docs.themeisle.com/article/1389-whats-the-difference-between-feedzy-content-and-full-post-content#full-content"
				   class="btn btn-outline-primary" target="blank">Learn more</a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3">Paraphrase & Translate
					Content<?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
				<p>Automatically paraphrase or translate content on import.</p>
				<a href="https://docs.themeisle.com/article/1690-how-to-use-the-translating-service-in-feedzy"
				   class="btn btn-outline-primary" target="blank">Learn more</a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3">Spintax Text<?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
				<p>The Spintax service is very useful for blogs, as it is easily configurable and a time saver.</p>
				<a href="https://docs.themeisle.com/article/1689-how-to-use-the-spintax-service-in-feedzy"
				   class="btn btn-outline-primary" target="blank">Learn more</a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3">Enhanced Elementor
					support<?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
				<p>Advanced Elementor template builder integration to build content areas directly from feeds.</p>
				<a href="https://docs.themeisle.com/article/1396-elementor-compatibility-in-feedzy"
				   class="btn btn-outline-primary" target="blank">Learn more</a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3">Keyword
					Filtering<?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
				<p>Filter feed items, and Display or Exclude items if the title or content contains specific
					keyword(s). </p>
				<a href="https://docs.themeisle.com/article/1154-how-to-use-feed-to-post-feature-in-feedzy#filters"
				   class="btn btn-outline-primary" target="blank">Learn more</a>
			</div>
		</li>
	</ul>
	<?php if ( ! $is_pro ) : ?>
		<div class="cta">
			<a href="<?php echo tsdk_utmify( FEEDZY_UPSELL_LINK, 'viewall', 'tutorial' ); ?>#pro-features" class="btn btn-ghost" target="blank">View all Feedzy
				features</a>
		</div>
	<?php endif; ?>
</div>
