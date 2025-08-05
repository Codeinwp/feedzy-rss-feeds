<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$all_filter_url = admin_url( 'admin.php?page=feedzy-settings&tab=logs' );

$logs_type = isset( $_REQUEST['logs_type'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_REQUEST['logs_type'] ) ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification

$log_types = array(
	'debug'    => __( 'Debug', 'feedzy-rss-feeds' ),
	'info'     => __( 'Info', 'feedzy-rss-feeds' ),
	'warning'  => __( 'Warning', 'feedzy-rss-feeds' ),
	'error'    => __( 'Error', 'feedzy-rss-feeds' ),
	'critical' => __( 'Critical', 'feedzy-rss-feeds' ),
);

?>
<div class="fz-logs">
	<div class="fz-logs-header">
		<h3><?php esc_html_e( 'Recent Logs', 'feedzy-rss-feeds' ); ?></h3>
		<div class="fz-logs-header-actions">
			<a href="<?php echo esc_url( $all_filter_url ); ?>" class="button button-secondary<?php echo esc_attr( is_null( $logs_type ) ? ' disabled' : '' ); ?>">
				<?php esc_html_e( 'All', 'feedzy-rss-feeds' ); ?>
			</a>
			<?php foreach ( $log_types as $type => $label ) : ?>
				<?php
				$filter_url  = add_query_arg(
					array(
						'logs_type' => $type,
					),
					$all_filter_url
				);
				$is_selected = $logs_type === strtoupper( $type );
				?>
				<a href="<?php echo esc_url( $filter_url ); ?>" class="button button-secondary<?php echo esc_attr( $is_selected ? ' disabled' : '' ); ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</div>  
	</div>
	<div class="fz-logs-view">
		<?php
		
		if ( ! isset( $logs ) || empty( $logs ) ) {
			echo '<p>' . esc_html__( 'No logs found.', 'feedzy-rss-feeds' ) . '</p>';
			$logs = array();
		}

		foreach ( $logs as $log ) :
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
							echo esc_html( $log['level'] );
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