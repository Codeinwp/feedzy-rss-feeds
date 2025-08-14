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
							// translators: %1$s: opening anchor tag, %2$s: closing anchor tag
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
		<div class="fz-form-row">
			<div class="fz-form-col-6">
				<div class="fz-form-group">
					<label class="form-label" for="fz-logs-email-frequency">
						<?php esc_html_e( 'Email Reporting Frequency', 'feedzy-rss-feeds' ); ?>
					</label>
					<?php
					$email_error_frequency = isset( $this->free_settings['logs'], $this->free_settings['logs']['email_frequency'] ) ? $this->free_settings['logs']['email_frequency'] : '';

					$registered_schedules = wp_get_schedules();
					$schedules            = array();

					if ( isset( $registered_schedules['weekly'] ) ) {
						$schedules['weekly'] = $registered_schedules['weekly'];
					}

					if ( isset( $registered_schedules['daily'] ) ) {
						$schedules['daily'] = $registered_schedules['daily'];
					}
					
					?>
					<select id="fz-logs-email-frequency" class="form-control fz-select-control" name="logs-email-frequency">
						<?php
						foreach ( $schedules as $slug => $schedule ) :
							if ( empty( $schedule['interval'] ) ) {
								continue;
							}
							?>
							<option
								value="<?php echo esc_attr( $slug ); ?>"
								<?php selected( $email_error_frequency, $slug ); ?>
							>
								<?php echo esc_html( $schedule['display'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
	</div>
</div>
