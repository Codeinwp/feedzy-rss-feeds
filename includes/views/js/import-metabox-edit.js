/**
 * Core Library for managing feedzy custom post page js actions.
 * import-metabox-edit.js
 *
 * @since	1.2.0
 * @package feedzy-rss-feeds-pro
 */
/* global jQuery, ajaxurl, feedzy, tb_show */
(function ($) {
	function scroll_to_class(element_class, removed_height) {
		var scroll_to = $(element_class).offset().top - removed_height;
		if ($(window).scrollTop() !== scroll_to) {
			$("html, body").stop().animate({ scrollTop: scroll_to }, 0);
		}
	}

	function bar_progress(progress_line_object, direction) {
		var number_of_steps = progress_line_object.data("number-of-steps");
		var now_value = progress_line_object.data("now-value");
		var new_value = 0;
		if (direction === "right") {
			new_value = now_value + 100 / number_of_steps;
		} else if (direction === "left") {
			new_value = now_value - 100 / number_of_steps;
		}
		progress_line_object
			.attr("style", "width: " + new_value + "%;")
			.data("now-value", new_value);
	}

	function add_source() {
		var field_name = $(this).data("field-name");
		var field_tag = $(this).data("field-tag");
		$('[name="feedzy_meta_data[' + field_name + ']"]').data('tagify')
			.addTags(field_tag);
		$('[data-toggle="dropdown"]').parent().removeClass("open");
		$('[name="feedzy_meta_data[' + field_name + ']"]').focus();
		return false;
	}

	function append_outside_tag() {
		var outsideWrap = $( this ).parents( '.fz-form-group, .fz-input-group' );
		var tags = outsideWrap.find( '.form-control' ).val();

		if ( '' === tags ) {
			return false;
		}

		outsideWrap.find( '.form-control .tag-list' )?.val('');
		if ( outsideWrap.next( '.tag-list' ).length > 0 ) {
			outsideWrap.
			next( '.tag-list' )
			.find( 'input.fz-tagify-outside, input.fz-tagify--outside' )
			.data('tagify')
			.addTags( tags );
		} else {
			outsideWrap.find( 'input.fz-tagify-outside, input.fz-tagify--outside' )
			.data('tagify')
			.addTags( tags );
		}
		return false;
	}

	function append_tag() {
		var field_name = $(this).data("field-name");
		var field_tag = $(this).data("field-tag");
		if (field_name === "import_post_date" || field_name === "import_post_featured_img") {
			$('[name="feedzy_meta_data[' + field_name + ']"]').data('tagify').removeAllTags();
			$('[name="feedzy_meta_data[' + field_name + ']"]').data('tagify').addTags(
				"[#" + field_tag + "]"
			);
		} else if( field_name === "import_post_content" ) {
			$('[name="feedzy_meta_data[' + field_name + ']"]').focus();
			$('[name="feedzy_meta_data[' + field_name + ']"]').data('tagify').addTags(
				"[#" + field_tag + "]"
			);
			return false;
		} else {
			$('[name="feedzy_meta_data[' + field_name + ']"]').data('tagify').addTags(
				"[#" + field_tag + "]"
			);
		}
		$('[data-toggle="dropdown"]').parent().removeClass("open");
		$('[name="feedzy_meta_data[' + field_name + ']"]').focus();
		return false;
	}

	function remove_row() {
		$(this).closest(".key-value-item").remove();
		return false;
	}

	function new_row() {
		var html_row = "";
		html_row = $("#new_field_tpl").html();
		$(".custom_fields").append(html_row);
		$(".btn.btn-remove-fields").on("click", remove_row);
		initCustomFieldAutoComplete();
		document.dispatchEvent(new Event('feedzy_new_row_added'));
		return false;
	}

	function update_status() {
		var toggle = $(this);
		var post_id = $(this).val();
		var status = $(this).is(":checked");

		var data = {
			security: feedzy.ajax.security,
			action: "feedzy",
			_action: "import_status",
			id: post_id,
			status: status,
		};

		showSpinner(toggle);
		$.ajax({
			url: ajaxurl,
			data: data,
			method: "POST",
			success: function (data) {
				if (!data.success && status) {
					toggle
						.parents("tr")
						.find("td.feedzy-source")
						.find(".feedzy-error-critical")
						.remove();
					toggle
						.parents("tr")
						.find("td.feedzy-source")
						.append($(data.data.msg));
					toggle.prop("checked", false);
				}
			},
			complete: function () {
				hideSpinner(toggle);
			},
		});
		return true;
	}

	function toggle_dropdown() {
		$( '.dropdown.open' ).not( $(this).parent() ).removeClass( 'open' );
		$(this).parent().toggleClass("open");
	}

	function in_array($key, $array) {
		if (typeof $array === "undefined" || $array.length === 0 || $key === "") {
			return false;
		}
		for (var i = 0; i < $array.length; i++) {
			if ($array[i] === $key) {
				return true;
			}
		}
		return false;
	}

	function update_taxonomy() {
		var selected = $(this).val();
		var tax_selected = $(this).data("tax");
		if (typeof tax_selected !== "undefined") {
			tax_selected = tax_selected.split(",");
		} else {
			tax_selected = "";
		}

		var data = {
			security: feedzy.ajax.security,
			action: "feedzy",
			_action: "get_taxonomies",
			post_type: selected,
		};

		$("#feedzy_post_terms")
			.html($("#loading_select_tpl").html())
			.trigger("chosen:updated");

		$.post(ajaxurl, data, function (response) {
			var show_terms = true;

			var options = "";
			if (response.length !== 0) {
				$.each(response, function (index, terms) {
					if (terms) {
						var groupName = index;
						if ( 'category' === groupName ) {
							groupName = 'Category';
						} else if( 'post_tag' === groupName ) {
							groupName = 'Tag';
						}
						options += '<optgroup label="' + groupName + '">';
						$.each(terms, function (i, term) {
							var sel_option = "";
							if (in_array(index + "_" + i, tax_selected)) {
								sel_option = "selected";
								if ( $("#feedzy_post_terms").hasClass('fz-chosen-custom-tag') ) {
									var removeItem = index + "_" + i;
									tax_selected = $.grep(tax_selected, function(value) {
										return value != removeItem;
									});
								}
							}
							options +=
								'<option value="' +
								index +
								"_" +
								i +
								'" ' +
								sel_option +
								">" +
								term +
								"</option>";
						});
						options += "</optgroup>";
					}
				});
				tax_selected = tax_selected.filter( function(item) { return '' !== item; } );
				if ( tax_selected.length > 0 && $("#feedzy_post_terms").hasClass('fz-chosen-custom-tag') ) {
					$.each(tax_selected, function (index, customTag) {
						options += '<option value="' + customTag + '" selected>' + customTag + '</option>';
					} );
				}
			} else {
				show_terms = false;
				options = $("#empty_select_tpl").html();
			}

			$("#feedzy_post_terms").html(options).trigger("chosen:updated");
		}, 'json');
		return true;
	}

	function htmlEntities(str) {
		return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, "&quot;").replace(/'/g, "&#039;");
	}

	function escapeSelector(string) {
		return String(string).replace(/([ !"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~])/g,'\\$1');
	}

	$(document).ready(function () {
		initImportScreen();
		initSummary();
		initCustomFieldAutoComplete();
		feedzyAccordion();
		feedzyTab();
		feedzyMediaUploader();
		initRemoveFallbackImageBtn();
	});

	function initImportScreen() {
		$("button.btn-submit").on("click", function (e) {
			$(window).unbind("beforeunload");
			$("#custom_post_status").val($(this).val());
			e.preventDefault();
			$("#post").submit();
			return false;
		});

		$( '#feedzy-import-source' ).on( 'blur', function(e) {
			var addTagBtn = $( this ).parents( '.fz-input-icon' ).find( '.add-outside-tags' );
			addTagBtn.trigger( 'click' );
			$( this ).val('');
		} );

		$( '.feedzy-keyword-filter, #feedzy-import-source' ).on('keyup keypress', function(e) {
			var keyCode = e.keyCode || e.which;
			var addTagBtn = $( this ).parents( '.fz-input-icon' ).find( '.add-outside-tags' );

			if ( '' === $( this ).val() ) {
				addTagBtn.attr( 'disabled', true );
			} else if ( addTagBtn.hasClass( 'fz-plus-btn' ) ) {
				addTagBtn.removeAttr( 'disabled' );
			}

			if ( keyCode === 13 ) {
				e.preventDefault();
				addTagBtn.trigger( 'click' );
				$( this ).val('');
				return false;
			}
		} );

		$("a.dropdown-item:not(.source,[data-action_popup])").on("click", append_tag);
		$(".add-outside-tags").on("click", append_outside_tag);
		$("a.dropdown-item.source").on("click", add_source);
		$( document ).on( 'click', '.btn-remove-fields', remove_row );
		$("#new_custom_fields").on("click", new_row);

		$(".feedzy-toggle").on("click", update_status);
		$(".dropdown-toggle").on("click", toggle_dropdown);
		if ($("#toplevel_page_feedzy-admin-menu li").hasClass("current")) {
			$("#toplevel_page_feedzy-admin-menu")
				.removeClass("wp-not-current-submenu")
				.addClass("wp-has-current-submenu");
		}
		$(".feedzy-chosen").chosen({ width: "100%" });
		$("#feedzy_post_type").on("change", update_taxonomy);
		$("#feedzy_post_status").trigger("change");

		// Add magic tag support for post taxonomy field.
		$("#feedzy_post_terms.fz-chosen-custom-tag").on("chosen:no_results", function() {
			var select = $(this);
			var search = select.siblings(".chosen-container").find(".chosen-choices .search-field:last input");
			var text = htmlEntities(search.val());
    		// dont add if it already exists
			if (! select.find('option[value=' + escapeSelector(search.val()) + ']').length) {
				var btn = $('<li class="active-result highlighted">' + text + '</li>');
				btn.on("mousedown mouseup click", function(e) {
					var arr = select.val();
					select.html(select.html() + "<option value='" + text + "'>" + text + "</option>");
					select.val(arr.concat([text]));
					select.trigger("chosen:updated").trigger('chosen:close');
					// search.focus();
					e.stopImmediatePropagation();
					return false;
				});
				search.on("keydown", function(e) {
					if (e.which == 13) {
						btn.click();
						return false;
					}
				});
				select.siblings(".chosen-container").find(".no-results").replaceWith(btn);
			} else {
				select.siblings(".chosen-container").find(".no-results").replaceWith('');
			}
		})

		// Add magic tag support for post taxonomy field.
		$("#feedzy_post_author.fz-chosen-custom-tag").on("chosen:no_results", function() {
			var select = $(this);
			var search = select.siblings(".chosen-container").find(".chosen-search-input");
			var text = htmlEntities(search.val().replace(/\s+/g, ''));
			// dont add if it already exists
			if (! select.find('option[value=' + escapeSelector(search.val().replace(/\s+/g, '')) + ']').length) {
				var btn = $('<li class="active-result highlighted">' + text + '</li>');
				btn.on("mousedown mouseup click", function(e) {
					var arr = select.val() || [];
					select.append("<option value='" + text + "' selected>" + text + "</option>");
					select.trigger("chosen:updated").trigger('chosen:close');
					e.stopImmediatePropagation();
					return false;
				});
				search.on("keydown", function(e) {
					if (e.which == 13) {
						btn.click();
						return false;
					}
				});
				select.siblings(".chosen-container").find(".no-results").replaceWith(btn);
				select.siblings(".chosen-container").find(".chosen-results").append('<li class="helper-text">' + feedzy.i10n.author_helper + '</li>');
			} else {
				select.siblings(".chosen-container").find(".no-results").replaceWith('');
			}
		})

		/*
         Form
         */
		$(".f1 fieldset:first").fadeIn("slow");

		// next step
		$(".f1 .btn-next").on("click", function () {
			var parent_fieldset = $(this).parents("fieldset");
			var next_step = true;
			// navigation steps / progress steps
			var current_active_step = $(this).parents(".f1").find(".f1-step.active");
			var progress_line = $(this).parents(".f1").find(".f1-progress-line");

			// fields validation
			if (next_step) {
				parent_fieldset.fadeOut(400, function () {
					// change icons
					current_active_step
						.removeClass("active")
						.addClass("activated")
						.next()
						.addClass("active");
					// progress bar
					bar_progress(progress_line, "right");
					// show next step
					$(this).next().fadeIn();
					// scroll window to beginning of the form
					scroll_to_class($(".f1"), 20);
				});
			}
		});

		// previous step
		$(".f1 .btn-previous").on("click", function () {
			// navigation steps / progress steps
			var current_active_step = $(this).parents(".f1").find(".f1-step.active");
			var progress_line = $(this).parents(".f1").find(".f1-progress-line");

			$(this)
				.parents("fieldset")
				.fadeOut(400, function () {
					// change icons
					current_active_step
						.removeClass("active")
						.prev()
						.removeClass("activated")
						.addClass("active");
					// progress bar
					bar_progress(progress_line, "left");
					// show previous step
					$(this).prev().fadeIn();
					// scroll window to beginning of the form
					scroll_to_class($(".f1"), 20);
				});
		});

		$("#feedzy-validate-feed").on("click", function (e) {
			let $url = $("#feedzy-source-tags").val();
			$url = $url.split( ',' );
			$url = $.trim( $url.pop() );
			let $anchor = $(this);
			$anchor.attr("href", $anchor.attr("data-href-base") + $url);
		});

		$("#preflight").on("click", function (e) {
			e.preventDefault();
			var $fields = {};
			// collect all elements.
			$("#feedzy-import-form")
				.find(":input")
				.each(function (index, element) {
					if ("undefined" === typeof $(element).attr("name")) {
						return;
					}
					$fields[$(element).attr("name")] = $(element).val();
				});
			tb_show(feedzy.i10n.dry_run_title, "TB_inline?");
			$("#TB_ajaxContent").html(feedzy.i10n.dry_run_loading);
			$.ajax({
				url: ajaxurl,
				method: "post",
				data: {
					security: feedzy.ajax.security,
					fields: $.param($fields),
					action: "feedzy",
					_action: "dry_run",
				},
				success: function (data) {
					$("#TB_ajaxContent").addClass("loaded");
					$("#TB_ajaxContent div").html(data.data.output);
				},
			});
		});

		// Click on pro/upgrade button.
		$(document).on("click", ".only-pro-inner a", function (e) {
			if ($(this).parents(".only-pro-content").css("opacity") == 0) {
				e.preventDefault();
			}
		});

		// Click to hide upsell notice.
		$(document).on("click", ".remove-alert", function (e) {
			var upSellNotice = $(this).parents( '.upgrade-alert' );
			upSellNotice.fadeOut( 500,
				function() {
					upSellNotice.remove();
					jQuery.post(
						ajaxurl,
						{
							security: feedzy.ajax.security,
							action: "feedzy",
							_action: "remove_upsell_notice"
						}
					);
				}
			);
		});

		// Save/Update Post Title.
		$(document).on('input', "#post_title", function (e) {
			jQuery( '.fz-form-wrap input[name="post_title"]' ).val( $(this).val() );
		});

		// Close dropdown when user click on document.
		$( 'body' ).on( 'click', function( e ) {
			if ( $( e.target ).parent( '.dropdown-item' ).length > 0 ) {
				return;
			}
			if ( $( e.target ).attr( 'disabled' ) ) {
				return;
			}
			if ( $( e.target ).hasClass( 'dashicons-arrow-down-alt2' ) ) {
				return;
			}
			if ( $( e.target ).hasClass( 'dashicons-plus-alt2' ) ) {
				return;
			}
			if ( $( e.target ).hasClass( 'dropdown-toggle' ) ) {
				return;
			}
			$( '.dropdown.open' ).removeClass( 'open' );
		} );

		// Tagify for normal textbox.
		$( '.fz-input-tagify:not(.fz-tagify-image):not([name="feedzy_meta_data[import_post_title]"])' ).tagify( {
			editTags: false,
			originalInputValueFormat: function( valuesArr ) {
				return valuesArr.map( function( item ) {
					return item.value;
				} )
				.join( ', ' );
			}
		} );

		// Tagify for normal mix content field.
		$( '.fz-tagify-image' ).tagify( {
			mode: 'mix',
			editTags: true,
			userInput: true,
			addTagOn: [],
			templates: {
				tag: function(tagData) {
					try{
						var decodeTagData = decodeURIComponent(tagData.value);
						var isEncoded = typeof tagData.value === "string" && decodeTagData !== tagData.value;
						var tagLabel = tagData.value;
						if ( isEncoded ) {
							decodeTagData = JSON.parse( decodeTagData );
							decodeTagData = decodeTagData[0] || {};
							tagLabel = decodeTagData.tag.replaceAll( '_', ' ' );
							tagData['data-actions'] = tagData.value;
							tagData['data-field_id'] = 'fz-image-action-tags';
						}
						return `
						<tag title='${tagLabel}' contenteditable='false' spellcheck="false" class='tagify__tag ${isEncoded ? 'fz-content-action' : ''}'>
							<x title='remove tag' class='tagify__tag__removeBtn'></x>
							<div>
								<span class='tagify__tag-text'>${tagLabel}</span>
								${tagData['data-actions'] ?
									`<a href="javascript:;" class="tagify__filter-icon" ${this.getAttributes(tagData)}></a>` : ''
								}
							</div>
						</tag>`
					}
					catch(err){}
				}
			}
		} );

		// Tagify for normal mix content field.
		var mixContent = $( '.fz-textarea-tagify' ).tagify( {
			mode: 'mix',
			editTags: false,
			templates: {
				tag: function( tagData ) {
					try{
						var decodeTagData = decodeURIComponent(tagData.value);
						var isEncoded = typeof tagData.value === "string" && decodeTagData !== tagData.value;
						var tagLabel = tagData.value;
						if ( isEncoded ) {
							decodeTagData = JSON.parse( decodeTagData );
							decodeTagData = decodeTagData[0] || {};
							tagLabel = decodeTagData.tag.replaceAll( '_', ' ' );
							tagData['data-actions'] = tagData.value;
							tagData['data-field_id'] = 'fz-content-action-tags';
						}
						return `
						<tag title='${tagLabel}' contenteditable='false' spellcheck="false" class='tagify__tag ${isEncoded ? 'fz-content-action' : ''}'>
							<x title='remove tag' class='tagify__tag__removeBtn'></x>
							<div>
								<span class='tagify__tag-text'>${tagLabel}</span>
								${tagData['data-actions'] ?
									`<a href="javascript:;" class="tagify__filter-icon" ${this.getAttributes(tagData)}></a>` : ''
								}
							</div>
						</tag>`
					}
					catch(err){}
				}
			}
		} );

		$( '.fz-input-tagify[name="feedzy_meta_data[import_post_title]"]:not(.fz-tagify-image)' ).tagify( {
			mode: 'mix',
			editTags: false,
			templates: {
				tag: function( tagData ) {
					try{
						var decodeTagData = decodeURIComponent(tagData.value);
						var isEncoded = typeof tagData.value === "string" && decodeTagData !== tagData.value;
						var tagLabel = tagData.value;
						if ( isEncoded ) {
							decodeTagData = JSON.parse( decodeTagData );
							decodeTagData = decodeTagData[0] || {};
							tagLabel = decodeTagData.tag.replaceAll( '_', ' ' );
							tagData['data-actions'] = tagData.value;
							tagData['data-field_id'] = 'fz-title-action-tags';
						}
						return `
						<tag title='${tagLabel}' contenteditable='false' spellcheck="false" class='tagify__tag ${isEncoded ? 'fz-content-action' : ''}'>
							<x title='remove tag' class='tagify__tag__removeBtn'></x>
							<div>
								<span class='tagify__tag-text'>${tagLabel}</span>
								${tagData['data-actions'] ?
									`<a href="javascript:;" class="tagify__filter-icon" ${this.getAttributes(tagData)}></a>` : ''
								}
							</div>
						</tag>`
					}
					catch(err){
						console.error(err);
					}
				}
			}
		} );

		// Tagify for outside tags with allowed duplicates.
		$( '.fz-tagify-outside' ).tagify( {
			editTags: true,
			duplicates: true,
			userInput: false,
			originalInputValueFormat: function( valuesArr ) {
				return valuesArr.map( function( item ) {
					return item.value;
				} )
				.join( ', ' );
			}
		} )
		.on( 'add', function( e ) {
			var target = $( e.target );
			target.parents( '.tag-list' ).removeClass( 'hidden' );
		} )
		.on( 'removeTag', function( e, tagData ) {
			var target = $( e.target );
			var emptyTags = target.parents( '.tag-list' ).find( '.tagify--empty' ).length;
			if ( emptyTags ) {
				target.parents( '.tag-list' ).addClass( 'hidden' );
			}
		} );

		// Tagify for outside tags with not allowed duplicates.
		$( '.fz-tagify--outside' ).tagify( {
			editTags: true,
			userInput: false,
			originalInputValueFormat: function( valuesArr ) {
				return valuesArr.map( function( item ) {
					return item.value;
				} )
				.join( ', ' );
			}
		} )
		.on( 'add', function( e ) {
			var target = $( e.target );
			target.parents( '.tag-list' ).removeClass( 'hidden' );
		} )
		.on( 'removeTag', function( e, tagData ) {
			var target = $( e.target );
			var tagList = target.parents( '.tag-list' );
			if ( tagList.find( '.tagify__tag' ).length === 0 ) {
				tagList.addClass( 'hidden' );
				tagList.find( 'input:text' ).val( '' );
			}
		} );

		$( document ).on( 'change', 'input#remove-duplicates', function() {
			if ( $(this).is(':checked') ) {
				$('input#feedzy_mark_duplicate').attr( 'disabled', false );
			} else {
				$('input#feedzy_mark_duplicate').attr( 'disabled', true );
			}
		} );

		$(document).on( 'input', 'input[name="custom_vars_value[]"]', function () {
			$(this)
			.next('.fz-action-icon')
			.toggleClass( 'disabled', $(this).val() === '' );

			$(this)
			.parent( '.fz-form-group' )
			.find( 'input:hidden' )
			.attr( 'disabled', $(this).val() === '' );
		} );

    // Append import button.
		$( feedzy.i10n.importButton ).insertAfter( $( '.page-title-action', document ) );
		$( $( '.page-title-action', document ) ).wrapAll( '<div class="fz-header-action"></div>' );

		// Create dialog box
		$( '#fz_import_export_upsell' ).dialog( {
			title: '',
			dialogClass: 'wp-dialog',
			autoOpen: false,
			draggable: false,
			width: 'auto',
			modal: true,
			resizable: false,
			closeOnEscape: true,
			position: {
				my: "center",
				at: "center",
				of: window
			},
			open: function( event, ui ) {
				$(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
				$(".ui-dialog-titlebar", ui.dialog | ui).hide();
			},
			create: function() {
				// style fix for WordPress admin
				$( '.ui-dialog-titlebar-close' ).addClass( 'ui-button' );
			},
		} );

		$( '.fz-export-import-btn.only-pro, .fz-export-btn-pro' ).on( 'click', function( e ) {
            e.preventDefault();
            $( '#fz_import_export_upsell' ).dialog( 'open' );
        });

        $( '#fz_import_export_upsell' ).on( 'click', '.close-modal', function ( e ) {
        	e.preventDefault();
            $( '#fz_import_export_upsell' ).dialog( 'close' );
        } );

        $(document).on( 'click', '.fz-export-import-btn:not(.only-pro)', function( e ) {
            e.preventDefault();
            if ( $('.fz-import-field').length === 0 ) {
	            var importField = $( '#fz_import_field_section' ).html();
	            $( importField ).insertAfter( $( this ).parents( 'div.wrap' ).find( '.wp-header-end' ) );
	        }
            $('.fz-import-field').toggleClass('hidden');
        });

		let url = new URL(window.location.href);
		if (url.searchParams.has('imported')) {
			url.searchParams.delete('imported');
			history.replaceState(history.state, '', url.href);
		}
	}

	function initSummary() {
		$("tr.type-feedzy_imports").each(function (i, e) {
			var $lastRunData = $(e).find("script.feedzy-last-run-data").html();
			$($lastRunData).insertAfter(e);
		});

		// pop-ups for informational text
		$(".feedzy-dialog").dialog({
			modal: true,
			autoOpen: false,
			height: 400,
			classes: {
				"ui-dialog-content": "feedzy-dialog-content",
			},
			width: 500,
			buttons:[
				{
					text: feedzy.i10n.okButton,
					click: function () {
						$(this).dialog('close');
					}
				}
			]
		});

		// Error logs popup.
		$(".feedzy-errors-dialog").dialog({
			modal: true,
			autoOpen: false,
			height: 400,
			width: 500,
			buttons:[
				{
					text: feedzy.i10n.clearLogButton,
					class: 'button button-primary feedzy-clear-logs',
					click: function (event) {
						var clearButton = $(event.target);
						var dialogBox = $(this);
						$('<span class="feedzy-spinner spinner is-active"></span>').insertAfter(clearButton);
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
								clearButton
								.next('.feedzy-spinner')
								.remove();

								dialogBox
								.find('.feedzy-error.feedzy-api-error')
								.html('<div class="notice notice-success"><p>' + feedzy.i10n.removeErrorLogsMsg + '</p></div>')
							}
						);
					}
				},
				{
					text: feedzy.i10n.okButton,
					class: 'alignright',
					click: function () {
						$(this).dialog('close');
					}
				}
			]
		});

		$(".feedzy-dialog-open").on("click", function (e) {
			e.preventDefault();
			var dialog = $(this).attr("data-dialog");
			$("." + dialog).dialog("open");
		});

		// run now.
		$(".feedzy-run-now").on("click", function (e) {
			e.preventDefault();
			var button = $(this);
			button.val(feedzy.i10n.importing);

			var numberRow = button
				.parents("tr")
				.find("~ tr.feedzy-import-status-row:first")
				.find("td tr:first");
			numberRow.find("td").hide();
			numberRow
				.find("td:first")
				.addClass("feedzy_run_now_msg")
				.attr("colspan", 5)
				.html(feedzy.i10n.importing)
				.show();

			$.ajax({
				url: ajaxurl,
				method: "post",
				data: {
					security: feedzy.ajax.security,
					id: $(this).attr("data-id"),
					action: "feedzy",
					_action: "run_now",
				},
				success: function (data) {
					if ( data.data.import_success ) {
						numberRow.find("td:first").addClass('import_success');
					}
					numberRow.find("td:first").html(data.data.msg);
				},
				complete: function () {
					button.val(feedzy.i10n.run_now);
				},
			});
		});

		// toggle the errors div to expand/collapse
		$("td.column-feedzy-last_run .feedzy-api-error").on("click", function () {
			if ($(this).hasClass("expand")) {
				$(this).removeClass("expand");
			} else {
				$(this).addClass("expand");
			}
		});

		// purge data ajax call
		$(".feedzy_purge").on("click", function (e) {
			e.preventDefault();
			var element = $(this);
			var deleteImportedPosts = confirm(feedzy.i10n.delete_post_message);
			if (!deleteImportedPosts) {
				return;
			}
			showSpinner(element);
			$.ajax({
				url: ajaxurl,
				method: "post",
				data: {
					security: feedzy.ajax.security,
					id: $(this).find("a").attr("data-id"),
					action: "feedzy",
					_action: "purge",
					del_imported_posts: deleteImportedPosts
				},
				success: function () {
					location.reload();
				},
				complete: function () {
					hideSpinner(element);
				},
			});
		});
	}

	function initCustomFieldAutoComplete() {
		$('input[name="custom_vars_key[]"]')
			.autocomplete({
				minLength: 0,
				source: function (request, response) {
					jQuery.post(
						ajaxurl,
						{
							security: feedzy.ajax.security,
							action: "feedzy",
							_action: "fetch_custom_fields",
							post_type: $("#feedzy_post_type").val(),
							search_key: request.term,
						},
						function (res) {
							if (res.success) {
								response(res.data);
							} else {
								response([
									{
										label: res.data.not_found_msg,
										value: "not_found_msg",
									},
								]);
							}
						}
					);
				},
				select: function (event, ui) {
					if ("not_found_msg" === ui.item.value) {
						setTimeout(function () {
							$(event.target).val("");
						});
					}
				},
				focus: function () {
					if ($(this).autocomplete("widget").is(":visible")) {
						return;
					}
					$(this).autocomplete("search", $(this).val());
				},
			})
			.on("click", function () {
				$(this).keydown();
			});
	}

	function showSpinner(el) {
		el.parent().find(".feedzy-spinner").addClass("is-active");
	}
	function hideSpinner(el) {
		el.parent().find(".feedzy-spinner").removeClass("is-active");
	}

	function feedzyAccordion() {
		$(
			".feedzy-accordion .feedzy-accordion-item .feedzy-accordion-item__button"
		).on("click", function () {
			var current_item = $(this).parents();
			$(
				".feedzy-accordion .feedzy-accordion-item .feedzy-accordion-item__content"
			).each(function (i, el) {
				if ($(el).parent().is(current_item)) {
					$(el).prev().toggleClass("is-active");
					$(el).slideToggle();
					$(this).toggleClass("is-active");
				} else {
					$(el).prev().removeClass("is-active");
					$(el).slideUp();
					$(this).removeClass("is-active");
				}
			});
			if ( $('#fz-import-map-content').hasClass('is-active') ) {
				$('#feedzy_post_type').trigger('change');
			}
		});
		$(".feedzy-accordion .feedzy-accordion-item .feedzy-accordion-item__button")
			.first()
			.parent()
			.addClass("is-active");
		$(
			".feedzy-accordion .feedzy-accordion-item > .feedzy-accordion-item__content"
		)
			.first()
			.show()
			.addClass("is-active");
	}

	function feedzyTab() {
		$(".fz-tabs-menu a").click(function () {
			$(".fz-tabs-menu a").removeClass("active");
			$(this).addClass("active");
			var tagid = $(this).data("id");
			$(".fz-tab-content").hide();
			$("#" + tagid).show();
		});
		$(".fz-tabs-content .fz-tab-content").hide();
		$(".fz-tabs-menu a").first().addClass("active");
		$(".fz-tabs-content .fz-tab-content").first().show();
	}

	function feedzyMediaUploader() {
		// on upload button click
		$( 'body' ).on( 'click', '.feedzy-open-media', function( e ) {
			e.preventDefault();
			var button = $( this ),
			wp_media_uploader = wp.media( {
				title: feedzy.i10n.media_iframe_title,
				library : {
					type : 'image'
				},
				button: {
					text: feedzy.i10n.media_iframe_button
				},
				multiple: true
			} ).on( 'select', function() { // it also has "open" and "close" events
				var selectedAttachments = wp_media_uploader.state().get( 'selection' );
				var countSelected = selectedAttachments?.toJSON()?.length;
				button.parents( '.fz-form-group' ).find( '.feedzy-media-preview' ).remove();
				// Display image preview when a single image is selected.
				if ( 1 === countSelected ) {
					var attachment = selectedAttachments.first().toJSON();
					var attachmentUrl = attachment.url;
					if ( attachment.sizes.thumbnail ) {
						attachmentUrl = attachment.sizes.thumbnail.url;
					}
					if ( $( '.feedzy-media-preview' ).length ) {
						$( '.feedzy-media-preview' ).find( 'img' ).attr( 'src', attachmentUrl );
					} else {
						$( '<div class="fz-form-group mb-20 feedzy-media-preview"><img src="' + attachmentUrl + '"></div>' ).insertBefore( button.parent() );
					}
				} else {
					$( '<div class="fz-form-group mb-20 feedzy-media-preview"><a href="javascript:;" class="btn btn-outline-primary feedzy-images-selected">' + feedzy.i10n.action_btn_text_3.replace( '%d', countSelected ) + '</a></div>' ).insertBefore( button.parent() );
				}
				// Get all selected attachment ids.
				var ids = selectedAttachments.map( function( attachment ) {
					return attachment.id;
				} ).join( ',' );

				button.parent().find( '.feedzy-remove-media' ).addClass( 'is-show' );
				button.parent().find( 'input:hidden' ).val( ids ).trigger( 'change' );
				$( '.feedzy-open-media' ).html( feedzy.i10n.action_btn_text_2 );
			} );

			wp_media_uploader.on(' open', function() {
				var selectedVal = button.parent().find( 'input:hidden' ).val();
				if ( '' === selectedVal ) {
					return;
				}
				var selection = wp_media_uploader.state().get('selection');

				selectedVal.split(',').forEach(function( id ) {
					var attachment = wp.media.attachment( id );
					attachment.fetch();
					selection.add(attachment ? [attachment] : []);
				});
			} );

			wp_media_uploader.open();
		});

		$(document).on( 'click', '.feedzy-images-selected', function( e ) {
			$(this)
			.parents( '.fz-form-group' )
			.find( '.feedzy-open-media' )
			.trigger( 'click' );
			e.preventDefault();
		} );
	}
}(jQuery, feedzy));

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
		document.querySelector('input[name="feedzy_meta_data[default_thumbnail_id]"]').value = '0';
		document.querySelector('.feedzy-open-media').innerHTML = feedzy.i10n.action_btn_text_1;
	});
}
