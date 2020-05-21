<?php
/**
 * View for Import Post Type Meta Box Import Options
 *
 * @since   1.2.0
 * @package feedzy-rss-feeds-pro
 */
?>
<div class="f1">

	<h3><?php echo __( 'Import Setup Wizard', 'feedzy-rss-feeds' ); ?></h3>
	<p><?php echo __( 'Follow the steps to setup an import rule.', 'feedzy-rss-feeds' ); ?></p>
	<div class="f1-steps">
		<div class="f1-progress">
			<div class="f1-progress-line" data-now-value="16.66" data-number-of-steps="3" style="width: 16.66%;"></div>
		</div>
		<div class="f1-step active">
			<div class="f1-step-icon"><span class="dashicons dashicons-rss"></span></div>
			<p><?php echo __( 'Sources', 'feedzy-rss-feeds' ); ?></p>
		</div>
		<div class="f1-step">
			<div class="f1-step-icon"><span class="dashicons dashicons-filter"></span></div>
			<p><?php echo __( 'Filters', 'feedzy-rss-feeds' ); ?></p>
		</div>
		<div class="f1-step">
			<div class="f1-step-icon"><span class="dashicons dashicons-randomize"></span></div>
			<p><?php echo __( 'Assign', 'feedzy-rss-feeds' ); ?></p>
		</div>
	</div>

	<fieldset class="feedzy-screen1">
		<h4><?php echo __( 'Feed sources:', 'feedzy-rss-feeds' ); ?></h4>
		<div class="form-group">
			<label class="feedzy-sr-only"><?php echo __( 'Feedzy RSS Feed sources (comma separated URLs or Feed Categories slug)', 'feedzy-rss-feeds' ); ?></label>
		</div>
		<div class="form-group input-group">
			<input type="text" name="feedzy_meta_data[source]" placeholder="<?php echo __( 'Source', 'feedzy-rss-feeds' ); ?>" class="form-control  " value="<?php echo $source; ?>"/>
			<div class="input-group-btn">
			<?php
			if ( isset( $feed_categories ) && ! empty( $feed_categories ) ) {
				?>
				<button type="button" class="btn btn-add-fields dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<?php echo __( 'Add Feed Category', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span>
				</button>
				<div class="dropdown-menu dropdown-menu-right">
					<?php
					foreach ( $feed_categories as $category ) {
						?>
						<a class="dropdown-item source" href="#" data-field-name="source" data-field-tag="<?php echo $category->post_name; ?>"><?php echo $category->post_title; ?></a>
						<?php
					}
					?>
				</div>
				<?php
			} else {
				?>
				<button type="button" class="  disabled btn-add-fields btn "  >
					<?php echo __( 'No feed categories available', 'feedzy-rss-feeds' ); ?>
				</button>
				<?php

			}
			?>
			</div>
		</div>
		<div class="f1-buttons">
			<button type="button" class="btn btn-next"><?php echo __( 'Next', 'feedzy-rss-feeds' ); ?></button>
		</div>
	</fieldset>

	<fieldset class="feedzy-screen2">
		<h4><?php echo __( 'Feed filters:', 'feedzy-rss-feeds' ); ?></h4>
		<div class="form-group <?php echo apply_filters( 'feedzy_upsell_class', '' ); ?>">
			<?php echo apply_filters( 'feedzy_upsell_content', '' ); ?>
			<label class="feedzy-sr-only"><?php echo __( 'Only display item if title contains specific keyword(s) (comma-separated list/case sensitive).', 'feedzy-rss-feeds' ); ?></label>
			<input type="text" name="feedzy_meta_data[inc_key]" placeholder="<?php echo __( '(eg. news, sports etc.)', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo $inc_key; ?>"/>
		</div>
		<div class="form-group <?php echo apply_filters( 'feedzy_upsell_class', '' ); ?>">
			<?php echo apply_filters( 'feedzy_upsell_content', '' ); ?>
			<label class="feedzy-sr-only"><?php echo __( 'Exclude items if title contains specific keyword(s) (comma-separated list/case sensitive).', 'feedzy-rss-feeds' ); ?></label>
			<input type="text" name="feedzy_meta_data[exc_key]" placeholder="<?php echo __( '(eg. news, sports etc.)', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo $exc_key; ?>"/>
		</div>
		<div class="form-group">
			<label class="feedzy-sr-only"><?php _e( 'How many feed items to process from the feed?', 'feedzy-rss-feeds' ); ?></label>
			<select id="feedzy_item_limit" class="form-control feedzy-chosen" name="feedzy_meta_data[import_feed_limit]" >
				<?php
				$limits = apply_filters( 'feedzy_items_limit', range( 10, 100, 10 ), $post );
				$limits[] = 9999;
				if ( '' === $import_feed_limit ) {
					$import_feed_limit = 20;
				}
				foreach ( $limits as $v ) {
					$selected = '';
					// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					if ( $v == $import_feed_limit ) {
						$selected = 'selected';
					}
					$display = $v;
					if ( $v === 9999 ) {
						$display = __( 'All (check that your server configuration can support this)', 'feedzy-rss-feeds' );
						if ( ! feedzy_is_pro() ) {
							$display = __( 'More options available in PRO.', 'feedzy-rss-feeds' );
							$selected = 'disabled';
							$v = '';
						}
					}
					?>
					<option value="<?php echo $v; ?>" <?php echo $selected; ?>><?php echo $display; ?></option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="form-group <?php echo apply_filters( 'feedzy_upsell_class', '' ); ?>">
			<?php echo apply_filters( 'feedzy_upsell_content', '' ); ?>
			<label class="feedzy-sr-only"><?php _e( 'Delete the posts created for this import after how many days?', 'feedzy-rss-feeds' ); ?></label>
			<select id="feedzy_delete_limit" class="form-control feedzy-chosen" name="feedzy_meta_data[import_feed_delete_days]" >
				<?php
				// 0 is never delete.
				$days = apply_filters( 'feedzy_items_delete_days', range( 0, 100, 1 ), $post );
				if ( '' === $import_feed_delete_days ) {
					$import_feed_delete_days = 0;
				}
				foreach ( $days as $v ) {
					$selected = '';
					if ( $v === $import_feed_delete_days ) {
						$selected = 'selected';
					}
					$display = $v;
					if ( $v === 0 ) {
						$display = __( 'Never', 'feedzy-rss-feeds' );
					}
					?>
					<option value="<?php echo $v; ?>" <?php echo $selected; ?>><?php echo $display; ?></option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="f1-buttons">
			<button type="button" class="btn btn-previous"><?php echo __( 'Previous', 'feedzy-rss-feeds' ); ?></button>
			<button type="button" class="btn btn-next"><?php echo __( 'Next', 'feedzy-rss-feeds' ); ?></button>
		</div>
	</fieldset>

	<fieldset class="feedzy-screen3">
		<h4><?php echo __( 'Feed assign:', 'feedzy-rss-feeds' ); ?></h4>
		<p><?php echo __( 'Map post elements to custom post from feed imports.', 'feedzy-rss-feeds' ); ?></p>

		<div class="feedzy-rows">
			<div class="feedzy-row">
				<div class="label_description">
					<h4><?php echo __( 'Post Element', 'feedzy-rss-feeds' ); ?></h4>
				</div>
				<div class="feedzy-separator"></div>
				<div class="label_description">
					<h4><?php echo __( 'Element Value', 'feedzy-rss-feeds' ); ?></h4>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-type"><?php echo __( 'Post Type', 'feedzy-rss-feeds' ); ?></label><br/>
					<small><?php _e( 'The post type you want to use for the generated post.', 'feedzy-rss-feeds' ); ?></small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<select id="feedzy_post_type" class="form-control feedzy-chosen" name="feedzy_meta_data[import_post_type]" data-tax="<?php echo $import_post_term; ?>" >
						<?php
						foreach ( $post_types as $post_type ) {
							$selected = '';
							if ( $post_type === $import_post_type ) {
								$selected = 'selected';
							}
							?>
							<option value="<?php echo $post_type; ?>" <?php echo $selected; ?>><?php echo $post_type; ?></option>
							<?php
						}
						?>
					</select>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-type"><?php echo __( 'Post Taxonomy', 'feedzy-rss-feeds' ); ?></label><br/>
					<small><?php _e( 'Assign to a taxonomy term (eg. "Category", "Tags" etc.)', 'feedzy-rss-feeds' ); ?></small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<select id="feedzy_post_terms" multiple class="form-control feedzy-chosen" name="feedzy_meta_data[import_post_term][]" >
					</select>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-status"><?php echo __( 'Post Status', 'feedzy-rss-feeds' ); ?></label><br/>
					<small>
						<?php
						echo __( 'The post status you want your posts to have. You can choose Publish if you want to publish your posts right away, or you can use Draft if you want to draft your posts and publish it after reviewing them manually.', 'feedzy-rss-feeds' );
						?>
					</small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<select id="feedzy_post_status" class="form-control feedzy-chosen" name="feedzy_meta_data[import_post_status]" >
						<?php
						foreach ( $published_status as $status ) {
							$selected = '';
							if ( $status === $import_post_status ) {
								$selected = 'selected';
							}
							?>
							<option value="<?php echo $status; ?>" <?php echo $selected; ?>><?php echo ucfirst( $status ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-title"><?php echo __( 'Post Title', 'feedzy-rss-feeds' ); ?></label><br/>
					<small>
					<?php
					$magic_tags = array(
						'item_title',
						'item_author',
						'item_date',
					);
					$magic_tags = apply_filters( 'feedzy_get_service_magic_tags', $magic_tags, 'title' );

					echo __( 'The title for the generated post. You can use ', 'feedzy-rss-feeds' ) .
					'<b>[#' . implode( ']</b>, <b>[#', $magic_tags ) . ']</b>' .
						__( ' tags to append the feed item title to the generated post title or mix and match your own.', 'feedzy-rss-feeds' );
					?>
					</small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<input type="text" name="feedzy_meta_data[import_post_title]" placeholder="<?php echo __( 'Post Title', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo $import_title; ?>"/>
					<div class="input-group-btn">
						<button type="button" class="btn btn-add-fields dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<?php echo __( 'Insert Tag', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span>
						</button>
						<div class="dropdown-menu dropdown-menu-right">
							<?php echo apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_title', array() ), 'import_post_title' ); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-title"><?php echo __( 'Post Date', 'feedzy-rss-feeds' ); ?></label><br/>
					<small>
						<?php
						echo __( 'The date for the generated post. You can use ', 'feedzy-rss-feeds' ) .
							'<b>[#item_date]</b>, <b>[#post_date]</b>' .
							__( ' tags or leave blank.', 'feedzy-rss-feeds' );
						?>
					</small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<input type="text" name="feedzy_meta_data[import_post_date]" placeholder="<?php echo __( 'Post Date', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo $import_date; ?>"/>
					<div class="input-group-btn">
						<button type="button" class="btn btn-add-fields dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<?php echo __( 'Insert Tag', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span>
						</button>
						<div class="dropdown-menu dropdown-menu-right">
							<?php echo apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_date', array() ), 'import_post_date' ); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-content"><?php echo __( 'Item Content', 'feedzy-rss-feeds' ); ?></label><br/>
					<small>
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

					echo __( 'The content for the generated post. You can use ', 'feedzy-rss-feeds' ) .
					'<b>[#' . implode( ']</b>, <b>[#', $magic_tags ) . ']</b>' .
						__( ' tags to append the feed item content for the generated post content.', 'feedzy-rss-feeds' );
					?>
					</small>

					<p class="feedzy-highlight"><i class="dashicons dashicons-megaphone"></i>
					<?php
					if ( apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ) {
						echo sprintf( __( 'You can add custom magic tags to extract custom elements from your feed as explained %1$shere%2$s. This will work only for single-feeds (not if you have specified a feed category that contains multiple feeds).', 'feedzy-rss-feeds' ), '<a href="https://docs.themeisle.com/article/977-how-do-i-extract-values-from-custom-tags-in-feedzy" target="_blank">', '</a>' );
					} else {
						echo sprintf( __( 'Want to extract custom elements from your feed as explained %1$shere%2$s? Upgrade your %3$slicense%4$s today!', 'feedzy-rss-feeds' ), '<a href="https://docs.themeisle.com/article/977-how-do-i-extract-values-from-custom-tags-in-feedzy" target="_blank">', '</a>', '<a href="' . FEEDZY_UPSELL_LINK . '" target="_blank">', '</a>' );
					}
					?>
					</p>

				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<textarea name="feedzy_meta_data[import_post_content]" placeholder="<?php echo __( 'Post Content', 'feedzy-rss-feeds' ); ?>" class="form-control"><?php echo $import_content; ?></textarea>
					<div class="input-group-btn">
						<button type="button" class="btn btn-add-fields dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<?php echo __( 'Insert Tag', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span>
						</button>
						<div class="dropdown-menu dropdown-menu-right">
							<?php echo apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_content', array() ), 'import_post_content' ); ?>
						</div>
					</div>
				</div>
			</div>

			<?php echo do_action( 'feedzy_metabox_show_rows', '', $post->ID, 'language-dropdown' ); ?>

			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-content"><?php echo __( 'Featured Image', 'feedzy-rss-feeds' ); ?></label><br/>
					<small>
						<?php
						echo __( 'The URL for the featured image. You can use ', 'feedzy-rss-feeds' ) .
							'<b>[#item_image]</b>' .
							__( ' tag, use your own URL or leave it empty. (*optional)', 'feedzy-rss-feeds' );
						?>
					</small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<input type="text" name="feedzy_meta_data[import_post_featured_img]" placeholder="<?php echo __( 'Featured Image', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo $import_featured_img; ?>"/>
					<div class="input-group-btn">
						<button type="button" class="btn btn-add-fields dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<?php echo __( 'Insert Tag', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span>
						</button>
						<div class="dropdown-menu dropdown-menu-right">
							<?php echo apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_image', array() ), 'import_post_featured_img' ); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-content"><?php echo __( 'Post Author', 'feedzy-rss-feeds' ); ?></label><br/>
					<small>
						<?php
						 _e( 'Show the original author', 'feedzy-rss-feeds' );
						?>
					</small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item <?php echo apply_filters( 'feedzy_upsell_class', '' ); ?>">
					<?php echo apply_filters( 'feedzy_upsell_content', '' ); ?>
					<div>
						<input type="checkbox" name="feedzy_meta_data[import_link_author_admin]" id="import_link_author_admin" value="yes" <?php echo $import_link_author[0]; ?>/>
						<label class="feedzy-inline" for="import_link_author_admin"><?php echo __( 'In the backend', 'feedzy-rss-feeds' ); ?></label>
					</div>
					<div>
						<input type="checkbox" name="feedzy_meta_data[import_link_author_public]" id="import_link_author_public" value="yes" <?php echo $import_link_author[1]; ?>/>
						<label class="feedzy-inline" for="import_link_author_public"><?php echo __( 'Link to the original post in the frontend', 'feedzy-rss-feeds' ); ?></label>
					</div>
				</div>
			</div>


			<div class="custom_fields <?php echo apply_filters( 'feedzy_upsell_class', '' ); ?>">
				<?php echo apply_filters( 'feedzy_upsell_content', '' ); ?>
				<!-- Custom Fields Added By JS -->
				<?php
				if ( isset( $import_custom_fields ) && ! empty( $import_custom_fields ) ) {
					foreach ( $import_custom_fields as $custom_field_key => $custom_field_value ) {
						?>
						<div class="row">
							<div class="feedzy-row fields">
								<div class="form-group form_item">
									<input type="text" name="custom_vars_key[]" placeholder="<?php echo __( 'Key Name', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo $custom_field_key; ?>"/>
								</div>
								<div class="feedzy-separator dashicons dashicons-leftright"></div>
								<div class="form-group input-group form_item">
									<input type="text" name="custom_vars_value[]" placeholder="<?php echo __( 'Value', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo $custom_field_value; ?>"/>
									<div class="input-group-btn">
										<button type="button" class="btn btn-remove-fields">
											<span class="dashicons dashicons-trash"></span>
										</button>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
				}
				?>
			</div>

			<button id="new_custom_fields" type="button" class="btn btn-add-fields" style="width: 100%; margin-bottom: 16px; margin-top: 16px;" ><?php echo __( 'Add custom fields', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-plus-alt"></span></button>
		</div>


		<div class="f1-buttons">
			<input type="hidden" id="custom_post_status" name="custom_post_status" value="draft" />
			<button type="button" class="btn btn-previous"><?php echo __( 'Previous', 'feedzy-rss-feeds' ); ?></button>
			<?php
			if ( $post_status === 'publish' ) {
				?>
				<button type="submit" name="publish" class="btn btn-submit" value="Publish"><?php echo __( 'Save', 'feedzy-rss-feeds' ); ?></button>
				<?php
			} else {
				?>
				<button type="submit" name="save" class="btn btn-submit" value="Save Draft" style="float: none;"><?php echo __( 'Save', 'feedzy-rss-feeds' ); ?></button>
				<button type="submit" name="publish" class="btn btn-submit btn-activate" value="Publish" ><?php echo __( 'Save & Activate', 'feedzy-rss-feeds' ); ?></button>
				<?php
			}
			?>
		</div>
	</fieldset>
</div>

<script id="empty_select_tpl" type="text/template">
	<option value="none"><?php echo __( 'None', 'feedzy-rss-feeds' ); ?></option>
</script>

<script id="loading_select_tpl" type="text/template">
	<option value=""><?php echo __( 'Loading...', 'feedzy-rss-feeds' ); ?></option>
</script>

<script id="new_field_tpl" type="text/template">
	<?php echo apply_filters( 'feedzy_custom_field_template', '' ); ?>
</script>

