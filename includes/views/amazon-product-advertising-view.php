<?php
$content = ! apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ? __( 'Your current plan does not include support for this feature.', 'feedzy-rss-feeds' ) . ' ' : __( 'You are using Feedzy Lite.', 'feedzy-rss-feeds' ) . ' ';
?>

<div class="fz-form-wrap">
	<div class="form-block">
		<div class="upgrade-alert mb-24">
			<?php
				$upgrade_url = tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'amazonproductadvertising' ) );

				$content .= wp_sprintf(
					// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
					__( 'Unlock more powerful features, by %1$s upgrading to Feedzy Pro %2$s', 'feedzy-rss-feeds' ),
					'<a href="' . esc_url( $upgrade_url ) . '" target="_blank">',
					'</a>'
				);
				echo wp_kses_post( $content );
				?>
		</div>
		<div class="locked-form-block">
			<div class="fz-form-group mb-20">
				<?php
					echo wp_kses_post(
						wp_sprintf(
						// translators: %1$s to available amazon domain, %2$s example URL with ASIN, %3$s list of available Amazon domains.
							__( 'Please use this URL structure %1$s or %2$s while getting Amazon product information. <br> Here are the available Amazon domains: %3$s', 'feedzy-rss-feeds' ),
							'<strong>amazon.[extension]?keyword=Laptop</strong>',
							'<strong>amazon.com?asin=ASIN_1|ASIN_2</strong>',
							'<strong>com, au, br, ca, fr, de, in, it, jp, mx, nl, pl, sg, sa, es, se, tr, ae, uk</strong>'
						)
					);
					?>
			</div>
			<div class="fz-form-row">
				<div class="fz-form-col-6">
					<div class="fz-form-group">
						<label class="form-label"><?php esc_html_e( 'Access Key:', 'feedzy-rss-feeds' ); ?></label>
						<input type="password" class="form-control" placeholder="<?php echo esc_attr( __( 'Access Key', 'feedzy-rss-feeds' ) ); ?>"/>
					</div>
				</div>
				<div class="fz-form-col-6">
					<div class="fz-form-group">
						<label class="form-label"><?php esc_html_e( 'Secret key:', 'feedzy-rss-feeds' ); ?></label>
						<input type="password" class="form-control" placeholder="<?php echo esc_attr( __( 'Secret key', 'feedzy-rss-feeds' ) ); ?>"/>
					</div>
				</div>
			</div>
			<div class="fz-form-row">
			<div class="fz-form-col-6">
				<div class="fz-form-group">
					<label class="form-label"><?php esc_html_e( 'Host:', 'feedzy-rss-feeds' ); ?></label>
					<select class="form-control fz-select-control">
						<option>webservices.amazon.com</option>
					</select>
				</div>
			</div>
			<div class="fz-form-col-6">
				<div class="fz-form-group">
					<label class="form-label"><?php esc_html_e( 'Region:', 'feedzy-rss-feeds' ); ?></label>
					<select class="form-control fz-select-control">
						<option>us-east-1</option>
					</select>
				</div>
			</div>
		</div>
			<div class="fz-form-group">
				<label class="form-label"><?php esc_html_e( 'Partner Tag (store/tracking id):', 'feedzy-rss-feeds' ); ?></label>
				<div class="fz-input-group">
					<div class="fz-input-group-left">
						<input type="text" class="form-control" name="amazon_partner_tag" placeholder="<?php echo esc_attr( __( 'Partner Tag (store/tracking id)', 'feedzy-rss-feeds' ) ); ?>"/>
						<div class="help-text"><?php esc_html_e( 'API Status: Invalid | Last check: Never', 'feedzy-rss-feeds' ); ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
