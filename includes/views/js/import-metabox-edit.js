/**
 * Core Library for managing feedzy custom post page js actions.
 * import-metabox-edit.js
 *
 * @since	1.2.0
 * @package
 */
/* global jQuery, ajaxurl, feedzy, tb_show */
(function ($) {
	function scroll_to_class(element_class, removed_height) {
		const scroll_to = $(element_class).offset().top - removed_height;
		if ($(window).scrollTop() !== scroll_to) {
			$('html, body').stop().animate({ scrollTop: scroll_to }, 0);
		}
	}

	function bar_progress(progress_line_object, direction) {
		const number_of_steps = progress_line_object.data('number-of-steps');
		const now_value = progress_line_object.data('now-value');
		let new_value = 0;
		if (direction === 'right') {
			new_value = now_value + 100 / number_of_steps;
		} else if (direction === 'left') {
			new_value = now_value - 100 / number_of_steps;
		}
		progress_line_object
			.attr('style', 'width: ' + new_value + '%;')
			.data('now-value', new_value);
	}

	function add_source() {
		const field_name = $(this).data('field-name');
		const field_tag = $(this).data('field-tag');
		$('[name="feedzy_meta_data[' + field_name + ']"]')
			.data('tagify')
			.addTags(field_tag);
		$('[data-toggle="dropdown"]').parent().removeClass('open');
		$('[name="feedzy_meta_data[' + field_name + ']"]').focus();
		return false;
	}

	/**
	 * Lock a button with a spinner icon.
	 * @param {HTMLElement} button The button to lock.
	 * @return {{ release: () => void }} An object with a release method to unlock the button.
	 */
	function lock_btn_with_spinner(button) {
		const $button = $(button);
		const $icon = $button.find('i');
		const originalClasses = $icon.attr('class');

		$button.prop('disabled', true);
		$icon.attr('class', 'spinner is-active');

		return {
			release() {
				$button.prop('disabled', false);
				$icon.attr('class', originalClasses);
			},
		};
	}

	/**
	 * Add valid URLs to the tag list.
	 *
	 * @this {HTMLElement} this - The button that was clicked to trigger the function.
	 *
	 * @return {Promise<void>}
	 */
	async function add_valid_urls_to_tag_list() {
		const parentFormGroup = $(this).parents(
			'.fz-form-group, .fz-input-group'
		);
		const tags = parentFormGroup.find('.form-control').val();
		const $input = parentFormGroup.find('.form-control');

		if ('' === tags) {
			return false;
		}

		$('.fz-validation-message').remove();

		const { release: unlockButton } = lock_btn_with_spinner(this);

		try {
			const validationResult = await validate_feed_url(tags);

			handle_validation_response(validationResult);

			if (!validationResult.success) {
				return;
			}

			const results = validationResult.data.results || [];

			const validUrls = results
				.filter(({ status }) => status === 'success')
				.map(({ url }) => url);
			const invalidUrls = results
				.filter(({ status }) => status !== 'success')
				.map(({ url }) => url);

			$input.val(invalidUrls.join(', ')); // Keep invalid URLs in the input field.

			parentFormGroup.find('.form-control .tag-list')?.val(''); // Clear existing tags if any.

			// Add valid URLs to the tag list.
			const $tagifyInput =
				parentFormGroup.next('.tag-list').length > 0
					? parentFormGroup
							.next('.tag-list')
							.find(
								'input.fz-tagify-outside, input.fz-tagify--outside'
							)
					: parentFormGroup.find(
							'input.fz-tagify-outside, input.fz-tagify--outside'
						);
			$tagifyInput.data('tagify').addTags(validUrls.join(', '));
		} catch (error) {
			console.error(error);
		} finally {
			unlockButton();
		}
	}

	/**
	 * Validate the feed URL.
	 *
	 * @param {string} feedUrl
	 * @return {Promise<{ success: boolean, message: string, data: { results: Array<{ url: string, status: string, message: string }> } }>} The API response object.
	 */
	async function validate_feed_url(feedUrl) {
		if (!feedUrl) {
			return {
				success: false,
				message:
					window.feedzy.i10n.validation_messages.invalid_feed_url,
			};
		}

		try {
			const response = await $.ajax({
				url: window.feedzy.ajax.url,
				method: 'POST',
				data: {
					nonce: window.feedzy.ajax.security,
					action: 'feedzy_validate_feed',
					feed_url: feedUrl,
				},
			});

			return response;
		} catch (error) {
			return {
				success: false,
				message:
					window.feedzy.i10n.validation_messages
						.error_validating_feed_url +
					': ' +
					(error.responseJSON?.message || error.statusText),
			};
		}
	}

	/**
	 * Handle validation response and display appropriate messages
	 * @param {Object} response - The validation response
	 */
	function handle_validation_response(response) {
		if (!response || !response.data || !response.data.results) {
			showMessage('✗ ' + response?.message, false);
			return;
		}

		let validationSummaryHtml = '<div class="fz-validation-summary">';

		response.data.results.forEach(({ url, status, message }) => {
			const icon =
				'<span class="dashicons dashicons-' +
				(status === 'success'
					? 'yes'
					: status === 'error'
						? 'no'
						: 'warning') +
				'"></span>';

			validationSummaryHtml += `<div class="${'fz-feed-result fz-' + status}">`;
			validationSummaryHtml += `${icon} <span class="fz-feed-url">${url}</span>`;
			validationSummaryHtml += ` - ${message}`;
			validationSummaryHtml += `</div>`;
		});

		validationSummaryHtml += '</div>';

		const hasErrors = response.data.results.some(
			({ status }) => status !== 'success'
		);

		showMessage(validationSummaryHtml, !hasErrors);
	}

	function showMessage(message, autoDismiss = true) {
		const $container = $('.fz-validation-summary');
		if (!$container.length) {
			return;
		}

		$container.find('.fz-validation-message').remove();

		const $message = $('<div>', {
			class: 'fz-validation-message',
			html: message,
		});

		$container.append($message);
		if (autoDismiss) {
			$message.delay(5000).fadeOut(300, () => $message.remove());
		} else {
			const $closeButton = $('<button>', {
				type: 'button',
				class: 'button button-primary',
				text: '✕',
			});

			$message.append($closeButton);
			$closeButton.on('click', function (e) {
				e.preventDefault();
				$(this)
					.parent()
					.fadeOut(300, () => $(this).remove());
			});
		}
	}

	function append_tag() {
		const field_name = $(this).data('field-name');
		const field_tag = $(this).data('field-tag');
		if (
			field_name === 'import_post_date' ||
			field_name === 'import_post_featured_img'
		) {
			$('[name="feedzy_meta_data[' + field_name + ']"]')
				.data('tagify')
				.removeAllTags();
			$('[name="feedzy_meta_data[' + field_name + ']"]')
				.data('tagify')
				.addTags('[#' + field_tag + ']');
		} else if (field_name === 'import_post_content') {
			$('[name="feedzy_meta_data[' + field_name + ']"]').focus();
			$('[name="feedzy_meta_data[' + field_name + ']"]')
				.data('tagify')
				.addTags('[#' + field_tag + ']');
			return false;
		} else {
			$('[name="feedzy_meta_data[' + field_name + ']"]')
				.data('tagify')
				.addTags('[#' + field_tag + ']');
		}
		$('[data-toggle="dropdown"]').parent().removeClass('open');
		$('[name="feedzy_meta_data[' + field_name + ']"]').focus();
		return false;
	}

	function remove_row() {
		$(this).closest('.key-value-item').remove();
		return false;
	}

	function new_row() {
		let html_row = '';
		html_row = $('#new_field_tpl').html();
		$('.custom_fields').append(html_row);
		$('.btn.btn-remove-fields').on('click', remove_row);
		initCustomFieldAutoComplete();
		document.dispatchEvent(new Event('feedzy_new_row_added'));
		return false;
	}

	function update_status() {
		const toggle = $(this);
		const post_id = $(this).val();
		const status = $(this).is(':checked');

		const data = {
			security: feedzy.ajax.security,
			action: 'feedzy',
			_action: 'import_status',
			id: post_id,
			status,
		};

		showSpinner(toggle);
		$.ajax({
			url: ajaxurl,
			data,
			method: 'POST',
			success(data) {
				if (!data.success && status) {
					toggle
						.parents('tr')
						.find('td.feedzy-source')
						.find('.feedzy-error-critical')
						.remove();
					toggle
						.parents('tr')
						.find('td.feedzy-source')
						.append($(data.data.msg));
					toggle.prop('checked', false);
				}
			},
			complete() {
				hideSpinner(toggle);
			},
		});
		return true;
	}

	function toggle_dropdown() {
		$('.dropdown.open').not($(this).parent()).removeClass('open');
		$(this).parent().toggleClass('open');
	}

	function in_array($key, $array) {
		if (
			typeof $array === 'undefined' ||
			$array.length === 0 ||
			$key === ''
		) {
			return false;
		}
		for (let i = 0; i < $array.length; i++) {
			if ($array[i] === $key) {
				return true;
			}
		}
		return false;
	}

	function update_taxonomy() {
		const selected = $(this).val();
		let tax_selected = $(this).data('tax');
		const custom_tag = $(this).data('custom-tag');
		if (typeof tax_selected !== 'undefined') {
			tax_selected = tax_selected.split(',');
		} else {
			tax_selected = '';
		}

		const data = {
			security: feedzy.ajax.security,
			action: 'feedzy',
			_action: 'get_taxonomies',
			post_type: selected,
		};

		$('#feedzy_post_terms')
			.html($('#loading_select_tpl').html())
			.trigger('chosen:updated');

		$.post(
			ajaxurl,
			data,
			function (response) {
				let show_terms = true;

				let options = '';
				if (response.length !== 0) {
					$.each(response, function (index, terms) {
						if (terms) {
							let groupName = index;
							if ('category' === groupName) {
								groupName = 'Category';
							} else if ('post_tag' === groupName) {
								groupName = 'Tag';
							}
							options += '<optgroup label="' + groupName + '">';
							$.each(terms, function (i, term) {
								let sel_option = '';
								if (in_array(index + '_' + i, tax_selected)) {
									sel_option = 'selected';
									if (
										$('#feedzy_post_terms').hasClass(
											'fz-chosen-custom-tag'
										)
									) {
										const removeItem = index + '_' + i;
										tax_selected = $.grep(
											tax_selected,
											function (value) {
												return value != removeItem;
											}
										);
									}
								}
								options +=
									'<option value="' +
									index +
									'_' +
									i +
									'" ' +
									sel_option +
									'>' +
									term +
									'</option>';
							});
							options += '</optgroup>';
						}
					});
					tax_selected = tax_selected.filter(function (item) {
						return '' !== item;
					});
					const selected_tax_length = tax_selected.length;
					if (!feedzy.i10n.is_pro) {
						options +=
							'<optgroup class="feedzy-pro-terms" label="Pro">';
					}
					options +=
						'<option class="feedzy-separator">separator</option>';
					$.each(custom_tag, function (key, customTag) {
						let is_selected = '';
						if (in_array(key, tax_selected)) {
							is_selected = 'selected';
							const index = tax_selected.indexOf(key);

							if (-1 !== index) {
								tax_selected.splice(index, 1);
							}
						}
						options +=
							'<option class="' +
							(!feedzy.i10n.is_pro ? 'feedzy-pro-term' : '') +
							'" value="' +
							key +
							'"' +
							is_selected +
							'>' +
							customTag +
							'</option></a>';
					});

					if (
						selected_tax_length > 0 &&
						$('#feedzy_post_terms').hasClass('fz-chosen-custom-tag')
					) {
						$.each(tax_selected, function (index, customTag) {
							options +=
								'<option value="' +
								customTag +
								'" selected>' +
								customTag +
								'</option>';
						});
					}

					if (!feedzy.i10n.is_pro) {
						options += '</optgroup>';
					}
				} else {
					show_terms = false;
					options = $('#empty_select_tpl').html();
				}

				$('#feedzy_post_terms').html(options).trigger('chosen:updated');
			},
			'json'
		);
		return true;
	}

	function htmlEntities(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function escapeSelector(string) {
		return String(string).replace(
			/([ !"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~])/g,
			'\\$1'
		);
	}

	$(document).ready(function () {
		initImportScreen();
		initSummary();
		initCustomFieldAutoComplete();
		feedzyAccordion();
		feedzyTab();
		feedzyMediaUploader();
		initRemoveFallbackImageBtn();
		initFallbackImageOptions();
	});

	function initImportScreen() {
		$('button.btn-submit').on('click', function (e) {
			$(window).unbind('beforeunload');
			$('#custom_post_status').val($(this).val());
			e.preventDefault();
			$('#post').submit();
			return false;
		});

		$('.feedzy-keyword-filter').on('keyup keypress', function (e) {
			const keyCode = e.keyCode || e.which;
			const addTagBtn = $(this)
				.parents('.fz-input-icon')
				.find('.add-outside-tags');

			if ('' === $(this).val()) {
				addTagBtn.attr('disabled', true);
			} else if (addTagBtn.hasClass('fz-plus-btn')) {
				addTagBtn.removeAttr('disabled');
			}

			if (keyCode === 13) {
				e.preventDefault();
				addTagBtn.trigger('click');
				$(this).val('');
				return false;
			}
		});

		$('a.dropdown-item:not(.source,[data-action_popup])').on(
			'click',
			append_tag
		);
		$('.add-outside-tags').on('click', add_valid_urls_to_tag_list);
		$('a.dropdown-item.source').on('click', add_source);
		$(document).on('click', '.btn-remove-fields', remove_row);
		$('#new_custom_fields').on('click', new_row);

		$('.feedzy-toggle').on('click', update_status);
		$('.dropdown-toggle').on('click', toggle_dropdown);
		if ($('#toplevel_page_feedzy-admin-menu li').hasClass('current')) {
			$('#toplevel_page_feedzy-admin-menu')
				.removeClass('wp-not-current-submenu')
				.addClass('wp-has-current-submenu');
		}
		$('.feedzy-chosen').chosen({ width: '100%' });
		$('#feedzy_post_type').on('change', update_taxonomy);
		$('#feedzy_post_status').trigger('change');

		// Add magic tag support for post taxonomy field.
		$('#feedzy_post_terms.fz-chosen-custom-tag').on(
			'chosen:no_results',
			function () {
				const select = $(this);
				const search = select
					.siblings('.chosen-container')
					.find('.chosen-choices .search-field:last input');
				const text = htmlEntities(search.val());
				// dont add if it already exists
				if (
					!select.find(
						'option[value=' + escapeSelector(search.val()) + ']'
					).length
				) {
					const btn = $(
						'<li class="active-result highlighted">' +
							text +
							'</li>'
					);
					btn.on('mousedown mouseup click', function (e) {
						const arr = select.val();
						select.html(
							select.html() +
								"<option value='" +
								text +
								"'>" +
								text +
								'</option>'
						);
						select.val(arr.concat([text]));
						select
							.trigger('chosen:updated')
							.trigger('chosen:close');
						// search.focus();
						e.stopImmediatePropagation();
						return false;
					});
					search.on('keydown', function (e) {
						if (e.which == 13) {
							btn.click();
							return false;
						}
					});
					select
						.siblings('.chosen-container')
						.find('.no-results')
						.replaceWith(btn);
				} else {
					select
						.siblings('.chosen-container')
						.find('.no-results')
						.replaceWith('');
				}
			}
		);

		// Add magic tag support for post taxonomy field.
		$('#feedzy_post_author.fz-chosen-custom-tag').on(
			'chosen:no_results',
			function () {
				const select = $(this);
				const search = select
					.siblings('.chosen-container')
					.find('.chosen-search-input');
				const text = htmlEntities(search.val().replace(/\s+/g, ''));
				// dont add if it already exists
				if (
					!select.find(
						'option[value=' +
							escapeSelector(search.val().replace(/\s+/g, '')) +
							']'
					).length
				) {
					const btn = $(
						'<li class="active-result highlighted">' +
							text +
							'</li>'
					);
					btn.on('mousedown mouseup click', function (e) {
						const arr = select.val() || [];
						select.append(
							"<option value='" +
								text +
								"' selected>" +
								text +
								'</option>'
						);
						select
							.trigger('chosen:updated')
							.trigger('chosen:close');
						e.stopImmediatePropagation();
						return false;
					});
					search.on('keydown', function (e) {
						if (e.which == 13) {
							btn.click();
							return false;
						}
					});
					select
						.siblings('.chosen-container')
						.find('.no-results')
						.replaceWith(btn);
					select
						.siblings('.chosen-container')
						.find('.chosen-results')
						.append(
							'<li class="helper-text">' +
								feedzy.i10n.author_helper +
								'</li>'
						);
				} else {
					select
						.siblings('.chosen-container')
						.find('.no-results')
						.replaceWith('');
				}
			}
		);

		/*
         Form
         */
		$('.f1 fieldset:first').fadeIn('slow');

		// next step
		$('.f1 .btn-next').on('click', function () {
			const parent_fieldset = $(this).parents('fieldset');
			const next_step = true;
			// navigation steps / progress steps
			const current_active_step = $(this)
				.parents('.f1')
				.find('.f1-step.active');
			const progress_line = $(this)
				.parents('.f1')
				.find('.f1-progress-line');

			// fields validation
			if (next_step) {
				parent_fieldset.fadeOut(400, function () {
					// change icons
					current_active_step
						.removeClass('active')
						.addClass('activated')
						.next()
						.addClass('active');
					// progress bar
					bar_progress(progress_line, 'right');
					// show next step
					$(this).next().fadeIn();
					// scroll window to beginning of the form
					scroll_to_class($('.f1'), 20);
				});
			}
		});

		// previous step
		$('.f1 .btn-previous').on('click', function () {
			// navigation steps / progress steps
			const current_active_step = $(this)
				.parents('.f1')
				.find('.f1-step.active');
			const progress_line = $(this)
				.parents('.f1')
				.find('.f1-progress-line');

			$(this)
				.parents('fieldset')
				.fadeOut(400, function () {
					// change icons
					current_active_step
						.removeClass('active')
						.prev()
						.removeClass('activated')
						.addClass('active');
					// progress bar
					bar_progress(progress_line, 'left');
					// show previous step
					$(this).prev().fadeIn();
					// scroll window to beginning of the form
					scroll_to_class($('.f1'), 20);
				});
		});

		$('#preflight').on('click', function (e) {
			e.preventDefault();
			const $fields = {};
			// collect all elements.
			$('#feedzy-import-form')
				.find(':input')
				.each(function (index, element) {
					if ('undefined' === typeof $(element).attr('name')) {
						return;
					}
					$fields[$(element).attr('name')] = $(element).val();
				});
			tb_show(feedzy.i10n.dry_run_title, 'TB_inline?');
			$('#TB_ajaxContent').html(feedzy.i10n.dry_run_loading);
			$.ajax({
				url: ajaxurl,
				method: 'post',
				data: {
					security: feedzy.ajax.security,
					fields: $.param($fields),
					action: 'feedzy',
					_action: 'dry_run',
				},
				success(data) {
					$('#TB_ajaxContent').addClass('loaded');
					$('#TB_ajaxContent div').html(data.data.output);
				},
			});
		});

		// Click on pro/upgrade button.
		$(document).on('click', '.only-pro-inner a', function (e) {
			if ($(this).parents('.only-pro-content').css('opacity') == 0) {
				e.preventDefault();
			}
		});

		// Click to hide upsell notice.
		$(document).on('click', '.remove-alert', function (e) {
			const upSellNotice = $(this).parents('.upgrade-alert');
			upSellNotice.fadeOut(500, function () {
				upSellNotice.remove();
				jQuery.post(ajaxurl, {
					security: feedzy.ajax.security,
					action: 'feedzy',
					_action: 'remove_upsell_notice',
				});
			});
		});

		// Save/Update Post Title.
		$(document).on('input', '#post_title', function (e) {
			jQuery('.fz-form-wrap input[name="post_title"]').val($(this).val());
		});

		// Close dropdown when user click on document.
		$('body').on('click', function (e) {
			if ($(e.target).parent('.dropdown-item').length > 0) {
				return;
			}
			if ($(e.target).attr('disabled')) {
				return;
			}
			if ($(e.target).hasClass('dashicons-arrow-down-alt2')) {
				return;
			}
			if ($(e.target).hasClass('dashicons-plus-alt2')) {
				return;
			}
			if ($(e.target).hasClass('dropdown-toggle')) {
				return;
			}
			$('.dropdown.open').removeClass('open');
		});

		// Tagify for normal textbox.
		$(
			'.fz-input-tagify:not(.fz-tagify-image):not([name="feedzy_meta_data[import_post_title]"]):not([name="feedzy_meta_data[import_post_content]"])'
		).tagify({
			editTags: false,
			originalInputValueFormat(valuesArr) {
				return valuesArr
					.map(function (item) {
						return item.value;
					})
					.join(', ');
			},
		});

		// Tagify for normal mix content field.
		$('.fz-tagify-image').tagify({
			mode: 'mix',
			editTags: true,
			userInput: true,
			addTagOn: [],
			templates: {
				tag(tagData) {
					try {
						let decodeTagData = decodeURIComponent(tagData.value);
						const isEncoded =
							typeof tagData.value === 'string' &&
							decodeTagData !== tagData.value;
						let tagLabel = tagData.value;
						if (isEncoded) {
							decodeTagData = JSON.parse(decodeTagData);
							decodeTagData = decodeTagData[0] || {};
							tagLabel = decodeTagData.tag.replaceAll('_', ' ');
							tagData['data-actions'] = tagData.value;
							tagData['data-field_id'] = 'fz-image-action-tags';
						}
						return `
						<tag title='${tagLabel}' contenteditable='false' spellcheck="false" class='tagify__tag ${isEncoded ? 'fz-content-action' : ''}'>
							<x title='remove tag' class='tagify__tag__removeBtn'></x>
							<div>
								<span class='tagify__tag-text'>${tagLabel}</span>
								${
									tagData['data-actions']
										? `<a href="javascript:;" class="tagify__filter-icon" ${this.getAttributes(tagData)}></a>`
										: ''
								}
							</div>
						</tag>`;
					} catch (err) {}
				},
			},
		});

		// Tagify for normal mix content field.
		const mixContent = $(
			'.fz-input-tagify[name="feedzy_meta_data[import_post_content]"]:not(.fz-tagify-image)'
		).tagify({
			mode: 'mix',
			editTags: false,
			templates: {
				tag(tagData) {
					try {
						let decodeTagData = decodeURIComponent(tagData.value);
						const isEncoded =
							typeof tagData.value === 'string' &&
							decodeTagData !== tagData.value;
						let tagLabel = tagData.value;
						if (isEncoded) {
							decodeTagData = JSON.parse(decodeTagData);
							decodeTagData = decodeTagData[0] || {};
							tagLabel = decodeTagData.tag.replaceAll('_', ' ');
							tagData['data-actions'] = tagData.value;
							tagData['data-field_id'] = 'fz-content-action-tags';
						}
						return `
						<tag title='${tagLabel}' contenteditable='false' spellcheck="false" class='tagify__tag ${isEncoded ? 'fz-content-action' : ''}'>
							<x title='remove tag' class='tagify__tag__removeBtn'></x>
							<div>
								<span class='tagify__tag-text'>${tagLabel}</span>
								${
									tagData['data-actions']
										? `<a href="javascript:;" class="tagify__filter-icon" ${this.getAttributes(tagData)}></a>`
										: ''
								}
							</div>
						</tag>`;
					} catch (err) {}
				},
			},
		});

		$(
			'.fz-input-tagify[name="feedzy_meta_data[import_post_title]"]:not(.fz-tagify-image)'
		).tagify({
			mode: 'mix',
			editTags: false,
			templates: {
				tag(tagData) {
					try {
						let decodeTagData = decodeURIComponent(tagData.value);
						const isEncoded =
							typeof tagData.value === 'string' &&
							decodeTagData !== tagData.value;
						let tagLabel = tagData.value;
						if (isEncoded) {
							decodeTagData = JSON.parse(decodeTagData);
							decodeTagData = decodeTagData[0] || {};
							tagLabel = decodeTagData.tag.replaceAll('_', ' ');
							tagData['data-actions'] = tagData.value;
							tagData['data-field_id'] = 'fz-title-action-tags';
						}
						return `
						<tag title='${tagLabel}' contenteditable='false' spellcheck="false" class='tagify__tag ${isEncoded ? 'fz-content-action' : ''}'>
							<x title='remove tag' class='tagify__tag__removeBtn'></x>
							<div>
								<span class='tagify__tag-text'>${tagLabel}</span>
								${
									tagData['data-actions']
										? `<a href="javascript:;" class="tagify__filter-icon" ${this.getAttributes(tagData)}></a>`
										: ''
								}
							</div>
						</tag>`;
					} catch (err) {
						console.error(err);
					}
				},
			},
		});

		// Tagify for outside tags with allowed duplicates.
		$('.fz-tagify-outside')
			.tagify({
				editTags: true,
				duplicates: true,
				userInput: false,
				originalInputValueFormat(valuesArr) {
					return valuesArr
						.map(function (item) {
							return item.value;
						})
						.join(', ');
				},
			})
			.on('add', function (e) {
				const target = $(e.target);
				target.parents('.tag-list').removeClass('hidden');
			})
			.on('removeTag', function (e, tagData) {
				const target = $(e.target);
				const emptyTags = target
					.parents('.tag-list')
					.find('.tagify--empty').length;
				if (emptyTags) {
					target.parents('.tag-list').addClass('hidden');
				}
			});

		// Tagify for outside tags with not allowed duplicates.
		$('.fz-tagify--outside')
			.tagify({
				editTags: true,
				userInput: false,
				originalInputValueFormat(valuesArr) {
					return valuesArr
						.map(function (item) {
							return item.value;
						})
						.join(', ');
				},
			})
			.on('add', function (e) {
				const target = $(e.target);
				target.parents('.tag-list').removeClass('hidden');
			})
			.on('removeTag', function (e, tagData) {
				const target = $(e.target);
				const tagList = target.parents('.tag-list');
				if (tagList.find('.tagify__tag').length === 0) {
					tagList.addClass('hidden');
					tagList.find('input:text').val('');
				}
			});

		$(document).on('change', 'input#remove-duplicates', function () {
			if ($(this).is(':checked')) {
				$('input#feedzy_mark_duplicate').attr('disabled', false);
			} else {
				$('input#feedzy_mark_duplicate').attr('disabled', true);
			}
		});

		$(document).on(
			'input',
			'input[name="custom_vars_value[]"]',
			function () {
				$(this)
					.next('.fz-action-icon')
					.toggleClass('disabled', $(this).val() === '');

				$(this)
					.parent('.fz-form-group')
					.find('input:hidden')
					.attr('disabled', $(this).val() === '');
			}
		);

		// Append import button.
		$(feedzy.i10n.importButton).insertAfter(
			$('.page-title-action', document)
		);
		$($('.page-title-action', document)).wrapAll(
			'<div class="fz-header-action"></div>'
		);

		$('.fz-export-import-btn.only-pro, .fz-export-btn-pro').on(
			'click',
			function (e) {
				e.preventDefault();
				$('#fz_import_export_upsell').show();
			}
		);

		$('#fz_import_export_upsell').on('click', '.close-modal', function (e) {
			e.preventDefault();
			$('#fz_import_export_upsell').hide();
		});

		$(document).on(
			'click',
			'.fz-export-import-btn:not(.only-pro)',
			function (e) {
				e.preventDefault();
				if ($('.fz-import-field').length === 0) {
					const importField = $('#fz_import_field_section').html();
					$(importField).insertAfter(
						$(this).parents('div.wrap').find('.wp-header-end')
					);
				}
				$('.fz-import-field').toggleClass('hidden');
			}
		);

		const url = new URL(window.location.href);
		if (url.searchParams.has('imported')) {
			url.searchParams.delete('imported');
			history.replaceState(history.state, '', url.href);
		}
	}

	function initSummary() {
		$('tr.type-feedzy_imports').each(function (i, e) {
			const $lastRunData = $(e)
				.find('script.feedzy-last-run-data')
				.html();
			$($lastRunData).insertAfter(e);
		});

		// pop-ups for informational text
		$('.feedzy-dialog').dialog({
			modal: true,
			autoOpen: false,
			height: 400,
			classes: {
				'ui-dialog-content': 'feedzy-dialog-content',
			},
			width: 500,
			buttons: [
				{
					text: feedzy.i10n.okButton,
					click() {
						$(this).dialog('close');
					},
				},
			],
		});

		// Error logs popup.
		$('.feedzy-errors-dialog').dialog({
			modal: true,
			autoOpen: false,
			height: 400,
			width: 500,
			buttons: [
				{
					text: feedzy.i10n.clearLogButton,
					class: 'button button-primary feedzy-clear-logs',
					click(event) {
						const clearButton = $(event.target);
						const dialogBox = $(this);
						$(
							'<span class="feedzy-spinner spinner is-active"></span>'
						).insertAfter(clearButton);
						clearButton.attr('disabled', true);
						$.post(
							ajaxurl,
							{
								security: feedzy.ajax.security,
								id: $(this).attr('data-id'),
								action: 'feedzy',
								_action: 'clear_error_logs',
							},
							function () {
								clearButton.next('.feedzy-spinner').remove();

								dialogBox
									.find('.feedzy-error.feedzy-api-error')
									.html(
										'<div class="notice notice-success fz-notice"><p>' +
											feedzy.i10n.removeErrorLogsMsg +
											'</p></div>'
									);
							}
						);
					},
				},
				{
					text: window.feedzy.i10n.goToLogsTab,
					class: 'button button-secondary',
					click: () => {
						window.location.href = window.feedzy.pages.logs;
					},
				},
				{
					text: feedzy.i10n.okButton,
					class: 'alignright',
					click() {
						$(this).dialog('close');
					},
				},
			],
		});

		$('.feedzy-dialog-open').on('click', function (e) {
			e.preventDefault();
			const dialog = $(this).attr('data-dialog');
			$('.' + dialog).dialog('open');
		});

		// run now.
		$('.feedzy-run-now').on('click', function (e) {
			e.preventDefault();
			const button = $(this);
			button.val(feedzy.i10n.importing);

			const numberRow = button
				.parents('tr')
				.find('~ tr.feedzy-import-status-row:first')
				.find('td tr:first');
			numberRow.find('td').hide();
			numberRow
				.find('td:first')
				.addClass('feedzy_run_now_msg')
				.attr('colspan', 5)
				.html(feedzy.i10n.importing)
				.show();

			$.ajax({
				url: ajaxurl,
				method: 'post',
				data: {
					security: feedzy.ajax.security,
					id: $(this).attr('data-id'),
					action: 'feedzy',
					_action: 'run_now',
				},
				success(data) {
					if (data.data.import_success) {
						numberRow.find('td:first').addClass('import_success');
					}
					numberRow.find('td:first').html(data.data.msg);
				},
				complete() {
					button.val(feedzy.i10n.run_now);
				},
			});
		});

		// toggle the errors div to expand/collapse
		$('td.column-feedzy-last_run .feedzy-api-error').on(
			'click',
			function () {
				if ($(this).hasClass('expand')) {
					$(this).removeClass('expand');
				} else {
					$(this).addClass('expand');
				}
			}
		);

		// purge data ajax call
		$('.feedzy_purge').on('click', function (e) {
			e.preventDefault();
			const element = $(this);
			const deleteImportedPosts = confirm(
				feedzy.i10n.delete_post_message
			);
			if (!deleteImportedPosts) {
				return;
			}
			showSpinner(element);
			$.ajax({
				url: ajaxurl,
				method: 'post',
				data: {
					security: feedzy.ajax.security,
					id: $(this).find('a').attr('data-id'),
					action: 'feedzy',
					_action: 'purge',
					del_imported_posts: deleteImportedPosts,
				},
				success() {
					location.reload();
				},
				complete() {
					hideSpinner(element);
				},
			});
		});
	}

	function initCustomFieldAutoComplete() {
		$('input[name="custom_vars_key[]"]')
			.autocomplete({
				minLength: 0,
				source(request, response) {
					jQuery.post(
						ajaxurl,
						{
							security: feedzy.ajax.security,
							action: 'feedzy',
							_action: 'fetch_custom_fields',
							post_type: $('#feedzy_post_type').val(),
							search_key: request.term,
						},
						function (res) {
							if (res.success) {
								response(res.data);
							} else {
								response([
									{
										label: res.data.not_found_msg,
										value: 'not_found_msg',
									},
								]);
							}
						}
					);
				},
				select(event, ui) {
					if ('not_found_msg' === ui.item.value) {
						setTimeout(function () {
							$(event.target).val('');
						});
					}
				},
				focus() {
					if ($(this).autocomplete('widget').is(':visible')) {
						return;
					}
					$(this).autocomplete('search', $(this).val());
				},
			})
			.on('click', function () {
				$(this).keydown();
			});
	}

	function showSpinner(el) {
		el.parent().find('.feedzy-spinner').addClass('is-active');
	}
	function hideSpinner(el) {
		el.parent().find('.feedzy-spinner').removeClass('is-active');
	}

	function feedzyAccordion() {
		$(
			'.feedzy-accordion .feedzy-accordion-item .feedzy-accordion-item__button'
		).on('click', function () {
			const current_item = $(this).parents();
			$(
				'.feedzy-accordion .feedzy-accordion-item .feedzy-accordion-item__content'
			).each(function (i, el) {
				if ($(el).parent().is(current_item)) {
					$(el).prev().toggleClass('is-active');
					$(el).slideToggle();
					$(this).toggleClass('is-active');
				} else {
					$(el).prev().removeClass('is-active');
					$(el).slideUp();
					$(this).removeClass('is-active');
				}
			});
			if ($('#fz-import-map-content').hasClass('is-active')) {
				$('#feedzy_post_type').trigger('change');
			}
		});
		$(
			'.feedzy-accordion .feedzy-accordion-item .feedzy-accordion-item__button'
		)
			.first()
			.parent()
			.addClass('is-active');
		$(
			'.feedzy-accordion .feedzy-accordion-item > .feedzy-accordion-item__content'
		)
			.first()
			.show()
			.addClass('is-active');
	}

	function feedzyTab() {
		$('.fz-tabs-menu a').click(function () {
			$(this).parents('.fz-tabs-menu').find('a').removeClass('active');
			$(this).addClass('active');
			const tagid = $(this).data('id');
			$(this)
				.parents('.feedzy-accordion-item__content')
				.find('.fz-tabs-content .fz-tab-content')
				.hide();
			$('#' + tagid).show();
		});
		$('.fz-tabs-content .fz-tab-content').hide();
		$('.fz-tabs-menu').each(function () {
			$(this).find('a').first().addClass('active');
		});
		$('.fz-tabs-content').each(function () {
			$(this).find('.fz-tab-content').first().show();
		});
	}

	function feedzyMediaUploader() {
		// on upload button click
		$('body').on('click', '.feedzy-open-media', function (e) {
			e.preventDefault();
			const button = $(this);
			const wpMediaUploader = wp.media({
				title: feedzy.i10n.media_iframe_title,
				library: {
					type: 'image',
				},
				button: {
					text: feedzy.i10n.media_iframe_button,
				},
				multiple: true,
			});

			wpMediaUploader.on('select', function () {
				// it also has "open" and "close" events
				const selectedAttachments = wpMediaUploader
					.state()
					.get('selection');
				const countSelected = selectedAttachments?.toJSON()?.length;
				button
					.parents('.fz-form-group')
					.find('.feedzy-media-preview')
					.remove();
				// Display image preview when a single image is selected.
				if (1 === countSelected) {
					const attachment = selectedAttachments.first().toJSON();
					let attachmentUrl = attachment.url;
					if (attachment.sizes?.thumbnail) {
						attachmentUrl = attachment.sizes.thumbnail.url;
					}
					if ($('.feedzy-media-preview').length) {
						$('.feedzy-media-preview')
							.find('img')
							.attr('src', attachmentUrl);
					} else {
						$(
							'<div class="fz-form-group mb-20 feedzy-media-preview"><img src="' +
								attachmentUrl +
								'"></div>'
						).insertBefore(button.parent());
					}
				} else {
					$(
						'<div class="fz-form-group mb-20 feedzy-media-preview fz-fallback-images">' +
							selectedAttachments
								?.toJSON()
								?.map(({ url, sizes }) => {
									if (sizes?.thumbnail) {
										url = sizes.thumbnail.url;
									}
									return `<img width="150" height="150" src="${url}" class="attachment-thumbnail size-thumbnail" alt="" decoding="async" loading="lazy">`;
								})
								.join('') +
							'</div>'
					).insertBefore(button.parent());
				}
				// Get all selected attachment ids.
				const ids = selectedAttachments
					.map(function (attachment) {
						return attachment.id;
					})
					.join(',');

				button
					.parent()
					.find('.feedzy-remove-media')
					.addClass('is-show');
				button.parent().find('input:hidden').val(ids).trigger('change');
				$('.feedzy-open-media').html(feedzy.i10n.action_btn_text_2);
			});

			wpMediaUploader.on(' open', function () {
				const selectedVal = button.parent().find('input:hidden').val();
				if ('' === selectedVal) {
					return;
				}
				const selection = wpMediaUploader.state().get('selection');

				selectedVal.split(',').forEach(function (id) {
					const attachment = wp.media.attachment(id);
					attachment.fetch();
					selection.add(attachment ? [attachment] : []);
				});
			});

			wpMediaUploader.open();
		});

		$(document).on('click', '.feedzy-images-selected', function (e) {
			$(this)
				.parents('.fz-form-group')
				.find('.feedzy-open-media')
				.trigger('click');
			e.preventDefault();
		});
	}

	function initFallbackImageOptions() {
		$('#use-inherited-thumbnail').on('change', function () {
			if ($(this).is(':checked')) {
				$('#custom-fallback-image-section').hide();
				$('#inherited-fallback-image-section').show();
				$('#feed-post-default-thumbnail').val('');
			} else {
				$('#custom-fallback-image-section').show();
				$('#inherited-fallback-image-section').hide();
			}
		});

		$('.feedzy-remove-media').on('click', function () {
			if (
				$('#use-inherited-thumbnail').length &&
				$('#use-inherited-thumbnail').is(':checked')
			) {
				return false;
			}
		});

		$('input[name="feedzy_meta_data[fallback_image_option]"]').on(
			'change',
			function () {
				const selectedValue = $(this).val();
				const thumbnailValue = $(
					'input[name="feedzy_meta_data[default_thumbnail_id]"]'
				).val();
				const hasImagePreview =
					$('#custom-fallback-section .feedzy-media-preview').length >
					0;
				const hasThumbnails =
					(thumbnailValue &&
						thumbnailValue !== '' &&
						thumbnailValue !== '0') ||
					hasImagePreview;

				if (selectedValue === 'custom') {
					$('#custom-fallback-section').show();
					$('#general-fallback-preview').hide();

					if (hasThumbnails) {
						$('.feedzy-open-media').html(
							feedzy.i10n.action_btn_text_2
						);
						$('.feedzy-remove-media').addClass('is-show');
					} else {
						$('.feedzy-open-media').html(
							feedzy.i10n.action_btn_text_1
						);
						$('.feedzy-remove-media').removeClass('is-show');
					}
				} else if (selectedValue === 'general') {
					$('#custom-fallback-section').hide();
					$('#general-fallback-preview').show();
					$(
						'input[name="feedzy_meta_data[default_thumbnail_id]"]'
					).val('');
				}
			}
		);

		$(
			'input[name="feedzy_meta_data[fallback_image_option]"]:checked'
		).trigger('change');

		initNewPostActions();
	}
})(jQuery, feedzy);

/**
 * Initialize the remove fallback image button from General Feed Settings tab.
 */
function initRemoveFallbackImageBtn() {
	const removeFallbackImage = document.querySelector('.feedzy-remove-media');
	removeFallbackImage?.addEventListener('click', (e) => {
		e.preventDefault();

		// Reset the image preview.
		document.querySelector('.feedzy-media-preview').remove();
		removeFallbackImage.classList.remove('is-show');

		// Reset the input.
		document.querySelector(
			'input[name="feedzy_meta_data[default_thumbnail_id]"]'
		).value = '0';
		document.querySelector('.feedzy-open-media').innerHTML =
			feedzy.i10n.action_btn_text_1;
	});
}

/**
 * Run actions when creating a new post.
 */
function initNewPostActions() {
	if (-1 === window.location.href.indexOf('post-new.php')) {
		return;
	}

	// Focus on the post title field when creating a new post.
	const postTitle = document.querySelector('#post_title');
	if (postTitle) {
		postTitle.focus();
		postTitle.select();
	}
}
