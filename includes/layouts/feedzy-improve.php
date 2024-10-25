<div id="fz-feedback-modal"></div>
<div class="fz-help-improve-wrap">
	<div class="fz-help-improve-box">
		<div class="fz-right">
			<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/improve-feedzy.png' ); ?>" alt="">
		</div>
		<div class="fz-left">
			<h3 class="h3 pb-16"><?php esc_html_e( 'Answer a few questions to help us improve Feedzy', 'feedzy-rss-feeds' ); ?></h3>
			<p>
				<?php
				esc_html_e( 'We\'re always looking for suggestions to further improve Feedzy.', 'feedzy-rss-feeds' );
				if ( ! feedzy_is_pro() ) {
					esc_html_e( 'If your feedback is especially helpful and we choose to do an interview with you to discuss your suggestions, you will even gain a yearly membership for free for your trouble.', 'feedzy-rss-feeds' );
				}
				?>
			</p>
			<div class="cta">
				<a href="javascript:void(0)" id="fz-feedback-btn" class="btn btn-outline-primary" role="button"><?php esc_html_e( 'Take the Survey', 'feedzy-rss-feeds' ); ?></a>
			</div>
		</div>		
	</div>
</div>
