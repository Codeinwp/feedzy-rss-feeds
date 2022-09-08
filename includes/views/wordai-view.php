<div class="fz-form-wrap">
	<div class="form-block">
		<div class="upgrade-alert mb-24">
			<?php
				echo wp_kses_post( wp_sprintf( __( 'You\'re using Feedzy Lite.  Unlock more powerful features, by <a href="%s" target="_blank">upgrading to Feedzy Pro</a>', 'feedzy-rss-feeds' ), tsdk_utmify( FEEDZY_UPSELL_LINK, 'wordai' ) ) );
			?>
		</div>
		<div class="locked-form-block">
			<div class="fz-form-group mb-24">
				<label class="form-label"><?php esc_html_e( 'The WordAi account email:', 'feedzy-rss-feeds' ); ?></label>
				<input type="text" class="form-control" placeholder="<?php echo esc_attr( __( 'WordAi Email', 'feedzy-rss-feeds' ) ); ?>"/>
			</div>
			<div class="fz-form-group">
				<label class="form-label"><?php esc_html_e( 'The WordAi account API key:', 'feedzy-rss-feeds' ); ?></label>
				<div class="fz-input-group">
					<div class="fz-input-group-left">
						<input type="password" id="wordai_pass" class="form-control" placeholder="<?php echo esc_attr( __( 'WordAI API key', 'feedzy-rss-feeds' ) ); ?>"/>
						<div class="help-text"><?php esc_html_e( 'API Status: Invalid | Last check: Never', 'feedzy-rss-feeds' ); ?></div>
					</div>
					<div class="fz-input-group-right">
						<div class="fz-input-group-btn">
							<button id="check_wordai_api" type="button" class="btn btn-outline-primary disabled"><?php esc_html_e( 'Validate connection', 'feedzy-rss-feeds' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
