(function ($) {
	/**
	 * Toggles check button state based on license key input and syncs value across inputs
	 * @param {window.jQuery} $input - License key input element
	 */
	const handleLicenseKeyInput = ($input) => {
		const licenseKey = $input.val();
		const $checkButton = $('#check_ti_license');

		if (licenseKey !== '') {
			$checkButton.removeAttr('disabled');
		} else {
			$checkButton.attr('disabled', true);
		}

		$('.fz-license-section input[name="license_key"]').val(licenseKey);
	};

	/**
	 * Handles license validation response - shows errors or reloads page on success
	 * @param {Object}        response - API response with success flag and message
	 * @param {window.jQuery} $button  - Check license button element
	 */
	const handleLicenseResponse = (response, $button) => {
		if (!response.success) {
			const $errorMessage = $(
				'<p class="feedzy-api-error">' + response.message + '</p>'
			);
			$errorMessage.insertAfter(
				$('.fz-license-section').find('.help-text')
			);
			$button.removeAttr('disabled').removeClass('fz-checking');
			return;
		}
		window.location.reload();
	};

	/**
	 * Validates license via AJAX, disables button and clears previous errors
	 * @param {Event} e - Click event from check license button
	 */
	const checkLicense = (e) => {
		e.preventDefault();
		const $button = $(e.currentTarget);

		$button.attr('disabled', true).addClass('fz-checking');
		$button
			.parents('.fz-license-section')
			.find('.feedzy-api-error')
			.remove();

		const licenseData = $button
			.parent('.fz-input-group-btn')
			.find('input')
			.serialize();

		$.post(
			window.ajaxurl,
			licenseData,
			(response) => handleLicenseResponse(response, $button),
			'json'
		);
	};

	$(document).ready(() => {
		$('.fz-license-section #license_key').on('input', function () {
			handleLicenseKeyInput($(this));
		});

		$('.fz-license-section #check_ti_license').on('click', checkLicense);
	});
})(window.jQuery);
