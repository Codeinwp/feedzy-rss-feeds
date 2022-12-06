<?php
/**
 * View for Import Post Type Meta Box Import Options
 *
 * @since   1.2.0
 * @package feedzy-rss-feeds-pro
 */

global $post;
?>
<?php if ( get_option( 'feedzy_import_tour' ) && ! defined( 'TI_CYPRESS_TESTING' ) ) : ?>
	<div id="fz-on-boarding"></div>
<?php endif; ?>
<div class="feedzy-wrap" id="feedzy-import-form">
	<div class="feedzy-accordion">
		<!-- <div> -->
		<!-- Sources configuration Step Start -->
		<div class="feedzy-accordion-item">
			<div class="feedzy-accordion-item__title">
				<button type="button" class="feedzy-accordion-item__button">
					<div class="feedzy-accordion__step-number help-text"><?php esc_html_e( 'Step 1', 'feedzy-rss-feeds' ); ?></div>
					<div class="feedzy-accordion__step-title h2"><?php esc_html_e( 'Sources configuration', 'feedzy-rss-feeds' ); ?></div>
					<div class="feedzy-accordion__icon"><span class="dashicons dashicons-arrow-down-alt2"></span></div>
				</button>
			</div>
			<div class="feedzy-accordion-item__content border-top">
				<div class="fz-form-wrap">
					<div class="form-block">
						<?php if ( ! feedzy_is_pro() ) : ?>
							<div class="fz-upsell-notice upgrade-alert mb-24">
								<?php
								$upsell_url = add_query_arg(
									array(
										'utm_source'   => 'wpadmin',
										'utm_medium'   => 'importfeed',
										'utm_campaign' => 'amazonproductadvertising',
										'utm_content'  => 'feedzy-rss-feeds',
									),
									FEEDZY_UPSELL_LINK
								);
								echo wp_kses_post( wp_sprintf( __( '<strong>NEW! </strong>Enable Amazon Product Advertising feeds to generate affiliate revenue by <a href="%s" target="_blank">upgrading to Feedzy Pro.</a><button type="button" class="remove-alert"><span class="dashicons dashicons-no-alt"></span></button>', 'feedzy-rss-feeds' ), esc_url_raw( $upsell_url ) ) );
								?>
							</div>
						<?php endif; ?>
						<label class="form-label"><?php esc_html_e( 'RSS Feed sources ', 'feedzy-rss-feeds' ); ?></label>
						<?php echo wp_kses_post( $invalid_source_msg ); ?>
						<input type="hidden" name="post_title" value="<?php echo $post ? esc_attr( $post->post_title ) : ''; ?>">
						<input type="hidden" id="feedzy_post_nonce" name="feedzy_post_nonce"
							value="<?php echo esc_attr( wp_create_nonce( 'feedzy_post_nonce' ) ); ?>" />

						<div class="fz-input-group">
							<div class="fz-input-group-left">
								<div class="fz-group">
									<div class="fz-input-icon">
										<input type="text" id="feedzy-import-source" title="<?php esc_attr_e( 'Make sure you validate the feed by using the validate button on the right', 'feedzy-rss-feeds' ); ?>"
											placeholder="<?php esc_attr_e( 'Paste your feed URL and click the plus icon to add it in the list', 'feedzy-rss-feeds' ); ?>"
											class="form-control" />
										<div class="fz-input-group-append">
											<button class="fz-plus-btn add-outside-tags" disabled>
												<span class="dashicons dashicons-plus-alt2"></span>
											</button>
										</div>
									</div>
									<div class="cta">
										<a class="btn btn-flate btn-icon" id="feedzy-validate-feed" target="_blank" data-href-base="https://validator.w3.org/feed/check.cgi?url="
											href="#" title="<?php esc_attr_e( 'Validate Feed', 'feedzy-rss-feeds' ); ?>"><i
												title="<?php esc_attr_e( 'Validate Feed', 'feedzy-rss-feeds' ); ?>"
												class="dashicons dashicons-external"></i></a>
									</div>
								</div>
								<div class="help-text">
									<?php esc_html_e( 'You can add multiple sources at once, by separating them with commas. Make sure to use the validate button. Invalid feeds may not import anything.', 'feedzy-rss-feeds' ); ?>
								</div>
							</div>
							<div class="fz-input-group-right">
									<div class="dropdown">
										<button type="button" class="btn btn-outline-primary dropdown-toggle" aria-haspopup="true"
												aria-expanded="false">
										<?php esc_html_e( 'Use Feed Category', 'feedzy-rss-feeds' ); ?> <span
													class="dashicons dashicons-arrow-down-alt2"></span>
											</button>
											<div class="dropdown-menu dropdown-menu-right">
										<?php
										if ( ! empty( $feed_categories ) ) {
											foreach ( $feed_categories as $category ) {
												?>
												<a class="dropdown-item source" href="#" data-field-name="source" data-field-tag="<?php echo esc_attr( $category->post_name ); ?>"><?php echo wp_kses_post( $category->post_title ); ?></a>
													<?php
											}
										} else {
											?>
											<div class="no-data p-8">
												<div class="help-text"><?php esc_html_e( 'You don&#8217;t have any categories, yet.', 'feedzy-rss-feeds' ); ?></div>
												<div class="cta-text"><a href="<?php echo esc_url( add_query_arg( 'post_type', 'feedzy_categories', admin_url( 'post-new.php' ) ) ); ?>" target="_blank"><?php esc_html_e( 'Add a Category', 'feedzy-rss-feeds' ); ?></a></div>
											</div>
											<?php
										}
										?>
										</div>
									</div>
							</div>
						</div>
						<div class="tag-list<?php echo empty( $source ) ? esc_attr( ' hidden' ) : ''; ?>">
							<input type="text" id="feedzy-source-tags" name="feedzy_meta_data[source]" class="fz-tagify--outside" value="<?php echo esc_attr( $source ); ?>" />
						</div>
						<div class="cta-text pt-8">
							<a href="<?php echo esc_url( 'https://docs.themeisle.com/article/799-how-to-find-feed-url-for-feedzy-rss-feeds' ); ?>" target="_blank"><?php esc_html_e( 'How do I find an RSS feed URL? ', 'feedzy-rss-feeds' ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Sources configuration Step End -->
		<!-- Filters Step Start -->
		<div class="feedzy-accordion-item">
			<div class="feedzy-accordion-item__title" id="fz-import-filters">
				<button type="button" class="feedzy-accordion-item__button">
					<div class="feedzy-accordion__step-number help-text"><?php esc_html_e( 'Step 2', 'feedzy-rss-feeds' ); ?></div>
					<div class="feedzy-accordion__step-title h2"><?php esc_html_e( 'Filters', 'feedzy-rss-feeds' ); ?></div>
					<div class="feedzy-accordion__icon"><span class="dashicons dashicons-arrow-down-alt2"></span></div>
				</button>
			</div>
			<div class="feedzy-accordion-item__content border-top">
				<div class="fz-form-wrap">
					<div class="form-block form-block-two-column <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
						<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '' ) ); ?>
						<div class="left">
							<h4 class="h4"><?php esc_html_e( 'Filter by Keyword(s)', 'feedzy-rss-feeds' ); ?><?php echo ! feedzy_is_pro() ? ' <span class="pro-label">PRO</span>' : ''; ?></h4>
							<div class="form-block-pro-text">
								<?php
								if ( ! feedzy_is_pro() ) {
									esc_html_e( 'This feature is only for Pro users.', 'feedzy-rss-feeds' );
									?>
								<br>
									<?php
								}
								?>
								<a href="https://docs.themeisle.com/article/1154-how-to-use-feed-to-post-feature-in-feedzy#filters" target="_blank"><?php esc_html_e( 'Learn More', 'feedzy-rss-feeds' ); ?></a>
							</div>
						</div>
						<div class="right">
							<div class="fz-form-group">
								<label class="form-label"><?php esc_html_e( 'Display item only if the selected field contains specific keyword(s)', 'feedzy-rss-feeds' ); ?></label>
								<div class="fz-input-group">
									<div class="fz-input-group-left">
										<div class="fz-group">
											<div class="fz-input-icon">
												<input type="text" placeholder="<?php esc_html_e( '(eg. news, stock + market etc.)', 'feedzy-rss-feeds' ); ?>" class="form-control feedzy-keyword-filter"/>
												<div class="fz-input-group-append">
													<button class="fz-plus-btn add-outside-tags">
														<span class="dashicons dashicons-plus-alt2"></span>
													</button>
												</div>
											</div>
										</div>
										<div class="help-text">
											<?php echo wp_kses_post( sprintf( __( 'You can add multiple keywords at once by separating them with %1$s,%2$s or use the %1$s+%2$s sign to bind multiple keywords. Remember, these words are case sensitive .e.g. NEWS, news, stock+market.', 'feedzy-rss-feeds' ), '<code>', '</code>' ) ); ?>
										</div>
									</div>
									<div class="fz-input-group-right">
										<select class="form-control feedzy-chosen feedzy-chosen-nosearch" name="feedzy_meta_data[inc_on]">
											<?php
											foreach ( $keyword_filter_fields as $field ) :
												$field_val = sanitize_key( $field );
												?>
											<option value="<?php echo esc_attr( $field_val ); ?>" <?php selected( $inc_on, $field_val ); ?>>
												<?php echo esc_html( $field ); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
								<div class="tag-list<?php echo empty( $inc_key ) ? esc_attr( ' hidden' ) : ''; ?>">
									<input type="text" name="feedzy_meta_data[inc_key]" class="fz-tagify-outside" value="<?php echo esc_attr( $inc_key ); ?>" />
								</div>
							</div>
						</div>
					</div>

					<div class="form-block form-block-two-column <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
						<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '' ) ); ?>
						<div class="left">
							<h4 class="h4"><?php esc_html_e( 'Exclude Items', 'feedzy-rss-feeds' ); ?><?php echo ! feedzy_is_pro() ? ' <span class="pro-label">PRO</span>' : ''; ?></h4>
						</div>
						<div class="right">
							<div class="fz-form-group">
								<label class="form-label"><?php esc_html_e( 'Exclude item if the selected field contains specific keyword(s)', 'feedzy-rss-feeds' ); ?></label>
								<div class="fz-input-group">
									<div class="fz-input-group-left">
										<div class="fz-group">
											<div class="fz-input-icon">
												<input type="text" placeholder="<?php esc_html_e( '(eg. news, stock + market etc.)', 'feedzy-rss-feeds' ); ?>" class="form-control feedzy-keyword-filter" />
												<div class="fz-input-group-append">
													<button class="fz-plus-btn add-outside-tags">
														<span class="dashicons dashicons-plus-alt2"></span>
													</button>
												</div>
											</div>
										</div>
										<div class="help-text">
											<?php echo wp_kses_post( sprintf( __( 'You can add multiple keywords at once by separating them with %1$s,%2$s or use the %1$s+%2$s sign to bind multiple keywords.', 'feedzy-rss-feeds' ), '<code>', '</code>' ) ); ?>
										</div>
									</div>
									<div class="fz-input-group-right">
										<select class="form-control feedzy-chosen feedzy-chosen-nosearch" name="feedzy_meta_data[exc_on]">
											<?php
											foreach ( $keyword_filter_fields as $field ) :
												$field_val = sanitize_key( $field );
												?>
											<option value="<?php echo esc_attr( $field_val ); ?>" <?php selected( $exc_on, $field_val ); ?>>
												<?php echo esc_html( $field ); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
								<div class="tag-list<?php echo empty( $exc_key ) ? esc_attr( ' hidden' ) : ''; ?>">
									<input type="text" name="feedzy_meta_data[exc_key]" class="fz-tagify-outside" value="<?php echo esc_attr( $exc_key ); ?>" />
								</div>
							</div>
						</div>
					</div>

					<div class="form-block form-block-two-column <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
						<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '' ) ); ?>
						<div class="left">
							<h4 class="h4"><?php esc_html_e( 'Filter by Time Range', 'feedzy-rss-feeds' ); ?><?php echo ! feedzy_is_pro() ? ' <span class="pro-label">PRO</span>' : ''; ?></h4>
						</div>
						<div class="right">
							<div class="date-range-group">
								<div class="fz-form-group">
									<label class="form-label"
										for="import_link_author_admin"><?php esc_html_e( 'From', 'feedzy-rss-feeds' ); ?></label>
									<input type="datetime-local" name="feedzy_meta_data[from_datetime]"
										placeholder="<?php esc_html_e( 'From', 'feedzy-rss-feeds' ); ?>" class="form-control"
										value="<?php echo esc_attr( $from_datetime ); ?>" />
								</div>
								<div class="fz-dash"><span class="dashicons dashicons-minus"></span></div>
								<div class="fz-form-group">
										<label class="form-label"
											for="import_link_author_admin"><?php esc_html_e( 'To', 'feedzy-rss-feeds' ); ?></label>
										<input type="datetime-local" name="feedzy_meta_data[to_datetime]"
											placeholder="<?php esc_html_e( 'From', 'feedzy-rss-feeds' ); ?>" class="form-control"
											value="<?php echo esc_attr( $to_datetime ); ?>" />
								</div>
							</div>
							<div class="help-text pt-8">
								<?php echo wp_kses_post( sprintf( __( 'Select a time range to import items within the selected dates.', 'feedzy-rss-feeds' ), '<code>', '</code>' ) ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Filters Step End -->
		<div class="feedzy-accordion-item">
			<div class="feedzy-accordion-item__title" id="fz-import-map-content">
				<button type="button" class="feedzy-accordion-item__button">
					<div class="feedzy-accordion__step-number help-text"><?php esc_html_e( 'Step 3', 'feedzy-rss-feeds' ); ?></div>
					<div class="feedzy-accordion__step-title h2"><?php esc_html_e( 'Map content', 'feedzy-rss-feeds' ); ?></div>
					<div class="feedzy-accordion__icon"><span class="dashicons dashicons-arrow-down-alt2"></span></div>
				</button>
			</div>
			<div class="feedzy-accordion-item__content">
				<div class="fz-content">
					<p>
						<?php
							esc_html_e( 'Using magic tags, specify what part(s) of the source should form part of the imported post.', 'feedzy-rss-feeds' );
						?>
						<?php if ( false === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) { ?>
							<?php echo wp_kses_post( sprintf( __( 'The magic tags that are greyed out and disabled, are unavailable for your current license. Unlock all features, by %1$supgrading to Feedzy Pro%2$s', 'feedzy-rss-feeds' ), '<a href="' . tsdk_utmify( FEEDZY_UPSELL_LINK, 'magictags' ) . '" target="_blank" title="' . __( 'upgrading to Feedzy Pro', 'feedzy-rss-feeds' ) . '">', '</a>' ) ); ?>
						<?php } ?>
					</p>
				</div>
				<div class="fz-tabs-menu">
					<ul>
						<li>
							<a href="javascript:;" data-id="fz-general"><?php esc_html_e( 'General', 'feedzy-rss-feeds' ); ?></a>
						</li>
						<li>
							<a href="javascript:;" data-id="fz-advanced-options"><?php esc_html_e( 'Advanced Options', 'feedzy-rss-feeds' ); ?></a>
						</li>
					</ul>
				</div>
				<div class="fz-tabs-content">
					<div class="fz-tab-content" id="fz-general">
						<div class="fz-form-wrap">
							<div class="form-block form-block-two-column">
								<div class="left">
									<h4 class="h4"><?php esc_html_e( 'Post Type', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="right">
									<div class="fz-form-group">
										<label class="form-label"><?php esc_html_e( 'The post type you want to use for the generated post.', 'feedzy-rss-feeds' ); ?></label>
										<div class="mx-320">
											<select id="feedzy_post_type" class="form-control feedzy-chosen" name="feedzy_meta_data[import_post_type]"
												data-tax="<?php echo esc_attr( $import_post_term ); ?>">
												<?php
												foreach ( $post_types as $_post_type ) {
													?>
												<option value="<?php echo esc_attr( $_post_type ); ?>" <?php selected( $import_post_type, $_post_type ); ?>>
													<?php echo esc_html( $_post_type ); ?></option>
													<?php
												}
												?>
											</select>
										</div>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="left">
									<h4 class="h4"><?php esc_html_e( 'Post Taxonomy', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="right">
									<div class="fz-form-group">
										<label class="form-label"><?php esc_html_e( 'Assigns the post to a Category', 'feedzy-rss-feeds' ); ?></label>
										<div class="mx-320">
											<select id="feedzy_post_terms" multiple class="form-control feedzy-chosen"
												name="feedzy_meta_data[import_post_term][]">
											</select>
										</div>
										<div class="help-text pt-8">
											<?php esc_html_e( 'The imported post will be assigned to a taxonomy (eg. "Post Category", "Post Tag" etc.). Leave blank, if unsure.', 'feedzy-rss-feeds' ); ?>
										</div>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="left">
									<h4 class="h4"><?php esc_html_e( 'Post Status', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="right">
									<div class="fz-form-group">
										<label class="form-label"><?php esc_html_e( 'The post status for the imported posts.', 'feedzy-rss-feeds' ); ?></label>
										<div class="mx-320">
											<select id="feedzy_post_status" class="form-control feedzy-chosen" name="feedzy_meta_data[import_post_status]">
												<?php
												foreach ( $published_status as $_status ) {
													?>
												<option value="<?php echo esc_attr( $_status ); ?>" <?php selected( $import_post_status, $_status ); ?>>
													<?php echo esc_html( ucfirst( $_status ) ); ?></option>
													<?php
												}
												?>
											</select>
										</div>
										<div class="help-text pt-8">
											<?php
												esc_html_e( 'Choose Published if you want to publish your posts right away, or you can use Draft if you want to draft your posts and publish them after reviewing them manually.', 'feedzy-rss-feeds' );
											?>
										</div>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="left">
									<h4 class="h4"><?php esc_html_e( 'Post Title', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="right">
									<div class="fz-form-group">
										<label class="form-label"><?php esc_html_e( 'The title for the generated post.', 'feedzy-rss-feeds' ); ?></label>
										<div class="fz-input-group">
											<div class="fz-input-group-left">
												<div class="fz-group">
													<input type="text" name="feedzy_meta_data[import_post_title]"
														placeholder="<?php esc_html_e( 'Post Title', 'feedzy-rss-feeds' ); ?>" class="form-control fz-input-tagify"
														value="<?php echo esc_attr( $import_title ); ?>" />
												</div>
												<div class="help-text">
													<?php
														$magic_tags = array(
															'item_title',
															'item_author',
															'item_date',
														);
														$magic_tags = apply_filters( 'feedzy_get_service_magic_tags', $magic_tags, 'title' );

														esc_html_e( 'You can add multiple items. Keep in mind that this field is mandatory - without it, a post will not be created.', 'feedzy-rss-feeds' );
														?>
												</div>
											</div>
											<div class="fz-input-group-right">
													<div class="dropdown">
														<button type="button" class="btn btn-outline-primary btn-add-fields dropdown-toggle" 	aria-haspopup="true" aria-expanded="false">
															<?php esc_html_e( 'Insert Tag', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-plus-alt2"></span>
														</button>
														<div class="dropdown-menu dropdown-menu-right">
															<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_title', array() ), 'import_post_title' ) ); ?>
														</div>
													</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="left">
									<h4 class="h4"><?php esc_html_e( 'Post Date', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="right">
									<div class="fz-form-group">
										<label class="form-label"><?php esc_html_e( 'The date for the generated post. ', 'feedzy-rss-feeds' ); ?></label>
										<div class="fz-input-group">
											<div class="fz-input-group-left">
												<div class="fz-group">
													<input type="text" name="feedzy_meta_data[import_post_date]"
														placeholder="<?php esc_html_e( 'Post Date', 'feedzy-rss-feeds' ); ?>" class="form-control fz-input-tagify"
														value="<?php echo esc_attr( $import_date ); ?>" />
												</div>
												<div class="help-text">
													<?php
														esc_html_e( 'Leave blank, if unsure.', 'feedzy-rss-feeds' );
													?>
												</div>
											</div>
											<div class="fz-input-group-right">
													<div class="dropdown">
														<button type="button" class="btn btn-outline-primary btn-add-fields dropdown-toggle" aria-haspopup="true"
															aria-expanded="false">
															<?php esc_html_e( 'Insert Tag', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-plus-alt2"></span>
														</button>
														<div class="dropdown-menu dropdown-menu-right">
															<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_date', array() ), 'import_post_date' ) ); ?>
														</div>
													</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="left">
									<h4 class="h4"><?php esc_html_e( 'Content', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="right">
									<div class="fz-form-group">
										<label class="form-label"><?php esc_html_e( 'The content for the generated post', 'feedzy-rss-feeds' ); ?></label>
										<div class="fz-input-group">
											<div class="fz-input-group-left">
												<div class="fz-group">
													<textarea name="feedzy_meta_data[import_post_content]"
														placeholder="<?php esc_html_e( 'Post Content', 'feedzy-rss-feeds' ); ?>"
														class="form-control fz-textarea-tagify"><?php echo esc_html( feedzy_custom_tag_escape( $import_content ) ); ?></textarea>
												</div>
												<div class="help-text">
													<?php
														$magic_tags = array(
															'item_description',
															'item_content',
															'item_image',
														);
														if ( apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ) {
															$magic_tags = array_merge(
																$magic_tags,
																array(
																	'item_full_content',
																)
															);
															$magic_tags = apply_filters( 'feedzy_get_service_magic_tags', $magic_tags, 'full_content' );
														}
														$magic_tags = apply_filters( 'feedzy_get_service_magic_tags', $magic_tags, 'content' );

														esc_html_e( 'You can add more tags and other things that will be added in the Single Post layout. This field is mandatory.', 'feedzy-rss-feeds' );
														?>
												</div>
											</div>
											<div class="fz-input-group-right">
													<div class="dropdown">
														<button type="button" class="btn btn-outline-primary btn-add-fields dropdown-toggle" aria-haspopup="true"
															aria-expanded="false">
															<?php esc_html_e( 'Insert Tag', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-plus-alt2"></span>
														</button>
														<div class="dropdown-menu dropdown-menu-right">
															<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_content', array() ), 'import_post_content' ) ); ?>
														</div>
													</div>
											</div>
										</div>
									</div>
									<?php if ( ! feedzy_is_pro() ) : ?>
										<div class="upgrade-alert">
											<?php
												echo wp_kses_post( sprintf( __( 'Add more advanced tags, like item price, rating and many more, by %1$supgrading to Feedzy Pro%2$s', 'feedzy-rss-feeds' ), '<a href="' . tsdk_utmify( FEEDZY_UPSELL_LINK, 'moreadvanced' ) . '" target="_blank">', '</a><button type="button" class="remove-alert"><span class="dashicons dashicons-no-alt"></span></button>' ) );
											?>
										</div>
									<?php endif; ?>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="left">
									<h4 class="h4"><?php esc_html_e( 'Featured image', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="right">
									<div class="fz-form-group">
										<label class="form-label"><?php esc_html_e( 'The Featured image for the generated post.', 'feedzy-rss-feeds' ); ?></label>
										<div class="fz-input-group">
											<div class="fz-input-group-left">
												<div class="fz-group">
													<input type="text" name="feedzy_meta_data[import_post_featured_img]"
														placeholder="<?php esc_html_e( 'Add a tag for the featured image', 'feedzy-rss-feeds' ); ?>" class="form-control fz-input-tagify"
														value="<?php echo esc_attr( $import_featured_img ); ?>" />
												</div>
												<div class="help-text">
													<?php
														esc_html_e( 'You can use the magic tags, your own URL or leave it empty.', 'feedzy-rss-feeds' );
													?>
												</div>
											</div>
											<div class="fz-input-group-right">
													<div class="dropdown">
														<button type="button" class="btn btn-outline-primary btn-add-fields dropdown-toggle" aria-haspopup="true"
															aria-expanded="false">
															<?php esc_html_e( 'Insert Tag', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-plus-alt2"></span>
														</button>
														<div class="dropdown-menu dropdown-menu-right">
															<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_image', array() ), 'import_post_featured_img' ) ); ?>
														</div>
													</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="fz-tab-content" id="fz-advanced-options">
						<div class="fz-form-wrap">
							<div class="form-block form-block-two-column">
								<div class="left">
									<h4 class="h4"><?php esc_html_e( 'External image', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="right">
									<div class="fz-form-group">
										<div class="fz-form-switch">
											<input id="use-external-image" name="feedzy_meta_data[import_use_external_image]"
											class="fz-switch-toggle" type="checkbox" value="yes"
											<?php echo esc_attr( $import_item_img_url ); ?>>
											<label class="feedzy-inline form-label" for="use-external-image"><?php esc_html_e( 'Use external image URL, Ignore feature post thumbnail', 'feedzy-rss-feeds' ); ?></label>
										</div>
									</div>
									<div class="help-text">
										<?php esc_html_e( 'This will use the external featured image of the imported article and won\'t save the image to the Media Library on your website.', 'feedzy-rss-feeds' ); ?>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
								<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '' ) ); ?>
								<div class="left">
									<h4 class="h4"><?php esc_html_e( 'Post Author', 'feedzy-rss-feeds' ); ?> <?php echo ! feedzy_is_pro() ? ' <span class="pro-label">PRO</span>' : ''; ?></h4>
								</div>
								<div class="right">
									<div class="fz-form-group">
										<div class="fz-form-switch">
											<input id="feedzy-toggle_author_admin" name="feedzy_meta_data[import_link_author_admin]"
												class="fz-switch-toggle" type="checkbox" value="yes"
												<?php echo wp_kses_post( $import_link_author[0] ); ?>>
											<label class="form-label" for="feedzy-toggle_author_admin"><?php esc_html_e( 'Save it in Backend', 'feedzy-rss-feeds' ); ?></label>
										</div>
										<div class="help-text">
											<?php
												esc_html_e( 'The source author will appear in the Dashboard', 'feedzy-rss-feeds' );
											?>
										</div>
									</div>
									<div class="fz-form-group pt-12">
										<div class="fz-form-switch">
											<input id="feedzy-toggle_author_public" name="feedzy_meta_data[import_link_author_public]"
												class="fz-switch-toggle" type="checkbox" value="yes"
												<?php echo wp_kses_post( $import_link_author[1] ); ?>>
											<label for="feedzy-toggle_author_public" class="feedzy-inline"></label>
											<label class="form-label" for="feedzy-toggle_author_public"><?php esc_html_e( 'Save it in Frontend', 'feedzy-rss-feeds' ); ?></label>
										</div>
										<div class="help-text">
											<?php
												esc_html_e( 'The source author will appear in Archive Pages', 'feedzy-rss-feeds' );
											?>
										</div>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="left">
									<h4 class="h4"><?php esc_html_e( 'Post Excerpt', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="right">
									<div class="fz-form-group">
										<label class="form-label"><?php esc_html_e( 'The Post Excerpt for the generated post', 'feedzy-rss-feeds' ); ?></label>
										<div class="fz-input-group">
											<div class="fz-input-group-left">
												<div class="fz-group">
													<input type="text" name="feedzy_meta_data[import_post_excerpt]"
														placeholder="<?php esc_html_e( 'Post Excerpt', 'feedzy-rss-feeds' ); ?>" class="form-control fz-input-tagify"
														value="<?php echo esc_attr( $post_excerpt ); ?>" />
												</div>
												<div class="help-text">
													<?php
														esc_html_e( 'Add magic tags to extract custom elements from your feed. This will work only for single-feeds, not feed categories.', 'feedzy-rss-feeds' );
													?>
												</div>
											</div>
											<div class="fz-input-group-right">
													<div class="dropdown">
														<button type="button" class="btn btn-outline-primary btn-add-fields dropdown-toggle" aria-haspopup="true"
															aria-expanded="false">
															<?php esc_html_e( 'Insert Tag', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-plus-alt2"></span>
														</button>
														<div class="dropdown-menu dropdown-menu-right">
															<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_post_excerpt', array() ), 'import_post_excerpt' ) ); ?>
														</div>
													</div>
											</div>
										</div>
									</div>
									<?php if ( ! feedzy_is_pro() ) : ?>
										<div class="upgrade-alert">
											<?php
												echo wp_kses_post( sprintf( __( 'Add more advanced tags, like item price, rating and many more, by %1$supgrading to Feedzy Pro%2$s', 'feedzy-rss-feeds' ), '<a href="' . tsdk_utmify( FEEDZY_UPSELL_LINK, 'upgradealert' ) . '" target="_blank">', '</a><button type="button" class="remove-alert"><span class="dashicons dashicons-no-alt"></span></button>' ) );
											?>
										</div>
									<?php endif; ?>
								</div>
							</div>

							<div class="form-block form-block-two-column <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
								<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '' ) ); ?>
								<div class="left">
									<h4 class="h4"><?php esc_html_e( 'Custom Fields', 'feedzy-rss-feeds' ); ?> <?php echo ! feedzy_is_pro() ? ' <span class="pro-label">PRO</span>' : ''; ?></h4>
									<div class="form-block-pro-text">
									<?php esc_html_e( 'This feature is only for Pro users.', 'feedzy-rss-feeds' ); ?><br>
										<a href="https://docs.themeisle.com/article/977-how-do-i-extract-values-from-custom-tags-in-feedzy" target="_blank"><?php esc_html_e( 'Learn More', 'feedzy-rss-feeds' ); ?></a>
									</div>
								</div>
								<div class="right">
									<div class="fz-form-group">
										<label class="form-label pb-16"><?php esc_html_e( 'Customizable fields to fetch custom values such as date updated, rating, etc.', 'feedzy-rss-feeds' ); ?></label>
										<div class="custom_fields">
											<?php
											if ( isset( $import_custom_fields ) && ! empty( $import_custom_fields ) ) {
												foreach ( $import_custom_fields as $custom_field_key => $custom_field_value ) {
													?>
														<div class="key-value-item">
															<div class="fz-form-group">
																<input type="text" name="custom_vars_key[]"
																	placeholder="<?php esc_html_e( 'Key Name', 'feedzy-rss-feeds' ); ?>" class="form-control"
																			value="<?php echo esc_attr( $custom_field_key ); ?>" />
															</div>
															<div class="key-value-arrow">
																<span class="dashicons dashicons-arrow-right-alt"></span>
															</div>
															<div class="fz-form-group">
																<input type="text" name="custom_vars_value[]" placeholder="<?php esc_html_e( 'Value', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo esc_attr( $custom_field_value ); ?>" />
															</div>
															<div class="remove-group">
																<button type="button" class="btn-remove-fields">
																</button>
															</div>
														</div>
													<?php
												}
											}
											?>
										</div>
									</div>
									<div class="cta">
										<button id="new_custom_fields" type="button" class="btn btn-outline-primary btn-block btn-add-fields"><?php esc_html_e( 'Add Custom Field', 'feedzy-rss-feeds' ); ?>
											<span class="dashicons dashicons-plus-alt2"></span>
										</button>
									</div>
									<div class="help-text pt-8">
										<?php
											echo wp_kses_post( sprintf( __( 'Check the  %1$sDocumentation%2$s for more details.', 'feedzy-rss-feeds' ), '<a href="https://docs.themeisle.com/article/977-how-do-i-extract-values-from-custom-tags-in-feedzy" target="_blank">', '</a>' ) );
										?>
									</div>
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="feedzy-accordion-item">
			<div class="feedzy-accordion-item__title" id="fz-import-general-settings">
				<button type="button" class="feedzy-accordion-item__button">
					<div class="feedzy-accordion__step-number help-text"><?php esc_html_e( 'Step 4', 'feedzy-rss-feeds' ); ?></div>
					<div class="feedzy-accordion__step-title h2"><?php esc_html_e( 'General feed settings', 'feedzy-rss-feeds' ); ?></div>
					<div class="feedzy-accordion__icon"><span class="dashicons dashicons-arrow-down-alt2"></span></div>
				</button>
			</div>
			<div class="feedzy-accordion-item__content border-top">
				<div class="fz-form-wrap">
					<div class="form-block form-block-two-column <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
						<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '' ) ); ?>
						<div class="left">
							<h4 class="h4"><?php esc_html_e( 'Auto-Delete', 'feedzy-rss-feeds' ); ?> <?php echo ! feedzy_is_pro() ? ' <span class="pro-label">PRO</span>' : ''; ?></h4>
						</div>
						<div class="right">
							<div class="fz-form-group">
								<label class="form-label"><?php esc_html_e( 'Delete the posts created for this import after a number of days', 'feedzy-rss-feeds' ); ?></label>
								<input type="number" min="0" max="9999" id="feedzy_delete_days" name="feedzy_meta_data[import_feed_delete_days]" class="form-control" value="<?php echo esc_attr( (int) $import_feed_delete_days ); ?>" />
								<div class="help-text pt-8">
									<?php esc_html_e( 'Helpful if you want to remove stale or old items automatically. Choose 0, and the imported items will not be automatically deleted.', 'feedzy-rss-feeds' ); ?>
								</div>
							</div>
						</div>
					</div>

					<div class="form-block form-block-two-column">
						<div class="left">
							<h4 class="h4"><?php esc_html_e( 'Remove Duplicates', 'feedzy-rss-feeds' ); ?></h4>
						</div>
						<div class="right">
							<div class="fz-form-group">
								<div class="fz-form-switch">
									<input id="remove-duplicates" name="feedzy_meta_data[import_remove_duplicates]"
									class="fz-switch-toggle" type="checkbox" value="yes"
									<?php echo esc_attr( $import_remove_duplicates ); ?>>
									<label class="feedzy-inline form-label" for="remove-duplicates"><?php esc_html_e( 'Remove Duplicate Items', 'feedzy-rss-feeds' ); ?></label>
								</div>
							</div>
							<div class="help-text">
								<?php echo wp_sprintf( esc_html__( 'To understand how duplicates will be removed, check out our', 'feedzy-rss-feeds' ) ); ?>
									<a href="<?php echo esc_url( 'https://docs.themeisle.com/article/638-how-to-eliminate-duplicate-feed-item' ); ?>" target="_blank"><?php esc_html_e( 'Documentation.', 'feedzy-rss-feeds' ); ?></a>
							</div>
						</div>
					</div>

					<div class="form-block form-block-two-column">
						<div class="left">
							<h4 class="h4"><?php esc_html_e( 'Items Count', 'feedzy-rss-feeds' ); ?></h4>
						</div>
						<div class="right">
							<div class="fz-form-group">
								<label class="form-label"><?php esc_html_e( 'How many feed items to import from the source?', 'feedzy-rss-feeds' ); ?></label>
								<input type="number" min="0" max="9999" id="feedzy_item_limit" name="feedzy_meta_data[import_feed_limit]" class="form-control" value="<?php echo esc_attr( (int) $import_feed_limit ); ?>" />
								<div class="help-text pt-8">
									<?php echo wp_kses_post( sprintf( __( 'If you choose a high number, please check that your configuration can support it or your imports may fail.', 'feedzy-rss-feeds' ), '<b>', '</b>' ) ); ?>
								</div>
							</div>
						</div>
					</div>		
					<?php if ( function_exists( 'icl_get_languages' ) ) : ?>
						<div class="form-block form-block-two-column">
							<div class="left">
								<h4 class="h4"><?php esc_html_e( 'Assign Language', 'feedzy-rss-feeds' ); ?><?php echo ! feedzy_is_pro() ? ' <span class="pro-label">PRO</span>' : ''; ?></h4>
								<?php if ( ! feedzy_is_pro() ) : ?>
									<div class="form-block-pro-text">
									<?php esc_html_e( 'This feature is only for Pro users.', 'feedzy-rss-feeds' ); ?><br>
										<a href="https://docs.themeisle.com/category/712-feedzy" target="_blank"><?php esc_html_e( 'Learn More', 'feedzy-rss-feeds' ); ?></a>
									</div>
								<?php endif; ?>
							</div>
							<div class="right">
								<div class="fz-form-group">
									<label class="form-label"><?php esc_html_e( 'Content Language after import', 'feedzy-rss-feeds' ); ?></label>
									<div class="mx-320">
										<select id="feedzy_site_language" class="form-control feedzy-chosen" name="feedzy_meta_data[language]">
											<?php
											$current_language         = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '';
											$import_selected_language = ! empty( $import_selected_language ) ? $import_selected_language : $current_language;
											$languages                = icl_get_languages();
											foreach ( $languages as $language ) {
												$selected = '';
												$code     = isset( $language['language_code'] ) ? $language['language_code'] : $language['code'];
												$name     = isset( $language['translated_name'] ) && ! empty( $language['translated_name'] ) ? $language['translated_name'] : $language['native_name'];
												if ( $code === $import_selected_language ) {
													$selected = 'selected';
												}
												?>
											<option value="<?php echo esc_attr( $code ); ?>" <?php echo esc_attr( $selected ); ?>>
												<?php echo esc_html( $name ); ?></option>
												<?php
											}
											?>
										</select>
									</div>
									<div class="help-text">
										<?php esc_html_e( 'Select the language the content will have when it will be imported.', 'feedzy-rss-feeds' ); ?>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<?php
					if ( $this->feedzy_is_agency() ) :
						$target_lang = array(
							'en' => __( 'English', 'feedzy-rss-feeds' ),
							'af' => __( 'Afrikaans', 'feedzy-rss-feeds' ),
							'am' => __( 'Amharic', 'feedzy-rss-feeds' ),
							'ar' => __( 'Arabic', 'feedzy-rss-feeds' ),
							'ast' => __( 'Asturian', 'feedzy-rss-feeds' ),
							'az' => __( 'Azerbaijani', 'feedzy-rss-feeds' ),
							'ba' => __( 'Bashkir', 'feedzy-rss-feeds' ),
							'be' => __( 'Belarusian', 'feedzy-rss-feeds' ),
							'bg' => __( 'Bulgarian', 'feedzy-rss-feeds' ),
							'bn' => __( 'Bengali', 'feedzy-rss-feeds' ),
							'br' => __( 'Breton', 'feedzy-rss-feeds' ),
							'bs' => __( 'Bosnian', 'feedzy-rss-feeds' ),
							'ca' => __( 'Catalan', 'feedzy-rss-feeds' ),
							'ceb' => __( 'Cebuano', 'feedzy-rss-feeds' ),
							'cs' => __( 'Czech', 'feedzy-rss-feeds' ),
							'cy' => __( 'Welsh', 'feedzy-rss-feeds' ),
							'da' => __( 'Danish', 'feedzy-rss-feeds' ),
							'de' => __( 'German', 'feedzy-rss-feeds' ),
							'el' => __( 'Greeek', 'feedzy-rss-feeds' ),
							'es' => __( 'Spanish', 'feedzy-rss-feeds' ),
							'et' => __( 'Estonian', 'feedzy-rss-feeds' ),
							'fa' => __( 'Persian', 'feedzy-rss-feeds' ),
							'ff' => __( 'Fulah', 'feedzy-rss-feeds' ),
							'fi' => __( 'Finnish', 'feedzy-rss-feeds' ),
							'fr' => __( 'French', 'feedzy-rss-feeds' ),
							'fy' => __( 'Western Frisian', 'feedzy-rss-feeds' ),
							'ga' => __( 'Irish', 'feedzy-rss-feeds' ),
							'gd' => __( 'Gaelic', 'feedzy-rss-feeds' ),
							'gl' => __( 'Galician', 'feedzy-rss-feeds' ),
							'gu' => __( 'Gujarati', 'feedzy-rss-feeds' ),
							'ha' => __( 'Hausa', 'feedzy-rss-feeds' ),
							'he' => __( 'Hebrew', 'feedzy-rss-feeds' ),
							'hi' => __( 'Hindi', 'feedzy-rss-feeds' ),
							'hr' => __( 'Croatian', 'feedzy-rss-feeds' ),
							'ht' => __( 'Haitian', 'feedzy-rss-feeds' ),
							'hu' => __( 'Hungarian', 'feedzy-rss-feeds' ),
							'hy' => __( 'Armenian', 'feedzy-rss-feeds' ),
							'id' => __( 'Indonesian', 'feedzy-rss-feeds' ),
							'ig' => __( 'Igbo', 'feedzy-rss-feeds' ),
							'ilo' => __( 'Iloko', 'feedzy-rss-feeds' ),
							'is' => __( 'Icelandic', 'feedzy-rss-feeds' ),
							'it' => __( 'Italian', 'feedzy-rss-feeds' ),
							'ja' => __( 'Japanese', 'feedzy-rss-feeds' ),
							'jv' => __( 'Javanese', 'feedzy-rss-feeds' ),
							'ka' => __( 'Georgian', 'feedzy-rss-feeds' ),
							'kk' => __( 'Kazakh', 'feedzy-rss-feeds' ),
							'km' => __( 'Central Khmer', 'feedzy-rss-feeds' ),
							'kn' => __( 'Kannada', 'feedzy-rss-feeds' ),
							'ko' => __( 'Korean', 'feedzy-rss-feeds' ),
							'lb' => __( 'Luxembourgish', 'feedzy-rss-feeds' ),
							'lg' => __( 'Ganda', 'feedzy-rss-feeds' ),
							'ln' => __( 'Lingala', 'feedzy-rss-feeds' ),
							'lo' => __( 'Lao', 'feedzy-rss-feeds' ),
							'lt' => __( 'Lithuanian', 'feedzy-rss-feeds' ),
							'lv' => __( 'Latvian', 'feedzy-rss-feeds' ),
							'mg' => __( 'Malagasy', 'feedzy-rss-feeds' ),
							'mk' => __( 'Macedonian', 'feedzy-rss-feeds' ),
							'ml' => __( 'Malayalam', 'feedzy-rss-feeds' ),
							'mn' => __( 'Mongolian', 'feedzy-rss-feeds' ),
							'mr' => __( 'Marathi', 'feedzy-rss-feeds' ),
							'ms' => __( 'Malay', 'feedzy-rss-feeds' ),
							'my' => __( 'Burmese', 'feedzy-rss-feeds' ),
							'ne' => __( 'Nepali', 'feedzy-rss-feeds' ),
							'nl' => __( 'Dutch', 'feedzy-rss-feeds' ),
							'no' => __( 'Norwegian', 'feedzy-rss-feeds' ),
							'ns' => __( 'Northern Sotho', 'feedzy-rss-feeds' ),
							'oc' => __( 'Occitan (post 1500)', 'feedzy-rss-feeds' ),
							'or' => __( 'Oriya', 'feedzy-rss-feeds' ),
							'pa' => __( 'Punjabi', 'feedzy-rss-feeds' ),
							'pl' => __( 'Polish', 'feedzy-rss-feeds' ),
							'ps' => __( 'Pushto', 'feedzy-rss-feeds' ),
							'pt' => __( 'Portuguese', 'feedzy-rss-feeds' ),
							'ro' => __( 'Romanian', 'feedzy-rss-feeds' ),
							'ru' => __( 'Russian', 'feedzy-rss-feeds' ),
							'sd' => __( 'Sindhi', 'feedzy-rss-feeds' ),
							'si' => __( 'Sinhala', 'feedzy-rss-feeds' ),
							'sk' => __( 'Slovak', 'feedzy-rss-feeds' ),
							'sl' => __( 'Slovenian', 'feedzy-rss-feeds' ),
							'so' => __( 'Somali', 'feedzy-rss-feeds' ),
							'sq' => __( 'Albanian', 'feedzy-rss-feeds' ),
							'sr' => __( 'Serbian', 'feedzy-rss-feeds' ),
							'ss' => __( 'Swati', 'feedzy-rss-feeds' ),
							'su' => __( 'Sundanese', 'feedzy-rss-feeds' ),
							'swe' => __( 'Swedish', 'feedzy-rss-feeds' ),
							'sw' => __( 'Swahili', 'feedzy-rss-feeds' ),
							'ta' => __( 'Tamil', 'feedzy-rss-feeds' ),
							'th' => __( 'Thai', 'feedzy-rss-feeds' ),
							'tl' => __( 'Tagalog', 'feedzy-rss-feeds' ),
							'tn' => __( 'Tswana', 'feedzy-rss-feeds' ),
							'tr' => __( 'Turkish', 'feedzy-rss-feeds' ),
							'uk' => __( 'Ukrainian', 'feedzy-rss-feeds' ),
							'ur' => __( 'Urdu', 'feedzy-rss-feeds' ),
							'uz' => __( 'Uzbek', 'feedzy-rss-feeds' ),
							'vi' => __( 'Vietnamese', 'feedzy-rss-feeds' ),
							'wo' => __( 'Wolof', 'feedzy-rss-feeds' ),
							'xh' => __( 'Xhosa', 'feedzy-rss-feeds' ),
							'yi' => __( 'Yiddish', 'feedzy-rss-feeds' ),
							'yo' => __( 'Yoruba', 'feedzy-rss-feeds' ),
							'zh' => __( 'Chinese', 'feedzy-rss-feeds' ),
							'zu' => __( 'Zulu', 'feedzy-rss-feeds' ),
						);
						$target_lang = apply_filters( 'feedzy_available_automatically_translation_language', $target_lang );
						?>
						<div class="form-block form-block-two-column">
							<div class="left">
								<h4 class="h4"><?php esc_html_e( 'Enable automatic translation?', 'feedzy-rss-feeds' ); ?></h4>
									<div class="form-block-pro-text">
										<?php esc_html_e( 'Enable and select the language to translate the text automatically. Enable this only if you used the Translate magic tags. The default is English', 'feedzy-rss-feeds' ); ?>
									</div>
							</div>
							<div class="right">
								<div class="fz-form-group">
									<div style="margin-bottom: 5px;">
										<input id="feedzy-auto-translation" name="feedzy_meta_data[import_auto_translation]" class="fz-switch-toggle" type="checkbox" value="yes" <?php echo esc_attr( $import_auto_translation ); ?>>
										<label for="feedzy-auto-translation" class="feedzy-inline"></label>
										<label class="feedzy-inline" style="margin-left: 10px;" for="import_auto_translation"></label>
									</div>
									<div>
										<select id="feedzy_auto_translation_lang" class="form-control feedzy-chosen" name="feedzy_meta_data[import_auto_translation_lang]"<?php echo empty( $import_auto_translation ) ? ' disabled' : ''; ?>>
											<?php foreach ( $target_lang as $code => $lang ) : ?>
												<option value="<?php echo esc_attr( $code ); ?>"<?php echo $import_translation_lang === $code ? ' selected' : ''; ?>><?php echo esc_html( $lang ); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<input type="hidden" id="custom_post_status" name="custom_post_status" value="draft" />
	<div class="fz-form-action">
		<div class="left">
			<?php
				$clone_url = wp_nonce_url(
					add_query_arg(
						array(
							'action' => 'feedzy_clone_import_job',
							'feedzy_job_id' => $post->ID,
						),
						'admin.php'
					),
					FEEDZY_BASENAME,
					'clone_import'
				);
				?>
			<a href="<?php echo esc_url( $clone_url ); ?>" class="btn btn-ghost"><?php esc_html_e( 'Clone Import', 'feedzy-rss-feeds' ); ?></a>
		</div>
		<div class="right">
			<button type="button" id="preflight" name="check" class="btn btn-ghost" value="Check"
			title="<?php esc_html_e( 'Click to see what items will be imported from the source, according to the filters specified', 'feedzy-rss-feeds' ); ?>"><?php esc_html_e( 'Preview  Import', 'feedzy-rss-feeds' ); ?></button>
			<?php
			if ( 'publish' === $post_status ) {
				?>
				<button type="submit" name="publish" class="btn btn-outline-primary" value="Publish"><?php esc_html_e( 'Save', 'feedzy-rss-feeds' ); ?></button>
				<?php
			} else {
				?>
				<button type="submit" name="save" class="btn btn-outline-primary" value="Save Draft" style="float: none;"><?php esc_html_e( 'Save', 'feedzy-rss-feeds' ); ?></button>
				<button type="submit" name="publish" class="btn btn-primary" value="Publish"><?php esc_html_e( 'Save & Activate importing', 'feedzy-rss-feeds' ); ?></button>
				<?php
			}
			?>

		</div>
	</div>
</div>

<script id="empty_select_tpl" type="text/template">
	<option value="none"><?php esc_html_e( 'None', 'feedzy-rss-feeds' ); ?></option>
</script>

<script id="loading_select_tpl" type="text/template">
	<option value=""><?php esc_html_e( 'Loading...', 'feedzy-rss-feeds' ); ?></option>
</script>

<script id="new_field_tpl" type="text/template">
	<?php echo wp_kses( apply_filters( 'feedzy_custom_field_template', '' ), apply_filters( 'feedzy_wp_kses_allowed_html', array() ) ); ?>
</script>
