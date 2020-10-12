<?php
/**
 * View for Import Post Type Meta Box Import Options
 *
 * @since   1.2.0
 * @package feedzy-rss-feeds-pro
 */
?>
<div class="f1" id="feedzy-import-form">

	<h3><span class="dashicons dashicons-rss"></span> <?php echo __( 'Sources', 'feedzy-rss-feeds' ); ?></h3>

		<div class="form-group">
			<label class="feedzy-sr-only"><?php echo __( 'RSS Feed sources (comma separated URLs or Feed Categories slug)', 'feedzy-rss-feeds' ); ?></label>
		</div>

		<?php echo $invalid_source_msg; ?>

		<div class="form-group input-group">
			<div class="feedzy-button-inside">
				<input type="text" id="feedzy-import-source" title="<?php _e( 'Make sure you validate the feed by using the validate button on the right', 'feedzy-rss-feeds' ); ?>" name="feedzy_meta_data[source]" placeholder="<?php echo __( 'Source', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo $source; ?>"/>
				<a class="feedzy-inside" target="_blank" data-href-base="https://validator.w3.org/feed/check.cgi?url=" href="#" title="<?php _e( 'Validate Feed', 'feedzy-rss-feeds' ); ?>"><i title="<?php _e( 'Validate Feed', 'feedzy-rss-feeds' ); ?>" class="dashicons dashicons-external"></i></a>
			</div>
			<small><i class="dashicons dashicons-lightbulb"></i><?php _e( 'Make sure to use the validate button. Invalid feeds may not import anything.', 'feedzy-rss-feeds' ); ?></small>
			<div class="input-group-btn">
			<?php
			if ( isset( $feed_categories ) && ! empty( $feed_categories ) ) {
				?>
				<button type="button" class="btn btn-add-fields dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<?php echo __( 'Use Feed Category', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span>
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

	<h3><span class="dashicons dashicons-filter"></span> <?php echo __( 'Filters', 'feedzy-rss-feeds' ); ?></h3>

		<div class="feedzy-rows">
			<div class="feedzy-row <?php echo apply_filters( 'feedzy_upsell_class', '' ); ?>">
				<?php echo apply_filters( 'feedzy_upsell_content', '' ); ?>
				<div class="label_description">
					<label class="feedzy-sr-only"><?php echo __( 'Display item only if the title contains specific keyword(s)', 'feedzy-rss-feeds' ); ?></label>
					<div>
						<small><?php echo sprintf( __( 'You can provide comma-separated words. Remember, these words are %1$scase sensitive%2$s .e.g. News, news, STOCK', 'feedzy-rss-feeds' ), '<b>', '</b>' ); ?></small>
					</div>
				</div>
				<div class="feedzy-separator"></div>
				<div class="form-group input-group form_item">
					<input type="text" name="feedzy_meta_data[inc_key]" placeholder="<?php echo __( '(eg. news, sports etc.)', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo $inc_key; ?>"/>
				</div>
			</div>

			<div class="feedzy-row <?php echo apply_filters( 'feedzy_upsell_class', '' ); ?>">
				<?php echo apply_filters( 'feedzy_upsell_content', '' ); ?>
				<div class="label_description">
					<label class="feedzy-sr-only"><?php echo __( 'Exclude item if the title contains specific keyword(s)', 'feedzy-rss-feeds' ); ?></label>
					<div>
						<small><?php echo sprintf( __( 'You can provide comma-separated words. Remember, these words are %1$scase sensitive%2$s .e.g. News, news, STOCK', 'feedzy-rss-feeds' ), '<b>', '</b>' ); ?></small>
					</div>
				</div>
				<div class="feedzy-separator"></div>
				<div class="form-group input-group form_item">
					<input type="text" name="feedzy_meta_data[exc_key]" placeholder="<?php echo __( '(eg. news, sports etc.)', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo $exc_key; ?>"/>
				</div>
			</div>

			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only"><?php _e( 'How many feed items to import from the source?', 'feedzy-rss-feeds' ); ?></label>
					<div>
						<small><?php echo sprintf( __( 'If you choose a high number, please check that you configuration can support it or your imports may fail.', 'feedzy-rss-feeds' ), '<b>', '</b>' ); ?></small>
					</div>
				</div>
				<div class="feedzy-separator"></div>
				<div class="form-group input-group form_item">
					<input type="number" min="0" max="9999" id="feedzy_item_limit" name="feedzy_meta_data[import_feed_limit]" class="form-control" value="<?php echo $import_feed_limit; ?>"/>
				</div>
			</div>

			<div class="feedzy-row <?php echo apply_filters( 'feedzy_upsell_class', '' ); ?>">
				<?php echo apply_filters( 'feedzy_upsell_content', '' ); ?>
				<div class="label_description">
					<label class="feedzy-sr-only"><?php _e( 'Automatically delete the posts created for this import after how many days?', 'feedzy-rss-feeds' ); ?></label>
					<div>
						<small><?php _e( 'Helpful if you want to remove stale or old items automatically. If you choose 0, the imported items will never be deleted.', 'feedzy-rss-feeds' ); ?></small>
					</div>
				</div>
				<div class="feedzy-separator"></div>
				<div class="form-group input-group form_item">
					<input type="number" min="0" max="9999" id="feedzy_delete_days" name="feedzy_meta_data[import_feed_delete_days]" class="form-control" value="<?php echo $import_feed_delete_days; ?>"/>
				</div>
			</div>

		</div>


	<h3><span class="dashicons dashicons-feedback"></span> <?php echo __( 'Assign Elements', 'feedzy-rss-feeds' ); ?></h3>

		<p><?php _e( 'Using magic tags, specify what part(s) of the source should form part of the imported post.', 'feedzy-rss-feeds' ); ?>
		<?php if ( false === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) { ?>
			<?php echo sprintf( __( 'The magic tags that are greyed out and disabled are unavailable for your current license. You can consider %1$supgrading%2$s.', 'feedzy-rss-feeds' ), '<a href="' . FEEDZY_UPSELL_LINK . '" target="_blank" title="' . __( 'Upgrade', 'feedzy-rss-feeds' ) . '">', '</a>' ); ?>
		<?php } ?>
		</p>

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
					<small><?php _e( 'Assign to a taxonomy (eg. "Post Category", "Post Tag" etc.). Leave blank, if unsure.', 'feedzy-rss-feeds' ); ?></small>
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

					_e( 'The title for the generated post. This field is mandatory - without this, a post will not be created.', 'feedzy-rss-feeds' );
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
							_e( 'The date for the generated post. Leave blank, if unsure.', 'feedzy-rss-feeds' );
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

					_e( 'The content for the generated post. This field is mandatory - without this, a post will not be created.', 'feedzy-rss-feeds' );
					?>
					</small>

					<p class="feedzy-highlight"><i class="dashicons dashicons-megaphone"></i>
					<?php
					if ( apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ) {
						echo sprintf( __( 'You can add custom magic tags to extract custom elements from your feed as explained %1$shere%2$s. This will work only for single-feeds (i.e. not if you have specified a feed category that contains multiple feeds or using comma-separated feeds in the source).', 'feedzy-rss-feeds' ), '<a href="https://docs.themeisle.com/article/977-how-do-i-extract-values-from-custom-tags-in-feedzy" target="_blank">', '</a>' );
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
						_e( 'The URL for the featured image. You can use the magic tags, use your own URL or leave it empty.', 'feedzy-rss-feeds' );
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
			<div class="feedzy-row <?php echo apply_filters( 'feedzy_upsell_class', '' ); ?>">
				<?php echo apply_filters( 'feedzy_upsell_content', '' ); ?>
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-content"><?php echo __( 'Post Author', 'feedzy-rss-feeds' ); ?></label><br/>
					<small>
						<?php
						 _e( 'Show the original author of the source item.', 'feedzy-rss-feeds' );
						?>
					</small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<div>
						<input id="feedzy-toggle_author_admin" name="feedzy_meta_data[import_link_author_admin]" class="feedzy-toggle feedzy-toggle-round" type="checkbox" value="yes" <?php echo $import_link_author[0]; ?>>
						<label for="feedzy-toggle_author_admin" class="feedzy-inline"></label>
						<label class="feedzy-inline" style="margin-left: 10px;" for="import_link_author_admin"><?php echo __( 'In the backend, on the post listing screen', 'feedzy-rss-feeds' ); ?></label>
					</div>
					<div>
						<input id="feedzy-toggle_author_public" name="feedzy_meta_data[import_link_author_public]" class="feedzy-toggle feedzy-toggle-round" type="checkbox" value="yes" <?php echo $import_link_author[1]; ?>>
						<label for="feedzy-toggle_author_public" class="feedzy-inline"></label>
						<label class="feedzy-inline" style="margin-left: 10px;" for="import_link_author_public"><?php echo __( 'In the frontend, link to the original post', 'feedzy-rss-feeds' ); ?></label>
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
			<button type="button" id="preflight" name="check" class="btn btn-previous" value="Check" title="<?php _e( 'Click to see what items will be imported from the source, according to the filters specified', 'feedzy-rss-feeds' ); ?>"><?php _e( 'Dry Run', 'feedzy-rss-feeds' ); ?></button>
			<?php
			if ( $post_status === 'publish' ) {
				?>
				<button type="submit" name="publish" class="btn btn-submit" value="Publish"><?php _e( 'Save', 'feedzy-rss-feeds' ); ?></button>
				<?php
			} else {
				?>
				<button type="submit" name="save" class="btn btn-submit" value="Save Draft" style="float: none;"><?php _e( 'Save', 'feedzy-rss-feeds' ); ?></button>
				<button type="submit" name="publish" class="btn btn-submit btn-activate" value="Publish" ><?php _e( 'Save & Activate', 'feedzy-rss-feeds' ); ?></button>
				<?php
			}
			?>
		</div>
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

