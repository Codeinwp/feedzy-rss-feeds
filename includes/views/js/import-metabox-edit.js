/**
 * Core Library for managing feedzy custom post page js actions.
 * import-metabox-edit.js
 *
 * @since	1.2.0
 * @package feedzy-rss-feeds-pro
 */
/* global jQuery, ajaxurl, feedzy */
(function($){
	function scroll_to_class(element_class, removed_height) {
		var scroll_to = $( element_class ).offset().top - removed_height;
		if ($( window ).scrollTop() !== scroll_to) {
			$( 'html, body' ).stop().animate( {scrollTop: scroll_to}, 0 );
		}
	}

	function bar_progress(progress_line_object, direction) {
		var number_of_steps = progress_line_object.data( 'number-of-steps' );
		var now_value = progress_line_object.data( 'now-value' );
		var new_value = 0;
		if (direction === 'right') {
			new_value = now_value + ( 100 / number_of_steps );
		} else if (direction === 'left') {
			new_value = now_value - ( 100 / number_of_steps );
		}
		progress_line_object.attr( 'style', 'width: ' + new_value + '%;' ).data( 'now-value', new_value );
	}

	function add_source() {
		var field_name = $( this ).data( 'field-name' );
		var field_tag = $( this ).data( 'field-tag' );
		$( '[name="feedzy_meta_data[' + field_name + ']"]' ).val( field_tag );
		$( '[name="feedzy_meta_data[' + field_name + ']"]' ).focus();
		return false;
	}

	function append_tag() {
	    var field_name = $( this ).data( 'field-name' );
		var field_tag = $( this ).data( 'field-tag' );
		if ( field_name === 'import_post_date' ) {
			$( '[name="feedzy_meta_data[' + field_name + ']"]' ).val( '[#' + field_tag + ']' );
		} else {
			var current_value = $( '[name="feedzy_meta_data[' + field_name + ']"]' ).val();
			$( '[name="feedzy_meta_data[' + field_name + ']"]' ).val( current_value + '[#' + field_tag + ']' );
		}
		$( '[data-toggle="dropdown"]' ).parent().removeClass( 'open' );
		$( '[name="feedzy_meta_data[' + field_name + ']"]' ).focus();
		return false;
	}

	function remove_row() {
		$( this ).closest( '.row' ).remove();
	    return false;
	}

	function new_row() {
	    var html_row = '';
	    html_row = $( '#new_field_tpl' ).html();
	    $( '.custom_fields' ).append( html_row );
		$( '.btn.btn-remove-fields' ).on( 'click', remove_row );
	    return false;
	}

	function update_status() {
	    var post_id = $( this ).val();
	    var status = $( this ).is( ':checked' );

		var data = {
            security    : feedzy.ajax.security,
			'action': 'feedzy',
			'_action': 'import_status',
			'id': post_id,
			'status': status
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post( ajaxurl, data, function() {} );
		return true;
	}

	function toggle_dropdown() {
		$( this ).parent().toggleClass( 'open' );
	}

    function in_array( $key, $array ) {
        if(typeof $array === 'undefined' || $array.length === 0 || $key === ''){
            return false;
        }
        for(var i = 0; i < $array.length; i++){
            if($array[i] === $key){
                return true;
            }
        }
        return false;
    }

	function update_taxonomy() {
		var selected = $( this ).val();
		var tax_selected = $( this ).data( 'tax' );
        if(typeof tax_selected !== 'undefined'){
            tax_selected = tax_selected.split(',');
        }else{
            tax_selected = '';
        }

		var data = {
            security    : feedzy.ajax.security,
			'action': 'feedzy',
			'_action': 'get_taxonomies',
			'post_type': selected
		};

        $( '#feedzy_post_terms' ).html( $( '#loading_select_tpl' ).html() ).trigger( 'chosen:updated' );

		$.post( ajaxurl, data, function(response) {
			var taxonomies = JSON.parse( response );
			var show_terms = true;

			var options = '';
			if ( taxonomies.length !== 0 ) {
				$.each(taxonomies, function ( index, terms ) {
					if ( terms.length !== 0 ) {
						options += '<optgroup label="' + index + '">';
						$.each(terms, function (i, term) {
							var sel_option = '';
							if ( in_array( index + '_' + term.term_id, tax_selected ) ) {
								sel_option = 'selected';
							}
							options += '<option value="' + index + '_' + term.term_id + '" ' + sel_option + '>' + term.name + '</option>';
						});
						options += '</optgroup>';
					}
				});
			} else {
				show_terms = false;
				options = $( '#empty_select_tpl' ).html();
			}

			$( '#feedzy_post_terms' ).html( options ).trigger( 'chosen:updated' );
		});
		return true;
	}

	$( document ).ready(function() {
		$( 'button.btn-submit' ).on( 'click', function( e ) {
			$( window ).unbind( 'beforeunload' );
			$( '#custom_post_status' ).val( $( this ).val() );
			e.preventDefault();
			$( '#post' ).submit();
			return false;
		} );

		$( 'a.dropdown-item' ).on( 'click', append_tag );
		$( 'a.dropdown-item.source' ).on( 'click', add_source );
		$( '.btn.btn-remove-fields' ).on( 'click', remove_row );
		$( '#new_custom_fields' ).on( 'click', new_row );

		$( '.feedzy-toggle' ).on( 'click', update_status );
		$( '.dropdown-toggle' ).on( 'click', toggle_dropdown );
		if ( $( '#toplevel_page_feedzy-admin-menu li' ).hasClass( 'current' ) ) {
			$( '#toplevel_page_feedzy-admin-menu' ).removeClass( 'wp-not-current-submenu' ).addClass( 'wp-has-current-submenu' );
		}
		$( '.feedzy-chosen' ).chosen( { width: '100%' } );
		$( '#feedzy_post_type' ).on( 'change', update_taxonomy );
		$( '#feedzy_post_type' ).trigger( 'change' );
		$( '#feedzy_post_status' ).trigger( 'change' );

		/*
         Form
         */
		$( '.f1 fieldset:first' ).fadeIn( 'slow' );

		// next step
		$( '.f1 .btn-next' ).on('click', function() {
			var parent_fieldset = $( this ).parents( 'fieldset' );
			var next_step = true;
			// navigation steps / progress steps
			var current_active_step = $( this ).parents( '.f1' ).find( '.f1-step.active' );
			var progress_line = $( this ).parents( '.f1' ).find( '.f1-progress-line' );

			// fields validation
			if ( next_step ) {
				parent_fieldset.fadeOut(400, function() {
					// change icons
					current_active_step.removeClass( 'active' ).addClass( 'activated' ).next().addClass( 'active' );
					// progress bar
					bar_progress( progress_line, 'right' );
					// show next step
					$( this ).next().fadeIn();
					// scroll window to beginning of the form
					scroll_to_class( $( '.f1' ), 20 );
				});
			}

		});

		// previous step
		$( '.f1 .btn-previous' ).on('click', function() {
			// navigation steps / progress steps
			var current_active_step = $( this ).parents( '.f1' ).find( '.f1-step.active' );
			var progress_line = $( this ).parents( '.f1' ).find( '.f1-progress-line' );

			$( this ).parents( 'fieldset' ).fadeOut(400, function() {
				// change icons
				current_active_step.removeClass( 'active' ).prev().removeClass( 'activated' ).addClass( 'active' );
				// progress bar
				bar_progress( progress_line, 'left' );
				// show previous step
				$( this ).prev().fadeIn();
				// scroll window to beginning of the form
				scroll_to_class( $( '.f1' ), 20 );
			});
		});

        initSummary();

	});

    function initSummary() {
        // pop-ups for informational text
        $( '.feedzy-dialog' ).dialog({
          modal: true,
          autoOpen: false,
          height: 400,
          width: 500,
          buttons: {
            Ok: function() {
              $( this ).dialog( 'close' );
            }
          }
        });

        $( '.feedzy-dialog-open' ).on('click', function(e){
            e.preventDefault();
            var dialog = $(this).attr('data-dialog');
            $('.' + dialog).dialog( 'open' );
        });

        // run now.
        $('.feedzy-run-now').on('click', function(e){
            e.preventDefault();
            var button = $(this);
            showSpinner(button);
            button.parent().find('.feedzy-error').remove();
            $.ajax({
                url     : ajaxurl,
                method  : 'post',
                data    : {
                    security    : feedzy.ajax.security,
                    id          : $(this).attr('data-id'),
                    action      : 'feedzy',
                    _action      : 'run_now'
                },
                success: function(data){
                    hideSpinner(button);
                    button.after($('<div class="feedzy-error feedzy-error-critical">' + data.data.msg + '</div>'));
                }
            });
        });

        // toggle the errors div to expand/collapse
        $('td.column-feedzy-last_run .feedzy-api-error').on('click', function(){
            if($(this).hasClass('expand')){
                $(this).removeClass('expand');
            }else{
                $(this).addClass('expand');
            }
        });

        // purge data ajax call
        $('.feedzy_purge').on('click', function(e){
            e.preventDefault();
            var element = $(this);
            showSpinner(element);

            $.ajax({
                url     : ajaxurl,
                method  : 'post',
                data    : {
                    security    : feedzy.ajax.security,
                    id          : $(this).find('a').attr('data-id'),
                    action      : 'feedzy',
                    _action      : 'purge'
                },
                success: function(){
                    hideSpinner(element);
                }
            });
        });
    }

    function showSpinner(el){
        el.parent().find('.feedzy-spinner').addClass('is-active');
    }
    function hideSpinner(el){
        el.parent().find('.feedzy-spinner').removeClass('is-active');
    }
})(jQuery, feedzy);
