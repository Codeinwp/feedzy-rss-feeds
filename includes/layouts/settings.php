<div class="wrap">
	<h2><?php _e( 'Settings', 'feedzy-rss-feeds' ); ?></h2>

	<?php
		$active_tab = isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'headers';
	?>

	<h2 class="nav-tab-wrapper">
		<a href="?page=feedzy-settings&tab=headers" class="nav-tab <?php echo $active_tab == 'headers' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Headers', 'feedzy-rss-feeds' ); ?></a>
		<a href="?page=feedzy-settings&tab=proxy" class="nav-tab <?php echo $active_tab == 'proxy' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Proxy', 'feedzy-rss-feeds' ); ?></a>
	</h2>

	<?php if ( $this->notice ) { ?>
	<div class="updated"><p><?php echo $this->notice; ?></p></div>
	<?php } ?>

	<?php if ( $this->error ) { ?>
	<div class="error"><p><?php echo $this->error; ?></p></div>
	<?php } ?>

	<form method="post" action="">
		<table class="form-table">
	<?php
	switch ( $active_tab ) {
		case 'headers':
	?>
		<tr valign="top">
			<th scope="row"><?php _e( 'User Agent', 'feedzy-rss-feeds' ); ?></th>
			<td scope="row"><input type="text" class="regular-text" name="user-agent" value="<?php echo isset( $settings['header']['user-agent'] ) ? $settings['header']['user-agent'] : ''; ?>"></td>
		</tr>
	<?php
			break;
		case 'proxy':
	?>
		<tr valign="top">
			<th scope="row"><?php _e( 'Host', 'feedzy-rss-feeds' ); ?></th>
			<td scope="row"><input type="text" class="regular-text" name="proxy-host" value="<?php echo isset( $settings['proxy']['host'] ) ? $settings['proxy']['host'] : ''; ?>"></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e( 'Port', 'feedzy-rss-feeds' ); ?></th>
			<td scope="row"><input type="number" min="0" max="65535" class="regular-text" name="proxy-port" value="<?php echo isset( $settings['proxy']['port'] ) ? $settings['proxy']['port'] : ''; ?>"></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e( 'Username', 'feedzy-rss-feeds' ); ?></th>
			<td scope="row"><input type="text" class="regular-text" name="proxy-user" value="<?php echo isset( $settings['proxy']['user'] ) ? $settings['proxy']['user'] : ''; ?>"></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e( 'Password', 'feedzy-rss-feeds' ); ?></th>
			<td scope="row"><input type="password" class="regular-text" name="proxy-pass" value="<?php echo isset( $settings['proxy']['pass'] ) ? $settings['proxy']['pass'] : ''; ?>"></td>
		</tr>
	<?php
			break;
	}
	?>
		</table>

		<input type="hidden" name="tab" value="<?php echo $active_tab; ?>">

		<?php
			wp_nonce_field( $active_tab, 'nonce' );
			submit_button( __( 'Save', 'feedzy-rss-feeds' ), 'primary', 'feedzy-settings-submit' );
		?>
	</form>

</div>
