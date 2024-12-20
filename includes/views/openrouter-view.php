<div class="fz-form-wrap">
	<div class="form-block">
		<div class="upgrade-alert mb-24">
			<?php
				$upgrade_url = tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'openrouter' ), 'query' );

				$content = __( 'You are using Feedzy Lite.', 'feedzy-rss-feeds' ) . ' ';
				// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
				$content .= wp_sprintf( __( 'Unlock more powerful features, by %1$s upgrading to Feedzy Pro %2$s', 'feedzy-rss-feeds' ), '<a href="' . esc_url( $upgrade_url ) . '" target="_blank">', '</a>' );
				echo wp_kses_post( $content );
			?>
		</div>
		<div class="locked-form-block">
			<div class="fz-form-group mb-24">
				<label class="form-label"><?php esc_html_e( 'The OpenRouter account API key:', 'feedzy-rss-feeds' ); ?></label>
				<div class="help-text pb-8">
				<?php
					// translators: %1$s: openrouter key document url, %2$s: link text.
					echo wp_kses_post( sprintf( __( 'Get your OpenRouter API key from <a href="%1$s" target="_blank">%2$s</a>', 'feedzy-rss-feeds' ), esc_url( 'https://openrouter.ai/keys' ), __( 'OpenRouter API keys', 'feedzy-rss-feeds' ) ) );
				?>
			</div>
				<input type="password" class="form-control" placeholder="<?php echo esc_attr( __( 'API key', 'feedzy-rss-feeds' ) ); ?>"/>
			</div>
			<div class="fz-form-group">
				<label class="form-label"><?php esc_html_e( 'The OpenRouter model:', 'feedzy-rss-feeds' ); ?></label>
				<div class="help-text pb-8">
				<?php
					// translators: %1$s: openrouter pricing url, %2$s: link text.
					echo wp_kses_post( sprintf( __( 'OpenRouter API models <a href="%1$s" target="_blank">%2$s</a>', 'feedzy-rss-feeds' ), esc_url( 'https://openrouter.ai/models' ), __( 'Pricing', 'feedzy-rss-feeds' ) ) );
				?>
				</div>
				<div class="fz-input-group">
					<div class="fz-input-group-left">
						<input type="text" class="form-control" placeholder="<?php echo esc_attr( __( 'Model', 'feedzy-rss-feeds' ) ); ?>"/>
						<div class="help-text"><?php esc_html_e( 'API Status: Invalid | Last check: Never', 'feedzy-rss-feeds' ); ?></div>
					</div>
					<div class="fz-input-group-right">
						<div class="fz-input-group-btn">
							<button type="button" class="btn btn-outline-primary disabled"><?php esc_html_e( 'Validate connection', 'feedzy-rss-feeds' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
