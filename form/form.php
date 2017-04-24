<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://themeisle.com
 * @since      3.0.0
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/form
 */

$html_parts = Feedzy_Rss_Feeds_Ui_Lang::get_form_elements();
?>
<!DOCTYPE html>
<html>
<head>
	<!-- Disable browser caching of dialog window -->
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="pragma" content="no-cache" />
	<link rel="stylesheet" href="<?php echo FEEDZY_ABSURL . 'css/form.css?h=' . date( 'dmYHis' ); ?>" type="text/css" media="all" />
</head>
<body>
	<div class="feedzy-popup-form container">
		<?php
		$output = '';
		if ( ! empty( $html_parts ) ) {
			foreach ( $html_parts as $item => $section ) {
				$output .= '<div class="container feedzy_' . $item . '">';
				$output .= '<h5>' . $section['title'] . '</h5>';
				if ( isset( $section['description'] ) ) {
					$output .= '<p>' . $section['description'] . '</p>';
				}
				if ( ! empty( $section['elements'] ) ) {
					foreach ( $section['elements'] as $name => $props ) {
						$element = '';
						$disabled = '';
						$badge = '';
						if ( isset( $props['disabled'] ) && $props['disabled'] ) {
							$disabled = 'disabled="true"';
							$badge = '<small class="feedzy_pro_tag">' . __( 'Premium', 'feedzy-rss-feeds' ) . '</small>';
						}
						switch ( $props['type'] ) {
							case 'select':
								$element = '<select name="' . $name . '" data-feedzy="' . $name . '" ' . $disabled . ' >';
								foreach ( $props['opts'] as $opt => $values ) {
									$checked = '';
									if ( $props['value'] == $values['value'] ) {
										$checked = 'selected';
									}
									$element .= '<option value="' . $values['value'] . '" ' . $checked . ' > ' . $values['label'] . '</option>';
								}
								$element .= '</select>';
								break;
							case 'radio':
								foreach ( $props['opts'] as $opt => $values ) {
									$checked = '';
									if ( $props['value'] == $values['value'] ) {
										$checked = 'checked="checked"';
									}
									$element .= '<label class="feedzy-radio-image feedzy-template-' . $values['value']
									            . '"><input type="radio" name="' . $name . '" data-feedzy="' . $name . '" value="' . $values['value'] . '" ' . $checked . ' ' . $disabled . ' />' .
												$values['label'] . '</label>';
								}
								break;
							case 'checkbox':
								foreach ( $props['opts'] as $opt => $values ) {
									$checked = '';
									if ( $props['value'] == $values['value'] ) {
										$checked = 'checked="checked"';
									}
									$element .= '<input type="checkbox" name="' . $name . '" data-feedzy="' . $name . '" value="' . $values['value'] . '" ' . $checked . ' ' . $disabled . ' /> ' . $values['label'];
								}
								break;
							case 'file':
								$element = '
                                    <input type="text" class="column column-50 float-left" name="' . $name . '" data-feedzy="' . $name . '" id="feedzy_image_url" placeholder="' . $props['placeholder'] . '" value="' . $props['value'] . '">
                                    <input type="button" class="column column-50 float-right button-outline" name="upload-btn" id="feedzy_upload_btn" value="' . $props['button']['button_text'] . '">
                                ';
								break;
							default:
								$element = '<input type="text" name="' . $name . '" data-feedzy="' . $name . '" value="' . $props['value'] . '" placeholder="' . $props['placeholder'] . '" ' . $disabled . ' />';
								break;
						} // End switch().
						$output .= '
                        <div class="row feedzy_element_' . $name . '">
                           <div class="column column-50">
                                <label for="' . $name . '">' . $props['label'] . $badge . '</label>
                            </div>
                            <div class="column column-50">
                                ' . $element . '
                            </div>
                            <hr/>
                        </div>
                        ';
					} // End foreach().
				} // End if().
				$output .= '</div>';
			} // End foreach().
		} // End if().
		echo $output;
		?>
	</div>
	<script type="text/javascript">
		var args = top.tinymce.activeEditor.windowManager.getParams();
		var $ = args.jquery;
		var editor = args.editor;
		var wp = args.wp;
		var custom_uploader;
		$(document).ready(function($) {
			$(document).on('click', '#feedzy_upload_btn', function (e) {
				e.preventDefault();
				var upload_button = $(this);
				custom_uploader = wp.media.frames.file_frame = wp.media({
					title: editor.getLang( 'feedzy_tinymce_plugin.image_button' ),
					button: {
						text: editor.getLang( 'feedzy_tinymce_plugin.image_button' )
					},
					multiple: false
				});

				custom_uploader.on('select', function () {
					var attachment = custom_uploader.state().get('selection').first().toJSON();
					upload_button.siblings('input[type="text"]').val(attachment.url);
				});

				custom_uploader.open();
			});
		});
	</script>
</body>
</html>
