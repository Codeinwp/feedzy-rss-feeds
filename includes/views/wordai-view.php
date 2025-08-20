<?php
$content = ! apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ? __( 'Your current plan does not include support for this feature.', 'feedzy-rss-feeds' ) . ' ' : __( 'You are using Feedzy Lite.', 'feedzy-rss-feeds' ) . ' ';
?>

<div class="fz-form-wrap">
	<div class="form-block">
		<div class="upgrade-alert mb-24">
			<?php
				$upgrade_url = tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'wordai' ) );

				// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
				$content .= wp_sprintf( __( 'Unlock more powerful features, by %1$s upgrading to Feedzy Pro %2$s', 'feedzy-rss-feeds' ), '<a href="' . esc_url( $upgrade_url ) . '" target="_blank">', '</a>' );

				echo wp_kses_post( $content );
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
				</div>
			</div>
		</div>
	</div>
</div>
