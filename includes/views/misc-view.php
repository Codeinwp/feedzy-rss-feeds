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
					// translators: %s to help URL.
					echo wp_kses_post( sprintf( __( 'Check the <a href="%s" target="_blank">Documentation</a> for more details.', 'feedzy-rss-feeds' ), esc_url( 'https://docs.themeisle.com/article/841-how-to-add-canonical-tags-for-imported-posts' ) ) );
				?>
			</div>
		</div>
	</div>
</div>
