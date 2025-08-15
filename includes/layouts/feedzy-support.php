<?php
// phpcs:ignore WordPress.Security.NonceVerification
$active_tab                  = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'help';
$onboarding_user_subscribed  = get_option( 'feedzy_onboarding_user_subscribed' );
$subscribed_notice_dismissed = get_option( 'feedzy_dismiss_subscribe_notice_dashboard' );

?>
<div id="fz-features" class="feedzy-wrap">

	<?php
	
	load_template( FEEDZY_ABSPATH . '/includes/layouts/header.php' );

	if ( 'yes' !== $onboarding_user_subscribed && 'yes' !== $subscribed_notice_dismissed ) {
		load_template( FEEDZY_ABSPATH . '/includes/layouts/feedzy-subscribe-notice.php' );
	}
	
	?>
	<div class="feedzy-container">
		<div class="feedzy-accordion-item mb-30">
			<div class="feedzy-accordion-item__content">
				<div class="fz-tabs-menu">
					<ul>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=help' ) ); ?>"
								class="<?php echo 'help' === $active_tab ? 'active' : ''; ?>"><?php esc_html_e( 'Getting Started', 'feedzy-rss-feeds' ); ?></a>
						</li>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=docs' ) ); ?>"
								class="<?php echo 'docs' === $active_tab ? 'active' : ''; ?>"><?php esc_html_e( 'Documentation', 'feedzy-rss-feeds' ); ?></a>
						</li>
						<?php if ( ! feedzy_is_pro() ) : ?>
							<li>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=feedzy-pro' ) ); ?>"
									class="<?php echo 'feedzy-pro' === $active_tab ? 'active' : ''; ?>"><?php esc_html_e( 'Free vs Pro', 'feedzy-rss-feeds' ); ?></a>
							</li>
						<?php endif; ?>
						<?php if ( defined( 'FEEDZY_PRO_VERSION' ) && has_action( 'feedzy_dashboard_license_content' ) ) : ?>
							<li>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=license' ) ); ?>"
									class="<?php echo 'license' === $active_tab ? 'active' : ''; ?>"><?php esc_html_e( 'License', 'feedzy-rss-feeds' ); ?></a>
							</li>
						<?php endif; ?>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=improve' ) ); ?>"
								class="<?php echo 'improve' === $active_tab ? 'active' : ''; ?>"><?php esc_html_e( 'Help us improve!', 'feedzy-rss-feeds' ); ?></a>
						</li>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-settings' ) ); ?>"
								class="<?php echo 'settings' === $active_tab ? 'active' : ''; ?>">
								<?php esc_html_e( 'Settings', 'feedzy-rss-feeds' ); ?>
								<span class="dashicons dashicons-external"></span>
							</a>
						</li>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-integration' ) ); ?>"
								class="<?php echo 'integration' === $active_tab ? 'active' : ''; ?>">
								<?php esc_html_e( 'Integration', 'feedzy-rss-feeds' ); ?>
								<span class="dashicons dashicons-external"></span>
							</a>
						</li>
						<?php $support_tab_heading = apply_filters( 'feedzy_support_tab_heading', '', $active_tab ); ?>
						<?php if ( ! empty( $support_tab_heading ) ) : ?>
							<li>
								<?php echo wp_kses_post( $support_tab_heading ); ?>
							</li>
						<?php endif; ?>
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
					case 'license':
						load_template( FEEDZY_ABSPATH . '/includes/layouts/feedzy-license.php' );
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
		<?php if ( in_array( $active_tab, array( 'help', 'docs' ), true ) ) : ?>
			<div class="feedzy-accordion-item need-help-box">
				<div class="feedzy-accordion-item__content">
					<h3 class="h3"><?php esc_html_e( 'Need help with Feedzy?', 'feedzy-rss-feeds' ); ?></h3>
					<?php
					if ( ! defined( 'FEEDZY_PRO_VERSION' ) ) {
						$upsell_link = tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'dedicatedsupport' ) );
						?>
						<p>
							<?php
							echo wp_kses_post(
								wp_sprintf(
									// Translators: %1$s is the opening anchor tag for the upsell link, %2$s is the closing anchor tag.
									__( 'If you did not found an answer in our Knowledge Base, you can always ask for help from our community based forum or %1$s get dedicated support with our premium plans. %2$s', 'feedzy-rss-feeds' ),
									'<a target="_blank" href="' . esc_url( $upsell_link ) . '">',
									'</a>'
								)
							);
							?>
						</p>
						<a href="https://wordpress.org/support/plugin/feedzy-rss-feeds/" class="btn btn-outline-primary"
							target="_blank">
							<?php esc_html_e( 'Community Forum', 'feedzy-rss-feeds' ); ?>
						</a>
						<?php
					} else {
						?>
						<p><?php echo wp_kses_post( wp_sprintf( __( 'If you didn\'t found an answer in our Knowledge Base, our dedicated support team standby to help you.', 'feedzy-rss-feeds' ) ) ); ?>
						</p>
						<a href="https://store.themeisle.com/contact" class="btn btn-outline-primary"
							target="_blank"><?php esc_html_e( 'Contact Support', 'feedzy-rss-feeds' ); ?></a>
						<?php
					}
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>