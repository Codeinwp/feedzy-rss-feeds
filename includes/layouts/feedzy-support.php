<div id="fz-features" class="feedzy-wrap">

	<?php load_template( FEEDZY_ABSPATH . '/includes/layouts/header.php' ); ?>

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
	$active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'help';
	?>

	<div class="feedzy-container">
		<div class="feedzy-accordion-item mb-30">
			<div class="feedzy-accordion-item__title">
				<div class="feedzy-accordion-item__button">
					<div class="feedzy-accordion__step-title h2"><?php esc_html_e( 'Gettting Started', 'feedzy-rss-feeds' ); ?></div>
				</div>
			</div>
			<div class="feedzy-accordion-item__content">
				<div class="fz-tabs-menu">
					<ul>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=help' ) ); ?>"
								class="<?php echo 'help' === $active_tab ? 'active' : ''; ?>"><?php esc_html_e( 'Gettting Started', 'feedzy-rss-feeds' ); ?></a>
						</li>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=docs' ) ); ?>"
							class="<?php echo 'docs' === $active_tab ? 'active' : ''; ?>"><?php esc_html_e( 'Documentation', 'feedzy-rss-feeds' ); ?></a>
						</li>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=feedzy-pro' ) ); ?>"
								class="<?php echo 'feedzy-pro' === $active_tab ? 'active' : ''; ?>"><?php esc_html_e( 'Feedzy Pro', 'feedzy-rss-feeds' ); ?></a>
						</li>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=improve' ) ); ?>"
								class="<?php echo 'improve' === $active_tab ? 'active' : ''; ?>"><?php esc_html_e( 'Help us improve!', 'feedzy-rss-feeds' ); ?></a>
						</li>
						<li>
							<?php echo wp_kses_post( apply_filters( 'feedzy_support_tab_heading', '', $active_tab ) ); ?>
						</li>
					</ul>
				</div>

				<?php
				switch ( $active_tab ) {
					case 'help':
						load_template( FEEDZY_ABSPATH . '/includes/layouts/feedzy-tutorial.php' );
						break;
					case 'docs':
						load_template( FEEDZY_ABSPATH . '/includes/layouts/feedzy-documentation.php' );
						break;
					case 'feedzy-pro':
						load_template( FEEDZY_ABSPATH . '/includes/layouts/feedzy-pro.php' );
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

		<div class="feedzy-accordion-item need-help-box">
			<div class="feedzy-accordion-item__content">
				<h3 class="h3"><?php esc_html_e( 'Need help with Feedzy?', 'feedzy-rss-feeds' ); ?></h3>
				<p><?php echo wp_kses_post( __( 'If you didn\'t found an answer in our Knowledge Base, you can always ask for help from our support team or get priority support with your Developer or Agency license.', 'feedzy-rss-feeds' ) ); ?></a></p>
				<a href="https://store.themeisle.com/contact/" class="btn btn-outline-primary" target="_blank"><?php esc_html_e( 'Support Forum', 'feedzy-rss-feeds' ); ?></a>
			</div>
		</div>
	</div>
</div>
