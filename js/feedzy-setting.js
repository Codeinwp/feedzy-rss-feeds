/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: https://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 *
 * @package
 */
/* global feedzy_setting */
/* jshint unused:false */
jQuery(function ($) {
	// Snackbar notice.
	const snackbarNotice = function () {
		$('.fz-snackbar-notice').toggleClass('open', 1000);
		if ($('.fz-snackbar-notice').hasClass('open')) {
			setTimeout(function () {
				snackbarNotice();
			}, 3000);
		}
	};

	// on upload button click
	$('body').on('click', '.feedzy-open-media', function (e) {
		e.preventDefault();
		var button = $(this),
			wp_media_uploader = wp
				.media({
					title: feedzy_setting.l10n.media_iframe_title,
					library: {
						type: 'image',
					},
					button: {
						text: feedzy_setting.l10n.media_iframe_button,
					},
					multiple: false,
				})
				.on('select', function () {
					// it also has "open" and "close" events
					const attachment = wp_media_uploader
						.state()
						.get('selection')
						.first()
						.toJSON();
					let attachmentUrl = attachment.url;
					if (attachment.sizes.thumbnail) {
						attachmentUrl = attachment.sizes.thumbnail.url;
					}
					if ($('.feedzy-media-preview').length) {
						$('.feedzy-media-preview')
							.find('img')
							.attr('src', attachmentUrl);
					} else {
						$(
							'<div class="fz-form-group feedzy-media-preview"><img src="' +
								attachmentUrl +
								'"></div>'
						).insertBefore(button.parent());
					}
					button
						.parent()
						.find('.feedzy-remove-media')
						.addClass('is-show');
					button
						.parent()
						.find('input:hidden')
						.val(attachment.id)
						.trigger('change');
					$('.feedzy-open-media').html(
						feedzy_setting.l10n.action_btn_text_2
					);
				})
				.open();
	});

	// on remove button click
	$('body').on('click', '.feedzy-remove-media', function (e) {
		e.preventDefault();
		const button = $(this);
		button.parent().prev('.feedzy-media-preview').remove();
		button.removeClass('is-show');
		button.parent().find('input:hidden').val('').trigger('change');
		$('.feedzy-open-media').html(feedzy_setting.l10n.action_btn_text_1);
	});

	// Unsaved form exit confirmation.
	let unsaved = false;
	$(':input').change(function () {
		unsaved = true;
	});
	$(
		'#feedzy-settings-submit, #check_wordai_api, #check_spinnerchief_api, #check_aws_api, #check_openai_api, #check_openrouter_api, #check_ti_license'
	).on('click', function () {
		unsaved = false;
	});
	window.addEventListener('beforeunload', function (e) {
		if (unsaved) {
			e.preventDefault();
			e.returnValue = '';
		}
	});

	// Select cron execution time.
	$(document).on('change', '#fz-event-execution', function () {
		$('#fz-execution-offset').val(new Date().getTimezoneOffset() / 60);
	});

	// License key.
	jQuery('.fz-license-section #license_key').on('input', function () {
		const licenseKey = jQuery(this).val();
		if (licenseKey !== '') {
			jQuery('#check_ti_license').removeAttr('disabled');
		} else {
			jQuery('#check_ti_license').attr('disabled', true);
		}
		jQuery('.fz-license-section input[name="license_key"]').val(licenseKey);
	});

	jQuery('.fz-license-section #check_ti_license').on('click', function (e) {
		e.preventDefault();
		const _this = jQuery(this);
		_this.attr('disabled', true).addClass('fz-checking');

		_this.parents('.fz-license-section').find('.feedzy-api-error').remove();

		const LicenseData = _this
			.parent('.fz-input-group-btn')
			.find('input')
			.serialize();

		jQuery.post(
			ajaxurl,
			LicenseData,
			function (response) {
				if (!response.success) {
					jQuery(
						'<p class="feedzy-api-error">' +
							response.message +
							'</p>'
					).insertAfter(
						jQuery('.fz-license-section').find('.help-text')
					);
					_this.removeAttr('disabled').removeClass('fz-checking');
				} else {
					window.location.reload();
				}
			},
			'json'
		);
	});
	snackbarNotice();

	const initializeAutoCatActions = () => {
		const elements = {
			table: document.querySelector('.fz-auto-cat'),
			tbody: document.querySelector('.fz-auto-cat tbody'),
			addBtn: document.querySelector('.fz-auto-cat-actions button'),
		};

		if (!Object.values(elements).every(Boolean)) {
			return;
		}

		const rows = elements.tbody.querySelectorAll('tr');
		let rowIndex = rows.length - 1;

		const getNewRow = (index) => {
			const row = rows[0].cloneNode(true);
			const input = row.querySelector('input');
			const select = row.querySelector('select');
			const deleteBtn = row.querySelector('button');

			if (input) {
				input.value = '';
				input.name = `auto-categories[${index}][keywords]`;
				input.addEventListener('keydown', (e) => {
					if (e.key === 'Enter') {
						e.preventDefault();
					}
				});
			}

			if (select) {
				select.name = `auto-categories[${index}][category]`;
			}

			if (deleteBtn) {
				deleteBtn.classList.remove('disabled');
				deleteBtn.removeAttribute('disabled');
			}

			return row;
		};

		elements.tbody.addEventListener('click', (e) => {
			if (e.target.matches('button:not(.disabled)')) {
				e.target.closest('tr')?.remove();
			}
		});

		elements.addBtn.addEventListener('click', (e) => {
			e.preventDefault();
			if (rows.length > 0) {
				const newRow = getNewRow(++rowIndex);
				elements.tbody.appendChild(newRow);
			}
		});

		// Add event listener to existing inputs
		rows.forEach((row) => {
			const input = row.querySelector('input');
			if (input) {
				input.addEventListener('keydown', (e) => {
					if (e.key === 'Enter') {
						e.preventDefault();
					}
				});
			}
		});
	};

	initializeAutoCatActions();
});
