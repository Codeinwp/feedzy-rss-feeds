<div id="fz-features" class="fz-settings">

	<?php load_template( FEEDZY_ABSPATH . '/includes/layouts/header.php' ); ?>

	<?php
	$active_tab  = isset( $_REQUEST['tab'] ) ? sanitize_text_field( $_REQUEST['tab'] ) : 'help';
	$show_more = ! class_exists( 'Feedzy_Rss_Feeds_Pro' ) || false === apply_filters( 'feedzy_is_license_of_type', false, 'agency' );
	?>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=help' ) ); ?>"
		   class="nav-tab <?php echo $active_tab === 'help' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Support', 'feedzy-rss-feeds' ); ?></a>
		<?php
		if ( $show_more ) {
			?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-support&tab=more' ) ); ?>"
	   class="nav-tab <?php echo $active_tab === 'more' ? 'nav-tab-active' : ''; ?>"><?php _e( 'More Features', 'feedzy-rss-feeds' ); ?></a>
			<?php
		}
		?>
	</h2>

	<div class="fz-features-content">
		<div class="fz-feature">
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
					}
					?>
			</div>
		</div>
	</div>

</div>
