<div class="fz-form-wrap">
	<div class="form-block">
		<div class="upgrade-alert mb-24">
			<?php
				echo wp_kses_post(
					wp_sprintf(
						// translators: %1$s: opening anchor tag, %2$s: closing anchor tag
						__( 'You\'re using Feedzy Lite.  Unlock more powerful features, by %1$s upgrading to Feedzy Pro %2$s', 'feedzy-rss-feeds' ),
						'<a target="_blank" href="' . esc_url( tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'spinnerchief' ) ) ) . '">',
						'</a>'
					)
				);
				?>
		</div>
		<div class="locked-form-block">
			<div class="fz-form-group">
				<label class="form-label"><?php esc_html_e( 'SpinnerChief API key', 'feedzy-rss-feeds' ); ?></label>
				<div class="fz-input-group">
					<div class="fz-input-group-left">
						<input type="password" id="spinnerchief_key" class="form-control" placeholder="<?php esc_attr_e( 'SpinnerChief API Key', 'feedzy-rss-feeds' ); ?>"/>
						<div class="help-text"><?php esc_html_e( 'API Status: Invalid | Last check: Never', 'feedzy-rss-feeds' ); ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
