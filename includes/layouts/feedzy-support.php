<div id="fz-features" class="fz-settings">

	<?php load_template( FEEDZY_ABSPATH . '/includes/layouts/header.php' ); ?>

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
	$active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'help';
	$show_more  = ! feedzy_is_pro() || false === apply_filters( 'feedzy_is_license_of_type', false, 'agency' );
	?>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=help' ) ); ?>"
			class="nav-tab <?php echo 'help' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Support', 'feedzy-rss-feeds' ); ?></a>
		<?php
		if ( $show_more ) {
			?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=more' ) ); ?>"
			class="nav-tab <?php echo 'more' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'More Features', 'feedzy-rss-feeds' ); ?></a>
			<?php
		}
		?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=improve' ) ); ?>"
			class="nav-tab <?php echo 'improve' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Help us improve!', 'feedzy-rss-feeds' ); ?></a>

		<?php echo wp_kses_post( apply_filters( 'feedzy_support_tab_heading', '', $active_tab ) ); ?>
	</h2>

	<div class="fz-features-content">
			<div id="feedzy_import_feeds" class="fz-feature-features">
					<?php
					switch ( $active_tab ) {
						case 'help':
							load_template( FEEDZY_ABSPATH . '/includes/layouts/feedzy-tutorial.php' );
							break;
						case 'more':
							if ( $show_more ) {
								load_template( FEEDZY_ABSPATH . '/includes/layouts/feedzy-upsell.php' );
							}
							break;
						case 'improve':
							load_template( FEEDZY_ABSPATH . '/includes/layouts/feedzy-improve.php' );
							break;
						default:
							$template = apply_filters( 'feedzy_support_tab_content', '', $active_tab );
							if ( ! empty( $template ) ) {
								load_template( $template );
							}
					}
					?>
			</div>
	</div>

</div>
