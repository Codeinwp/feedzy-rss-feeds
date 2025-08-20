<?php
/**
 * The misc view.
 * 
 * @package feedzy-rss-feeds
 */

?>
<h2><?php // esc_html_e( 'Import Posts', 'feedzy-rss-feeds' ); ?></h2>
<div class="fz-form-wrap">
	<div class="form-block">
		<?php $canonical = isset( $this->free_settings['canonical'] ) ? $this->free_settings['canonical'] : 0; ?>
		<div class="fz-form-group">
			<div class="fz-form-switch">
				<input type="checkbox" id="canonical" class="fz-switch-toggle" name="canonical"
				value="1" <?php checked( 1, intval( $canonical ) ); ?> />
				<label class="form-label" for="canonical"><?php esc_html_e( 'Add canonical URL to imported posts from RSS feeds.', 'feedzy-rss-feeds' ); ?></label>
			</div>
			<div class="help-text">
				<?php
					echo wp_kses_post(
						sprintf(
							// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
							__( 'Check the %1$s Documentation %2$s for more details.', 'feedzy-rss-feeds' ),
							'<a href="' . esc_url( 'https://docs.themeisle.com/article/841-how-to-add-canonical-tags-for-imported-posts' ) . '" target="_blank">',
							'</a>'
						)
					);
					?>
			</div>
		</div>
	</div>
	<div class="form-block">
		<div class="fz-form-group">
			<div class="fz-form-switch">
				<?php
				$disble_featured_image = '';
				if ( isset( $this->free_settings['general']['rss-feeds'] ) && 1 === intval( $this->free_settings['general']['rss-feeds'] ) ) {
					$disble_featured_image = 'checked';
				}
				?>
				<input type="checkbox" id="rss-feeds" class="fz-switch-toggle" name="rss-feeds"
					value="1" <?php echo esc_html( $disble_featured_image ); ?> />
				<label for="rss-feeds" class="form-label"><?php echo esc_html_e( 'Do NOT add the featured image to the website\'s RSS feed.', 'feedzy-rss-feeds' ); ?></label>
			</div>
			<div class="help-text pb-8"><?php esc_html_e( 'This setting controls whether there are featured images available in the RSS XML Feed of your own website.', 'feedzy-rss-feeds' ); ?></div>
		</div>
	</div>
	<div class="form-block">
		<div class="fz-form-group">
			<label class="form-label">
				<?php esc_html_e( 'User Agent', 'feedzy-rss-feeds' ); ?>
			</label>
			<input
				type="text"
				class="form-control"
				name="user-agent"
				placeholder="<?php esc_attr_e( 'Add the user agent string', 'feedzy-rss-feeds' ); ?>"
				value="<?php echo isset( $this->free_settings['header']['user-agent'] ) ? esc_attr( $this->free_settings['header']['user-agent'] ) : ''; ?>"
			>
		</div>
		<div class="help-text pt-8">
			<?php esc_html_e( 'Specify a custom User-Agent string for feed requests. Some feed providers block automated requests. If you\'re experiencing issues accessing a feed, try setting this to a common browser string like "Mozilla/5.0". In most cases, you can leave this field empty.', 'feedzy-rss-feeds' ); ?>
		</div>
	</div>
	<?php if ( feedzy_is_pro() ) : ?>
		<?php
		$delete_media = 0;
		if (
			isset( $this->free_settings['general']['feedzy-delete-media'] ) &&
			1 === intval( $this->free_settings['general']['feedzy-delete-media'] )
		) {
			$delete_media = 1;
		}

		$feedzy_delete_days = isset( $this->free_settings['general']['feedzy-delete-days'] ) ? $this->free_settings['general']['feedzy-delete-days'] : 0;
		?>
		<div class="form-block">
			<div class="fz-form-group">
				<label class="form-label"><?php esc_html_e( 'Delete the posts created from all feeds, after a number of days', 'feedzy-rss-feeds' ); ?></label>
				<input type="number" min="0" max="9999" id="feedzy_delete_days" name="feedzy-delete-days" class="form-control" value="<?php echo esc_attr( $feedzy_delete_days ); ?>"/>
				<div class="help-text pt-8"><?php esc_html_e( 'Helpful if you want to remove stale or old items automatically. If you choose 0, it will be considered the individual import setting.', 'feedzy-rss-feeds' ); ?></div>
			</div>
		</div>
		<div class="form-block">
			<div class="fz-form-switch pb-0">
				<input type="checkbox" id="feedzy-delete-media" class="fz-switch-toggle" name="feedzy-delete-media"
				value="1" <?php checked( 1, $delete_media ); ?> />
				<label for="feedzy-delete-media" class="form-label"><?php esc_html_e( 'Delete attached featured image', 'feedzy-rss-feeds' ); ?></label>
			</div>
			<div class="fz-form-group">
				<div class="help-text pt-8"><?php esc_html_e( 'Helpful if you want to delete attached featured image when posts are automatically deleted.', 'feedzy-rss-feeds' ); ?></div>
			</div>
		</div>
	<?php endif; ?>
</div>
