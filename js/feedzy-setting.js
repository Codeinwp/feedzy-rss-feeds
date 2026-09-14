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
		const l10n = feedzy_setting.l10n;
		let rowIndex = rows.length - 1;
		let comboCount = 0;

		/**
		 * Turn a row's native select into a searchable combobox.
		 *
		 * The select stays in the DOM and remains the submitted value, so the
		 * form still works when this never runs. Categories beyond the bounded
		 * server rendered list are pulled in on demand and appended to it.
		 *
		 * @param {HTMLElement} row The mapping row.
		 */
		const enhanceRow = (row) => {
			const select = row.querySelector('select');
			const picker = row.querySelector('.fz-auto-cat-picker');

			if (!select || !picker || select.dataset.fzCombo === '1') {
				return;
			}

			select.dataset.fzCombo = '1';
			select.classList.add('fz-combo-native');

			const listId = `fz-combo-list-${comboCount++}`;

			const input = document.createElement('input');
			input.type = 'text';
			input.className = 'form-control fz-combo-input';
			input.placeholder = l10n.select_category;
			input.autocomplete = 'off';
			input.setAttribute('role', 'combobox');
			input.setAttribute('aria-autocomplete', 'list');
			input.setAttribute('aria-expanded', 'false');
			input.setAttribute('aria-controls', listId);
			input.setAttribute('aria-label', l10n.search_categories);

			const list = document.createElement('ul');
			list.id = listId;
			list.className = 'fz-combo-list';
			list.setAttribute('role', 'listbox');
			list.hidden = true;

			picker.insertBefore(input, select);
			picker.appendChild(list);

			const state = {
				search: '',
				page: 1,
				hasMore: false,
				request: null,
				timer: null,
				active: -1,
				open: false,
			};

			const selectedOption = () =>
				select.options[select.selectedIndex] || null;

			const syncInput = () => {
				const option = selectedOption();
				input.value = option && option.value ? option.textContent : '';
			};

			/**
			 * The loaded options matching the current search, taken from the
			 * select itself so server rendered and searched entries are one list.
			 *
			 * @return {HTMLOptionElement[]} Matching options.
			 */
			const matchingOptions = () => {
				const term = state.search.toLowerCase();

				return Array.from(select.options).filter(
					(option) =>
						option.value &&
						(!term ||
							option.textContent.toLowerCase().includes(term))
				);
			};

			const items = () =>
				Array.from(list.querySelectorAll('[role="option"]'));

			const setActive = (index) => {
				const options = items();

				options.forEach((item, i) => {
					const isActive = i === index;
					item.classList.toggle('is-active', isActive);
					item.setAttribute(
						'aria-selected',
						isActive ? 'true' : 'false'
					);
				});

				state.active = index;

				if (index < 0 || !options[index]) {
					input.removeAttribute('aria-activedescendant');
					return;
				}

				input.setAttribute('aria-activedescendant', options[index].id);
				options[index].scrollIntoView({ block: 'nearest' });
			};

			const renderList = () => {
				list.innerHTML = '';
				const options = matchingOptions();

				if (!options.length) {
					const empty = document.createElement('li');
					empty.className = 'fz-combo-empty';
					empty.textContent = l10n.no_categories_found;
					list.appendChild(empty);
				}

				options.forEach((option, i) => {
					const item = document.createElement('li');
					item.id = `${listId}-option-${i}`;
					item.className = 'fz-combo-option';
					item.setAttribute('role', 'option');
					item.setAttribute('aria-selected', 'false');
					item.dataset.value = option.value;
					item.textContent = option.textContent;
					list.appendChild(item);
				});

				if (state.hasMore) {
					const more = document.createElement('li');
					more.className = 'fz-combo-more';
					more.textContent = l10n.load_more;
					list.appendChild(more);
				}

				setActive(-1);
			};

			const open = () => {
				state.open = true;
				list.hidden = false;
				input.setAttribute('aria-expanded', 'true');
				renderList();
			};

			const close = () => {
				state.open = false;
				list.hidden = true;
				input.setAttribute('aria-expanded', 'false');
				input.removeAttribute('aria-activedescendant');
				state.active = -1;
				state.search = '';
				syncInput();
			};

			const choose = (value) => {
				select.value = value;
				close();
			};

			/**
			 * Merge searched categories into the select, which is the single
			 * store of loaded options.
			 *
			 * @param {Array} categories Categories from the search response.
			 */
			const mergeResults = (categories) => {
				categories.forEach((category) => {
					const value = String(category.id);

					if (select.querySelector(`option[value="${value}"]`)) {
						return;
					}

					const option = document.createElement('option');
					option.value = value;
					option.textContent = category.name;
					select.appendChild(option);
				});
			};

			const fetchCategories = () => {
				if (typeof window.ajaxurl === 'undefined') {
					return;
				}

				if (state.request) {
					state.request.abort();
				}

				list.classList.add('is-loading');

				state.request = $.post(window.ajaxurl, {
					action: 'feedzy_search_auto_categories',
					security: feedzy_setting.ajax.security,
					search: state.search,
					page: state.page,
				})
					.done((response) => {
						if (!response || !response.success) {
							return;
						}
						mergeResults(response.data.categories);
						state.hasMore = response.data.has_more;
						if (state.open) {
							renderList();
						}
					})
					.always(() => {
						state.request = null;
						list.classList.remove('is-loading');
					});
			};

			input.addEventListener('focus', open);

			input.addEventListener('input', () => {
				state.search = input.value.trim();
				state.page = 1;
				state.hasMore = false;

				if (!state.open) {
					open();
				} else {
					renderList();
				}

				clearTimeout(state.timer);
				state.timer = setTimeout(fetchCategories, 300);
			});

			input.addEventListener('keydown', (e) => {
				if (e.key === 'ArrowDown' && !state.open) {
					e.preventDefault();
					open();
					return;
				}

				const options = items();

				switch (e.key) {
					case 'ArrowDown':
						e.preventDefault();
						setActive(
							Math.min(state.active + 1, options.length - 1)
						);
						break;
					case 'ArrowUp':
						e.preventDefault();
						setActive(Math.max(state.active - 1, 0));
						break;
					case 'Home':
						if (state.open) {
							e.preventDefault();
							setActive(0);
						}
						break;
					case 'End':
						if (state.open) {
							e.preventDefault();
							setActive(options.length - 1);
						}
						break;
					case 'Enter':
						e.preventDefault();
						if (state.open && options[state.active]) {
							choose(options[state.active].dataset.value);
						}
						break;
					case 'Escape':
						if (state.open) {
							e.preventDefault();
							close();
						}
						break;
					default:
						break;
				}
			});

			list.addEventListener('mousedown', (e) => {
				// Keep focus on the input so the blur handler does not close first.
				e.preventDefault();
			});

			list.addEventListener('click', (e) => {
				if (e.target.matches('.fz-combo-more')) {
					state.page += 1;
					fetchCategories();
					return;
				}

				const item = e.target.closest('.fz-combo-option');

				if (item) {
					choose(item.dataset.value);
				}
			});

			document.addEventListener('click', (e) => {
				if (state.open && !picker.contains(e.target)) {
					close();
				}
			});

			syncInput();
		};

		const getNewRow = (index) => {
			const row = rows[0].cloneNode(true);
			const input = row.querySelector('input[type="text"]');
			const select = row.querySelector('select');
			const deleteBtn = row.querySelector('.fz-auto-cat-delete');

			// The cloned row carries a copy of the first row's combobox; drop it
			// and let enhanceRow() build a fresh one bound to this row's select.
			row.querySelectorAll('.fz-combo-input, .fz-combo-list').forEach(
				(node) => node.remove()
			);

			if (input) {
				input.value = '';
				input.name = `auto-categories[${index}][keywords]`;
			}

			if (select) {
				select.name = `auto-categories[${index}][category]`;
				select.selectedIndex = 0;
				select.classList.remove('fz-combo-native');
				delete select.dataset.fzCombo;
			}

			if (deleteBtn) {
				deleteBtn.classList.remove('disabled');
				deleteBtn.removeAttribute('disabled');
			}

			return row;
		};

		elements.tbody.addEventListener('keydown', (e) => {
			if (e.key === 'Enter' && e.target.matches('input[type="text"]')) {
				e.preventDefault();
			}
		});

		elements.tbody.addEventListener('click', (e) => {
			if (e.target.matches('.fz-auto-cat-delete:not(.disabled)')) {
				e.target.closest('tr')?.remove();
			}
		});

		elements.addBtn.addEventListener('click', (e) => {
			e.preventDefault();
			if (rows.length > 0) {
				const newRow = getNewRow(++rowIndex);
				elements.tbody.appendChild(newRow);
				enhanceRow(newRow);
			}
		});

		rows.forEach(enhanceRow);
	};

	initializeAutoCatActions();

	// Disable the Add Schedule button until all fields are filled.
	const validateScheduleForm = () => {
		const button = $('#fz-add-schedule');

		if (!button.length) {
			return;
		}

		const interval = $('#fz-schedule-interval').val().trim();
		const display = $('#fz-schedule-display').val().trim();
		const name = $('#fz-schedule-name').val().trim();

		const isValid = interval && display && name;
		button.prop('disabled', !isValid);
		button.toggleClass('disabled', !isValid);
	};

	// Initial validation check.
	validateScheduleForm();

	// Add event listeners to schedule form inputs.
	$('#fz-schedule-interval, #fz-schedule-display, #fz-schedule-name').on(
		'input keyup',
		validateScheduleForm
	);

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

		const formElem = document.querySelector('form:has(.fz-form-wrap)');
		if (formElem && formElem.checkValidity() === false) {
			formElem.reportValidity();
			return;
		}

		const interval = $('#fz-schedule-interval').val();
		const display = $('#fz-schedule-display').val();
		const name = $('#fz-schedule-name').val();

		if (!interval || !display || !name) {
			return;
		}

		$('.fz-schedules-table').show();
		const scheduleTable = $('.fz-schedules-table tbody');

		const newRow = $('<tr>').attr('data-schedule', name);

		const nameCell = $('<td>')
			.addClass('fz-schedule-attributes')
			.append($('<strong>').text(name));

		const intervalCell = $('<td>')
			.addClass('fz-schedule-attributes')
			.text(interval);
		const displayCell = $('<td>')
			.addClass('fz-schedule-attributes')
			.text(display);

		const deleteButton = $('<button>')
			.attr({
				type: 'button',
				'data-schedule': name,
			})
			.addClass(
				'btn btn-outline-primary fz-delete-schedule fz-is-destructive'
			)
			.text(window.feedzy_setting.l10n.delete_btn_label);

		const actionCell = $('<td>')
			.addClass('fz-schedule-attributes')
			.append(deleteButton);

		const intervalInput = $('<input>').attr({
			type: 'hidden',
			value: interval,
			name: `fz-custom-schedule-interval[${name}][interval]`,
		});

		const displayInput = $('<input>').attr({
			type: 'hidden',
			value: display,
			name: `fz-custom-schedule-interval[${name}][display]`,
		});

		newRow.append(
			nameCell,
			intervalCell,
			displayCell,
			actionCell,
			intervalInput,
			displayInput
		);

		scheduleTable.append(newRow);

		// Update counter
		const currentCount = scheduleTable.children().length;
		$('.fz-schedule-counter').text(`${currentCount} items`);

		$('#fz-schedule-interval').val('');
		$('#fz-schedule-display').val('');
		$('#fz-schedule-name').val('');

		// Re-validate form after clearing fields
		validateScheduleForm();
	});

	$(document).on('click', '.fz-delete-schedule', function (e) {
		e.preventDefault();

		const $button = $(this);
		const $row = $button.closest('tr');
		const scheduleTable = $('.fz-schedules-table tbody');

		$row.fadeOut(300, function () {
			$(this).remove();

			// Update counter
			const currentCount = scheduleTable.children().length;
			$('.fz-schedule-counter').text(`${currentCount} items`);

			// Show empty state and hide table if no schedules left
			if (currentCount === 0) {
				$('.fz-schedules-table').hide();
				$('.fz-empty-state').show();
			}
		});
	});

	/**
	 * Toggle visibility of the email error address field based on email error enabled checkbox.
	 */
	const toggleEmailErrorField = () => {
		const checkbox = $('#feedzy-email-error-enabled');

		$('.fz-log-email-address').toggleClass(
			'fz-hidden',
			!checkbox.is(':checked')
		);
		$('.fz-log-email-freq').toggleClass(
			'fz-hidden',
			!checkbox.is(':checked')
		);
	};

	$('#feedzy-email-error-enabled').on('change', toggleEmailErrorField);
});
