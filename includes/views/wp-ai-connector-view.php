<?php
$content = ! apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ? __( 'Your current plan does not include support for this feature.', 'feedzy-rss-feeds' ) . ' ' : __( 'You are using Feedzy Lite.', 'feedzy-rss-feeds' ) . ' ';
?>

<div class="fz-form-wrap">
	<div class="form-block">
		<div class="upgrade-alert mb-24">
			<?php
			$upgrade_url = tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'wp-ai-connector' ) );

			// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
			$content .= wp_sprintf( __( 'Unlock more powerful features, by %1$supgrading to Feedzy Pro%2$s', 'feedzy-rss-feeds' ), '<a href="' . esc_url( $upgrade_url ) . '" target="_blank">', '</a>' );
			echo wp_kses_post( $content );
			?>
		</div>
		<div class="locked-form-block">
			<div class="fz-form-group">
				<label class="form-label" for="fz-ai-connection-source-locked"><?php esc_html_e( 'AI Connection Source', 'feedzy-rss-feeds' ); ?></label>
				<div class="help-text pb-8">
					<?php esc_html_e( 'Choose which AI connection Feedzy uses for AI actions (rewrite, summarize, image generation).', 'feedzy-rss-feeds' ); ?>
				</div>
				<select id="fz-ai-connection-source-locked" class="form-control fz-select-control" disabled>
					<option selected><?php esc_html_e( 'Feedzy AI Connection', 'feedzy-rss-feeds' ); ?></option>
					<option><?php esc_html_e( 'WordPress AI Connector', 'feedzy-rss-feeds' ); ?></option>
				</select>
			</div>
			<div class="fz-form-group">
				<label class="form-label"><?php esc_html_e( 'Connector', 'feedzy-rss-feeds' ); ?></label>
				<div class="help-text pb-8">
					<?php esc_html_e( 'Connect Feedzy AI actions to any AI provider configured through the WordPress AI Connector (WordPress 7.0+). Supports OpenAI, Anthropic, Gemini, and any future provider added to the WordPress AI ecosystem.', 'feedzy-rss-feeds' ); ?>
				</div>
				<select class="form-control fz-select-control" disabled>
					<option><?php esc_html_e( 'Select a provider', 'feedzy-rss-feeds' ); ?></option>
				</select>
			</div>
			<div class="fz-form-group">
				<div class="help-text">
					<?php esc_html_e( 'Connector Status: Not checked | Last check: Never', 'feedzy-rss-feeds' ); ?>
				</div>
			</div>
		</div>
	</div>
</div>
