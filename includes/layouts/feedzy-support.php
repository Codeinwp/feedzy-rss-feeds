<div id="fz-features" class="feedzy-wrap">

	<?php load_template( FEEDZY_ABSPATH . '/includes/layouts/header.php' ); ?>

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification
	$active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'help';
	?>

	<?php
	$skip_subscribe    = get_option( 'feedzy_rss_feeds_skip_subscribe' );
	$subscribed_notice = get_option( 'feedzy_rss_feeds_dismiss_subscribe_notice' );
	if ( 'yes' === $skip_subscribe && 'no' === $subscribed_notice ) :
		?>
		<div class ="feedzy-container">
			<div class="feedzy-helper-notice" style="padding: 30px ;position: relative">
				<button
					type="button" 
					class="feedzy-notice-dismiss" 
					aria-label="<?php esc_attr_e( 'Dismiss this notice', 'feedzy-rss-feeds' ); ?>"
				>
					&times;
				</button>
				
				<h2 class="feedzy-notice-title">
					<?php esc_html_e( 'Welcome to Feedzy!', 'feedzy-rss-feeds' ); ?>
				</h2>

				<p class="feedzy-notice-subtitle">
					<?php esc_html_e( 'Join 50,000+ users aggregating RSS feeds effortlessly', 'feedzy-rss-feeds' ); ?>
				</p>

				<form id="feedzy-subscribe-form" method="post" action="">
					<?php wp_nonce_field( 'feedzy_subscribe_nonce', 'feedzy_subscribe_nonce_field' ); ?>
					
					<input
						type="email" 
						id="fz_subscribe_email"
						name="feedzy_email"
						value="admin@yoursite.com"
						placeholder="Enter your email"
						class="feedzy-helper-subscribe"
						required
					>

					<div class="mb-20">
						<div class="feedzy-helper-info">
							Get tips, updates & unlock exclusive guides
						</div>
						<div style="font-size: 16px;">
							Help improve Feedzy with anonymous usage insights
						</div>
					</div>
					
					<button
						type="button" 
						name="feedzy_subscribe_button"
						class="feedzy-subscribe"
					>
						Get Started →
					</button>
					
				</form>
			</div>
		</div>
	<?php endif; ?>
	
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

<script>
jQuery(document).ready(function($) {
	$('body').on('click', '.feedzy-notice-dismiss', function(e) {
		e.preventDefault();
		const $notice = $(this).closest('.feedzy-helper-notice');
		const $button = $(this);
		$button.prop('disabled', true);
		
		const postData = {
			action: 'feedzy_dashboard_subscribe',
			_wpnonce: $('#feedzy_subscribe_nonce_field').val(),
			integrate_with: 'shortcode'
		};

		$.post(ajaxurl, postData, function(res) {
			if (res.success) {
				$notice.fadeOut(400, function() {
					$(this).remove();
				});
			} else {
				$button.prop('disabled', false);
			}
		}).fail(function() {
			$button.prop('disabled', false);
			$notice.fadeOut(400, function() {
				$(this).remove();
			});
		});
	});
	
	$('body').on('click', '.feedzy-subscribe', function(e) {
		e.preventDefault();
	   
		const $button = $(this);
		const withSubscribe = $(this).data('fz_subscribe');

		/**
		* @type {HTMLFormElement}
		*/
		const subscriptionForm = document.getElementById('feedzy-subscribe-form');

		if (withSubscribe && subscriptionForm.checkValidity() === false) {
			subscriptionForm.reportValidity();
			return false;
		}

		const emailElement = $('#fz_subscribe_email');
	   
		emailElement.next('.fz-field-error').remove();
	   
		const postData = {
			action: 'feedzy_dashboard_subscribe',
			_wpnonce: $('#feedzy_subscribe_nonce_field').val(),
			integrate_with: 'shortcode'
		};

		if (withSubscribe) {
			postData.with_subscribe = 1;
			postData.email = emailElement.val();
		}

		$button.prop('disabled', true);

		$.post(ajaxurl, postData, function(res) {
			if (res.success) {
				$('.feedzy-helper-notice').fadeOut(400, function() {
					$(this).remove();
				});
			   
				if (res.data && res.data.redirect_to) {
					window.location.href = res.data.redirect_to;
				}
			}
		   
			$button.prop('disabled', false);
		}).fail(function() {
			$button.prop('disabled', false);
			alert('An error occurred. Please try again.');
		});
	});
});
</script>