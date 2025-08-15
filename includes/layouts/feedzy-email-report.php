<?php
/**
 * Email report template for Feedzy RSS Feeds.
 *
 * @package Feedzy_Rss_Feeds
 * @since   5.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Extract variables that should be available
$site_name         = isset( $site_name ) ? $site_name : get_bloginfo( 'name' );
$generated_date    = isset( $generated_date ) ? $generated_date : date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
$stats             = isset( $stats ) ? $stats : array();
$logs_entries      = isset( $logs_entries ) ? $logs_entries : array();
$see_all_logs_link = add_query_arg(
	array(
		'page' => 'feedzy-settings',
		'tab'  => 'logs',
	),
	admin_url( 'admin.php' )
);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<style>
		body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
		.header { background-color: #f4f4f4; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
		.stats { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
		.log-entry { background-color: #f8f9fa; border-left: 4px solid #dc3545; padding: 10px; margin-bottom: 10px; }
		.timestamp { color: #6c757d; font-size: 0.9em; }
		.level { font-weight: bold; color: #dc3545; }
		.context { color: #6c757d; font-style: italic; font-size: 0.9em; margin-top: 5px; }
		.logs-link { text-align: center; margin-top: 20px; }
		.logs-link a { color: #0073aa; text-decoration: none; font-weight: bold; }
		.logs-link a:hover { text-decoration: underline; }
	</style>
</head>
<body>

<div class="header">
	<h2>
		<?php
		// translators: %s is the site name.
		printf( esc_html__( 'Feedzy RSS Feeds Log Report for %s', 'feedzy-rss-feeds' ), esc_html( $site_name ) );
		?>
	</h2>
	<p>
		<?php
		// translators: %s is the generated date.
		printf( esc_html__( 'Generated on %s', 'feedzy-rss-feeds' ), esc_html( $generated_date ) );
		?>
	</p>
</div>

<?php if ( ! empty( $stats ) && isset( $stats['error_count'] ) && $stats['error_count'] > 0 ) : ?>
	<?php
	$stats_since = isset( $stats['stats_since'] ) ? $stats['stats_since'] : current_datetime()->format( DATE_ATOM );
	?>
	<div class="stats">
		<h3><?php esc_html_e( 'Error Statistics', 'feedzy-rss-feeds' ); ?></h3>
		<p>
			<?php
			printf(
				// translators: %1$s is the error count, %2$s is the date since errors are counted.
				esc_html__( 'Errors logged: %1$s (since %2$s)', 'feedzy-rss-feeds' ),
				'<strong>' . esc_html( $stats['error_count'] ) . '</strong>',
				esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $stats_since ) ) )
			);
			?>
		</p>
	</div>
<?php endif; ?>

<h3><?php esc_html_e( 'Recent Log Entries', 'feedzy-rss-feeds' ); ?></h3>

<?php if ( empty( $logs_entries ) ) : ?>
	<p><?php esc_html_e( 'No recent error entries found.', 'feedzy-rss-feeds' ); ?></p>
<?php else : ?>
	
	<?php foreach ( $logs_entries as $entry ) : ?>
		<div class="log-entry">
			<div class="timestamp"><?php echo esc_html( $entry['timestamp'] ); ?></div>
			<div>
				<span class="level">[<?php echo esc_html( $entry['level'] ); ?>]</span>
				<?php echo esc_html( $entry['message'] ); ?>
			</div>
			<?php if ( ! empty( $entry['context'] ) ) : ?>
				<div class="context">
					<?php esc_html_e( 'Context:', 'feedzy-rss-feeds' ); ?>
					<?php echo esc_html( wp_json_encode( $entry['context'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
<?php endif; ?>

<div class="logs-link">
	<p>
		<a href="<?php echo esc_url( $see_all_logs_link ); ?>">
			<?php esc_html_e( 'See all the logs', 'feedzy-rss-feeds' ); ?>
		</a>
	</p>
</div>

</body>
</html>
