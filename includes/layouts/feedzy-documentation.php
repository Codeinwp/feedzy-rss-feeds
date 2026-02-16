<!--
Layout For Document Page of Feedzy RSS Feeds

@since    3.0.12
@package  feedzy-rss-feeds
-->

<?php
	$is_pro = feedzy_is_pro();
?>

<div class="fz-document-list">
	<ul>
		<li>
			<div class="fz-document-box">
				<div class="fz-document-box-img">
					<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/feed-to-post.jpg' ); ?>" alt="">
				</div>
				<div class="fz-document-box-content">
					<h3 class="h3"><?php esc_html_e( 'Feed to Post', 'feedzy-rss-feeds' ); ?></h3>
					<p><?php esc_html_e( 'Learn how to convert feed items into Posts, Pages, or any custom post type.', 'feedzy-rss-feeds' ); ?></p>
					<div class="cta">
						<a href="https://docs.themeisle.com/article/1154-how-to-use-feed-to-post-feature-in-feedzy" class="btn btn-outline-primary" target="_blank"><?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?></a>
					</div>
				</div>
			</div>
		</li>
		<li>
			<div class="fz-document-box">
				<div class="fz-document-box-img">
					<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/shortcode.jpg' ); ?>" alt="">
				</div>
				<div class="fz-document-box-content">
					<h3 class="h3"<?php esc_html_e( 'Shortcode', 'feedzy-rss-feeds' ); ?></h3>
					<p>
						<?php
						// translators: %s is the shortcode [feedzy-rss]
						printf( esc_html__( 'Learn how to display feed items using the %s shortcode in a few easy steps.', 'feedzy-rss-feeds' ), '[feedzy-rss]' );
						?>
					</p>
					<div class="cta">
						<a href="https://docs.themeisle.com/article/1130-how-to-use-feedzy-with-a-shortcode" class="btn btn-outline-primary" target="_blank"><?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?></a>
					</div>
				</div>
			</div>
		</li>
		<li>
			<div class="fz-document-box">
				<div class="fz-document-box-img">
					<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/rephrase-feeds-content.jpg' ); ?>" alt="">
				</div>
				<div class="fz-document-box-content">
					<h3 class="h3"><?php esc_html_e( 'Rephrase Feeds content', 'feedzy-rss-feeds' ); ?></h3>
					<p>
						<?php

						// translators: %1$s and %2$s are the service names (WordAi and SpinnerChief)
						printf( esc_html__( 'Learn how to use %1$s and %2$s to rephrase RSS feeds content.', 'feedzy-rss-feeds' ), 'WordAi', 'SpinnerChief' );
						?>
					</p>
					<div class="cta">
						<a href="https://docs.themeisle.com/article/746-how-to-use-wordai-to-rephrase-rss-content-in-feedzy" class="btn btn-outline-primary" target="_blank"><?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?></a>
					</div>
				</div>
			</div>
		</li>
		<li>
			<div class="fz-document-box">
				<div class="fz-document-box-img">
					<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/validate-RSS-feed.jpg' ); ?>" alt="">
				</div>
				<div class="fz-document-box-content">
					<h3 class="h3"><?php esc_html_e( 'How to validate a RSS feed', 'feedzy-rss-feeds' ); ?></h3>
					<p><?php esc_html_e( 'Learn how to check if a RSS feed is valid or not in Feedzy.', 'feedzy-rss-feeds' ); ?></p>
					<div class="cta">
						<a href="https://docs.themeisle.com/article/716-feedzy-how-to-check-whether-the-rss-feed-is-valid-or-not" class="btn btn-outline-primary" target="_blank"><?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?></a>
					</div>
				</div>
			</div>
		</li>
		<li>
			<div class="fz-document-box">
				<div class="fz-document-box-img">
					<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/in-feedzy.jpg' ); ?>" alt="">
				</div>
				<div class="fz-document-box-content">
					<h3 class="h3"><?php esc_html_e( 'In Feedzy how do I...', 'feedzy-rss-feeds' ); ?></h3>
					<p><?php esc_html_e( 'Learn some of the most popular hooks you can use with the Feedzy plugin.', 'feedzy-rss-feeds' ); ?></p>
					<div class="cta">
						<a href="https://docs.themeisle.com/article/942-in-feedzy-how-do-i" class="btn btn-outline-primary" target="_blank"><?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?></a>
					</div>
				</div>
			</div>
		</li>
		<li>
			<div class="fz-document-box">
				<div class="fz-document-box-img">
					<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/youtube-to-wordpress.jpg' ); ?>" alt="">
				</div>
				<div class="fz-document-box-content">
					<h3 class="h3"><?php esc_html_e( 'How to Add a YouTube Feed to WordPress in 3 Easy Ways.', 'feedzy-rss-feeds' ); ?></h3>
					<div class="cta">
						<a href="https://youtu.be/AV4UpR5GoXA?si=-rFNRwCWh_sQ7Rwi" class="btn btn-outline-primary" target="_blank"><?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?></a>
					</div>
				</div>
			</div>
		</li>
		<li>
			<div class="fz-document-box">
				<div class="fz-document-box-img">
					<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/jobs-from-rss.jpg' ); ?>" alt="">
				</div>
				<div class="fz-document-box-content">
					<h3 class="h3"><?php esc_html_e( 'How to Build a Job Board That Pulls Listings From RSS Feeds (on WordPress).', 'feedzy-rss-feeds' ); ?></h3>
					<div class="cta">
						<a href="https://youtu.be/Y_vxps6VEe8?si=aqxtJ3n0K7saE86r" class="btn btn-outline-primary" target="_blank"><?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?></a>
					</div>
				</div>
			</div>
		</li><li>
			<div class="fz-document-box">
				<div class="fz-document-box-img">
					<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/rss-to-wordpress.jpg' ); ?>" alt="">
				</div>
				<div class="fz-document-box-content">
					<h3 class="h3"><?php esc_html_e( 'Can You Auto-Import Blog Posts From Any RSS Feed Into WordPress? Yes!', 'feedzy-rss-feeds' ); ?></h3>
					<div class="cta">
						<a href="https://youtu.be/vvC5gdrt38o?si=ZERVWfQ8G9v-FoTg" class="btn btn-outline-primary" target="_blank"><?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?></a>
					</div>
				</div>
			</div>
		</li><li>
			<div class="fz-document-box">
				<div class="fz-document-box-img">
					<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/import-full-rss-posts.jpg' ); ?>" alt="">
				</div>
				<div class="fz-document-box-content">
					<h3 class="h3"><?php esc_html_e( 'Import Full RSS Posts with Images into WordPress.', 'feedzy-rss-feeds' ); ?></h3>
					<div class="cta">
						<a href="https://youtu.be/1b19_x6JZk8?si=zP_t2AGdOg_eq3hi" class="btn btn-outline-primary" target="_blank"><?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?></a>
					</div>
				</div>
			</div>
		</li>
	</ul>
	<div class="cta">
		<a href="https://docs.themeisle.com/category/712-feedzy" class="btn btn-ghost" target="blank"><?php esc_html_e( 'Open Feedzy Documentation page', 'feedzy-rss-feeds' ); ?></a>
	</div>
</div>
