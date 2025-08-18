<?php
/**
 * View for Import Post Type Meta Box Import Options
 *
 * @since   1.2.0
 * @package feedzy-rss-feeds-pro
 */

global $post;
?>
<?php if ( feedzy_show_import_tour() && ! defined( 'TI_CYPRESS_TESTING' ) ) : ?>
	<div id="fz-on-boarding"></div>
<?php endif; ?>
<div id="fz-feedback-modal"></div>
<div id="fz-action-popup"></div>
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

						<label class="form-label"><?php esc_html_e( 'RSS Feed sources ', 'feedzy-rss-feeds' ); ?></label>
						<?php echo wp_kses_post( $invalid_source_msg ); ?>
						<input type="hidden" name="post_title" value="<?php echo $post ? esc_attr( $post->post_title ) : ''; ?>">
						<input type="hidden" id="feedzy_post_nonce" name="feedzy_post_nonce"
							value="<?php echo esc_attr( wp_create_nonce( 'feedzy_post_nonce' ) ); ?>" />

						<input type="hidden" id="feedzy_auto_translate" name="feedzy_meta_data[import_auto_translation]" value="<?php echo esc_attr( $import_auto_translation ); ?>" />
						<input type="hidden" id="feedzy_auto_translate_lang" name="feedzy_meta_data[import_auto_translation_lang]" value="<?php echo esc_attr( $import_translation_lang ); ?>" />

						<div class="fz-input-group">
							<div class="fz-input-group-left">
								<div class="fz-group">
									<div class="fz-input-icon">
										<div class="fz-feed-validator-wrapper">
											<input type="text" 
												id="feedzy-import-source" 
												title="<?php esc_attr_e( 'Make sure you validate the feed by using the validate button on the right', 'feedzy-rss-feeds' ); ?>"
												placeholder="<?php esc_attr_e( 'Paste your feed URL and click the plus icon to add it in the list', 'feedzy-rss-feeds' ); ?>"
												class="form-control" />
											<div class="fz-input-group-append">
												<button class="btn btn-flate btn-icon add-outside-tags" 
												type="button"
												id="feedzy-validate-feed" 
												role="button"
												title="<?php esc_attr_e( 'Validate Feed', 'feedzy-rss-feeds' ); ?>">
													<i class="dashicons dashicons-plus-alt2" aria-hidden="true"></i>
												</button>
											</div>
										</div>
									</div>
								</div>
								<div class="help-text">
									<?php esc_html_e( 'You can add multiple sources at once, by separating them with commas. Make sure to use the validate button. Invalid feeds may not import anything.', 'feedzy-rss-feeds' ); ?>
								</div>
								<div class="fz-validation-summary">
								</div>
							</div>
							<div class="fz-input-group-right">
									<div class="dropdown">
										<button type="button" class="btn btn-outline-primary dropdown-toggle" aria-haspopup="true"
												aria-expanded="false">
										<?php esc_html_e( 'Use Feed Group', 'feedzy-rss-feeds' ); ?> <span
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
												<div class="help-text"><?php esc_html_e( 'You don&#8217;t have any groups, yet.', 'feedzy-rss-feeds' ); ?></div>
												<div class="cta-text"><a href="<?php echo esc_url( add_query_arg( 'post_type', 'feedzy_categories', admin_url( 'post-new.php' ) ) ); ?>" target="_blank"><?php esc_html_e( 'Add a Group', 'feedzy-rss-feeds' ); ?></a></div>
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
							<a href="<?php echo esc_url( 'https://docs.themeisle.com/article/799-how-to-find-feed-url-for-feedzy-rss-feeds' ); ?>" target="_blank"><?php esc_html_e( 'How do I find an RSS feed URL?', 'feedzy-rss-feeds' ); ?></a>
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

					<input type="hidden" name="feedzy_meta_data[filter_conditions]" id="feed-post-filters-conditions" value="<?php echo esc_attr( $filter_conditions ); ?>">
					<div class="fz-conditions" id="fz-conditions"></div>
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
								<div class="fz-left">
									<h4 class="h4"><?php esc_html_e( 'Post Type', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<div class="mx-320">
											<select id="feedzy_post_type" class="form-control feedzy-chosen" name="feedzy_meta_data[import_post_type]"
												data-tax="<?php echo esc_attr( $import_post_term ); ?>" data-custom-tag="<?php echo esc_attr( isset( $custom_post_term ) ? $custom_post_term : '' ); ?>">
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
								<div class="fz-left">
									<h4 class="h4"><?php esc_html_e( 'Post Taxonomy', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<div class="mx-320">
											<select id="feedzy_post_terms" multiple class="form-control feedzy-chosen<?php echo feedzy_is_pro() ? ' fz-chosen-custom-tag' : ''; ?>"
												name="feedzy_meta_data[import_post_term][]">
											</select>
										</div>
										<div class="help-text pt-8">
											<?php esc_html_e( 'If you don\'t select at least one taxonomy, posts will be assigned to Uncategorized by default.', 'feedzy-rss-feeds' ); ?>
										</div>
									</div>
									<?php if ( feedzy_is_pro() ) : ?>
										<div class="help-text pt-8">
											<?php
												echo wp_kses_post(
													sprintf(
														// translators: %1$s: magic tag, %2$s: opening anchor tag, %3$s: closing anchor tag.
														__( 'Auto-create categories from source: %1$s %2$s Learn More %3$s', 'feedzy-rss-feeds' ),
														'<strong>[#item_categories]</strong>',
														'<a href="' . esc_url( 'https://docs.themeisle.com/article/1154-how-to-use-feed-to-post-feature-in-feedzy#dynamic-post-taxonomy' ) . '" target="_blank">',
														'</a>'
													)
												);
											?>
										</div>
										<div class="help-text pt-8">
											<?php
												// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
												echo wp_kses_post(
													sprintf(
														// translators: %1$s: magic tag, %2$s: opening anchor tag, %3$s: closing anchor tag.
														__( 'Auto-assign by title keywords: %1$s %2$s Configure %3$s', 'feedzy-rss-feeds' ),
														'<strong>[#auto_categories]</strong>',
														'<a href="' . esc_url( get_admin_url( null, 'admin.php?page=feedzy-settings' ) ) . '" target="_blank">',
														'</a>'
													)
												);
											?>
										</div>
									<?php endif; ?>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="fz-left">
									<h4 class="h4"><?php esc_html_e( 'Post Status', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
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
								<div class="fz-left">
									<h4 class="h4"><?php esc_html_e( 'Post Title', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<div class="fz-input-group">
											<div class="fz-input-group-left">
												<div class="fz-group position-relative">
													<input type="text" name="feedzy_meta_data[import_post_title]"
														placeholder="<?php esc_attr_e( 'Post Title', 'feedzy-rss-feeds' ); ?>" class="form-control fz-input-tagify"
														value="<?php echo esc_attr( $import_title ); ?>" />
													<div class="fz-input-group-right fz-title-action-tags position-absolute fz-insert-tags">
														<div class="dropdown">
															<button type="button" class="btn btn-outline-primary btn-add-fields dropdown-toggle" aria-haspopup="true" aria-expanded="false">
																<span class="dashicons dashicons-plus-alt2"></span>
															</button>
															<div class="dropdown-menu dropdown-menu-right">
																<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_title', array() ), 'import_post_title' ) ); ?>
															</div>
														</div>
												</div>
												</div>
												<div class="help-text">
													<?php
														esc_html_e( 'You can add multiple items. Keep in mind that this field is mandatory - without it, a post will not be created.', 'feedzy-rss-feeds' );
													?>
												</div>
												<div class="help-text pt-8">
													<?php
														esc_html_e( 'Tip: Click the settings icon to add actions to this field.', 'feedzy-rss-feeds' );
													?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="fz-left">
									<h4 class="h4">
										<?php esc_html_e( 'Post Date', 'feedzy-rss-feeds' ); ?>
									</h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<div class="fz-input-group">
											<div class="fz-input-group-left">
												<div class="fz-group position-relative">
													<input type="text" name="feedzy_meta_data[import_post_date]"
														placeholder="<?php esc_attr_e( 'Post Date', 'feedzy-rss-feeds' ); ?>" class="form-control fz-input-tagify"
														value="<?php echo esc_attr( $import_date ); ?>" />
													<div class="fz-input-group-right fz-date-action-tags position-absolute fz-insert-tags">
														<div class="dropdown">
															<button type="button" class="btn btn-outline-primary btn-add-fields dropdown-toggle" aria-haspopup="true"
																aria-expanded="false">
																<span class="dashicons dashicons-plus-alt2"></span>
															</button>
															<div class="dropdown-menu dropdown-menu-right">
																<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_date', array() ), 'import_post_date' ) ); ?>
															</div>
														</div>
												</div>
												</div>
												<div class="help-text">
													<?php
														esc_html_e( 'Leave blank, if unsure.', 'feedzy-rss-feeds' );
													?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="fz-left">
									<h4 class="h4">
										<?php esc_html_e( 'Content', 'feedzy-rss-feeds' ); ?>
									</h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<div class="fz-input-group">
											<div class="fz-input-group-left">
												<div class="fz-group position-relative">
													<input type="text" name="feedzy_meta_data[import_post_content]"
														placeholder="<?php esc_attr_e( 'Post Content', 'feedzy-rss-feeds' ); ?>"
														class="form-control fz-input-tagify"
														value="<?php echo esc_attr( feedzy_custom_tag_escape( $import_content ) ); ?>" />
													<div class="fz-input-group-right fz-content-action-tags position-absolute fz-insert-tags">
														<div class="dropdown">
															<button type="button" class="btn btn-outline-primary btn-add-fields dropdown-toggle" aria-haspopup="true"
																aria-expanded="false">
																<span class="dashicons dashicons-plus-alt2"></span>
															</button>
															<div class="dropdown-menu dropdown-menu-right">
																<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_content', array() ), 'import_post_content' ) ); ?>
															</div>
														</div>
												</div>
												</div>
												<div class="help-text">
													<?php
														esc_html_e( 'You can add more tags and other things that will be added in the Single Post layout. This field is mandatory.', 'feedzy-rss-feeds' );
													?>
												</div>
											</div>
										</div>
									</div>
									<?php if ( ! feedzy_is_pro() && ! Feedzy_Rss_Feeds_Ui::had_dismissed_notice() ) : ?>
										<div class="upgrade-alert">
											<?php
												echo wp_kses_post(
													sprintf(
														// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
														__( 'Add more advanced tags, like item price, rating and many more, by %1$s upgrading to Feedzy Pro %2$s', 'feedzy-rss-feeds' ),
														'<a href="' . esc_url( tsdk_translate_link( tsdk_utmify( FEEDZY_UPSELL_LINK, 'moreadvanced' ) ) ) . '" target="_blank">',
														'</a><button type="button" class="remove-alert"><span class="dashicons dashicons-no-alt"></span></button>'
													)
												);
											?>
										</div>
									<?php endif; ?>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="fz-left">
									<h4 class="h4"><?php esc_html_e( 'Featured image', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<div class="fz-input-group">
											<div class="fz-input-group-left">
												<div class="fz-group position-relative">
													<input type="text" name="feedzy_meta_data[import_post_featured_img]"
														placeholder="<?php esc_attr_e( 'Add a tag for the featured image', 'feedzy-rss-feeds' ); ?>" class="form-control fz-input-tagify fz-tagify-image"
														value="<?php echo esc_attr( $import_featured_img ); ?>" />
													<div class="fz-input-group-right fz-image-action-tags position-absolute fz-insert-tags">
														<div class="dropdown">
															<button type="button" class="btn btn-outline-primary btn-add-fields dropdown-toggle" aria-haspopup="true"
																aria-expanded="false">
																<span class="dashicons dashicons-plus-alt2"></span>
															</button>
															<div class="dropdown-menu dropdown-menu-right">
																<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_image', array() ), 'import_post_featured_img' ) ); ?>
															</div>
														</div>
													</div>
												</div>
												<div class="help-text">
													<?php
														esc_html_e( 'You can use the magic tags, URL, or leave it empty.', 'feedzy-rss-feeds' );
													?>
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
								<div class="fz-left">
									<h4 class="h4"><?php esc_html_e( 'External image', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="fz-right">
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
								<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '', 'post-author', 'import' ) ); ?>
								<div class="fz-left">
									<h4 class="h4"><?php esc_html_e( 'Post Author', 'feedzy-rss-feeds' ); ?> <?php echo ! feedzy_is_pro() ? ' <span class="pro-label">PRO</span>' : ''; ?></h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<div class="mx-320">
											<select id="feedzy_post_author" class="form-control feedzy-chosen fz-chosen-custom-tag" name="feedzy_meta_data[import_post_author]">
												<?php
												foreach ( $authors_array as $_author ) {
													?>
												<option value="<?php echo esc_attr( $_author ); ?>" <?php selected( $import_post_author, $_author ); ?>>
													<?php echo esc_html( $_author ); ?></option>
													<?php
												}
												?>
											</select>
										</div>
										<div class="help-text pt-8 pb-8">
											<?php
												esc_html_e( 'Select the author to assign to the imported posts. By default, this will be set to your current account. Note that this choice is independent of the options below, which control how the source author details are displayed.', 'feedzy-rss-feeds' );
											?>
										</div>
									</div>
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
								<div class="fz-left">
									<h4 class="h4">
										<?php esc_html_e( 'Post Excerpt', 'feedzy-rss-feeds' ); ?>
									</h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<div class="fz-input-group">
											<div class="fz-input-group-left">
												<div class="fz-group position-relative">
													<input type="text" name="feedzy_meta_data[import_post_excerpt]"
														placeholder="<?php esc_attr_e( 'Post Excerpt', 'feedzy-rss-feeds' ); ?>" class="form-control fz-input-tagify"
														value="<?php echo esc_attr( $post_excerpt ); ?>" />
													<div class="fz-input-group-right position-absolute fz-insert-tags">
														<div class="dropdown">
															<button type="button" class="btn btn-outline-primary btn-add-fields dropdown-toggle" aria-haspopup="true"
																aria-expanded="false">
																<span class="dashicons dashicons-plus-alt2"></span>
															</button>
															<div class="dropdown-menu dropdown-menu-right">
																<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_post_excerpt', array() ), 'import_post_excerpt' ) ); ?>
															</div>
														</div>
													</div>
												</div>
												<div class="help-text">
													<?php
														esc_html_e( 'Add magic tags to extract custom elements from your feed. This will work only for single-feeds, not feed groups.', 'feedzy-rss-feeds' );
													?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
								<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '', 'custom-fields', 'import' ) ); ?>
								<div class="fz-left">
									<h4 class="h4">
										<?php esc_html_e( 'Custom Fields', 'feedzy-rss-feeds' ); ?> <?php echo ! feedzy_is_pro() ? ' <span class="pro-label">PRO</span>' : ''; ?>
									</h4>
									<div class="form-block-pro-text">
										<a href="https://docs.themeisle.com/article/977-how-do-i-extract-values-from-custom-tags-in-feedzy" target="_blank">
											<?php esc_html_e( 'Learn More', 'feedzy-rss-feeds' ); ?>
										</a>
									</div>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<div class="custom_fields">
											<?php
											if ( isset( $import_custom_fields ) && ! empty( $import_custom_fields ) ) {
												foreach ( $import_custom_fields as $custom_field_key => $custom_field_value ) {
													?>
														<div class="key-value-item">
															<div class="fz-form-group">
																<input type="text" name="custom_vars_key[]"
																	placeholder="<?php esc_attr_e( 'Key Name', 'feedzy-rss-feeds' ); ?>" class="form-control"
																			value="<?php echo esc_attr( $custom_field_key ); ?>" />
															</div>
															<div class="key-value-arrow">
																<span class="dashicons dashicons-arrow-right-alt"></span>
															</div>
															<div class="fz-form-group">
																<input type="text" name="custom_vars_value[]" placeholder="<?php esc_attr_e( 'Value', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo esc_attr( $custom_field_value ); ?>" />
																<span class="fz-action-icon<?php echo empty( $custom_field_value ) ? ' disabled' : ''; ?>"></span>
																<input type="hidden" name="custom_vars_action[]" value="<?php echo isset( $custom_fields_actions[ $custom_field_key ] ) ? esc_attr( $custom_fields_actions[ $custom_field_key ] ) : ''; ?>">
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
											echo wp_kses_post(
												sprintf(
													// translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
													__( 'Check the %1$s Documentation %2$s for more details.', 'feedzy-rss-feeds' ),
													'<a href="' . esc_url( 'https://docs.themeisle.com/article/977-how-do-i-extract-values-from-custom-tags-in-feedzy' ) . '" target="_blank">',
													'</a>'
												)
											);
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
			<div class="feedzy-accordion-item__content">
				<div class="fz-tabs-menu">
					<ul>
						<li>
							<a href="javascript:;" data-id="fz-general-settings"><?php esc_html_e( 'General', 'feedzy-rss-feeds' ); ?></a>
						</li>
						<li>
							<a href="javascript:;" data-id="fz-advanced-settings"><?php esc_html_e( 'Advanced', 'feedzy-rss-feeds' ); ?></a>
						</li>
					</ul>
				</div>
				<div class="fz-tabs-content">
					<div class="fz-tab-content" id="fz-general-settings">
						<div class="fz-form-wrap">
							<div class="form-block form-block-two-column <?php echo ! feedzy_is_pro() && ! feedzy_is_legacyv5() ? esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ) : ''; ?>">

								<?php echo ! feedzy_is_pro() && ! feedzy_is_legacyv5() ? wp_kses_post( apply_filters( 'feedzy_upsell_content', '', 'item-count', 'import' ) ) : ''; ?>
								<div class="fz-left">
									<h4 class="h4"><?php esc_html_e( 'Items', 'feedzy-rss-feeds' ); ?><?php echo ! feedzy_is_pro() && ! feedzy_is_legacyv5() ? ' <span class="pro-label">PRO</span>' : ''; ?></h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<label class="form-label"><?php esc_html_e( 'Number of items to import', 'feedzy-rss-feeds' ); ?></label>
										<input type="number" min="0" max="9999" id="feedzy_item_limit" name="feedzy_meta_data[import_feed_limit<?php echo ! feedzy_is_pro() && ! feedzy_is_legacyv5() ? 'locked' : ''; ?>]" class="form-control" value="<?php echo esc_attr( (int) $import_feed_limit ); ?>" />
										<div class="help-text pt-8">
											<?php echo wp_kses_post( sprintf( __( 'Set the number of feed items to import per run.', 'feedzy-rss-feeds' ), '<b>', '</b>' ) ); ?>
										</div>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="fz-left">
									<h4 class="h4"><?php esc_html_e( 'Schedule', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<label class="form-label"><?php esc_html_e( 'Import schedule', 'feedzy-rss-feeds' ); ?></label>
										<select id="fz-event-schedule" class="form-control fz-select-control" name="feedzy_meta_data[fz_cron_schedule]">
											<?php
											$save_schedule = ! empty( $import_schedule['fz_cron_schedule'] ) ? $import_schedule['fz_cron_schedule'] : '';

											$schedules = wp_get_schedules();
											if ( isset( $schedules['daily'] ) ) {
												$daily = $schedules['daily'];
												unset( $schedules['daily'] );
												$schedules = array_merge( array( 'daily' => $daily ), $schedules );
											}
											$internal_cron_schedules = apply_filters( 'feedzy_internal_cron_schedule_slugs', array() );
											$duplicate_schedule      = array();
											foreach ( $schedules as $slug => $schedule ) :
												if ( empty( $schedule['interval'] ) || in_array( $schedule['interval'], $duplicate_schedule, true ) ) {
													continue;
												}
												$duplicate_schedule[] = $schedule['interval'];
												$display_text         = $schedule['display'];

												if ( ! in_array( $slug, $internal_cron_schedules, true ) ) {
													$display_text .= ' (' . esc_html__( 'externally created)', 'feedzy-rss-feeds' ) . ')';
												}
												?>
												<option data-slug="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $slug ); ?>"<?php selected( $save_schedule, $slug ); ?>
												<?php disabled( ! feedzy_is_pro() && 'daily' !== $slug ); ?>
												>
													<?php echo esc_html( $display_text ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="fz-left">
									<h4 class="h4">
										<?php esc_html_e( 'Fallback Image', 'feedzy-rss-feeds' ); ?>
									</h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<div class="form-label">
											<?php esc_html_e( 'Which fallback featured image should be used for this feed?', 'feedzy-rss-feeds' ); ?>
										</div>
										
										<?php 
										$fallback_option = ! empty( $default_thumbnail_id ) ? 'custom' : 'general';
										?>
										
										<div class="fz-spacing">
											<input
												type="radio"
												id="use-general-fallback"
												name="feedzy_meta_data[fallback_image_option]"
												value="general"
												<?php checked( $fallback_option, 'general' ); ?>
											>
											<label for="use-general-fallback" class="fz-radio-label">
												<strong class="fz-radio-title">
													<?php esc_html_e( 'Use general setting', 'feedzy-rss-feeds' ); ?>
												</strong>
												<br>
												<span class="fz-radio-description">
													<?php esc_html_e( 'Updates automatically when the general fallback image changes.', 'feedzy-rss-feeds' ); ?>
												</span>
											</label>
										</div>

										<div
											id="general-fallback-preview"
											class="fz-spacing"
											style="<?php echo 'general' === $fallback_option ? '' : 'display: none;'; ?>"
										>
											<?php if ( ! empty( $inherited_thumbnail_id ) ) : ?>
												<div class="fz-form-group">
													<?php 
													$image = wp_get_attachment_image( $inherited_thumbnail_id, 'thumbnail' );
													if ( $image ) {
														echo wp_kses_post( $image );
													} else {
														echo '<div class="help-text">' . esc_html__( 'General fallback image not found (may have been deleted)', 'feedzy-rss-feeds' ) . '</div>';
													}
													?>
												</div>
											<?php else : ?>
												<div class="fz-form-group mb-20">
													<div class="help-text">
														<?php 
														echo wp_kses_post(
															__( 'No general fallback image set. ', 'feedzy-rss-feeds' )
														);
														?>
													</div>
												</div>
											<?php endif; ?>
											
											<div class="help-text pt-8">
												<?php 
												echo wp_kses_post( 
													sprintf(
														/* translators: %s is replaced with a link to the Feedzy Settings page */
														__( 'You can update the general fallback image in %s.', 'feedzy-rss-feeds' ),
														'<a href="' . esc_url( admin_url( 'admin.php?page=feedzy-settings' ) ) . '" target="_blank">' . esc_html__( 'Feedzy Settings', 'feedzy-rss-feeds' ) . '</a>'
													) 
												); 
												?>
											</div>
										</div>
										
										<div class="fz-spacing">
											<input
												type="radio"
												id="use-custom-fallback"
												name="feedzy_meta_data[fallback_image_option]"
												value="custom"
												<?php checked( $fallback_option, 'custom' ); ?>
											>
											<label for="use-custom-fallback" class="fz-radio-label">
												<strong class="fz-radio-title">
													<?php esc_html_e( 'Add custom fallback image', 'feedzy-rss-feeds' ); ?>
												</strong>
												<br>
												<span class="fz-radio-description small">
													<?php esc_html_e( 'Use a specific image just for this feed.', 'feedzy-rss-feeds' ); ?>
												</span>
											</label>
										</div>

										<div
											id="custom-fallback-section"
											style="<?php echo 'custom' === $fallback_option ? '' : 'display: none;'; ?>"
										>
											<?php
											$btn_label                    = esc_html__( 'Choose image', 'feedzy-rss-feeds' );
											$saved_fallback_thumbnail_ids = is_string( $default_thumbnail_id ) ? explode( ',', $default_thumbnail_id ) : array();
											$valid_thumbnail_ids          = array_filter( $saved_fallback_thumbnail_ids, 'is_numeric' );
											$valid_thumbnail_ids          = array_filter(
												$valid_thumbnail_ids,
												function ( $id ) {
													return wp_attachment_is_image( intval( $id ) );
												} 
											);
										
											if ( ! empty( $valid_thumbnail_ids ) ) :
												$btn_label = esc_html__( 'Replace image', 'feedzy-rss-feeds' );
												?>
												<div class="fz-form-group feedzy-media-preview ">
													<div class="fz-fallback-images">
														<?php
														foreach ( $valid_thumbnail_ids as $thumbnail_id ) {
															echo wp_get_attachment_image( $thumbnail_id, 'thumbnail' );
														}
														?>
													</div>
												</div>
											<?php endif; ?>

											<div class="fz-cta-group pb-8">
												<a 
													href="javascript:;"
													class="feedzy-open-media btn btn-outline-primary"
												>
													<?php 
													echo esc_html( $btn_label );
													?>
												</a>
												<a
													href="javascript:;"
													class="feedzy-remove-media btn btn-outline-primary <?php echo ! empty( $valid_thumbnail_ids ) ? esc_attr( 'is-show' ) : ''; ?>"
												>
													<?php esc_html_e( 'Remove', 'feedzy-rss-feeds' ); ?>
												</a>
												<input 
													type="hidden" 
													name="feedzy_meta_data[default_thumbnail_id]" 
													id="feed-post-default-thumbnail" 
													value="<?php echo esc_attr( implode( ',', $valid_thumbnail_ids ) ); ?>"
												>
											</div>
										</div>
									</div>
									<div class="help-text pt-8">
										<?php esc_html_e( 'Helpful for setting a fallback image for feed items without an image during the import process.', 'feedzy-rss-feeds' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="fz-tab-content" id="fz-advanced-settings">
						<div class="fz-form-wrap">

							<div class="form-block form-block-two-column no-border">
								<div class="fz-left">
									<h4 class="h4">
										<?php esc_html_e( 'Remove Duplicates', 'feedzy-rss-feeds' ); ?>
									</h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<div class="fz-form-switch">
											<input
												id="remove-duplicates"
												name="feedzy_meta_data[import_remove_duplicates]"
												class="fz-switch-toggle" type="checkbox" value="yes"
												<?php echo esc_attr( $import_remove_duplicates ); ?>
											>
											<label class="feedzy-inline form-label" for="remove-duplicates">
												<?php esc_html_e( 'Remove Duplicate Items', 'feedzy-rss-feeds' ); ?>
											</label>
										</div>
									</div>
									<div class="help-text">
										<?php echo wp_sprintf( esc_html__( 'To understand how duplicates will be removed, check out our', 'feedzy-rss-feeds' ) ); ?>
										<a href="<?php echo esc_url( 'https://docs.themeisle.com/article/638-how-to-eliminate-duplicate-feed-item' ); ?>" target="_blank"><?php esc_html_e( 'Documentation.', 'feedzy-rss-feeds' ); ?></a>
									</div>
								</div>
							</div>
							<div class="form-block form-block-two-column <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
								<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '', 'remove-duplicates', 'import' ) ); ?>
								<div class="fz-left">
									<h4 class="h4"><?php esc_html_e( 'Duplication Key', 'feedzy-rss-feeds' ); ?> <?php echo ! feedzy_is_pro() ? ' <span class="pro-label">PRO</span>' : ''; ?></h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<label class="form-label"><?php esc_html_e( 'Set a custom duplication key to identify unique feed items', 'feedzy-rss-feeds' ); ?></label>
										<input type="text" id="feedzy_mark_duplicate" name="feedzy_meta_data[mark_duplicate_tag]" class="form-control" value="<?php echo esc_attr( $mark_duplicate_tag ); ?>"<?php disabled( true, 'checked' !== $import_remove_duplicates ); ?> />
										<div class="help-text pt-8">
											<?php esc_html_e( 'Define a custom duplication key for identifying unique feed items when importing content. By default, items are considered unique based on their title and URL. Enter one or multiple magic tags.', 'feedzy-rss-feeds' ); ?>
										</div>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="fz-left">
									<h4 class="h4"><?php esc_html_e( 'Strip HTML Tags', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-group">
										<div class="fz-form-switch">
											<input id="remove-html" name="feedzy_meta_data[import_remove_html]"
													class="fz-switch-toggle" type="checkbox" value="yes"
												<?php echo esc_attr( isset( $import_remove_html ) ? $import_remove_html : '' ); ?>>
											<label class="feedzy-inline form-label" for="remove-html"><?php esc_html_e( 'Remove HTML tags from improted content', 'feedzy-rss-feeds' ); ?></label>
										</div>
									</div>
									<div class="help-text">
										<?php echo wp_sprintf( esc_html__( 'Enable this to automatically remove all HTML tags from feed content upon import. This can help ensure clean, plain-text output and is especially useful for feeds that contain unexpected or unsafe markup.', 'feedzy-rss-feeds' ) ); ?>
									</div>
								</div>
							</div>

							<div class="form-block form-block-two-column">
								<div class="fz-left">
									<h4 class="h4"><?php esc_html_e( 'Feed Order', 'feedzy-rss-feeds' ); ?></h4>
								</div>
								<div class="fz-right">
									<div class="fz-form-row">

										<div class="fz-form-col-6">
											<div class="fz-form-group">
												<select id="import-order" class="form-control fz-select-control" name="feedzy_meta_data[import_order]">
													<?php
													$orders = array(
														'' => __( 'Original feed order', 'feedzy-rss-feeds' ),
														'date_asc' => __( 'Latest items first', 'feedzy-rss-feeds' ),
														'date_desc' => __( 'Oldest items first', 'feedzy-rss-feeds' ),
													);
													foreach ( $orders as $key => $value ) :
														?>
														<option data-order="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $key ); ?>"<?php selected( isset( $import_order ) ? $import_order : '', $key ); ?>
														>
															<?php echo esc_html( $value ); ?>
														</option>
													<?php endforeach; ?>
												</select>
												<!-- <div class="help-text pt-8"><?php esc_html_e( 'How often Feedzy will run the import.', 'feedzy-rss-feeds' ); ?></div> -->
											</div>
										</div>
									</div>
								</div>
							</div>

							<?php if ( function_exists( 'icl_get_languages' ) ) : ?>
								<div class="form-block form-block-two-column">
									<div class="fz-left">
										<h4 class="h4"><?php esc_html_e( 'Assign Language', 'feedzy-rss-feeds' ); ?><?php echo ! feedzy_is_pro() ? ' <span class="pro-label">PRO</span>' : ''; ?></h4>
										<?php if ( ! feedzy_is_pro() ) : ?>
											<div class="form-block-pro-text">
											<?php esc_html_e( 'This feature is only for Pro users.', 'feedzy-rss-feeds' ); ?><br>
												<a href="https://docs.themeisle.com/category/712-feedzy" target="_blank"><?php esc_html_e( 'Learn More', 'feedzy-rss-feeds' ); ?></a>
											</div>
										<?php endif; ?>
									</div>
									<div class="fz-right">
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
						</div>
					</div>
				</div>
				<div class="fz-content">
					<div class="form-block form-block-two-column">
						<div class="cta-text pt-8">
							<a href="javascript:void(0)" id="fz-feedback-btn" role="button"><?php esc_html_e( 'Help us improve Feedzy', 'feedzy-rss-feeds' ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<input type="hidden" id="custom_post_status" name="custom_post_status" value="draft" />
	<div class="fz-form-action">
		<div class="fz-left">
			<?php
			if ( feedzy_is_pro() ) {
				$clone_url = wp_nonce_url(
					add_query_arg(
						array(
							'action'        => 'feedzy_clone_import_job',
							'feedzy_job_id' => $post->ID,
						),
						'admin.php'
					),
					FEEDZY_BASENAME,
					'clone_import'
				);
				?>
				<a href="<?php echo esc_url( $clone_url ); ?>"
					class="btn btn-ghost"><?php esc_html_e( 'Clone Import', 'feedzy-rss-feeds' ); ?></a>
			<?php } ?>
		</div>
		<div class="fz-right">
			<button type="button" id="preflight" name="check" class="btn btn-ghost" value="Check"
			title="<?php esc_attr_e( 'Click to see what items will be imported from the source, according to the filters specified', 'feedzy-rss-feeds' ); ?>">
				<?php esc_html_e( 'Preview Import', 'feedzy-rss-feeds' ); ?>
			</button>
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
