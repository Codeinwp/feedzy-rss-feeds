<?php
/**
 * Subscribe notice layout.
 *
 * @package Feedzy
 * @since   5.1.0
 */

?>
<div class="feedzy-container">
	<div class="feedzy-helper-notice" style="padding: 30px; position: relative">
		<button
			type="button" 
			class="feedzy-notice-dismiss" 
			aria-label="<?php esc_attr_e( 'Dismiss this notice', 'feedzy-rss-feeds' ); ?>"
		>
			&times;
		</button>
		
		<h2 class="feedzy-notice-title">
			<?php esc_html_e( 'Welcome to Feedzy!', 'feedzy-rss-feeds' ); ?>
		</h2>

		<p class="feedzy-notice-subtitle">
			<?php
			$users_number = number_format_i18n( 50000 );
			echo esc_html(
				sprintf(
					// translators: %s is the number of users.
					__( 'Join %s+ users aggregating RSS feeds effortlessly', 'feedzy-rss-feeds' ),
					$users_number
				)
			);
			?>
		</p>

		<form id="feedzy-subscribe-form" method="post" action="">
			<?php wp_nonce_field( 'feedzy_subscribe_nonce', 'feedzy_subscribe_nonce_field' ); ?>
			
			<input
				type="email" 
				id="fz_subscribe_email"
				name="feedzy_email"
				value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"  
				class="feedzy-helper-subscribe"
				required
			>

			<div class="mb-20">
				<div class="feedzy-helper-info">
					<?php esc_html_e( 'Get tips, updates & unlock exclusive guides', 'feedzy-rss-feeds' ); ?>
				</div>
				<div style="font-size: 16px;">
					<?php esc_html_e( 'Help improve Feedzy with anonymous usage insights', 'feedzy-rss-feeds' ); ?>
				</div>
			</div>
			
			<button
				type="button" 
				name="feedzy_subscribe_button"
				class="feedzy-subscribe"
			>
				<?php esc_html_e( 'Get Started', 'feedzy-rss-feeds' ); ?> →
			</button>
		</form>

		<div class="feedzy-notice-error"></div>
	</div>
</div>
<style>
	.feedzy-notice-dismiss {
		position: absolute;
		top: 10px;
		right: 10px; 
		background: transparent; 
		border: none; 
		cursor: pointer;
		font-size: 24px;
	}

	.feedzy-notice-title {
		font-size: clamp(28px, 5vw, 42px);
		margin: 0 0 10px 0;
	}

	.feedzy-helper-notice .feedzy-notice-subtitle {
		font-size: 20px;
		margin-bottom: 20px;
	}

	.feedzy-wrap .feedzy-helper-subscribe {
		width: 100%;
		max-width: 600px;
		padding: 15px; 
		font-size: 16px;
		border: none;
		border-radius: 5px;
		margin-bottom: 20px;
	}

	.feedzy-helper-info {
		font-size: 18px;
		font-weight: 600;
		margin-bottom: 8px;
	}

	.feedzy-helper-notice .feedzy-subscribe{
		background: white;
		padding: 12px 30px;
		font-size: 18px;
		font-weight: 600;
		border: none;
		border-radius: 5px;
		cursor: pointer;
	}

	.feedzy-notice-error {
		display: none;
		color: #d63638;
		margin-top: 10px;
		padding: 8px;
		background-color: #fcf0f1;
		border: 1px solid #d63638;
		border-radius: 4px;
	}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
	const showError = (message) => {
		const errorDiv = document.querySelector('.feedzy-notice-error');
		errorDiv.textContent = message;
		errorDiv.style.display = 'block';
	};

	const hideError = () => {
		const errorDiv = document.querySelector('.feedzy-notice-error');
		errorDiv.style.display = 'none';
		errorDiv.textContent = '';
	};

	const send = async (formData) => {
		return await fetch(window.ajaxurl, {
			method: 'POST',
			body: formData,
			credentials: 'same-origin'
		});
	}

	const dismissNotice = async (button) => {
		const notice = button.closest('.feedzy-helper-notice');
		try {
			button.disabled = true;
			const nonceField = document.getElementById('feedzy_subscribe_nonce_field');
			
			const formData = new FormData();
			formData.append('action', 'feedzy_dashboard_subscribe');
			formData.append('_wpnonce', nonceField.value);
			formData.append('skip', 'yes');

			await send(formData);
		} catch (error) {
			console.error('Error dismissing notice:', error);
		} finally {
			button.disabled = false;
			jQuery(notice).fadeOut(400, function() {
				jQuery(this).remove();
			});
		}
	};

	const handleSubscription = async (button) => {
		try {
			hideError();
			const subscriptionForm = document.getElementById('feedzy-subscribe-form');

			if ( !subscriptionForm.checkValidity() ) {
				subscriptionForm.reportValidity();
				return;
			}

			const emailElement = document.getElementById('fz_subscribe_email');
			const nonceField = document.getElementById('feedzy_subscribe_nonce_field');

			button.disabled = true;

			const formData = new FormData();
			formData.append('action', 'feedzy_dashboard_subscribe');
			formData.append('_wpnonce', nonceField.value);
			formData.append('with_subscribe', '1');
			formData.append('email', emailElement.value);

			const response = await send(formData);
			const result = await response.json();

			if (result.success) {
				const notice = document.querySelector('.feedzy-helper-notice');
				jQuery(notice).fadeOut(400, function() {
					jQuery(this).remove();
				});

				if (result.data?.redirect_to) {
					window.location.href = result.data.redirect_to;
				}
			} else {
				const errorMessage = result.data?.message || 'Unknown';
				showError(errorMessage);
			}
		} catch (error) {
			showError(error);
		} finally {
			button.disabled = false;
		}
	};
	
	const dismissButton = document.querySelector('.feedzy-notice-dismiss');
	const subscribeButton = document.querySelector('.feedzy-subscribe');

	if (dismissButton) {
		dismissButton.addEventListener('click', (event) => {
			event.preventDefault();
			dismissNotice(event.target);
		});
	}

	if (subscribeButton) {
		subscribeButton.addEventListener('click', (event) => {
			event.preventDefault();
			handleSubscription(event.target);
		});
	}
});
</script>