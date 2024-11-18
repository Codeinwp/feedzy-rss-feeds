/**
 * Plugin Name: FEEDZY RSS Feeds
 * Plugin URI: https://themeisle.com/plugins/feedzy-rss-feeds/
 * Author: Themeisle
 *
 * @package feedzy-rss-feeds
 */
/* jshint unused:false */
jQuery(function ($) {
	$( '#smartwizard' ).smartWizard({
		transition: {
			animation: 'fade', // Animation effect on navigation, none|fade|slideHorizontal|slideVertical|slideSwing|css(Animation CSS class also need to specify)
			speed: '400', // Animation speed. Not used if animation is 'css'
		},
		lang: {
			// Language variables for button
			next: feedzySetupWizardData.nextButtonText,
			previous: feedzySetupWizardData.backButtonText,
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
	$( document ).on( 'click', '.feed-demo-link', function( e ) {
		var feedUrl = $( this ).attr( 'href' );
		$( this )
		.parents( '.fz-row' )
		.find( 'input:text' )
		.val( feedUrl );

		$( '[data-step_number="2"]' )
		.removeClass( 'disabled' );
		return false;
	} );
	
	// click to open accordion.
	$( '.feedzy-accordion .feedzy-accordion-item .feedzy-accordion-item__button' ).on( 'click', function () {
		var current_item = $(this).parents();
		$( '.feedzy-accordion .feedzy-accordion-item .feedzy-accordion-item__content' ).each(function ( i, el ) {
			if ( $(el).parent().is( current_item ) ) {
				$(el).prev().toggleClass( 'is-active' );
				$(el).slideToggle();
				$(this).toggleClass( 'is-active' );
			} else {
				$(el).prev().removeClass( 'is-active' );
				$(el).slideUp();
				$(this).removeClass( 'is-active' );
			}
		});
	});

	// Click to next step.
	$( document ).on( 'click', '.btn-primary:not(.next-btn,.fz-wizard-feed-import,.fz-wizard-draft-page,.fz-subscribe)', function( e ) {
		var stepNumber = $( this ).data( 'step_number' );
		switch ( stepNumber ) {
			case 1:
				if ( $( '.fz-radio-btn' ).is( ':checked' ) ) {
					var urlParams = new URLSearchParams( window.location.search );
					urlParams.set( 'integrate-with', $( '.fz-radio-btn:checked' ).val() );
					window.location.hash = '#step-2';
					window.location.search = urlParams;
				}
				break;
			case 2:
				var feedSource = $( '#wizard_feed_source' ).val();
				if ( '' !== feedSource ) {
					var integrateWith = $( '.fz-radio-btn:checked' ).val();

					$( '#step-2' ).find( '.spinner' ).addClass( 'is-active' );
					$( '#step-2' ).find( '.fz-error-notice' ).addClass( 'hidden' );

					$.post(
						feedzySetupWizardData.ajax.url,
						{
							action: 'feedzy_wizard_step_process',
							feed: feedSource,
							security: feedzySetupWizardData.ajax.security,
							integrate_with: integrateWith,
							step: 'step_2',
						},
						function( res ) {
							if ( 1 === res.status ) {
								var accordionItem = $( '#feed_source' ).find( '.feedzy-accordion-item__button.hidden' );
								accordionItem.removeClass( 'hidden' ).prev( '.feedzy-accordion-item__button' ).addClass( 'hidden' );
								$( '#feed_source .feedzy-accordion-item__content' ).addClass( 'hidden' );
								$( '#step-2' ).find( '.spinner' ).removeClass( 'is-active' );

								if ( 'feed' === integrateWith ) {
									$( '#feed_import' ).removeClass( 'hidden' );
								} else if ( 'shortcode' === integrateWith ) {
									$( '#shortcode' ).removeClass( 'hidden' );
									$( '#basic_shortcode' ).val( $( '#basic_shortcode' ).val().replace( '{{feed_source}}', feedSource ) );
								} else {
									$( '#smartwizard' ).smartWizard( 'next' );
								}
							} else if( '' !== res.message ) {
								$( '#step-2' ).find( '.spinner' ).removeClass( 'is-active' );
								$( '#step-2' ).find( '.fz-error-notice' ).html( res.message ).removeClass( 'hidden' );
							}
						}
					)
					.fail( function() {
						$( '#step-2' ).find( '.spinner' ).removeClass( 'is-active' );
					} );
					return false;
				}
				break;
			case 3:
				$( '#step-3' ).find( '.spinner' ).addClass( 'is-active' );
				$( '#step-3' ).find( '.fz-error-notice' ).addClass( 'hidden' );

				$.post( feedzySetupWizardData.ajax.url,
					{
						action: 'feedzy_wizard_step_process',
						security: feedzySetupWizardData.ajax.security,
						slug: 'optimole-wp',
						step: 'step_3',
					},
					function( response ) {
						if ( 1 === response.status ) {
							$( '#smartwizard' ).smartWizard( 'next' );
						} else if ( 'undefined' !== typeof response.message ) {
							$( '#step-3' ).find( '.fz-error-notice .error' ).html( '<p>' + response.message + '</p>' );
							$( '#step-3' ).find( '.fz-error-notice' ).removeClass( 'hidden' );
						}
						$( '#step-3' ).find( '.spinner' ).removeClass( 'is-active' );
					}
				)
				.fail( function() {
					$( '#step-3' ).find( '.spinner' ).removeClass( 'is-active' );
				} );
				e.preventDefault();
				break;
			default:
				e.preventDefault();
				break;
		}
	} );

	// Save and import.
	$( document ).on( 'click', '.fz-wizard-feed-import', function( e ) {
		$( '#step-2' ).find( '.spinner' ).addClass( 'is-active' );
		$.post(
			feedzySetupWizardData.ajax.url,
			{
				security: feedzySetupWizardData.ajax.security,
				post_type: $( 'select[name="feedzy[wizard_data][import_post_type]"]' ).val(),
				action: "feedzy",
				_action: "wizard_import_feed",
			},
			function( res ) {
				if ( res.status > 0  ) {
					$( '#smartwizard' ).smartWizard( 'next' );
				} else if( '' !== res.message ) {
					$( '#step-2' ).find( '.spinner' ).removeClass( 'is-active' );
				}
			}
		)
		.fail( function() {
			$( '#step-2' ).find( '.spinner' ).removeClass( 'is-active' );
		} );
		e.preventDefault();
	} );

	// Create draft page.
	$( document ).on( 'click', '.fz-create-page', function( e ) {
		var _this = $( this );
		_this.next( '.spinner' ).addClass( 'is-active' );
		$.post(
			feedzySetupWizardData.ajax.url,
			{
				action: 'feedzy_wizard_step_process',
				security: feedzySetupWizardData.ajax.security,
				step: 'create_draft_page',
				basic_shortcode: $( '#basic_shortcode' ).val(),
				add_basic_shortcode: $( '#add_basic_shortcode' ).is( ':checked' ),
			},
			function( res ) {
				if ( res.status > 0  ) {
					$( '#smartwizard' ).smartWizard( 'next' );
				}
				_this.next( '.spinner' ).removeClass( 'is-active' );
			}
		)
		.fail( function() {
			_this.next( '.spinner' ).removeClass( 'is-active' );
		} );
		e.preventDefault();
	} );

	// Enable performance feature.
	$( '#step-3' ).on( 'change', 'input:checkbox', function() {
		if ( $( this ).is( ':checked' ) ) {
			$( '.skip-improvement' ).hide();
			$( '.fz-wizard-install-plugin' ).show();
		} else {
			$( '.skip-improvement' ).show();
			$( '.fz-wizard-install-plugin' ).hide();
		}
	} );

	// Step: 4 Skip and subscribe process.
	$( document ).on( 'click', '.fz-subscribe', function( e ) {
		var withSubscribe = $( this ).data( 'fz_subscribe' );
		var postData = {
			action: 'feedzy_wizard_step_process',
			security: feedzySetupWizardData.ajax.security,
			step: 'step_4',
		};
		var emailElement = $( '#fz_subscribe_email' );
		// Remove error message.
		emailElement.next( '.fz-field-error' ).remove();

		if ( withSubscribe ) {
			var subscribeEmail = emailElement.val();
			var EmailTest = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
			var errorMessage = '';

			if ( '' === subscribeEmail ) {
				errorMessage = feedzySetupWizardData.errorMessages.requiredEmail;
			} else if ( ! EmailTest.test( subscribeEmail ) ) {
				errorMessage = feedzySetupWizardData.errorMessages.invalidEmail;
			}
			if ( '' !== errorMessage ) {
				$( '<span class="fz-field-error">' + errorMessage + '</span>' ).insertAfter( emailElement );
				return false;
			}

			postData.email = subscribeEmail;
			postData.with_subscribe = withSubscribe;
		}
		$( '#step-4' ).find( '.spinner' ).addClass( 'is-active' );

		$.post(
			feedzySetupWizardData.ajax.url,
			postData,
			function( res ) {
				$( '.redirect-popup' ).find( 'h3.popup-title' ).html( res.message );
				$( '.redirect-popup' ).show();
				if ( 1 === res.status ) {
					setTimeout( function() {
						window.location.href = res.redirect_to;
					}, 5000 );
				} else {
					$( '.redirect-popup' ).hide();
				}
				$( '#step-4' ).find( '.spinner' ).removeClass( 'is-active' );
			}
		)
		.fail( function() {
			$( '.redirect-popup' ).hide();
			$( '#step-4' ).find( '.spinner' ).removeClass( 'is-active' );
		} );
		e.preventDefault();
	} );

	// Click to copy.
	var clipboard = new ClipboardJS('.fz-copy-code-btn');
	clipboard.on('success', function (e) {
		var inputElement = $( e.trigger ).prev( 'input:text' );
	});

	// Remove disabled class from save button.
	$( document ).on( 'input', '#wizard_feed_source', function() {
		console.log( $( this ).val() );
		if ( '' === $( this ).val() ) {
			$( '[data-step_number="2"]' ).addClass( 'disabled' );
		} else {
			$( '[data-step_number="2"]' ).removeClass( 'disabled' );
		}
	} );

	// Remove disabled class from get started button.
	$( '#step-1' ).on( 'change', 'input:radio', function() {
		$( '#step-1' ).find( '[data-step_number="1"]' ).removeClass( 'disabled' );
	} );

	// Change button text.
	$( document ).on( 'change', '#add_basic_shortcode', function() {
		if ( $( this ).is( ':checked' ) ) {
			$( '.fz-create-page' ).html( feedzySetupWizardData.draftPageButtonText.firstButtonText + ' <span class="dashicons dashicons-arrow-right-alt"></span>' );
		} else {
			$( '.fz-create-page' ).html( feedzySetupWizardData.draftPageButtonText.secondButtonText + ' <span class="dashicons dashicons-arrow-right-alt"></span>' );
		}
	} );

	// Init chosen selectbox.
	$( '.feedzy-chosen' ).chosen( { width: '100%' } );
});
