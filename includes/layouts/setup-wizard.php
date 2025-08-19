<?php
/**
 * Feedzy setup wizard template.
 *
 * @package Feedzy_Rss_Feeds
 */

// Note: It will be redirect to dashboard by `feedzy_dismiss_wizard` action.
$skip_onboarding_url_callback = add_query_arg(
	array(
		'action' => 'feedzy_dismiss_wizard',
		'status' => 0,
	),
	admin_url( 'admin.php' )
);
// phpcs:ignore WordPress.Security.NonceVerification
$integrate_with     = ! empty( $_GET['integrate-with'] ) ? sanitize_text_field( wp_unslash( $_GET['integrate-with'] ) ) : '';
$feed_source        = '';
$wp_optimole_active = is_plugin_active( 'optimole-wp/optimole-wp.php' );
$last_step_number   = 3;
if ( ! empty( $integrate_with ) ) {
	$wizard_data = get_option( 'feedzy_wizard_data', array() );
	$feed_source = ! empty( $wizard_data['feed'] ) ? $wizard_data['feed'] : '';
}
$published_status = array( 'publish', 'draft' );

add_thickbox();
?>
<div class="feedzy-wizard-wrap feedzy-wrap">
	<div class="feedzy-header--small">
		<div class="container">
			<div class="feedzy-logo">
				<div class="feedzy-logo-icon">
					<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/feedzy.svg' ); ?>" width="50" height="50">
				</div>
			</div>
			<div class="back-btn">
				<a href="<?php echo esc_url( $skip_onboarding_url_callback ); ?>" class="btn-link"><span class="dashicons dashicons-arrow-left-alt"></span> <?php esc_html_e( 'Go to dashboard', 'feedzy-rss-feeds' ); ?></a>
			</div>
		</div>
	</div>
	<div class="container">
		<div class="feedzy-wizard">
			<div id="smartwizard" class="sw">
				<ul class="nav">
					<li class="nav-item">
						<a class="nav-link" href="#step-1">1</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#step-2">2</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#step-3">3</a>
					</li>
					<?php
					if ( ! $wp_optimole_active ) :
						$last_step_number = 4;
						?>
						<li class="nav-item">
							<a class="nav-link" href="#step-4">4</a>
						</li>
					<?php endif; ?>
				</ul>
					<form method="post" class="fz-wizard-form">
						<div class="tab-content">
							<div id="step-1" class="tab-pane" role="tabpanel" aria-labelledby="step-1">
								<div class="feedzy-accordion-item">
									<div class="feedzy-accordion-item__title">
										<div class="feedzy-accordion-item__button">
											<h2 class="h3"><?php esc_html_e( 'How would you like to integrate Feedzy into your website?', 'feedzy-rss-feeds' ); ?></h2>
										</div>
									</div>
									<div class="feedzy-accordion-item__content border-top">
										<div class="fz-form-wrap">
											<div class="form-block">
												<div class="fz-radio pb-16">
													<input type="radio" class="fz-radio-btn" name="feedzy[wizard_data][integrate]" id="radio-1" value="feed" required<?php checked( $integrate_with, 'feed' ); ?>>
													<label for="radio-1"><?php esc_html_e( 'Importing RSS feeds to your website content', 'feedzy-rss-feeds' ); ?></label>
												</div>
												<div class="fz-radio pb-16">
													<input type="radio" class="fz-radio-btn" name="feedzy[wizard_data][integrate]" id="radio-2" value="shortcode" required<?php checked( $integrate_with, 'shortcode' ); ?>>
													<label for="radio-2"><?php esc_html_e( 'Display RSS feeds using shortcodes', 'feedzy-rss-feeds' ); ?></label>
												</div>
												<div class="fz-radio">
													<input type="radio" class="fz-radio-btn" name="feedzy[wizard_data][integrate]" id="radio-3" value="page_builder" required<?php checked( $integrate_with, 'page_builder' ); ?>>
													<label for="radio-3">
														<?php
														if ( defined( 'ELEMENTOR_PATH' ) && class_exists( 'Elementor\Widget_Base' ) ) {
															esc_html_e( 'Display RSS feed using elementor', 'feedzy-rss-feeds' );
														} else {
															esc_html_e( 'Display RSS feed using block editor', 'feedzy-rss-feeds' );
														}
														?>
													</label>
												</div>
											</div>
											<div class="form-block">
												<button class="btn btn-primary<?php echo empty( $integrate_with ) ? ' disabled' : ''; ?>" data-step_number="1"><?php esc_html_e( 'Get Started', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span></button>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div id="step-2" class="tab-pane" role="tabpanel" aria-labelledby="step-2">
								<div class="feedzy-accordion-item mb-30" id="feed_source">
									<div class="feedzy-accordion-item__title">
										<div class="feedzy-accordion-item__button">
											<h2 class="h3"><?php esc_html_e( 'Add RSS feed URL', 'feedzy-rss-feeds' ); ?></h2>
										</div>
										<div class="feedzy-accordion-item__button hidden">
											<h2 class="h3"><?php esc_html_e( 'Feed Source URL', 'feedzy-rss-feeds' ); ?></h2>
											<div class="feedzy-accordion__icon feedzy-accordion__icon--success"><span class="dashicons dashicons-saved"></span></div>
										</div>
									</div>
									<div class="feedzy-accordion-item__content border-top">
										<div class="fz-form-wrap">
											<div class="form-block">
												<div class="fz-error-notice hidden"></div>
												<div class="fz-row fz-row-center">
													<div class="fz-col-md-6">
														<div class="fz-form-group">
															<input type="text" id="wizard_feed_source" class="form-control" placeholder="<?php esc_attr_e( 'Paste RSS feed URL here', 'feedzy-rss-feeds' ); ?>" value="<?php echo esc_attr( $feed_source ); ?>" required>
														</div>
													</div>
													<div class="fz-col-md-6">
														<div class="help-text">
															<?php
															echo wp_kses_post(
																sprintf(
																	// translators: %1$s and %2$s are HTML tags for the link to the demo URL.
																	__( 'No Feed URL? %1$s Click here %2$s to use demo URL', 'feedzy-rss-feeds' ),
																	'<a target="_blank" class="feed-demo-link" href="' . esc_url( 'https://wpshout.com/feed/' ) . '" >',
																	'</a>'
																)
															);
															?>
															</div>
													</div>
												</div>
											</div>
											<div class="form-block">
												<?php if ( 'page_builder' !== $integrate_with ) : ?>
													<button class="btn btn-ghost<?php echo empty( $feed_source ) ? ' disabled' : ''; ?>" id="preflight">
														<?php esc_html_e( 'Preview Import', 'feedzy-rss-feeds' ); ?>
													</button>
												<?php endif; ?>
												<button class="btn btn-primary<?php echo empty( $feed_source ) ? ' disabled' : ''; ?>" data-step_number="2">
													<?php
													if ( 'page_builder' === $integrate_with ) {
														esc_html_e( 'Create draft page', 'feedzy-rss-feeds' );
													} else {
														esc_html_e( 'Save and Continue', 'feedzy-rss-feeds' );
													}
													?>
													<span class="dashicons dashicons-arrow-right-alt"></span>
												</button>
												<button class="btn btn-ghost btn-skip" style="color: #757575;">
													<?php
														esc_html_e( 'Skip', 'feedzy-rss-feeds' );
													?>
												</button>
												<span class="spinner"></span>
											</div>
										</div>
									</div>
								</div>
								<div class="feedzy-accordion-item mb-30 hidden" id="feed_import">
									<div class="feedzy-accordion-item__title">
										<div class="feedzy-accordion-item__button">
											<h2 class="h2"><?php esc_html_e( 'Import RSS feed content as', 'feedzy-rss-feeds' ); ?></h2>
										</div>
									</div>
									<div class="feedzy-accordion-item__content border-top">
										<div class="fz-form-wrap">
											<div class="form-block">
												<label class="form-label">
													<?php esc_html_e( 'Import as', 'feedzy-rss-feeds' ); ?>
												</label>
												<div class="mx-320">
													<select name="feedzy[wizard_data][import_post_type]" class="form-control feedzy-chosen">
														<option value="post"><?php esc_html_e( 'Post', 'feedzy-rss-feeds' ); ?></option>
														<option value="page"><?php esc_html_e( 'Page', 'feedzy-rss-feeds' ); ?></option>
													</select>
												</div>
											</div>
											<div class="form-block">
												<div class="fz-form-row">
													<div class="fz-form-col-6">
														<label class="form-label">
															<?php esc_html_e( 'Post status', 'feedzy-rss-feeds' ); ?>
														</label>
														<div class="mx-320">
															<select id="feedzy_post_status" class="form-control feedzy-chosen" name="feedzy_meta_data[import_post_status]">
																<?php
																foreach ( $published_status as $_status ) {
																	?>
																	<option value="<?php echo esc_attr( $_status ); ?>">
																		<?php echo esc_html( ucfirst( $_status ) ); ?>
																	</option>
																	<?php
																}
																?>
															</select>
														</div>
													</div>
													<div class="fz-form-col-6">
														<label class="form-label">
															<?php esc_html_e( 'Fallback Image', 'feedzy-rss-feeds' ); ?>
														</label>
														<div class="fz-cta-group pb-8">
															<a 
																href="javascript:;"
																class="feedzy-open-media btn btn-outline-primary"
															>
															<?php esc_html_e( 'Choose image', 'feedzy-rss-feeds' ); ?>
															</a>
															<a href="javascript:;" class="feedzy-remove-media btn btn-outline-primary"><?php esc_html_e( 'Remove', 'feedzy-rss-feeds' ); ?></a>
															<input type="hidden" name="feedzy_meta_data[default_thumbnail_id]" id="feed-post-default-thumbnail" value="">
															<div class="help-text pt-8">
																<?php esc_html_e( 'Optional. Used as fallback for imported posts without images.', 'feedzy-rss-feeds' ); ?>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-block">
												<label class="form-label">
													<?php esc_html_e( 'Exclude posts with title not containing', 'feedzy-rss-feeds' ); ?>
												</label>
												<div class="fz-form-group">
													<input type="text" id="feedzy_exclude_title" name="feedzy_meta_data[exc_key]" class="form-control" value="" />
													<div class="help-text pt-8">
														<?php esc_html_e( 'Posts will not be imported if their title includes these keywords.', 'feedzy-rss-feeds' ); ?>
													</div>
												</div>
											</div>
											<div class="form-block">
												<button class="btn btn-primary fz-wizard-feed-import">
													<?php esc_html_e( 'Create a draft import', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span>
												</button>
												<button class="btn btn-ghost btn-skip" style="color: #757575;">
													<?php
														esc_html_e( 'Skip', 'feedzy-rss-feeds' );
													?>
												</button>
												<span class="spinner"></span>
											</div>
										</div>
									</div>
								</div>
								<div class="feedzy-accordion-item hidden" id="shortcode">
									<div class="feedzy-accordion-item__title">
										<div class="feedzy-accordion-item__button">
											<h2 class="h2"><?php esc_html_e( 'Feed preview and shortcode', 'feedzy-rss-feeds' ); ?></h2>
										</div>
									</div>
									<div class="feedzy-accordion-item__content border-top">
										<div class="fz-form-wrap">
											<div class="form-block">
												<div class="fz-shortcode-preview-box">
													<div class="fz-shortcode-preview-title">
														<div class="icon">
															<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/alternate-file.svg' ); ?>" alt="">
														</div>
														<div class="txt">
															<h4 class="h4 pb-4"><?php esc_html_e( 'Create a draft page', 'feedzy-rss-feeds' ); ?></h4>
															<p class="p"><?php esc_html_e( 'We will automatically create a draft page with Feedzy shortcodes for preview', 'feedzy-rss-feeds' ); ?></p>
														</div>
													</div>
													<div class="fz-shortcode-preview-content">
														<div class="content-title">
															<h4 class="h4"><?php esc_html_e( 'Add basic short code in a draft page', 'feedzy-rss-feeds' ); ?> <span
																	class="pro-label free-label"><?php esc_html_e( 'Free', 'feedzy-rss-feeds' ); ?></span></h4>
															<div class="check">
																<input type="checkbox" id="add_basic_shortcode" class="fz-switch-toggle" value="1" checked>
															</div>
														</div>
														<div class="fz-shortcode-preview">
															<h4 class="h4 pb-8"><?php esc_html_e( 'Basic Shortcode', 'feedzy-rss-feeds' ); ?></h4>
															<div class="fz-code-box">
																<input type="text" readonly value='[feedzy-rss feeds={{feed_source}} max="6" meta="author, date" columns="3" summary="yes" summarylength="160" thumb="yes" target="_blank"]' id="basic_shortcode">
																<button type="button" class="fz-copy-code-btn" data-clipboard-target="#basic_shortcode"><?php esc_html_e( 'click to copy', 'feedzy-rss-feeds' ); ?> <img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/copy.svg' ); ?>" alt="">
																</button>
															</div>
															<p class="p"><?php esc_html_e( 'No other parameters specified beside the source', 'feedzy-rss-feeds' ); ?></p>
														</div>
													</div>
												</div>
											</div>
											<div class="form-block">
												<button class="btn btn-primary fz-create-page"><?php esc_html_e( 'Create Page', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span></button>
												<span class="spinner"></span>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php if ( ! $wp_optimole_active ) : ?>
								<div id="step-3" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
									<div class="feedzy-accordion-item">
										<div class="feedzy-accordion-item__title">
											<div class="feedzy-accordion-item__button">
												<h2 class="h2"><?php esc_html_e( 'Extra Features', 'feedzy-rss-feeds' ); ?></h2>
											</div>
										</div>
										<div class="feedzy-accordion-item__content border-top">
											<div class="fz-form-wrap">
												<div class="form-block">
													<div class="fz-error-notice hidden">
														<div id="message" class="error">
														</div>
													</div>
													<div class="feedzy-accordion">
														<div class="feedzy-accordion-item fz-features-accordion mb-0">
															<div class="feedzy-accordion-item__title feedzy-accordion-checkbox__title">
																<div class="fz-checkbox">
																	<input type="checkbox" name="feedzy[wizard_data][enable_perfomance]" class="fz-checkbox-btn" checked>
																</div>
																<button type="button" class="feedzy-accordion-item__button">
																	<div class="feedzy-accordion__step-title h4 pb-4"><?php esc_html_e( 'Enable performance features for your website.', 'feedzy-rss-feeds' ); ?>
																	</div>
																	<p class="help-text"><?php esc_html_e( 'Optimise and speed up your site with our trusted addon - It’s Free', 'feedzy-rss-feeds' ); ?></p>
																	<div class="feedzy-accordion__icon"><span class="dashicons dashicons-arrow-down-alt2"></span></div>
																</button>
															</div>
															<div class="feedzy-accordion-item__content feedzy-optimole-upsell">
																<div class="fz-features-list">
																	<ul>
																		<li>
																			<div class="icon">
																				<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/boost-logo.svg' ); ?>" width="37" height="30" alt="">
																			</div>
																			<div class="txt">
																				<div class="h4 pb-4"><?php esc_html_e( 'Boost your website speed', 'feedzy-rss-feeds' ); ?> <span class="pro-label free-label"><?php esc_html_e( 'Free', 'feedzy-rss-feeds' ); ?></span>
																				</div>
																				<p class="help-text">
																					<?php
																					echo wp_kses_post(
																						sprintf(
																							// translators: %1$s is the percentage improvement, %2$s and %3$s are HTML tags for the link to the Optimole website.
																							__( 'Improve your website speed and images by %1$s with %2$s Optimole %3$s', 'feedzy-rss-feeds' ),
																							'80%',
																							'<a target="_blank" href="' . esc_url( tsdk_translate_link( tsdk_utmify( 'https://optimole.com/pricing/', 'setupWizard' ) ) ) . '">',
																							'</a>'
																						)
																					);
																					?>
																				</p>
																			</div>
																		</li>
																	</ul>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="form-block">
													<button class="btn btn-primary fz-wizard-install-plugin" data-step_number="3"><?php esc_html_e( 'Improve now', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span></button>
													<button class="btn btn-primary next-btn skip-improvement" style="display: none;"><?php esc_html_e( 'Skip Improvement', 'feedzy-rss-feeds' ); ?></button>
													<span class="spinner"></span>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php endif; ?>
							<div id="step-<?php echo esc_attr( $last_step_number ); ?>" class="tab-pane" role="tabpanel" aria-labelledby="step-<?php echo esc_attr( $last_step_number ); ?>">
								<div class="feedzy-accordion-item">
									<div class="feedzy-accordion-item__title">
										<div class="feedzy-accordion-item__button">
											<h2 class="h2"><?php esc_html_e( 'Updates, tutorials, special offers & more', 'feedzy-rss-feeds' ); ?></h2>
										</div>
									</div>
									<div class="feedzy-accordion-item__content border-top">
										<div class="fz-form-wrap">
											<div class="form-block">
												<div class="fz-newsletter-wrap">
													<div class="fz-newsletter">
														<p class="p pb-30"><?php esc_html_e( 'Let us know your email so that we can send you product updates, helpful tutorials, exclusive offers and more useful stuff.', 'feedzy-rss-feeds' ); ?></p>
														
														<ul class="fz-benefits-list">
															<li>
																<svg width="16" height="16" viewBox="0 0 24 24" fill="#00a32a" aria-hidden="true">
																	<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
																</svg>
																<?php esc_html_e( 'Receive product updates, tutorials, and exclusive offers', 'feedzy-rss-feeds' ); ?>
															</li>
															<li>
																<svg width="16" height="16" viewBox="0 0 24 24" fill="#00a32a" aria-hidden="true">
																	<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
																</svg>
																<?php esc_html_e( 'Help improve Feedzy by sharing anonymous usage data', 'feedzy-rss-feeds' ); ?>
															</li>
															<li>
																<svg width="16" height="16" viewBox="0 0 24 24" fill="#00a32a" aria-hidden="true">
																	<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
																</svg>
																<?php esc_html_e( 'Get priority notification about new features', 'feedzy-rss-feeds' ); ?>
															</li>
														</ul>
														
														<div class="fz-privacy-note">
															<svg width="14" height="14" viewBox="0 0 24 24" fill="#666">
																<path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
															</svg>
															<span><?php esc_html_e( 'Your privacy matters: We only collect anonymous plugin usage data. No sensitive information is shared with third parties.', 'feedzy-rss-feeds' ); ?></span>
														</div>
														
														<div class="fz-form-group">
															<input type="email" id="fz_subscribe_email" class="form-control" placeholder="<?php echo esc_attr( get_bloginfo( 'admin_email' ) ); ?>" value="<?php echo esc_attr( get_bloginfo( 'admin_email' ) ); ?>">
														</div>
													</div>
													<div class="fz-newsletter-img">
														<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/newsletter-img.png' ); ?>" alt="">
													</div>
												</div>
											</div>
											<div class="form-block">
												<div class="fz-btn-group">
													<button class="btn btn-primary fz-subscribe" data-fz_subscribe="true"><?php esc_html_e( 'Complete Setup & Start Using Feedzy', 'feedzy-rss-feeds' ); ?></button>
													<button class="btn btn-link fz-subscribe fz-skip-link" data-fz_subscribe="false"><?php esc_html_e( 'Skip for now', 'feedzy-rss-feeds' ); ?></button>
													<span class="spinner"></span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</form>
			</div>
		</div>
	</div>
</div>
<div class="redirect-popup">
	<div class="redirect-popup-box">
		<div class="icon">
			<svg width="5" height="23" viewBox="0 0 5 23" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M0.6875 23V6.4375H4.57812V23H0.6875ZM4.15625 3.65625C3.73958 4.0625 3.22917 4.26562 2.625 4.26562C2.02083 4.26562 1.51042 4.0625 1.09375 3.65625C0.677083 3.23958 0.46875 2.73958 0.46875 2.15625C0.46875 1.5625 0.677083 1.0625 1.09375 0.65625C1.51042 0.239583 2.02083 0.03125 2.625 0.03125C3.22917 0.03125 3.73958 0.239583 4.15625 0.65625C4.58333 1.0625 4.79688 1.5625 4.79688 2.15625C4.79688 2.73958 4.58333 3.23958 4.15625 3.65625Z" fill="#2F5AAE" fill-opacity="0.75"/>
			</svg>
		</div>
		<h3 class="h3 popup-title"></h3>
		<div class="redirect-loader">
			<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/mask-loader.jpg' ); ?>" width="45" height="45" alt="loader">
		</div>
	</div>
</div>
