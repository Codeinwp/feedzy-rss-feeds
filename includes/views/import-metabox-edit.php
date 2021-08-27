<?php
/**
 * View for Import Post Type Meta Box Import Options
 *
 * @since   1.2.0
 * @package feedzy-rss-feeds-pro
 */
?>
<div class="f1" id="feedzy-import-form">

	<h3><span class="dashicons dashicons-rss"></span> <?php esc_html_e( 'Sources', 'feedzy-rss-feeds' ); ?></h3>

		<div class="form-group">
			<label class="feedzy-sr-only"><?php esc_html_e( 'RSS Feed sources (comma separated URLs or Feed Categories slug)', 'feedzy-rss-feeds' ); ?></label>
		</div>

		<?php echo wp_kses_post( $invalid_source_msg ); ?>

		<input type="hidden" id="feedzy_post_nonce" name="feedzy_post_nonce" value="<?php echo esc_attr( wp_create_nonce( 'feedzy_post_nonce' ) ); ?>"/>

		<div class="form-group input-group">
			<div class="feedzy-button-inside">
				<input type="text" id="feedzy-import-source" title="<?php esc_attr_e( 'Make sure you validate the feed by using the validate button on the right', 'feedzy-rss-feeds' ); ?>" name="feedzy_meta_data[source]" placeholder="<?php esc_attr_e( 'Source', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo esc_attr( $source ); ?>"/>
				<a class="feedzy-inside" target="_blank" data-href-base="https://validator.w3.org/feed/check.cgi?url=" href="#" title="<?php esc_attr_e( 'Validate Feed', 'feedzy-rss-feeds' ); ?>"><i title="<?php esc_attr_e( 'Validate Feed', 'feedzy-rss-feeds' ); ?>" class="dashicons dashicons-external"></i></a>
			</div>
			<small><i class="dashicons dashicons-lightbulb"></i><?php esc_html_e( 'Make sure to use the validate button. Invalid feeds may not import anything.', 'feedzy-rss-feeds' ); ?></small>
			<div class="input-group-btn">
			<?php
			if ( isset( $feed_categories ) && ! empty( $feed_categories ) ) {
				?>
				<button type="button" class="btn btn-add-fields dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<?php esc_html_e( 'Use Feed Category', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span>
				</button>
				<div class="dropdown-menu dropdown-menu-right">
					<?php
					foreach ( $feed_categories as $category ) {
						?>
						<a class="dropdown-item source" href="#" data-field-name="source" data-field-tag="<?php echo esc_attr( $category->post_name ); ?>"><?php echo wp_kses_post( $category->post_title ); ?></a>
						<?php
					}
					?>
				</div>
				<?php
			} else {
				?>
				<button type="button" class="  disabled btn-add-fields btn "  >
					<?php esc_html_e( 'No feed categories available', 'feedzy-rss-feeds' ); ?>
				</button>
				<?php

			}
			?>
			</div>
		</div>

	<h3><span class="dashicons dashicons-filter"></span> <?php esc_html_e( 'Filters', 'feedzy-rss-feeds' ); ?></h3>

		<div class="feedzy-rows">
			<div class="feedzy-row <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
				<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '' ) ); ?>
				<div class="label_description">
					<label class="feedzy-sr-only"><?php esc_html_e( 'Display item only if the title or content contains specific keyword(s)', 'feedzy-rss-feeds' ); ?></label>
					<div>
						<small><?php echo wp_kses_post( sprintf( __( 'You can provide comma-separated words. Remember, these words are %1$scase sensitive%2$s .e.g. News, news, STOCK', 'feedzy-rss-feeds' ), '<b>', '</b>' ) ); ?></small>
					</div>
				</div>
				<div class="feedzy-separator"></div>
				<div class="form-group input-group form_item">
					<input type="text" name="feedzy_meta_data[inc_key]" placeholder="<?php esc_html_e( '(eg. news, Sports, STOCK etc.)', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo esc_attr( $inc_key ); ?>"/>
				</div>
			</div>

			<div class="feedzy-row <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
				<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '' ) ); ?>
				<div class="label_description">
					<label class="feedzy-sr-only"><?php esc_html_e( 'Exclude item if the title or content contains specific keyword(s)', 'feedzy-rss-feeds' ); ?></label>
					<div>
						<small><?php echo wp_kses_post( sprintf( __( 'You can provide comma-separated words. Remember, these words are %1$scase sensitive%2$s .e.g. News, news, STOCK', 'feedzy-rss-feeds' ), '<b>', '</b>' ) ); ?></small>
					</div>
				</div>
				<div class="feedzy-separator"></div>
				<div class="form-group input-group form_item">
					<input type="text" name="feedzy_meta_data[exc_key]" placeholder="<?php esc_html_e( '(eg. news, Sports, STOCK etc.)', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo esc_attr( $exc_key ); ?>"/>
				</div>
			</div>

			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only"><?php esc_html_e( 'How many feed items to import from the source?', 'feedzy-rss-feeds' ); ?></label>
					<div>
						<small><?php echo wp_kses_post( sprintf( __( 'If you choose a high number, please check that your configuration can support it or your imports may fail.', 'feedzy-rss-feeds' ), '<b>', '</b>' ) ); ?></small>
					</div>
				</div>
				<div class="feedzy-separator"></div>
				<div class="form-group input-group form_item">
					<input type="number" min="0" max="9999" id="feedzy_item_limit" name="feedzy_meta_data[import_feed_limit]" class="form-control" value="<?php echo esc_attr( (int) $import_feed_limit ); ?>"/>
				</div>
			</div>

			<div class="feedzy-row <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
				<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '' ) ); ?>
				<div class="label_description">
					<label class="feedzy-sr-only"><?php esc_html_e( 'Automatically delete the posts created for this import after how many days?', 'feedzy-rss-feeds' ); ?></label>
					<div>
						<small><?php esc_html_e( 'Helpful if you want to remove stale or old items automatically. If you choose 0, the imported items will not be automatically deleted.', 'feedzy-rss-feeds' ); ?></small>
					</div>
				</div>
				<div class="feedzy-separator"></div>
				<div class="form-group input-group form_item">
					<input type="number" min="0" max="9999" id="feedzy_delete_days" name="feedzy_meta_data[import_feed_delete_days]" class="form-control" value="<?php echo esc_attr( (int) $import_feed_delete_days ); ?>"/>
				</div>
			</div>

			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only"><?php esc_html_e( 'Remove Duplicates?', 'feedzy-rss-feeds' ); ?></label>
					<div>
						<?php /* translators: %s: Documentation link */ ?>
						<small><?php echo wp_sprintf( esc_html__( 'To understand how duplicates will be removed, check out our', 'feedzy-rss-feeds' ) ); ?> <a href="<?php echo esc_url( 'https://docs.themeisle.com/article/638-how-to-eliminate-duplicate-feed-item' ); ?>" target="_blank"><?php esc_html_e( '[documentation]', 'feedzy-rss-feeds' ); ?></a></small>
					</div>
				</div>
				<div class="feedzy-separator"></div>
				<div class="form-group input-group form_item">
					<div>
						<input id="remove-duplicates" name="feedzy_meta_data[import_remove_duplicates]" class="feedzy-toggle feedzy-toggle-round" type="checkbox" value="yes" <?php echo esc_attr( $import_remove_duplicates ); ?>>
						<label for="remove-duplicates" class="feedzy-inline"></label>
						<label class="feedzy-inline" style="margin-left: 10px;" for="import_remove_duplicates"></label>
					</div>
				</div>
			</div>
			<?php if ( function_exists( 'icl_get_languages' ) ) : ?>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only"><?php esc_html_e( 'Assign language', 'feedzy-rss-feeds' ); ?></label>
					<div>
						<small><?php esc_html_e( 'Select the language the content will have when it will be imported', 'feedzy-rss-feeds' ); ?></small>
					</div>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<select id="feedzy_site_language" class="form-control feedzy-chosen" name="feedzy_meta_data[language]">
						<?php
						$current_language = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '';
						$import_selected_language = ! empty( $import_selected_language ) ? $import_selected_language : $current_language;
						$languages = icl_get_languages();
						foreach ( $languages as $language ) {
							$selected = '';
							$code = isset( $language['language_code'] ) ? $language['language_code'] : $language['code'];
							$name = isset( $language['translated_name'] ) && ! empty( $language['translated_name'] ) ? $language['translated_name'] : $language['native_name'];
							if ( $code === $import_selected_language ) {
								$selected = 'selected';
							}
							?>
							<option value="<?php echo esc_attr( $code ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $name ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
			</div>
		<?php endif; ?>
		</div>


	<h3><span class="dashicons dashicons-feedback"></span> <?php esc_html_e( 'Assign Elements', 'feedzy-rss-feeds' ); ?></h3>

		<p><?php esc_html_e( 'Using magic tags, specify what part(s) of the source should form part of the imported post.', 'feedzy-rss-feeds' ); ?>
		<?php if ( false === apply_filters( 'feedzy_is_license_of_type', false, 'agency' ) ) { ?>
			<?php echo wp_kses_post( sprintf( __( 'The magic tags that are greyed out and disabled are unavailable for your current license. You can consider %1$supgrading%2$s.', 'feedzy-rss-feeds' ), '<a href="' . esc_url( FEEDZY_UPSELL_LINK ) . '" target="_blank" title="' . __( 'Upgrade', 'feedzy-rss-feeds' ) . '">', '</a>' ) ); ?>
		<?php } ?>
		</p>

		<div class="feedzy-rows">
			<div class="feedzy-row">
				<div class="label_description">
					<h4><?php esc_html_e( 'Post Element', 'feedzy-rss-feeds' ); ?></h4>
				</div>
				<div class="feedzy-separator"></div>
				<div class="label_description">
					<h4><?php esc_html_e( 'Element Value', 'feedzy-rss-feeds' ); ?></h4>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-type"><?php esc_html_e( 'Post Type', 'feedzy-rss-feeds' ); ?></label><br/>
					<small><?php esc_html_e( 'The post type you want to use for the generated post.', 'feedzy-rss-feeds' ); ?></small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<select id="feedzy_post_type" class="form-control feedzy-chosen" name="feedzy_meta_data[import_post_type]" data-tax="<?php echo esc_attr( $import_post_term ); ?>" >
						<?php
						foreach ( $post_types as $_post_type ) {
							?>
							<option value="<?php echo esc_attr( $_post_type ); ?>" <?php selected( $import_post_type, $_post_type ); ?>><?php echo esc_html( $_post_type ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-type"><?php esc_html_e( 'Post Taxonomy', 'feedzy-rss-feeds' ); ?></label><br/>
					<small><?php esc_html_e( 'Assign to a taxonomy (eg. "Post Category", "Post Tag" etc.). Leave blank, if unsure.', 'feedzy-rss-feeds' ); ?></small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<select id="feedzy_post_terms" multiple class="form-control feedzy-chosen" name="feedzy_meta_data[import_post_term][]" >
					</select>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-status"><?php esc_html_e( 'Post Status', 'feedzy-rss-feeds' ); ?></label><br/>
					<small>
						<?php
						esc_html_e( 'The post status you want your posts to have. You can choose Publish if you want to publish your posts right away, or you can use Draft if you want to draft your posts and publish it after reviewing them manually.', 'feedzy-rss-feeds' );
						?>
					</small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<select id="feedzy_post_status" class="form-control feedzy-chosen" name="feedzy_meta_data[import_post_status]" >
						<?php
						foreach ( $published_status as $_status ) {
							?>
							<option value="<?php echo esc_attr( $_status ); ?>" <?php selected( $import_post_status, $_status ); ?>><?php echo esc_html( ucfirst( $_status ) ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-title"><?php esc_html_e( 'Post Title', 'feedzy-rss-feeds' ); ?></label><br/>
					<small>
					<?php
					$magic_tags = array(
						'item_title',
						'item_author',
						'item_date',
					);
					$magic_tags = apply_filters( 'feedzy_get_service_magic_tags', $magic_tags, 'title' );

					esc_html_e( 'The title for the generated post. This field is mandatory - without this, a post will not be created.', 'feedzy-rss-feeds' );
					?>
					</small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<input type="text" name="feedzy_meta_data[import_post_title]" placeholder="<?php esc_html_e( 'Post Title', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo esc_attr( $import_title ); ?>"/>
					<div class="input-group-btn">
						<button type="button" class="btn btn-add-fields dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<?php esc_html_e( 'Insert Tag', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span>
						</button>
						<div class="dropdown-menu dropdown-menu-right">
							<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_title', array() ), 'import_post_title' ) ); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-title"><?php esc_html_e( 'Post Date', 'feedzy-rss-feeds' ); ?></label><br/>
					<small>
						<?php
							esc_html_e( 'The date for the generated post. Leave blank, if unsure.', 'feedzy-rss-feeds' );
						?>
					</small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<input type="text" name="feedzy_meta_data[import_post_date]" placeholder="<?php esc_html_e( 'Post Date', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo esc_attr( $import_date ); ?>"/>
					<div class="input-group-btn">
						<button type="button" class="btn btn-add-fields dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<?php esc_html_e( 'Insert Tag', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span>
						</button>
						<div class="dropdown-menu dropdown-menu-right">
							<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_date', array() ), 'import_post_date' ) ); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-content"><?php esc_html_e( 'Item Content', 'feedzy-rss-feeds' ); ?></label><br/>
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

					esc_html_e( 'The content for the generated post. This field is mandatory - without this, a post will not be created.', 'feedzy-rss-feeds' );
					?>
					</small>

					<p class="feedzy-highlight"><i class="dashicons dashicons-megaphone"></i>
					<?php
					if ( apply_filters( 'feedzy_is_license_of_type', false, 'business' ) ) {
						echo wp_kses_post( sprintf( __( 'You can add custom magic tags to extract custom elements from your feed as explained %1$shere%2$s. This will work only for single-feeds (i.e. not if you have specified a feed category that contains multiple feeds or using comma-separated feeds in the source).', 'feedzy-rss-feeds' ), '<a href="https://docs.themeisle.com/article/977-how-do-i-extract-values-from-custom-tags-in-feedzy" target="_blank">', '</a>' ) );
					} else {
						echo wp_kses_post( sprintf( __( 'Want to extract custom elements from your feed as explained %1$shere%2$s? Upgrade your %3$slicense%4$s today!', 'feedzy-rss-feeds' ), '<a href="https://docs.themeisle.com/article/977-how-do-i-extract-values-from-custom-tags-in-feedzy" target="_blank">', '</a>', '<a href="' . FEEDZY_UPSELL_LINK . '" target="_blank">', '</a>' ) );
					}
					?>
					</p>

				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<textarea name="feedzy_meta_data[import_post_content]" placeholder="<?php esc_html_e( 'Post Content', 'feedzy-rss-feeds' ); ?>" class="form-control"><?php echo esc_html( feedzy_custom_tag_escape( $import_content ) ); ?></textarea>
					<div class="input-group-btn">
						<button type="button" class="btn btn-add-fields dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<?php esc_html_e( 'Insert Tag', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span>
						</button>
						<div class="dropdown-menu dropdown-menu-right">
							<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_content', array() ), 'import_post_content' ) ); ?>
						</div>
					</div>
				</div>
			</div>

			<?php do_action( 'feedzy_metabox_show_rows', '', $post->ID, 'language-dropdown' ); ?>

			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-content"><?php esc_html_e( 'External Image URL?', 'feedzy-rss-feeds' ); ?></label><br/>
					<small>
						<?php
						esc_attr_e( 'The image url will be 3rd party url, We will not download image to your site.', 'feedzy-rss-feeds' );
						?>
					</small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<div>
						<input id="use-external-image" name="feedzy_meta_data[import_use_external_image]" class="feedzy-toggle feedzy-toggle-round" type="checkbox" value="yes" <?php echo esc_attr( $import_item_img_url ); ?>>
						<label for="use-external-image" class="feedzy-inline"></label>
						<label class="feedzy-inline" style="margin-left: 10px;" for="import_use_external_image"><?php esc_html_e( 'User external image URL, Ignore feature post thumbnail', 'feedzy-rss-feeds' ); ?></label>
					</div>
				</div>
			</div>
			<div class="feedzy-row">
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-content"><?php esc_html_e( 'Featured Image', 'feedzy-rss-feeds' ); ?></label><br/>
					<small>
						<?php
						esc_html_e( 'The URL for the featured image. You can use the magic tags, use your own URL or leave it empty.', 'feedzy-rss-feeds' );
						?>
					</small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<input type="text" name="feedzy_meta_data[import_post_featured_img]" placeholder="<?php esc_html_e( 'Featured Image', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo esc_attr( $import_featured_img ); ?>"/>
					<div class="input-group-btn">
						<button type="button" class="btn btn-add-fields dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<?php esc_html_e( 'Insert Tag', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span>
						</button>
						<div class="dropdown-menu dropdown-menu-right">
							<?php echo wp_kses_post( apply_filters( 'feedzy_render_magic_tags', '', apply_filters( 'feedzy_magic_tags_image', array() ), 'import_post_featured_img' ) ); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="feedzy-row <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
				<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '' ) ); ?>
				<div class="label_description">
					<label class="feedzy-sr-only" for="f1-post-content"><?php esc_html_e( 'Post Author', 'feedzy-rss-feeds' ); ?></label><br/>
					<small>
						<?php
							esc_html_e( 'Show the original author of the source item.', 'feedzy-rss-feeds' );
						?>
					</small>
				</div>
				<div class="feedzy-separator dashicons dashicons-leftright"></div>
				<div class="form-group input-group form_item">
					<div>
						<input id="feedzy-toggle_author_admin" name="feedzy_meta_data[import_link_author_admin]" class="feedzy-toggle feedzy-toggle-round" type="checkbox" value="yes" <?php echo wp_kses_post( $import_link_author[0] ); ?>>
						<label for="feedzy-toggle_author_admin" class="feedzy-inline"></label>
						<label class="feedzy-inline" style="margin-left: 10px;" for="import_link_author_admin"><?php esc_html_e( 'In the backend, on the post listing screen', 'feedzy-rss-feeds' ); ?></label>
					</div>
					<div>
						<input id="feedzy-toggle_author_public" name="feedzy_meta_data[import_link_author_public]" class="feedzy-toggle feedzy-toggle-round" type="checkbox" value="yes" <?php echo wp_kses_post( $import_link_author[1] ); ?>>
						<label for="feedzy-toggle_author_public" class="feedzy-inline"></label>
						<label class="feedzy-inline" style="margin-left: 10px;" for="import_link_author_public"><?php esc_html_e( 'In the frontend, link to the original post', 'feedzy-rss-feeds' ); ?></label>
					</div>
				</div>
			</div>


			<div class="custom_fields <?php echo esc_attr( apply_filters( 'feedzy_upsell_class', '' ) ); ?>">
				<?php echo wp_kses_post( apply_filters( 'feedzy_upsell_content', '' ) ); ?>
				<!-- Custom Fields Added By JS -->
				<?php
				if ( isset( $import_custom_fields ) && ! empty( $import_custom_fields ) ) {
					foreach ( $import_custom_fields as $custom_field_key => $custom_field_value ) {
						?>
						<div class="row">
							<div class="feedzy-row fields">
								<div class="form-group form_item">
									<input type="text" name="custom_vars_key[]" placeholder="<?php esc_html_e( 'Key Name', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo esc_attr( $custom_field_key ); ?>"/>
								</div>
								<div class="feedzy-separator dashicons dashicons-leftright"></div>
								<div class="form-group input-group form_item">
									<input type="text" name="custom_vars_value[]" placeholder="<?php esc_html_e( 'Value', 'feedzy-rss-feeds' ); ?>" class="form-control" value="<?php echo esc_attr( $custom_field_value ); ?>"/>
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

			<button id="new_custom_fields" type="button" class="btn btn-add-fields" style="width: 100%; margin-bottom: 16px; margin-top: 16px;" ><?php esc_html_e( 'Add custom fields', 'feedzy-rss-feeds' ); ?> <span class="dashicons dashicons-plus-alt"></span></button>
		</div>


		<div class="f1-buttons">
			<input type="hidden" id="custom_post_status" name="custom_post_status" value="draft" />
			<button type="button" id="preflight" name="check" class="btn btn-previous" value="Check" title="<?php esc_html_e( 'Click to see what items will be imported from the source, according to the filters specified', 'feedzy-rss-feeds' ); ?>"><?php esc_html_e( 'Dry Run', 'feedzy-rss-feeds' ); ?></button>
			<?php
			if ( 'publish' === $post_status ) {
				?>
				<button type="submit" name="publish" class="btn btn-submit" value="Publish"><?php esc_html_e( 'Save', 'feedzy-rss-feeds' ); ?></button>
				<?php
			} else {
				?>
				<button type="submit" name="save" class="btn btn-submit" value="Save Draft" style="float: none;"><?php esc_html_e( 'Save', 'feedzy-rss-feeds' ); ?></button>
				<button type="submit" name="publish" class="btn btn-submit btn-activate" value="Publish" ><?php esc_html_e( 'Save & Activate', 'feedzy-rss-feeds' ); ?></button>
				<?php
			}
			?>
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

