<?php
$content = ! apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ? __( 'Your current plan does not include support for this feature.', 'feedzy-rss-feeds' ) . ' ' : __( 'You are using Feedzy Lite.', 'feedzy-rss-feeds' ) . ' ';
?>

<div class="fz-form-wrap">
	<div class="form-block">
		<div class="upgrade-alert mb-24">
			<?php
			$upgrade_url = tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'ai-quota' ) );
			// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
			$content .= wp_sprintf( __( 'Unlock more powerful features, by %1$supgrading to Feedzy Pro%2$s', 'feedzy-rss-feeds' ), '<a href="' . esc_url( $upgrade_url ) . '" target="_blank">', '</a>' );
			echo wp_kses_post( $content );
			?>
		</div>
		<div class="locked-form-block">
			<div class="fz-form-group">
				<label class="form-label"><?php esc_html_e( 'Current Month Quota', 'feedzy-rss-feeds' ); ?></label>
				<div class="fz-form-row">
					<div class="fz-form-col-4">
						<div>
							<div><?php esc_html_e( 'Monthly Limit', 'feedzy-rss-feeds' ); ?></div>
							<div>—</div>
						</div>
					</div>
					<div class="fz-form-col-4">
						<div>
							<div><?php esc_html_e( 'Used', 'feedzy-rss-feeds' ); ?></div>
							<div>—</div>
						</div>
					</div>
					<div class="fz-form-col-4">
						<div>
							<div><?php esc_html_e( 'Remaining', 'feedzy-rss-feeds' ); ?></div>
							<div>—</div>
						</div>
					</div>
					<div class="fz-form-col-4">
						<div>
							<div><?php esc_html_e( 'Next Reset', 'feedzy-rss-feeds' ); ?></div>
							<div>—</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
