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
				<h3 class="h3">
					<?php esc_html_e( 'Welcome to Feedzy!', 'feedzy-rss-feeds' ); ?>
				</h3>
				<p>
					<?php esc_html_e( 'Collect the Best Content, Automatically Add It to Your WordPress Site. Grow your site with features like:', 'feedzy-rss-feeds' ); ?>
				</p>
				<ul>
					<li>- <?php esc_html_e( 'Display or Import content from RSS feeds.', 'feedzy-rss-feeds' ); ?></li>
					<li>- <?php esc_html_e( 'Get full content from your RSS feeds.', 'feedzy-rss-feeds' ); ?></li>
					<li>- <?php esc_html_e( 'Paraphrase content before import.', 'feedzy-rss-feeds' ); ?></li>
					<li>- <?php esc_html_e( 'Automatically add referral links.', 'feedzy-rss-feeds' ); ?></li>
					<li>- <?php esc_html_e( 'Pagebuilders integration.', 'feedzy-rss-feeds' ); ?></li>
					<li>- <?php esc_html_e( 'Translate content automatically on import.', 'feedzy-rss-feeds' ); ?></li>
				</ul>
				<br/>
				<a href="<?php echo esc_url( add_query_arg( 'post_type', 'feedzy_imports', admin_url( 'post-new.php' ) ) ); ?>" class="btn btn-outline-primary" target="_blank">
					<?php esc_html_e( 'Import your first feed', 'feedzy-rss-feeds' ); ?>
				</a>
			</div>
		</li>
		<li>
			<div class="support-box quick-links-box">
				<h3 class="h3"><?php esc_html_e( 'Quick Links', 'feedzy-rss-feeds' ); ?></h3>
				<p><?php esc_html_e( 'New here? Learn how to use Feedzy by following our tips and tricks:', 'feedzy-rss-feeds' ); ?></p>
				<div class="quick-link-list">
					<ul>
						<li><a href="https://docs.themeisle.com/category/712-feedzy" target="blank"><?php esc_html_e( 'General Guide', 'feedzy-rss-feeds' ); ?></a></li>
						<li><a href="https://docs.themeisle.com/article/1154-how-to-use-feed-to-post-feature-in-feedzy" target="blank"><?php esc_html_e( 'How to use Feed to post', 'feedzy-rss-feeds' ); ?></a></li>
						<li><a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#widget" target="blank"><?php esc_html_e( 'Feedzy Widget', 'feedzy-rss-feeds' ); ?></a></li>
					</ul>
					<ul>
						<li><a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#troubleshooting" target="blank"><?php esc_html_e( 'Troubleshooting Guide', 'feedzy-rss-feeds' ); ?></a></li>
						<li><a href="https://docs.themeisle.com/article/540-what-actions-and-filters-are-available-in-feedzy" target="blank"><?php esc_html_e( 'Customizing Feedzy', 'feedzy-rss-feeds' ); ?></a></li>
						<li><a href="https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#categories" target="blank"><?php esc_html_e( 'Organize feeds in Categories', 'feedzy-rss-feeds' ); ?></a></li>
					</ul>
				</div>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3"><?php esc_html_e( 'Shortcode', 'feedzy-rss-feeds' ); ?></h3>
				<p>
					<?php
					printf(
						/* translators: %s: [feedzy-rss] */
						esc_html__( 'Display feed items using %s shortcode. The advantage of this approach is that it can be used with any WordPress theme or page builder.', 'feedzy-rss-feeds' ),
						'[feedzy-rss]'
					);
					?>
				</p>
				<a href="https://bit.ly/3IxzOI1" class="btn btn-outline-primary" target="blank">
					<?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?>
				</a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3"><?php esc_html_e( 'Pagebuilders integration', 'feedzy-rss-feeds' ); ?></h3>
				<p><?php esc_html_e( 'Feedzy is compatible with most popular page builders, so you can easily configure your feed imports directly in Elementor & Gutenberg.', 'feedzy-rss-feeds' ); ?></p>
				<a href="https://bit.ly/3RuN3gA" class="btn btn-outline-primary" target="blank">
					<?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?>
				</a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3"><?php esc_html_e( 'Referral Links', 'feedzy-rss-feeds' ); ?><?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
				<p><?php esc_html_e( 'Automatically add referral parameters, and Feedzy will automatically configure affiliate links for each item in the feed.', 'feedzy-rss-feeds' ); ?></p>
				<a href="https://docs.themeisle.com/article/715-feedzy-how-to-add-affiliate-referrals-to-feed-urls" class="btn btn-outline-primary" target="blank">
					<?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?>
				</a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3"><?php esc_html_e( 'Full text import', 'feedzy-rss-feeds' ); ?><?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
				<p><?php esc_html_e( 'Get full content of posts/articles from your RSS feeds.', 'feedzy-rss-feeds' ); ?></p>
				<a href="https://docs.themeisle.com/article/1389-whats-the-difference-between-feedzy-content-and-full-post-content#full-content" class="btn btn-outline-primary" target="blank">
					<?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?>
				</a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3"><?php esc_html_e( 'Paraphrase & Translate Content', 'feedzy-rss-feeds' ); ?><?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
				<p><?php esc_html_e( 'Automatically paraphrase or translate content on import.', 'feedzy-rss-feeds' ); ?></p>
				<a href="https://docs.themeisle.com/article/1690-how-to-use-the-translating-service-in-feedzy" class="btn btn-outline-primary" target="blank">
					<?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?>
				</a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3"><?php esc_html_e( 'Spintax Text', 'feedzy-rss-feeds' ); ?><?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
				<p><?php esc_html_e( 'The Spintax service is very useful for blogs, as it is easily configurable and a time saver.', 'feedzy-rss-feeds' ); ?></p>
				<a href="https://docs.themeisle.com/article/1689-how-to-use-the-spintax-service-in-feedzy" class="btn btn-outline-primary" target="blank">
					<?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?>
				</a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3"><?php esc_html_e( 'Enhanced Elementor support', 'feedzy-rss-feeds' ); ?><?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
				<p><?php esc_html_e( 'Advanced Elementor template builder integration to build content areas directly from feeds.', 'feedzy-rss-feeds' ); ?></p>
				<a href="https://docs.themeisle.com/article/1396-elementor-compatibility-in-feedzy" class="btn btn-outline-primary" target="blank"><?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?></a>
			</div>
		</li>
		<li>
			<div class="support-box">
				<h3 class="h3"><?php esc_html_e( 'Keyword Filtering', 'feedzy-rss-feeds' ); ?><?php echo ! $is_pro ? ' <span class="pro-label">PRO</span>' : ''; ?></h3>
				<p><?php esc_html_e( 'Filter feed items, and Display or Exclude items if the title or content contains specific keyword(s).', 'feedzy-rss-feeds' ); ?></p>
				<a href="https://docs.themeisle.com/article/1154-how-to-use-feed-to-post-feature-in-feedzy#filters" class="btn btn-outline-primary" target="blank">
					<?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?>
				</a>
			</div>
		</li>
	</ul>
	<?php if ( ! $is_pro ) : ?>
		<div class="cta">
			<a href="<?php echo tsdk_utmify( FEEDZY_UPSELL_LINK, 'viewall', 'tutorial' ); ?>#pro-features" class="btn btn-ghost" target="blank">
				<?php esc_html_e( 'View all Premium features', 'feedzy-rss-feeds' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
