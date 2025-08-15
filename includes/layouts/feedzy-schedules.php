<?php
$custom_schedules = array();
$has_pro          = feedzy_is_pro();
$settings         = apply_filters( 'feedzy_get_settings', array() );

if ( isset( $settings['custom_schedules'] ) && is_array( $settings['custom_schedules'] ) ) {
	$custom_schedules = $settings['custom_schedules'];
}
?>
<div class="fz-form-wrap">
	<div class="form-block">
		<div class="fz-form-group">
			<h4 class="h4">
				<?php esc_html_e( 'Add Cron Schedule', 'feedzy-rss-feeds' ); ?>
				<?php if ( ! $has_pro ) : ?>
					<span class="pro-label">PRO</span>
				<?php endif; ?>
			</h4>
			<div class="fz-condition-control" style="padding-bottom: 0;">
				<div class="fz-form-row" style="margin: 0; gap: 1rem; align-items: flex-end;">
					<div class="fz-form-group">
						<label class="form-label" for="fz-schedule-interval">
							<?php esc_html_e( 'Interval (seconds)', 'feedzy-rss-feeds' ); ?>
						</label>
						<input
							type="number"
							class="form-control"
							id="fz-schedule-interval"
							placeholder="3600"
							min="<?php echo esc_attr( defined( 'WP_CRON_LOCK_TIMEOUT' ) ? WP_CRON_LOCK_TIMEOUT : 60 ); ?>"
							<?php disabled( ! $has_pro ); ?>
						/>
					</div>

					<div class="fz-form-group">
						<label class="form-label" for="fz-schedule-display">
							<?php esc_html_e( 'Display Name', 'feedzy-rss-feeds' ); ?>
						</label>
						<input
							type="text"
							class="form-control"
							id="fz-schedule-display"
							placeholder="Once Hourly"
							<?php disabled( ! $has_pro ); ?>
						/>
					</div>

					<div class="fz-form-group">
						<label class="form-label" for="fz-schedule-name">
							<?php esc_html_e( 'Internal Name', 'feedzy-rss-feeds' ); ?>
						</label>
						<input
							type="text"
							class="form-control"
							id="fz-schedule-name"
							placeholder="hourly"
							<?php disabled( ! $has_pro ); ?>
						/>
					</div>
					<div class="fz-form-group">
						<button
							class="btn btn-primary"
							id="fz-add-schedule"
							<?php disabled( ! $has_pro ); ?>
						>
							<?php esc_html_e( 'Add Cron Schedule', 'feedzy-rss-feeds' ); ?>
						</button>
					</div>	
				</div>
			</div>
		</div>
	</div>

	<div
		class="form-block"
	>
		<div
			class="fz-schedules-table"
			style="<?php echo empty( $custom_schedules ) || ! $has_pro ? 'display: none;' : ''; ?>"
		>
			<div class="fz-schedule-counter">
				<?php 
				$schedule_count = count( $custom_schedules );

				// translators: %s is the number of custom schedules.
				echo esc_html( sprintf( __( '%s items', 'feedzy-rss-feeds' ), $schedule_count ) ); 
				?>
			</div>
			
			<table class="fz-schedules-table widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Internal Name', 'feedzy-rss-feeds' ); ?></th>
						<th><?php esc_html_e( 'Interval', 'feedzy-rss-feeds' ); ?></th>
						<th><?php esc_html_e( 'Display Name', 'feedzy-rss-feeds' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'feedzy-rss-feeds' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php 
					foreach ( $custom_schedules as $slug => $schedule ) : 
						$interval_seconds = $schedule['interval'];
						$interval_display = $interval_seconds . ' (' . human_time_diff( 0, $interval_seconds ) . ')';
						?>
						<tr>
							<td class="fz-schedule-attributes">
								<strong><?php echo esc_html( $slug ); ?></strong>
							</td>

							<td class="fz-schedule-attributes">
								<?php echo esc_html( $interval_display ); ?>
							</td>

							<td class="fz-schedule-attributes">
								<?php echo esc_html( $schedule['display'] ); ?>
							</td>

							<td class="fz-schedule-attributes">
								<button type="button" class="btn btn-outline-primary fz-delete-schedule fz-is-destructive" data-schedule="<?php echo esc_attr( $slug ); ?>">
									<?php esc_html_e( 'Delete', 'feedzy-rss-feeds' ); ?>
								</button>
							</td>
							
							<input
								type="hidden"
								value="<?php echo esc_attr( $schedule['interval'] ); ?>"
								name="fz-custom-schedule-interval[<?php echo esc_attr( $slug ); ?>][interval]"
							>

							<input
								type="hidden"
								value="<?php echo esc_attr( $schedule['display'] ); ?>"
								name="fz-custom-schedule-interval[<?php echo esc_attr( $slug ); ?>][display]"
							>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>