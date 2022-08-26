<div class="fz-form-wrap">
	<div class="form-block">
		<div class="upgrade-alert mb-24">
			<?php
				echo wp_kses_post( wp_sprintf( __( 'You\'re using Feedzy Lite.  Unlock more powerful features, by <a href="%s" target="_blank">upgrading to Feedzy Pro</a>', 'feedzy-rss-feeds' ), FEEDZY_UPSELL_LINK ) );
			?>
		</div>
		<div class="locked-form-block">
			<div class="fz-form-row">
				<div class="fz-form-col-6">
					<div class="fz-form-group">
						<label class="form-label"><?php esc_html_e( 'The SpinnerChief username:', 'feedzy-rss-feeds' ); ?></label>
						<input type="text" class="form-control" placeholder="<?php echo esc_html_x( 'SpinnerChief Username', 'Username for SpinnerChief service', 'feedzy-rss-feeds' ); ?>"/>
					</div>
				</div>
				<div class="fz-form-col-6">
					<div class="fz-form-group">
						<label class="form-label"><?php echo esc_html_x( 'The SpinnerChief password:', 'Password for SpinnerChief service', 'feedzy-rss-feeds' ); ?></label>
						<input type="password" class="form-control" placeholder="<?php echo esc_html_x( 'SpinnerChief Password', 'Password for SpinnerChief service', 'feedzy-rss-feeds' ); ?>"/>
					</div>
				</div>
			</div>
			<div class="fz-form-group">
				<label class="form-label"><?php esc_html_e( 'SpinnerChief API key', 'feedzy-rss-feeds' ); ?></label>
				<div class="fz-input-group">
					<div class="fz-input-group-left">
						<input type="password" id="spinnerchief_key" class="form-control" placeholder="<?php esc_attr_e( 'SpinnerChief API Key', 'feedzy-rss-feeds' ); ?>"/>
						<div class="help-text"><?php esc_html_e( 'API Status: Invalid | Last check: Never', 'feedzy-rss-feeds' ); ?></div>
					</div>
					<div class="fz-input-group-right">
						<button id="check_spinnerchief_api" type="button" class="btn btn-outline-primary disabled" ><?php echo esc_html_x( 'Validate connection', 'Check and save action button', 'feedzy-rss-feeds' ); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
