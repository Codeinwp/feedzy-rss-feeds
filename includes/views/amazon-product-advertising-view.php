<div class="fz-form-wrap">
	<div class="form-block">
		<div class="upgrade-alert mb-24">
			<?php
				echo wp_kses_post( wp_sprintf( __( 'You\'re using Feedzy Lite.  Unlock more powerful features, by <a href="%s" target="_blank">upgrading to Feedzy Pro</a>', 'feedzy-rss-feeds' ), FEEDZY_UPSELL_LINK ) );
			?>
		</div>
		<div class="locked-form-block">
			<div class="fz-form-group mb-20">
				<?php
					// translators: %1$s to available amazon domain.
					echo wp_kses_post( __( 'Please use this URL structure <strong>amazon.[extension]?keyword=Laptop</strong> or <strong>amazon.com?asin=ASIN_1|ASIN_2</strong> while getting Amazon product information. <br> Here is available amazon domain <strong>com, au, br, ca, fr, de, in, it, jp, mx, nl, pl, sg, sa, es, se, tr, ae, uk</strong>', 'feedzy-rss-feeds' ) );
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
			<div class="fz-form-group">
				<label class="form-label"><?php esc_html_e( 'Partner Tag (store/tracking id):', 'feedzy-rss-feeds' ); ?></label>
				<input type="text" class="form-control" name="amazon_partner_tag" placeholder="<?php echo esc_attr( __( 'Partner Tag (store/tracking id)', 'feedzy-rss-feeds' ) ); ?>"/>
			</div>
		</div>
	</div>
</div>
