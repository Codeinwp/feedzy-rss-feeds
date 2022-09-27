<div class="fz-help-improve-wrap">
	<div class="fz-help-improve-box">
		<div class="right">
			<img src="<?php echo esc_url( FEEDZY_ABSURL . 'img/improve-feedzy.png' ); ?>" alt="">
		</div>
		<div class="left">
			<h3 class="h3 pb-16">Answer a few questions to help us improve Feedzy</h3>
			<p>We're always looking for suggestions to further improve Feedzy.
			<?php if ( ! feedzy_is_pro() ) { ?>
				 If your feedback is especially helpful and we choose to do an interview with you to discuss your suggestions, you will even gain a yearly membership for free for your trouble.
			<?php } ?>
			</p>
			<div class="cta">
				<a href="<?php echo esc_url( feedzy_is_pro() ? FEEDZY_SURVEY_PRO : FEEDZY_SURVEY_FREE ); ?>" target="_blank" class="btn btn-outline-primary">Take the Survey</a>
			</div>
		</div>		
	</div>
</div>
