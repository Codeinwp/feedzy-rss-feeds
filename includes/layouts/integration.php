<div id="fz-features" class="feedzy-wrap">

	<?php load_template( FEEDZY_ABSPATH . '/includes/layouts/header.php' ); ?>

	<?php
	$active_tab  = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'openai';// phpcs:ignore WordPress.Security.NonceVerification
	$show_button = true;

	$help_btn_url = 'https://docs.themeisle.com/category/712-feedzy';

	if ( 'wordai' === $active_tab ) {
		$help_btn_url = 'https://docs.themeisle.com/article/746-how-to-use-wordai-to-rephrase-rss-content-in-feedzy#wordai';
	} elseif ( 'spinnerchief' === $active_tab ) {
		$help_btn_url = 'https://docs.themeisle.com/article/746-how-to-use-wordai-to-rephrase-rss-content-in-feedzy#spinner';
	}
	?>
	<?php if ( $this->notice ) { ?>
		<div class="fz-snackbar-notice updated"><p><?php echo wp_kses_post( $this->notice ); ?></p></div>
	<?php } ?>

	<?php if ( $this->error ) { ?>
		<div class="fz-snackbar-notice error"><p><?php echo wp_kses_post( $this->error ); ?></p></div>
	<?php } ?>
	
	<div id="tsdk_banner" class="feedzy-banner"></div>
	
	<div class="feedzy-container">
		<?php if ( ! empty( $offer_data['active'] ) ) { ?>
			<div class="feedzy-sale">
				<a href="<?php echo esc_url( $offer_data['bannerStoreUrl'] ); ?>">
					<img src="<?php echo esc_url( $offer_data['bannerUrl'] ); ?>" alt="<?php echo esc_attr( $offer_data['bannerAlt'] ); ?>">
					<div class="feedzy-urgency"><?php echo esc_html( $offer_data['urgencyText'] ); ?></div>
				</a>
			</div>
		<?php } ?>
		<div class="feedzy-accordion-item">
			<div class="feedzy-accordion-item__content">
				<div class="fz-tabs-menu">
					<ul>
						<?php
						$_tabs = apply_filters( 'feedzy_integration_tabs', array() );
						if ( $_tabs ) {
							foreach ( $_tabs as $_tab => $label ) {
								?>
								<li>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-integration&tab=' . $_tab ) ); ?>"
									class="<?php echo $_tab === $active_tab ? esc_attr( 'active' ) : ''; ?>"><?php echo wp_kses_post( $label ); ?></a>
								</li>
								<?php
							}
						}
						?>
					</ul>
				</div>

				<form method="post" action="">
					<?php
					$fields = apply_filters( 'feedzy_display_tab_settings', array(), $active_tab );
					if ( $fields ) {

						foreach ( $fields as $field ) {
							if ( ! empty( $field['content'] ) ) {
								echo wp_kses( $field['content'], apply_filters( 'feedzy_wp_kses_allowed_html', array() ) );
							}
							if ( isset( $field['ajax'] ) && $field['ajax'] ) {
								$show_button = false;
							}
						}
					}
					?>

					<input type="hidden" name="tab" value="<?php echo esc_attr( $active_tab ); ?>">

					<?php
					wp_nonce_field( $active_tab, 'nonce' );
					if ( $show_button ) {
							$disable_button = ! feedzy_is_pro() && in_array( $active_tab, array( 'spinnerchief', 'wordai', 'amazon-product-advertising', 'openai' ), true ) ? ' disabled' : '';
						?>
						<div class="mb-24">
							<button type="submit" class="btn btn-primary<?php echo esc_attr( $disable_button ); ?>" id="feedzy-settings-submit" name="feedzy-settings-submit" onclick="return ajaxUpdate(this);"><?php esc_html_e( 'Validate & Save', 'feedzy-rss-feeds' ); ?></button>
						</div>
						<?php
					}
					?>
				</form>

			</div>
		</div>

		<div class="cta pt-30">
			<a href="<?php echo esc_url( $help_btn_url ); ?>" class="btn btn-ghost" target="_blank"><?php esc_html_e( 'Need help?', 'feedzy-rss-feeds' ); ?></a>
		</div>
	</div>
</div>
