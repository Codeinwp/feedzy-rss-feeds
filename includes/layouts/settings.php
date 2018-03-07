<div id="fz-features" class="fz-settings">

	<?php load_template( FEEDZY_ABSPATH . '/includes/layouts/header.php' ); ?>

	<?php
	$active_tab  = isset( $_REQUEST['tab'] ) ? sanitize_text_field( $_REQUEST['tab'] ) : 'headers';
	$show_button = true;
	?>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-settings&tab=headers' ) ); ?>"
		   class="nav-tab <?php echo $active_tab == 'headers' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Headers', 'feedzy-rss-feeds' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-settings&tab=proxy' ) ); ?>"
		   class="nav-tab <?php echo $active_tab == 'proxy' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Proxy', 'feedzy-rss-feeds' ); ?></a>
		<?php
		$tabs = apply_filters( 'feedzy_settings_tabs', array() );
		if ( $tabs ) {
			foreach ( $tabs as $tab => $label ) {
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-settings&tab=' . $tab ) ); ?>"
				   class="nav-tab <?php echo $active_tab == $tab ? 'nav-tab-active' : ''; ?>"><?php echo $label; ?></a>
				<?php
			}
		}
		?>
	</h2>

	<?php if ( $this->notice ) { ?>
		<div class="updated"><p><?php echo $this->notice; ?></p></div>
	<?php } ?>

	<?php if ( $this->error ) { ?>
		<div class="error"><p><?php echo $this->error; ?></p></div>
	<?php } ?>

	<div class="fz-features-content">
		<div class="fz-feature">
			<div id="feedzy_import_feeds" class="fz-feature-features">

				<form method="post" action="">
					<?php
					switch ( $active_tab ) {
						case 'headers':
							?>
							<h2><?php _e( 'Headers', 'feedzy-rss-feeds' ); ?></h2>
							<div class="fz-form-group">
								<label><?php echo __( 'User Agent to use when accessing the feed', 'feedzy-rss-feeds' ); ?>
									:</label>
							</div>
							<div class="fz-form-group">
								<input type="text" class="fz-form-control" name="user-agent"
									   value="<?php echo isset( $settings['header']['user-agent'] ) ? $settings['header']['user-agent'] : ''; ?>">
							</div>
							<?php
							break;
						case 'proxy':
							?>
							<h2><?php _e( 'Proxy Settings', 'feedzy-rss-feeds' ); ?></h2>
							<div class="fz-form-group">
								<label><?php echo __( 'Host', 'feedzy-rss-feeds' ); ?>:</label>
							</div>
							<div class="fz-form-group">
								<input type="text" class="fz-form-control" name="proxy-host"
									   value="<?php echo isset( $settings['proxy']['host'] ) ? $settings['proxy']['host'] : ''; ?>">
							</div>

							<div class="fz-form-group">
								<label><?php echo __( 'Port', 'feedzy-rss-feeds' ); ?>:</label>
							</div>
							<div class="fz-form-group">
								<input type="number" min="0" max="65535" class="fz-form-control" name="proxy-port"
									   value="<?php echo isset( $settings['proxy']['port'] ) ? $settings['proxy']['port'] : ''; ?>">
							</div>

							<div class="fz-form-group">
								<label><?php echo __( 'Username', 'feedzy-rss-feeds' ); ?>:</label>
							</div>
							<div class="fz-form-group">
								<input type="text" class="fz-form-control" name="proxy-user"
									   value="<?php echo isset( $settings['proxy']['user'] ) ? $settings['proxy']['user'] : ''; ?>">
							</div>

							<div class="fz-form-group">
								<label><?php echo __( 'Password', 'feedzy-rss-feeds' ); ?>:</label>
							</div>
							<div class="fz-form-group">
								<input type="password" class="fz-form-control" name="proxy-pass"
									   value="<?php echo isset( $settings['proxy']['pass'] ) ? $settings['proxy']['pass'] : ''; ?>">
							</div>
							<?php
							break;
						default:
							$fields = apply_filters( 'feedzy_display_tab_settings', array(), $active_tab );
							if ( $fields ) {
								foreach ( $fields as $field ) {
									echo $field['content'];
									if ( isset( $field['ajax'] ) && $field['ajax'] ) {
										$show_button = false;
									}
								}
							}
							break;
					}
					?>

					<input type="hidden" name="tab" value="<?php echo $active_tab; ?>">

					<?php
					wp_nonce_field( $active_tab, 'nonce' );
					if ( $show_button ) {
						?>
						<button type="submit" class="fz-btn fz-btn-submit fz-btn-activate" id="feedzy-settings-submit"
								name="feedzy-settings-submit"><?php _e( 'Save', 'feedzy-rss-feeds' ); ?></button>
						<?php
					}
					?>
				</form>
			</div>
		</div>
	</div>

</div>
