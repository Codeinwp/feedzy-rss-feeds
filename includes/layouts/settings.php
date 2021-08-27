<div id="fz-features" class="fz-settings">

	<?php load_template( FEEDZY_ABSPATH . '/includes/layouts/header.php' ); ?>

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
	$active_tab  = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'general';
	$show_button = true;
	?>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-settings&tab=general' ) ); ?>"
			class="nav-tab <?php echo 'general' === $active_tab ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'General', 'feedzy-rss-feeds' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-settings&tab=headers' ) ); ?>"
			class="nav-tab <?php echo 'headers' === $active_tab ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'Headers', 'feedzy-rss-feeds' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-settings&tab=proxy' ) ); ?>"
			class="nav-tab <?php echo 'proxy' === $active_tab ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'Proxy', 'feedzy-rss-feeds' ); ?></a>
		<?php
		$_tabs = apply_filters( 'feedzy_settings_tabs', array() );
		if ( $_tabs ) {
			foreach ( $_tabs as $_tab => $label ) {
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-settings&tab=' . $_tab ) ); ?>"
					class="nav-tab <?php echo $_tab === $active_tab ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php echo wp_kses_post( $label ); ?></a>
				<?php
			}
		}
		?>
	</h2>

	<?php if ( $this->notice ) { ?>
		<div class="updated"><p><?php echo wp_kses_post( $this->notice ); ?></p></div>
	<?php } ?>

	<?php if ( $this->error ) { ?>
		<div class="error"><p><?php echo wp_kses_post( $this->error ); ?></p></div>
	<?php } ?>


	<div class="fz-features-content">
		<div id="feedzy_import_feeds" class="fz-feature-features">
			<div class="fz-feature">
				<div class="fz-feature-inner">
					<div class="fz-feature-features">

					<form method="post" action="">
					<?php
					$disble_featured_image = '';
					if ( isset( $settings['general']['rss-feeds'] ) && 1 === intval( $settings['general']['rss-feeds'] ) ) {
						$disble_featured_image = 'checked';
					}

					$default_thumbnail_id = isset( $settings['general']['default-thumbnail-id'] ) ? $settings['general']['default-thumbnail-id'] : 0;

					switch ( $active_tab ) {
						case 'general':
							?>
							<h2><?php esc_html_e( 'General', 'feedzy-rss-feeds' ); ?></h2>
							<div class="fz-form-group">
								<input type="checkbox" id="rss-feeds" class="fz-form-control" name="rss-feeds"
									value="1" <?php echo esc_html( $disble_featured_image ); ?> />
								<label for="rss-feeds"><?php echo esc_html_e( 'Do NOT add the featured image to the website\'s RSS feed.', 'feedzy-rss-feeds' ); ?></label>
							</div>
							<div class="fz-form-group">
								<label><?php esc_html_e( 'Choose default thumbnail image(Feed2Post):', 'feedzy-rss-feeds' ); ?></label>
							</div>
							<?php if ( $default_thumbnail_id ) : ?>
								<div class="fz-form-group feedzy-media-preview">
									<?php echo wp_get_attachment_image( $default_thumbnail_id, 'thumbnail' ); ?>
								</div>
							<?php endif; ?>
							<div class="fz-form-group">
								<a href="javascript:;" class="feedzy-open-media button action"><?php esc_html_e( 'Choose image', 'feedzy-rss-feeds' ); ?></a>
								<a href="javascript:;" class="feedzy-remove-media button action <?php echo $default_thumbnail_id ? esc_attr( 'is-show' ) : ''; ?>"><?php esc_html_e( 'Remove image', 'feedzy-rss-feeds' ); ?></a>
								<input type="hidden" name="default-thumbnail-id" id="feed-post-default-thumbnail" value="<?php echo esc_attr( $default_thumbnail_id ); ?>">
							</div>
							<?php
							break;
						case 'headers':
							?>
							<h2><?php esc_html_e( 'Headers', 'feedzy-rss-feeds' ); ?></h2>
							<div class="fz-form-group">
								<label><?php esc_html_e( 'User Agent to use when accessing the feed', 'feedzy-rss-feeds' ); ?>
									:</label>
							</div>
							<div class="fz-form-group">
								<input type="text" class="fz-form-control" name="user-agent"
									value="<?php echo isset( $settings['header']['user-agent'] ) ? esc_attr( $settings['header']['user-agent'] ) : ''; ?>">
							</div>
							<?php
							break;
						case 'proxy':
							?>
							<h2><?php esc_html_e( 'Proxy Settings', 'feedzy-rss-feeds' ); ?></h2>
							<div class="fz-form-group">
								<label><?php esc_html_e( 'Host', 'feedzy-rss-feeds' ); ?>:</label>
							</div>
							<div class="fz-form-group">
								<input type="text" class="fz-form-control" name="proxy-host"
									value="<?php echo isset( $settings['proxy']['host'] ) ? esc_attr( $settings['proxy']['host'] ) : ''; ?>">
							</div>

							<div class="fz-form-group">
								<label><?php esc_html_e( 'Port', 'feedzy-rss-feeds' ); ?>:</label>
							</div>
							<div class="fz-form-group">
								<input type="number" min="0" max="65535" class="fz-form-control" name="proxy-port"
									value="<?php echo isset( $settings['proxy']['port'] ) ? esc_attr( (int) $settings['proxy']['port'] ) : ''; ?>">
							</div>

							<div class="fz-form-group">
								<label><?php esc_html_e( 'Username', 'feedzy-rss-feeds' ); ?>:</label>
							</div>
							<div class="fz-form-group">
								<input type="text" class="fz-form-control" name="proxy-user"
									value="<?php echo isset( $settings['proxy']['user'] ) ? esc_attr( $settings['proxy']['user'] ) : ''; ?>">
							</div>

							<div class="fz-form-group">
								<label><?php esc_html_e( 'Password', 'feedzy-rss-feeds' ); ?>:</label>
							</div>
							<div class="fz-form-group">
								<input type="password" class="fz-form-control" name="proxy-pass"
									value="<?php echo isset( $settings['proxy']['pass'] ) ? esc_attr( $settings['proxy']['pass'] ) : ''; ?>">
							</div>
							<?php
							break;
						default:
							$fields = apply_filters( 'feedzy_display_tab_settings', array(), $active_tab );
							if ( $fields ) {

								foreach ( $fields as $field ) {
									echo wp_kses( $field['content'], apply_filters( 'feedzy_wp_kses_allowed_html', array() ) );
									if ( isset( $field['ajax'] ) && $field['ajax'] ) {
										$show_button = false;
									}
								}
							}
							break;
					}
					?>

					<input type="hidden" name="tab" value="<?php echo esc_attr( $active_tab ); ?>">

					<?php
					wp_nonce_field( $active_tab, 'nonce' );
					if ( $show_button ) {
						?>
						<button type="submit" class="fz-btn fz-btn-submit fz-btn-activate" id="feedzy-settings-submit"
								name="feedzy-settings-submit"><?php esc_html_e( 'Save', 'feedzy-rss-feeds' ); ?></button>
						<?php
					}
					?>
					</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
