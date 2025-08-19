/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: https://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 */

jQuery(function ($) {
	$('#smartwizard').smartWizard({
		transition: {
			animation: 'fade', // Animation effect on navigation, none|fade|slideHorizontal|slideVertical|slideSwing|css(Animation CSS class also need to specify)
			speed: '400', // Animation speed. Not used if animation is 'css'
		},
		lang: {
			// Language variables for button
			next: window.feedzySetupWizardData.nextButtonText,
			previous: window.feedzySetupWizardData.backButtonText,
		},
		keyboard: {
			keyNavigation: false, // Enable/Disable keyboard navigation(left and right keys are used if enabled)
		},
		anchor: {
			enableNavigation: false, // Enable/Disable anchor navigation
			enableNavigationAlways: false, // Activates all anchors clickable always
		},
		style: {
			// CSS Class settings
			btnCss: '',
			btnNextCss: 'btn-primary next-btn',
			btnPrevCss: 'btn-light',
		},
	});

	// Use demo feed link.
	$(document).on('click', '.feed-demo-link', function (e) {
		const feedUrl = $(this).attr('href');
		$(this).parents('.fz-row').find('input:text').val(feedUrl);

		$('[data-step_number="2"], #preflight').removeClass('disabled');
		return false;
	});

	// click to open accordion.
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
	});

	// Click to next step.
	$(document).on(
		'click',
		'.btn-primary:not(.next-btn,.fz-wizard-feed-import,.fz-wizard-draft-page,.fz-subscribe)',
		function (e) {
			const stepNumber = $(this).data('step_number');

			switch (stepNumber) {
				case 1:
					if ($('.fz-radio-btn').is(':checked')) {
						const urlParams = new URLSearchParams(
							window.location.search
						);
						urlParams.set(
							'integrate-with',
							$('.fz-radio-btn:checked').val()
						);
						window.location.hash = '#step-2';
						window.location.search = urlParams;
					}
					break;
				case 2:
					const feedSource = $('#wizard_feed_source').val();
					if ('' !== feedSource) {
						const integrateWith = $('.fz-radio-btn:checked').val();

						$('#step-2').find('.spinner').addClass('is-active');
						$('#step-2')
							.find('.fz-error-notice')
							.addClass('hidden');

						$.post(
							window.feedzySetupWizardData.ajax.url,
							{
								action: 'feedzy_wizard_step_process',
								feed: feedSource,
								security:
									window.feedzySetupWizardData.ajax.security,
								integrate_with: integrateWith,
								step: 'step_2',
							},
							function (res) {
								if (1 === res.status) {
									const accordionItem = $(
										'#feed_source'
									).find(
										'.feedzy-accordion-item__button.hidden'
									);
									accordionItem
										.removeClass('hidden')
										.prev('.feedzy-accordion-item__button')
										.addClass('hidden');
									$(
										'#feed_source .feedzy-accordion-item__content'
									).addClass('hidden');
									$('#step-2')
										.find('.spinner')
										.removeClass('is-active');

									if ('feed' === integrateWith) {
										$('#feed_import').removeClass('hidden');
									} else if ('shortcode' === integrateWith) {
										$('#shortcode').removeClass('hidden');
										$('#basic_shortcode').val(
											$('#basic_shortcode')
												.val()
												.replace(
													'{{feed_source}}',
													feedSource
												)
										);
									} else {
										$('#smartwizard').smartWizard('next');
									}
								} else if ('' !== res.message) {
									$('#step-2')
										.find('.spinner')
										.removeClass('is-active');
									$('#step-2')
										.find('.fz-error-notice')
										.html(res.message)
										.removeClass('hidden');
								}
							}
						).fail(function () {
							$('#step-2')
								.find('.spinner')
								.removeClass('is-active');
						});
						return false;
					}
					break;
				case 3:
					$('#step-3').find('.spinner').addClass('is-active');
					$('#step-3').find('.fz-error-notice').addClass('hidden');

					$.post(
						window.feedzySetupWizardData.ajax.url,
						{
							action: 'feedzy_wizard_step_process',
							security:
								window.feedzySetupWizardData.ajax.security,
							slug: 'optimole-wp',
							step: 'step_3',
						},
						function (response) {
							if (1 === response.status) {
								$('#smartwizard').smartWizard('next');
							} else if (
								'undefined' !== typeof response.message
							) {
								$('#step-3')
									.find('.fz-error-notice .error')
									.html('<p>' + response.message + '</p>');
								$('#step-3')
									.find('.fz-error-notice')
									.removeClass('hidden');
							}
							$('#step-3')
								.find('.spinner')
								.removeClass('is-active');
						}
					).fail(function () {
						$('#step-3').find('.spinner').removeClass('is-active');
					});
					e.preventDefault();
					break;
				default:
					e.preventDefault();
					break;
			}
		}
	);

	$(document).on('click', '.btn-skip', function (e) {
		e.preventDefault();
		$('#smartwizard').smartWizard('next');
	});

	// Save and import.
	$(document).on('click', '.fz-wizard-feed-import', function (e) {
		$('#step-2').find('.spinner').addClass('is-active');
		$.post(
			window.feedzySetupWizardData.ajax.url,
			{
				security: window.feedzySetupWizardData.ajax.security,
				post_type: $(
					'select[name="feedzy[wizard_data][import_post_type]"]'
				).val(),
				post_status: $(
					'select[name="feedzy_meta_data[import_post_status]"]'
				).val(),
				fallback_image: $('input[name="feedzy_meta_data[default_thumbnail_id]"]').val(),
				excluded_post_title: $('input[name="feedzy_meta_data[exc_key]"]').val(),
				action: 'feedzy',
				_action: 'wizard_import_feed',
			},
			function (res) {
				if (res.status > 0) {
					$('#smartwizard').smartWizard('next');
				} else if ('' !== res.message) {
					$('#step-2').find('.spinner').removeClass('is-active');
				}
			}
		).fail(function () {
			$('#step-2').find('.spinner').removeClass('is-active');
		});
		e.preventDefault();
	});

	// Create draft page.
	$(document).on('click', '.fz-create-page', function (e) {
		const _this = $(this);
		_this.next('.spinner').addClass('is-active');
		$.post(
			window.feedzySetupWizardData.ajax.url,
			{
				action: 'feedzy_wizard_step_process',
				security: window.feedzySetupWizardData.ajax.security,
				step: 'create_draft_page',
				basic_shortcode: $('#basic_shortcode').val(),
				add_basic_shortcode: $('#add_basic_shortcode').is(':checked'),
			},
			function (res) {
				if (res.status > 0) {
					$('#smartwizard').smartWizard('next');
				}
				_this.next('.spinner').removeClass('is-active');
			}
		).fail(function () {
			_this.next('.spinner').removeClass('is-active');
		});
		e.preventDefault();
	});

	// Enable performance feature.
	$('#step-3').on('change', 'input:checkbox', function () {
		if ($(this).is(':checked')) {
			$('.skip-improvement').hide();
			$('.fz-wizard-install-plugin').show();
		} else {
			$('.skip-improvement').show();
			$('.fz-wizard-install-plugin').hide();
		}
	});

	// Step: 4 Skip and subscribe process.
	$(document).on('click', '.fz-subscribe', function (e) {
		const withSubscribe = $(this).data('fz_subscribe');
		const urlParams = new URLSearchParams(window.location.search);
		const postData = {
			action: 'feedzy_wizard_step_process',
			security: window.feedzySetupWizardData.ajax.security,
			step: 'step_4',
			integrate_with: urlParams.get('integrate-with'),
		};
		const emailElement = $('#fz_subscribe_email');
		// Remove error message.
		emailElement.next('.fz-field-error').remove();

		if (withSubscribe) {
			const subscribeEmail = emailElement.val();
			const EmailTest =
				/^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
			let errorMessage = '';

			if ('' === subscribeEmail) {
				errorMessage =
					window.feedzySetupWizardData.errorMessages.requiredEmail;
			} else if (!EmailTest.test(subscribeEmail)) {
				errorMessage =
					window.feedzySetupWizardData.errorMessages.invalidEmail;
			}
			if ('' !== errorMessage) {
				$(
					'<span class="fz-field-error">' + errorMessage + '</span>'
				).insertAfter(emailElement);
				return false;
			}

			postData.email = subscribeEmail;
			postData.with_subscribe = withSubscribe;
		}
		$('#step-4').find('.spinner').addClass('is-active');

		$.post(window.feedzySetupWizardData.ajax.url, postData, function (res) {
			$('.redirect-popup').find('h3.popup-title').html(res.message);
			$('.redirect-popup').show();
			if (1 === res.status) {
				setTimeout(function () {
					window.location.href = res.redirect_to;
				}, 5000);
			} else {
				$('.redirect-popup').hide();
			}
			$('#step-4').find('.spinner').removeClass('is-active');
		}).fail(function () {
			$('.redirect-popup').hide();
			$('#step-4').find('.spinner').removeClass('is-active');
		});
		e.preventDefault();
	});

	// Click to copy.
	const clipboard = new ClipboardJS('.fz-copy-code-btn');
	clipboard.on('success', function (e) {
		$(e.trigger).prev('input:text');
	});

	// Remove disabled class from save button.
	$(document).on('input', '#wizard_feed_source', function () {
		if ('' === $(this).val()) {
			$('[data-step_number="2"], #preflight').addClass('disabled');
		} else {
			$('[data-step_number="2"], #preflight').removeClass('disabled');
		}
	});

	// Remove disabled class from get started button.
	$('#step-1').on('change', 'input:radio', function () {
		$('#step-1').find('[data-step_number="1"]').removeClass('disabled');
	});

	// Change button text.
	$(document).on('change', '#add_basic_shortcode', function () {
		if ($(this).is(':checked')) {
			$('.fz-create-page').html(
				window.feedzySetupWizardData.draftPageButtonText
					.firstButtonText +
					' <span class="dashicons dashicons-arrow-right-alt"></span>'
			);
		} else {
			$('.fz-create-page').html(
				window.feedzySetupWizardData.draftPageButtonText
					.secondButtonText +
					' <span class="dashicons dashicons-arrow-right-alt"></span>'
			);
		}
	});

	// Init chosen selectbox.
	$('.feedzy-chosen').chosen({ width: '100%' });

	// on upload button click
	$( 'body' ).on( 'click', '.feedzy-open-media', function( e ) {
		e.preventDefault();
		const button = $( this ),
		wp_media_uploader = wp.media( {
			title: feedzySetupWizardData.mediaUploadText.iframeTitle,
			library : {
				type : 'image'
			},
			button: {
				text: feedzySetupWizardData.mediaUploadText.iframeButton
			},
			multiple: false
		} ).on( 'select', function() { // it also has "open" and "close" events
			const selectedAttachments = wp_media_uploader.state().get( 'selection' );
			button.parents( '.fz-form-group' ).find( '.feedzy-media-preview' ).remove();
			// Display image preview when a single image is selected.
			const attachment = selectedAttachments.first().toJSON();
			let attachmentUrl = attachment.url;
			if ( attachment.sizes.thumbnail ) {
				attachmentUrl = attachment.sizes.thumbnail.url;
			}
			if ( $( '.feedzy-media-preview' ).length ) {
				$( '.feedzy-media-preview' ).find( 'img' ).attr( 'src', attachmentUrl );
			} else {
				$( '<div class="fz-form-group mb-20 feedzy-media-preview"><img src="' + attachmentUrl + '"></div>' ).insertBefore( button.parent() );
			}
			// Get all selected attachment ids.
			const ids = selectedAttachments.map( function( image ) {
				return image.id;
			} ).join( ',' );

			button.parent().find( '.feedzy-remove-media' ).addClass( 'is-show' );
			button.parent().find( 'input:hidden' ).val( ids ).trigger( 'change' );
			$( '.feedzy-open-media' ).html( feedzySetupWizardData.mediaUploadText.actionButtonTextTwo );
		} );

		wp_media_uploader.on(' open', function() {
			const selectedVal = button.parent().find( 'input:hidden' ).val();
			if ( '' === selectedVal ) {
				return;
			}
			const selection = wp_media_uploader.state().get('selection');

			selectedVal.split(',').forEach(function( id ) {
				const attachment = wp.media.attachment( id );
				attachment.fetch();
				selection.add(attachment ? [attachment] : []);
			});
		} );

		wp_media_uploader.open();
	});

	$(document).on( 'click', '.feedzy-remove-media', function( e ) {
		$(this)
		e.preventDefault();
		$('.feedzy-media-preview').remove();
		$(this).removeClass('is-show');

		// Reset the input.
		$('input[name="feedzy_meta_data[default_thumbnail_id]"]').val(0);
		$('.feedzy-open-media').html(feedzySetupWizardData.mediaUploadText.actionButtonTextOne);
	} );

	$('#preflight').on('click', function (e) {
		e.preventDefault();
		const $fields = {};
		// collect all elements.
		$('#smartwizard')
			.find(':input')
			.each(function (index, element) {
				if ('undefined' === typeof $(element).attr('name')) {
					return;
				}
				$fields[$(element).attr('name')] = $(element).val();
			});
		$fields['feedzy_meta_data[source]'] = $('#wizard_feed_source').val();
		tb_show(feedzySetupWizardData.dryRun.title, 'TB_inline?');
		$('#TB_ajaxContent').html(feedzySetupWizardData.dryRun.loading);
		$.post(
			ajaxurl,
			{
				security: window.feedzySetupWizardData.ajax.security,
				fields: $.param($fields),
				action: 'feedzy',
				_action: 'dry_run',
				environment: 'wizard',
			},
			function(data) {
				$('#TB_ajaxContent').addClass('loaded');
				$('#TB_ajaxContent div').html(data.data.output);
			},
		);
	});
});
