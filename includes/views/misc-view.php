<h2><?php esc_html_e( 'Import Posts', 'feedzy-rss-feeds' ); ?></h2>
<?php $canonical = isset( $this->free_settings['canonical'] ) ? $this->free_settings['canonical'] : 0; ?>
<div class="fz-form-group">
  <input type="checkbox" id="canonical" class="fz-form-control" name="canonical"
	value="1" <?php checked( 1, intval( $canonical ) ); ?> />
  <label for="canonical"><?php esc_html_e( 'Add canonical URL to imported posts from RSS feeds.', 'feedzy-rss-feeds' ); ?></label>

</div>
