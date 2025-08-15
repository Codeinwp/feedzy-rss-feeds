<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$all_filter_url = admin_url( 'admin.php?page=feedzy-settings&tab=logs' );

$logs_type_filter = isset( $_REQUEST['logs_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['logs_type'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification

$log_types = array(
	'debug'   => __( 'Debug', 'feedzy-rss-feeds' ),
	'info'    => __( 'Info', 'feedzy-rss-feeds' ),
	'warning' => __( 'Warning', 'feedzy-rss-feeds' ),
	'error'   => __( 'Error', 'feedzy-rss-feeds' ),
);

$file_size = Feedzy_Rss_Feeds_Log::get_instance()->get_log_file_size();
if ( is_numeric( $file_size ) && $file_size > 0 ) {
	$file_size = size_format( $file_size, 0 );
}

$logs_entries = isset( $logs ) && is_array( $logs ) ? $logs : array();

?>
<div class="fz-logs">
	<div class="fz-logs-header">
		<div class="fz-logs-header-title">
			<h3><?php esc_html_e( 'Recent Logs', 'feedzy-rss-feeds' ); ?></h3>
			<a
				target="_blank"
				href="<?php echo esc_url( add_query_arg( '_wpnonce', wp_create_nonce( 'wp_rest' ), rest_url( '/feedzy/v1/logs/download' ) ) ); ?>"
				class="btn btn-ghost"
			>
				<?php esc_html_e( 'Export', 'feedzy-rss-feeds' ); ?>
				<span class="dashicons dashicons-download"></span>
				<?php if ( $file_size ) : ?>
					<span class="fz-log-file-size">(<?php echo esc_html( $file_size ); ?>)</span>
				<?php endif; ?>
			</a>
		</div>
		<div class="fz-logs-header-actions">
			<a href="<?php echo esc_url( $all_filter_url ); ?>" class="btn <?php echo esc_attr( is_null( $logs_type_filter ) ? 'btn-primary' : 'btn-secondary' ); ?>">
				<?php esc_html_e( 'All', 'feedzy-rss-feeds' ); ?>
			</a>
			<?php foreach ( $log_types as $log_type => $label ) : ?>
				<?php
				$filter_url  = add_query_arg(
					array(
						'logs_type' => $log_type,
					),
					$all_filter_url
				);
				$is_selected = $log_type === $logs_type_filter;
				?>
				<a href="<?php echo esc_url( $filter_url ); ?>" class="btn <?php echo esc_attr( $is_selected ? 'btn-primary' : 'btn-secondary' ); ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</div>  
	</div>
	<div class="fz-logs-view">
		<?php
		
		if ( is_wp_error( $logs ) ) {
			printf(
				'<p>%1$s (%2$s)</p>',
				esc_html__( 'An error occurred while fetching logs.', 'feedzy-rss-feeds' ),
				esc_html( $logs->get_error_message() )
			);
		} elseif ( empty( $logs_entries ) ) {
			echo '<p>' . esc_html__( 'No logs found.', 'feedzy-rss-feeds' ) . '</p>';
			$logs = array();
		}

		foreach ( $logs_entries as $log ) :
			?>
			<?php
				$level = isset( $log['level'] ) ? $log['level'] : '';
			?>
			<div
				class="fz-log-container fz-log-container--<?php echo esc_attr( strtolower( $level ) ); ?>"
			>
				<div class="fz-log-container__left">
					<div class="fx-log-container__header">
						[
						<?php
						if ( $log['level'] ) {
							echo esc_html( strtoupper( $log['level'] ) );
						} else {
							echo esc_html( '-' );
						}
						?>
						]
						<?php
						if ( $log['timestamp'] ) {
							echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $log['timestamp'] ) );
						} else {
							echo esc_html( '-' );
						}
						?>
						
					</div>
					<div class="fz-log-container__message">
						<?php
						if ( $log['message'] ) {
							echo esc_html( $log['message'] );
						} else {
							echo esc_html( '-' );
						}
						?>
					</div>
				</div>
				<div class="fz-log-container__right">
					<?php if ( $log['context'] ) : ?>
						<div class="fz-log-container__context"><?php echo esc_html( wp_json_encode( $log['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); ?></div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>