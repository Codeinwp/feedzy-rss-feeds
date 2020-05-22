<?php
$canonical_checked = '';
if ( isset( $this->free_settings['canonical'] ) && 1 === intval( $this->free_settings['canonical'] ) ) {
	$canonical_checked = 'checked';
}
?>
<h2><?php echo __( 'Import Posts', 'feedzy-rss-feeds' ); ?></h2>
<div class="fz-form-group">
	<input type="checkbox" id="canonical" class="fz-form-control" name="canonical"
		   value="1" <?php echo $canonical_checked; ?> />
	<label><?php echo __( 'Add canonical URL to imported posts from RSS feeds.', 'feedzy-rss-feeds' ); ?></label>

</div>
