<div id="fz-features" class="feedzy-wrap">

	<?php load_template( FEEDZY_ABSPATH . '/includes/layouts/header.php' ); ?>

	<?php
	$active_tab  = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'general';// phpcs:ignore WordPress.Security.NonceVerification
	$show_button = true;

	$help_btn_url = 'https://docs.themeisle.com/category/712-feedzy';

	if ( 'general' === $active_tab ) {
		$help_btn_url = 'https://docs.themeisle.com/article/1119-feedzy-rss-feeds-documentation#general';
	} elseif ( 'headers' === $active_tab ) {
		$help_btn_url = 'https://docs.themeisle.com/article/713-how-to-change-user-agent-in-feedzy';
	} elseif ( 'proxy' === $active_tab ) {
		$help_btn_url = 'https://docs.themeisle.com/article/714-how-to-use-proxy-settings-in-feezy';
	} elseif ( 'misc' === $active_tab ) {
		$help_btn_url = 'https://docs.themeisle.com/article/841-how-to-add-canonical-tags-for-imported-posts';
	} elseif ( 'openai' === $active_tab ) {
		$help_btn_url = 'https://docs.themeisle.com/article/1962-how-to-paraphrase-using-chatgpt-in-feed-to-post';
	} elseif ( 'wordai' === $active_tab ) {
		$help_btn_url = 'https://docs.themeisle.com/article/746-how-to-use-wordai-to-rephrase-rss-content-in-feedzy#wordai';
	} elseif ( 'spinnerchief' === $active_tab ) {
		$help_btn_url = 'https://docs.themeisle.com/article/746-how-to-use-wordai-to-rephrase-rss-content-in-feedzy#spinner';
	} elseif ( 'amazon-product-advertising' === $active_tab ) {
		$help_btn_url = 'https://docs.themeisle.com/article/1745-how-to-display-amazon-products-using-feedzy';
	}

	$logs                            = array();
	$logging_level                   = isset( $settings['logs'], $settings['logs']['level'] ) ? $settings['logs']['level'] : 'error';
	$email_error_address             = isset( $settings['logs'], $settings['logs']['email'] ) ? $settings['logs']['email'] : '';
	$email_error_enabled             = isset( $settings['logs'], $settings['logs']['send_email_report'] ) ? $settings['logs']['send_email_report'] : 0;
	$email_error_address_placeholder = ( ! empty( $email_error_address ) ) ? $email_error_address : get_option( 'admin_email' );

	if ( 'logs' === $active_tab ) {
		$logs_type = isset( $_REQUEST['logs_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['logs_type'] ) ) : null;// phpcs:ignore WordPress.Security.NonceVerification
		$logs      = Feedzy_Rss_Feeds_Log::get_instance()->get_recent_logs( 50, $logs_type );
	}

	$file_size = Feedzy_Rss_Feeds_Log::get_instance()->get_log_file_size();
	if ( is_numeric( $file_size ) && $file_size > 0 ) {
		$file_size = size_format( $file_size, 0 );
	}


	?>
	<?php if ( $this->notice ) { ?>
		<div class="fz-snackbar-notice updated"><p><?php echo wp_kses_post( $this->notice ); ?></p></div>
	<?php } ?>

	<?php if ( $this->error ) { ?>
		<div class="fz-snackbar-notice error"><p><?php echo wp_kses_post( $this->error ); ?></p></div>
	<?php } ?>
	
	<div id="tsdk_banner" class="feedzy-banner"></div>
	
	<div class="feedzy-container">
		<?php if ( ! empty( $offer_data['active'] ) ) { ?>
			<div class="feedzy-sale">
				<a href="<?php echo esc_url( $offer_data['bannerStoreUrl'] ); ?>">
					<img src="<?php echo esc_url( $offer_data['bannerUrl'] ); ?>" alt="<?php echo esc_attr( $offer_data['bannerAlt'] ); ?>">
					<div class="feedzy-urgency"><?php echo esc_html( $offer_data['urgencyText'] ); ?></div>
				</a>
			</div>
		<?php } ?>
		<div class="feedzy-accordion-item">
			<div class="feedzy-accordion-item__content">
				<div class="fz-tabs-menu">
					<ul>
						<li>
							<a
								href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-settings&tab=general' ) ); ?>"
								class="<?php echo 'general' === $active_tab ? esc_attr( 'active' ) : ''; ?>"
							>
								<?php esc_html_e( 'General', 'feedzy-rss-feeds' ); ?>
							</a>
						</li>
						<li>
							<a
								href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-settings&tab=proxy' ) ); ?>"
								class="<?php echo 'proxy' === $active_tab ? esc_attr( 'active' ) : ''; ?>"
							>
								<?php esc_html_e( 'Proxy', 'feedzy-rss-feeds' ); ?>
							</a>
						</li>
						<li>
							<a
								href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-settings&tab=logs' ) ); ?>"
								class="<?php echo 'logs' === $active_tab ? esc_attr( 'active' ) : ''; ?>"
							>
								<?php esc_html_e( 'Logs', 'feedzy-rss-feeds' ); ?>
							</a>
						</li>
						<li>
							<a
								href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-settings&tab=schedules' ) ); ?>"
								class="<?php echo 'schedules' === $active_tab ? esc_attr( 'active' ) : ''; ?>"
							>
								<?php esc_html_e( 'Schedules', 'feedzy-rss-feeds' ); ?>
								<?php if ( ! feedzy_is_pro() ) : ?>
									<span class="pro-label">PRO</span>
								<?php endif; ?>
							</a>
						</li>
						<?php
						$_tabs = apply_filters( 'feedzy_settings_tabs', array() );
						if ( $_tabs ) {
							foreach ( $_tabs as $_tab => $label ) {
								?>
								<li>
									<a
										href="<?php echo esc_url( admin_url( 'admin.php?page=feedzy-settings&tab=' . $_tab ) ); ?>"
										class="<?php echo $_tab === $active_tab ? esc_attr( 'active' ) : ''; ?>"
									>
										<?php echo wp_kses_post( $label ); ?>
									</a>
								</li>
								<?php
							}
						}
						?>
					</ul>
				</div>

				<form method="post" action="">
					<?php
					$disble_default_style = 0;
					if ( isset( $settings['general']['disable-default-style'] ) && 1 === intval( $settings['general']['disable-default-style'] ) ) {
						$disble_default_style = 1;
					}
					
					$default_thumbnail_id = isset( $settings['general']['default-thumbnail-id'] ) ? $settings['general']['default-thumbnail-id'] : 0;
					$mapped_categories    = isset( $settings['general']['auto-categories'] ) && ! empty( $settings['general']['auto-categories'] ) ? $settings['general']['auto-categories'] : array(
						array(
							'keywords' => '',
							'category' => '',
						),
					);
					$categories           = get_categories(
						array(
							'hide_empty' => false,
						)
					);
					$telemetry_enabled    = get_option( 'feedzy_rss_feeds_logger_flag', 0 );

					switch ( $active_tab ) {
						case 'general':
							?>
							<div class="fz-form-wrap">
								<?php do_action( 'feedzy_general_setting_before' ); ?>	
								<div class="form-block">
									<div class="fz-form-group">
										<label for="feed-post-default-thumbnail" class="form-label"><?php echo esc_html_e( 'Fallback Featured Image Settings', 'feedzy-rss-feeds' ); ?></label>
										<div class="help-text pb-8"><?php esc_html_e( 'Choose a default image to display when RSS feeds don\'t include images.', 'feedzy-rss-feeds' ); ?></div>
										<?php
										$btn_label = esc_html__( 'Choose image', 'feedzy-rss-feeds' );
										if ( $default_thumbnail_id ) :
											$btn_label = esc_html__( 'Replace image', 'feedzy-rss-feeds' );
											?>
											<div class="fz-form-group feedzy-media-preview">
												<?php echo wp_get_attachment_image( $default_thumbnail_id, 'thumbnail' ); ?>
											</div>
										<?php endif; ?>
										<div class="fz-cta-group pb-8">
											<a href="javascript:;" class="feedzy-open-media btn btn-outline-primary"><?php echo esc_html( $btn_label ); ?></a>
											<a href="javascript:;" class="feedzy-remove-media btn btn-outline-primary <?php echo $default_thumbnail_id ? esc_attr( 'is-show' ) : ''; ?>"><?php esc_html_e( 'Remove', 'feedzy-rss-feeds' ); ?></a>
											<input type="hidden" name="default-thumbnail-id" id="feed-post-default-thumbnail" value="<?php echo esc_attr( $default_thumbnail_id ); ?>">
										</div>
									</div>
								</div>
								<div class="form-block">
									<div class="fz-form-switch pb-0">
										<input type="checkbox" id="disable-default-style" class="fz-switch-toggle" name="disable-default-style"
										value="1" <?php checked( 1, $disble_default_style ); ?> />
										<label for="disable-default-style" class="form-label"><?php esc_html_e( 'Disable default style', 'feedzy-rss-feeds' ); ?></label>
									</div>
									<div class="fz-form-group">
										<div class="help-text pt-8"><?php esc_html_e( 'This setting will be used to inherit the current theme style instead of the default style. If disabled, it will be considered the individual widget/block/shortcode setting.', 'feedzy-rss-feeds' ); ?></div>
									</div>
								</div>
								<div class="form-block <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
									<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '', 'auto-categories', 'settings' ) ); ?>
									<div class="fz-form-group">
										<label class="form-label"><?php esc_html_e( 'Auto Categories Mapping', 'feedzy-rss-feeds' ); ?></label>
										<table class="fz-auto-cat">
											<tbody>
												<?php foreach ( $mapped_categories as $index => $category_mapping ) : ?>
												<tr>
													<td class="fz-auto-cat-col-8">
														<input type="text" name="auto-categories[<?php echo esc_attr( $index ); ?>][keywords]" class="form-control" placeholder="<?php esc_attr_e( 'Values separated by commas', 'feedzy-rss-feeds' ); ?>" value="<?php echo esc_attr( $category_mapping['keywords'] ); ?>"/>
													</td>
													<td class="fz-auto-cat-col-4">
														<select name="auto-categories[<?php echo esc_attr( $index ); ?>][category]" class="form-control fz-select-control">
															<option value=""><?php esc_html_e( 'Select a category', 'feedzy-rss-feeds' ); ?></option>
															<?php
															foreach ( $categories as $category ) {
																$selected = $category->term_id == $category_mapping['category'] ? 'selected' : '';
																echo '<option value="' . esc_attr( $category->term_id ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $category->name ) . '</option>';
															}
															?>
														</select>
														<button
															type="button"
															class="btn btn-outline-primary<?php echo 0 === $index ? ' disabled' : ''; ?>" <?php echo 0 === $index ? 'disabled' : ''; ?>
														> 
															<?php esc_html_e( 'Delete', 'feedzy-rss-feeds' ); ?>
														</button>
													</td>
												</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
										<div class="fz-auto-cat-actions">
											<button type="button"class="btn btn-outline-primary"><?php esc_html_e( 'Add New', 'feedzy-rss-feeds' ); ?></button>
										</div>
										<div class="help-text pt-8">
											<?php
											printf(
												// translators: %s is a placeholder for the auto categories tag, like [#auto_categories].
												esc_html__( 'Automatically assign categories to your posts based on their titles. You need to add %s tag to the category field of your import to support this feature.', 'feedzy-rss-feeds' ),
												'<strong>[#auto_categories]</strong>'
											);
											?>
										</div>
									</div>
								</div>
								<?php if ( feedzy_is_pro() ) : ?>
									<div class="form-block">
										<div class="fz-form-row">
											<div class="fz-form-col-6">
												<div class="fz-form-group">
													<label class="form-label"><?php esc_html_e( 'Default Importing Schedule', 'feedzy-rss-feeds' ); ?></label>
													<?php
													$save_schedule = ! empty( $settings['general']['fz_cron_schedule'] ) ? $settings['general']['fz_cron_schedule'] : '';

													$schedules = wp_get_schedules();
													if ( isset( $schedules['hourly'] ) ) {
														$hourly = $schedules['hourly'];
														unset( $schedules['hourly'] );
														$schedules = array_merge( array( 'hourly' => $hourly ), $schedules );
													}
													$internal_cron_schedules = apply_filters( 'feedzy_internal_cron_schedule_slugs', array() );
													?>
													<select id="fz-event-schedule" class="form-control fz-select-control" name="fz_cron_schedule">
														<?php
														$duplicate_schedule = array();
														foreach ( $schedules as $slug => $schedule ) :
															if (
																empty( $schedule['interval'] ) ||
																in_array( $schedule['interval'], $duplicate_schedule, true )
															) {
																continue;
															}
															$duplicate_schedule[] = $schedule['interval'];
															$display_text         = $schedule['display'];
															
															if ( ! in_array( $slug, $internal_cron_schedules, true ) ) {
																// translators: (externally created) is used to indicate that the schedule is created by another plugin or manually.
																$display_text .= ' (' . esc_html__( 'externally created', 'feedzy-rss-feeds' ) . ')';
															}
															?>
															<option
																value="<?php echo esc_attr( $slug ); ?>"
																<?php selected( $save_schedule, $slug ); ?>
															>
																<?php echo esc_html( $display_text ); ?>
															</option>
														<?php endforeach; ?>
													</select>
												</div>
											</div>
										</div>
									</div>
								<?php endif; ?>
								<div class="form-block fz-block__column">
									<div class="fz-form-group">
										<label class="form-label">
											<?php esc_html_e( 'Logging Level', 'feedzy-rss-feeds' ); ?>
										</label>
										<select class="form-control fz-select-control" name="logs-logging-level">
											<option
												value="none"
												<?php selected( 'none', $logging_level ); ?>
											>
												<?php esc_html_e( 'None', 'feedzy-rss-feeds' ); ?>
											</option>
											<option
												value="error"
												<?php selected( 'error', $logging_level ); ?>
											>
												<?php esc_html_e( 'Error', 'feedzy-rss-feeds' ); ?>
											</option>
											<option
												value="warning"
												<?php selected( 'warning', $logging_level ); ?>
											>
												<?php esc_html_e( 'Warning', 'feedzy-rss-feeds' ); ?>
											</option>
											<option
												value="info"
												<?php selected( 'info', $logging_level ); ?>
											>
												<?php esc_html_e( 'Info', 'feedzy-rss-feeds' ); ?>
											</option>
											<option
												value="debug"
												<?php selected( 'debug', $logging_level ); ?>
											>
												<?php esc_html_e( 'Debug', 'feedzy-rss-feeds' ); ?>
											</option>
										</select>
										
									</div>
									<?php if ( $file_size ) : ?>
										<div class="fz-form-group fz-group__row fz-group__left">
											<div class="fz-log-file-size-wrapper">
												<span class="dashicons dashicons-media-default"></span>
												<span class="fz-log-file-size">(<?php echo esc_html( $file_size ); ?>)</span>
											</div>
											<button
												id="feedzy-delete-log-file"
												type="button"
												class="btn btn-outline-primary fz-is-destructive"
												data-url="<?php echo esc_url( $help_btn_url ); ?>"
											>
												
												<?php esc_html_e( 'Delete', 'feedzy-rss-feeds' ); ?>
											</button>
										</div>
									<?php endif; ?>
								</div>
								<div class="form-block fz-block__column">
										<div class="fz-form-group">
											<div class="fz-form-switch pb-0">
												<input
													type="checkbox"
													id="feedzy-email-error-enabled"
													class="fz-switch-toggle"
													name="feedzy-email-error-enabled"
													value="1"
													<?php checked( 1, $email_error_enabled ); ?>
												/>
												<label
													for="feedzy-email-error-enabled"
													class="form-label"
												>
													<?php esc_html_e( 'Report errors via email', 'feedzy-rss-feeds' ); ?>
												</label>
											</div>
											<div class="help-text pt-8">
												<?php esc_html_e( 'Enable email alerts for feed errors.', 'feedzy-rss-feeds' ); ?>
											</div>
										</div>
										<div
											class="fz-form-group fz-log-email-address <?php echo esc_attr( ! $email_error_enabled ? 'fz-hidden' : '' ); ?>"
										>
											<label class="form-label fz-email-error-text">
												<?php esc_html_e( 'Email address', 'feedzy-rss-feeds' ); ?>
											</label>
											<input
												type="email"
												class="form-control"
												name="feedzy-email-error-address"
												value="<?php echo esc_attr( $email_error_address ); ?>"
												placeholder="<?php echo esc_attr( $email_error_address_placeholder ); ?>"
											>
										</div>
										<div
											class="fz-form-group fz-log-email-freq <?php echo esc_attr( ! $email_error_enabled ? 'fz-hidden' : '' ); ?>"
										>
											<label class="form-label" for="fz-logs-email-frequency">
												<?php esc_html_e( 'Email Reporting Frequency', 'feedzy-rss-feeds' ); ?>
											</label>
											<?php
											$email_error_frequency = isset( $settings['logs'], $settings['logs']['email_frequency'] ) ? $settings['logs']['email_frequency'] : '';

											$registered_schedules = wp_get_schedules();
											$schedules            = array();

											if ( isset( $registered_schedules['weekly'] ) ) {
												$schedules['weekly'] = $registered_schedules['weekly'];
											}

											if ( isset( $registered_schedules['daily'] ) ) {
												$schedules['daily'] = $registered_schedules['daily'];
											}
											
											?>
											<select id="fz-logs-email-frequency" class="form-control fz-select-control" name="logs-email-frequency">
												<?php
												foreach ( $schedules as $slug => $schedule ) :
													if ( empty( $schedule['interval'] ) ) {
														continue;
													}
													?>
													<option
														value="<?php echo esc_attr( $slug ); ?>"
														<?php selected( $email_error_frequency, $slug ); ?>
													>
														<?php echo esc_html( $schedule['display'] ); ?>
													</option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
									<div class="form-block">
										<div class="fz-form-switch pb-0">
											<input type="checkbox" id="feedzy-telemetry" class="fz-switch-toggle" name="feedzy-telemetry"
											value="1" <?php checked( 'yes', $telemetry_enabled ); ?> />
											<label for="feedzy-telemetry" class="form-label"><?php esc_html_e( 'Enable Telemetry', 'feedzy-rss-feeds' ); ?></label>
										</div>
										<div class="fz-form-group">
											<div class="help-text pt-8"><?php esc_html_e( 'Send data about plugin settings to measure the usage of the features. The data is private and not shared with third-party entities. Only plugin data is collected without sensitive information.', 'feedzy-rss-feeds' ); ?></div>
										</div>
									</div>
							</div>
							<?php
							break;
						case 'proxy':
							?>
							<div class="fz-form-wrap">
								<div class="feedzy-helper-notice">
									<h5 class="feedzy-helper-notice__title">
										<?php esc_html_e( 'Proxy Configuration', 'feedzy-rss-feeds' ); ?>
									</h5>
									<p>
										<?php esc_html_e( 'Use a proxy to bypass firewalls, geographic restrictions, or IP blocks from feed sources. This ensures you can reliably access restricted RSS feeds.', 'feedzy-rss-feeds' ); ?>
										<a href="<?php echo esc_url( $help_btn_url ); ?>" target="_blank" >
											<?php esc_html_e( 'View proxy setup guide', 'feedzy-rss-feeds' ); ?>
										</a>
									</p>
								</div>
								<div class="form-block pb-0">
									<div class="fz-form-row">
										<div class="fz-form-col-6">
											<div class="fz-form-group">
												<label class="form-label"><?php esc_html_e( 'Username', 'feedzy-rss-feeds' ); ?>:</label>
												<input type="text" class="form-control" name="proxy-user" placeholder="<?php esc_attr_e( 'Enter the authorized username', 'feedzy-rss-feeds' ); ?>"
													value="<?php echo isset( $settings['proxy']['user'] ) ? esc_attr( $settings['proxy']['user'] ) : ''; ?>">
											</div>
										</div>
										<div class="fz-form-col-6">
											<div class="fz-form-group">
												<label class="form-label"><?php esc_html_e( 'Password', 'feedzy-rss-feeds' ); ?>:</label>
												<input type="password" class="form-control" name="proxy-pass" placeholder="<?php esc_attr_e( 'Enter the password for the authorized user', 'feedzy-rss-feeds' ); ?>"
													value="<?php echo isset( $settings['proxy']['pass'] ) ? esc_attr( $settings['proxy']['pass'] ) : ''; ?>">
											</div>
										</div>
										<div class="fz-form-col-8">
											<div class="fz-form-group">
												<label class="form-label"><?php esc_html_e( 'Host', 'feedzy-rss-feeds' ); ?>:</label>
												<input type="text" class="form-control" name="proxy-host" placeholder="<?php esc_attr_e( 'Enter the IP address or Domain name of the proxy server', 'feedzy-rss-feeds' ); ?>"
													value="<?php echo isset( $settings['proxy']['host'] ) ? esc_attr( $settings['proxy']['host'] ) : ''; ?>">
												<div class="help-text pt-8">
													<?php
													/* translators: %s: the value to introduce. */
													printf( esc_html__( 'Example: %s', 'feedzy-rss-feeds' ), '127.0.0.1' );
													?>
												</div>
											</div>
										</div>
										<div class="fz-form-col-4">
											<div class="fz-form-group">
												<label class="form-label"><?php esc_html_e( 'Port', 'feedzy-rss-feeds' ); ?>:</label>
												<input type="number" min="0" max="65535" class="form-control" name="proxy-port" placeholder="<?php esc_attr_e( 'Add the port number', 'feedzy-rss-feeds' ); ?>"
													value="<?php echo isset( $settings['proxy']['port'] ) ? esc_attr( (int) $settings['proxy']['port'] ) : ''; ?>">
												<div class="help-text pt-8">
													<?php
													/* translators: %s: the value to introduce. */
													printf( esc_html__( 'Example: %s', 'feedzy-rss-feeds' ), '8080' );
													?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php
							break;
						case 'logs':
							$show_button = false;
							break;
						case 'schedules':
							load_template( FEEDZY_ABSPATH . '/includes/layouts/feedzy-schedules.php' );
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
							$disable_button = ! feedzy_is_pro() && in_array( $active_tab, array( 'spinnerchief', 'wordai', 'amazon-product-advertising', 'openai' ), true ) ? ' disabled' : '';
						?>
						<div class="mb-24">
							<button type="submit" class="btn btn-primary<?php echo esc_attr( $disable_button ); ?>" id="feedzy-settings-submit" name="feedzy-settings-submit"><?php esc_html_e( 'Save Settings', 'feedzy-rss-feeds' ); ?></button>
						</div>
						<?php
					}
					?>
				</form>

				<?php
				if ( 'logs' === $active_tab ) {
					require_once FEEDZY_ABSPATH . '/includes/layouts/feedzy-logs-viewer.php';
				}
				?>
			</div>
		</div>

		<?php if ( 'proxy' !== $active_tab && 'headers' !== $active_tab && 'schedules' !== $active_tab && 'logs' !== $active_tab ) : ?>
			<div class="cta pt-30">
				<a href="<?php echo esc_url( $help_btn_url ); ?>" class="btn btn-ghost" target="_blank"><?php esc_html_e( 'Need help?', 'feedzy-rss-feeds' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</div>
