<?php // phpcs:disable ?>
<div class="fz-pro-features-wrap">
    <div class="fz-pro-features-table mb-30">
        <div class="fz-pro-features-table-header">
            <ul class="fz-pro-features-table-row">
                <li class="features-info">&nbsp;</li>
                <li class="free"><?php esc_html_e( 'Free', 'feedzy-rss-feeds' ); ?></li>
                <li class="pro"><?php esc_html_e( 'Pro', 'feedzy-rss-feeds' ); ?></li>
            </ul>
        </div>
        <div class="fz-pro-features-table-body">
            <ul class="fz-pro-features-table-row">
                <li class="features-info">
                    <h3 class="h3"><?php esc_html_e( 'Import content from RSS feeds', 'feedzy-rss-feeds' ); ?></h3>
                    <p><?php esc_html_e( 'Create unlimited import routines for your RSS feeds and let them run on autopilot.', 'feedzy-rss-feeds' ); ?></p>
                </li>
                <li class="pro" data-label="Free">
                    <span class="dashicons dashicons-saved"></span>
                </li>
                <li class="pro" data-label="Pro">
                    <span class="dashicons dashicons-saved"></span>
                </li>
            </ul>
            <ul class="fz-pro-features-table-row">
                <li class="features-info">
                    <h3 class="h3"><?php esc_html_e( 'Pagebuilder integration', 'feedzy-rss-feeds' ); ?></h3>
                    <p><?php esc_html_e( 'Filter and display RSS feeds items directly with your favourite page builders.', 'feedzy-rss-feeds' ); ?></p>
                </li>

                <li class="pro" data-label="Free">
                    <span class="dashicons dashicons-saved"></span>
                </li>
                <li class="pro" data-label="Pro">
                    <span class="dashicons dashicons-saved"></span>
                </li>
            </ul>
            <ul class="fz-pro-features-table-row">
                <li class="features-info">
                    <h3 class="h3"><?php esc_html_e( 'Advanced Filtering', 'feedzy-rss-feeds' ); ?></h3>
                    <p>
                        <?php esc_html_e( 'With robust filtering options, you can include items matching any condition, filter by fields like title, description, or custom fields, and use operators like contains, equals, or regex for precise control.', 'feedzy-rss-feeds' ); ?>
                    </p>
                </li>
                <li class="free" data-label="Free">
                    <span class="dashicons dashicons-no-alt"></span>
                </li>
                <li class="pro" data-label="Pro">
                    <span class="dashicons dashicons-saved"></span>
                </li>
            </ul>
            <ul class="fz-pro-features-table-row">
                <li class="features-info">
                    <h3 class="h3">
                        <?php esc_html_e( 'Multiple feed templates', 'feedzy-rss-feeds' ); ?>
                    </h3>
                    <p>
                        <?php esc_html_e( 'Choose the best template for each feed, such as standard grid layout, blog layout, online shop layout, or audio playback (soundcloud) layout.', 'feedzy-rss-feeds' ); ?>
                    </p>
                </li>
                <li class="free" data-label="Free">
                    <span class="dashicons dashicons-no-alt"></span>
                </li>
                <li class="pro" data-label="Pro">
                    <span class="dashicons dashicons-saved"></span>
                </li>
            </ul>

            <ul class="fz-pro-features-table-row">
                <li class="features-info">
                    <h3 class="h3">
                        <?php esc_html_e( 'Referral/Affiliate links', 'feedzy-rss-feeds' ); ?>
                    </h3>
                    <p>
                        <?php esc_html_e( 'Add referral parameters, and Feedzy will automatically configure affiliate links for each item in the feed. You can even import prices from product sources to create extra value for your readers.', 'feedzy-rss-feeds' ); ?>
                    </p>
                </li>
                <li class="free" data-label="Free">
                    <span class="dashicons dashicons-no-alt"></span>
                </li>
                <li class="pro" data-label="Pro">
                    <span class="dashicons dashicons-saved"></span>
                </li>
            </ul>

            <ul class="fz-pro-features-table-row">
                <li class="features-info">
                    <h3 class="h3">
                        <?php esc_html_e( 'Importing the full-text content', 'feedzy-rss-feeds' ); ?>
                        <span class="pro-label"><?php esc_html_e( 'Developer & Agency plans', 'feedzy-rss-feeds' ); ?></span>
                    </h3>
                    <p>
                        <?php esc_html_e( 'During the import, Feedzy will visit URLs of all items and parse the content directly from the website, importing ALL the content from a item , compared to #item_content tag which only imports the post excerpts.', 'feedzy-rss-feeds' ); ?>
                    </p>
                </li>
                <li class="free" data-label="Free">
                    <span class="dashicons dashicons-no-alt"></span>
                </li>
                <li class="pro" data-label="Pro">
                    <span class="dashicons dashicons-saved"></span>
                </li>
            </ul>
            <ul class="fz-pro-features-table-row">
                <li class="features-info">
                    <h3 class="h3">
                        <?php esc_html_e( 'Custom Fields', 'feedzy-rss-feeds' ); ?>
                        <span class="pro-label"><?php esc_html_e( 'Developer & Agency plans', 'feedzy-rss-feeds' ); ?></span>
                    </h3>
                    <p>
                        <?php esc_html_e( 'Create customizable fields and fetch custom values from the feed such as date updated, rating, etc.', 'feedzy-rss-feeds' ); ?>
                    </p>
                </li>
                <li class="free" data-label="Free">
                    <span class="dashicons dashicons-no-alt"></span>
                </li>
                <li class="pro" data-label="Pro">
                    <span class="dashicons dashicons-saved"></span>
                </li>
            </ul>
            <ul class="fz-pro-features-table-row">
                <li class="features-info">
                    <h3 class="h3">
                        <?php
                        echo esc_html__( 'AI Support', 'feedzy-rss-feeds' );
                        ?>
                        <span class="pro-label"><?php esc_html_e( 'Developer & Agency plans', 'feedzy-rss-feeds' ); ?></span>
                    </h3>
                    <p>
                        <?php
                        /* translators: %s: OpenAI (ChatGPT) or OpenRouter */
                        echo sprintf( esc_html__( 'Using the %s integration, paraphrase, summarize, generate missing featured images, or apply your custom prompt to the imported content.', 'feedzy-rss-feeds' ), __( 'OpenAI (ChatGPT) or OpenRouter', 'feedzy-rss-feeds' ) );
                        ?>
                    </p>
                </li>
                <li class="free" data-label="Free">
                    <span class="dashicons dashicons-no-alt"></span>
                </li>
                <li class="pro" data-label="Pro">
                    <span class="dashicons dashicons-saved"></span>
                </li>
            </ul>
            <ul class="fz-pro-features-table-row">
                <li class="features-info">
                    <h3 class="h3">
                        <?php
                        
                        /* translators: %s: WordAI & SpinnerChief */
                        echo sprintf( esc_html__( '%s integration', 'feedzy-rss-feeds' ), __( 'WordAI & SpinnerChief', 'feedzy-rss-feeds' ) );
                        ?>
                        <span class="pro-label"><?php esc_html_e( 'Agency plan', 'feedzy-rss-feeds' );?></span>
                    </h3>
                    <p>
                        <?php
                       
                        /* translators: %1$s: WordAi, %2$s: SpinnerChief */
                        echo sprintf( esc_html__( '%1$s and %2$s can be used to rephrase RSS feeds when they are imported as posts in WordPress.', 'feedzy-rss-feeds' ), __( 'WordAi', 'feedzy-rss-feeds' ), __( 'SpinnerChief', 'feedzy-rss-feeds' ) );
                        ?>
                    </p>
                </li>
                <li class="free" data-label="Free">
                    <span class="dashicons dashicons-no-alt"></span>
                </li>
                <li class="pro" data-label="Pro">
                    <span class="dashicons dashicons-saved"></span>
                </li>
            </ul>

            <ul class="fz-pro-features-table-row">
                <li class="features-info">
                    <h3 class="h3">
                        <?php esc_html_e( 'Access to Translation and Paraphrasing service', 'feedzy-rss-feeds' ); ?> <span class="pro-label"><?php esc_html_e( 'Agency plan', 'feedzy-rss-feeds' ); ?></span>
                    </h3>
                    <p>
                        <?php esc_html_e( 'Paraphrase or translate content before import using the built-in service, no separate subscription required.', 'feedzy-rss-feeds' ); ?>
                    </p>
                </li>
                <li class="free" data-label="Free">
                    <span class="dashicons dashicons-no-alt"></span>
                </li>
                <li class="pro" data-label="Pro">
                    <span class="dashicons dashicons-saved"></span>
                </li>
            </ul>
        </div>
    </div>

    <div class="cta">
        <a href="<?php echo esc_url( tsdk_translate_link( tsdk_utmify(FEEDZY_UPSELL_LINK,'viewall','freevspro') ) ) ; ?>" class="btn btn-block btn-primary btn-lg" target="_blank">
            <?php esc_html_e( 'View all Premium features', 'feedzy-rss-feeds' ); ?>
        </a>
    </div>
</div>