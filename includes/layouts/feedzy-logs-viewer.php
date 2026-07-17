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
			$level     = isset( $log['level'] ) ? $log['level'] : '';
			$timestamp = isset( $log['timestamp'] ) ? $log['timestamp'] : '';
			$message   = isset( $log['message'] ) ? $log['message'] : '';
			$context   = isset( $log['context'] ) && is_array( $log['context'] ) ? $log['context'] : array();

			// Surface the import/feed that produced this entry. Older entries used `job_id`/`source` keys.
			$import_id = '';
			if ( isset( $context['import_id'] ) && is_scalar( $context['import_id'] ) ) {
				$import_id = (string) $context['import_id'];
			} elseif ( isset( $context['job_id'] ) && is_scalar( $context['job_id'] ) ) {
				$import_id = (string) $context['job_id'];
			}
			$import_title = isset( $context['import_title'] ) && is_scalar( $context['import_title'] ) ? (string) $context['import_title'] : '';
			$feed_url     = '';
			if ( isset( $context['feed_url'] ) && is_scalar( $context['feed_url'] ) ) {
				$feed_url = (string) $context['feed_url'];
			} elseif ( isset( $context['source'] ) && is_scalar( $context['source'] ) ) {
				$feed_url = (string) $context['source'];
			}
			?>
			<div
				class="fz-log-container fz-log-container--<?php echo esc_attr( strtolower( $level ) ); ?>"
			>
				<div class="fz-log-container__left">
					<div class="fx-log-container__header">
						[
						<?php
						if ( $level ) {
							echo esc_html( strtoupper( $level ) );
						} else {
							echo esc_html( '-' );
						}
						?>
						]
						<?php
						if ( $timestamp ) {
							echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $timestamp ) ) );
						} else {
							echo esc_html( '-' );
						}
						?>

					</div>
					<?php if ( '' !== $import_id || '' !== $import_title || '' !== $feed_url ) : ?>
						<div class="fz-log-container__badges">
							<?php if ( '' !== $import_id || '' !== $import_title ) : ?>
								<span class="fz-log-context-import" title="<?php esc_attr_e( 'Import job that produced this entry', 'feedzy-rss-feeds' ); ?>">
									<?php
									if ( '' !== $import_title ) {
										echo esc_html( $import_title ) . ' ';
									}
									if ( '' !== $import_id ) {
										// translators: %s is the import job ID.
										echo esc_html( sprintf( __( '(Import #%s)', 'feedzy-rss-feeds' ), $import_id ) );
									}
									?>
								</span>
							<?php endif; ?>
							<?php if ( '' !== $feed_url ) : ?>
								<span class="fz-log-context-feed-url" title="<?php esc_attr_e( 'Feed source', 'feedzy-rss-feeds' ); ?>"><?php echo esc_html( $feed_url ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<div class="fz-log-container__message">
						<?php
						if ( $message ) {
							echo esc_html( $message );
						} else {
							echo esc_html( '-' );
						}
						?>
					</div>
				</div>
				<div class="fz-log-container__right">
					<?php if ( $context ) : ?>
						<div class="fz-log-container__context"><?php echo esc_html( wp_json_encode( $context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); ?></div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>