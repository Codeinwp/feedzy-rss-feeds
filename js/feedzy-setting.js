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

	$('#feedzy-delete-log-file').on('click', function (e) {
		e.preventDefault();
		const _this = $(this);
		const originalText = _this.html();
		_this.attr('disabled', true).addClass('fz-checking');

		const deleteUrl = new URL(`${window.wpApiSettings.root}feedzy/v1/logs`);
		deleteUrl.searchParams.append('_wpnonce', window.wpApiSettings.nonce);

		fetch(deleteUrl, {
			method: 'DELETE',
		})
			.then((response) => response.json())
			.then((response) => {
				if (!response.success) {
					_this.html(
						'<span class="dashicons dashicons-no-alt"></span>'
					);
					setTimeout(function () {
						_this.html(originalText);
						_this.removeAttr('disabled').removeClass('fz-checking');
					}, 3000);
				} else {
					window.location.reload();
				}
			})
			.catch((error) => {
				_this.html('<span class="dashicons dashicons-no-alt"></span>');
				setTimeout(function () {
					_this.html(originalText);
					_this.removeAttr('disabled').removeClass('fz-checking');
				}, 3000);
			});
	});

	$('#fz-add-schedule').on('click', function (e) {
		e.preventDefault();

		const interval = $('#fz-schedule-interval').val();
		const display = $('#fz-schedule-display').val();
		const name = $('#fz-schedule-name').val();

		if (!interval || !display || !name) {
			return;
		}

		const scheduleTable = $('.fz-schedules-table tbody');

		const newRow = $(`
			<tr data-schedule="${name}">
				<td class="fz-schedule-attributes">
					<strong>${name}</strong>
				</td>
				<td class="fz-schedule-attributes">
					${interval}
				</td>
				<td class="fz-schedule-attributes">
					${display}
				</td>
				<td class="fz-schedule-attributes">
					<button type="button" class="btn btn-outline-primary fz-delete-schedule fz-is-destructive" data-schedule="${name}">
						Delete
					</button>
				</td>
				<input type="hidden" value="${interval}" name="fz-custom-schedule-interval[${name}][interval]">
				<input type="hidden" value="${display}" name="fz-custom-schedule-interval[${name}][display]">
			</tr>
		`);

		scheduleTable.append(newRow);

		$('#fz-schedule-interval').val('');
		$('#fz-schedule-display').val('');
		$('#fz-schedule-name').val('');
	});

	$(document).on('click', '.fz-delete-schedule', function (e) {
		e.preventDefault();

		const $button = $(this);
		const $row = $button.closest('tr');

		$row.fadeOut(300, function () {
			$(this).remove();
		});
	});

	/**
	 * Toggle visibility of the email error address field based on email error enabled checkbox.
	 */
	const toggleEmailErrorField = () => {
		const checkbox = $('#feedzy-email-error-enabled');
		const emailField = checkbox
			.closest('.fz-form-group')
			.next('.fz-form-group');

		emailField.toggleClass('fz-hidden', !checkbox.is(':checked'));
	};

	$('#feedzy-email-error-enabled').on('change', toggleEmailErrorField);
});
