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
 *
 */
$wp_include = "../wp-load.php";
$i = 0;
while(!file_exists($wp_include) && $i++ < 10){
    $wp_include = "../$wp_include";
}
require($wp_include);

$feedzyLangClass = new Feedzy_Rss_Feeds_Ui_Lang();
$elements = $feedzyLangClass->get_form_elements();
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Disable browser caching of dialog window -->
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="stylesheet" href="<?php echo FEEDZY_ABSURL . 'css/form.css'; ?>" type="text/css" media="all" />
</head>
<body>
    <div class="feedzy-popup-form">
        <?php
        if( !empty( $elements ) ) {
            foreach ( $elements as $name => $props ) {
                $element = '';
                switch ($props['type']) {
                    case 'select':
                        $element = '<select name="' . $name . '">';
                        foreach ( $props['opts'] as $opt => $values ) {
                            $checked = '';
                            if( $props['value'] == $values['value'] ) {
                                $checked = 'selected';
                            }
                            $element .= '<option value="' . $values['value'] . '" ' . $checked . ' > ' . $values['label'] . '</option>';
                        }
                        $element .= '</select>';
                        break;
                    case 'radio':
                        foreach ( $props['opts'] as $opt => $values ) {
                            $checked = '';
                            if( $props['value'] == $values['value'] ) {
                                $checked = 'checked';
                            }
                            $element .= '<input type="radio" name="' . $name . '[]" value="' . $values['value'] . '" ' . $checked . ' /> ' . $values['label'];
                        }
                        break;
                    case 'checkbox':
                        foreach ( $props['opts'] as $opt => $values ) {
                            $checked = '';
                            if( $props['value'] == $values['value'] ) {
                                $checked = 'checked';
                            }
                            $element .= '<input type="checkbox" name="' . $name . '[]" value="' . $values['value'] . '" ' . $checked . ' /> ' . $values['label'];
                        }
                        break;
                    case 'file':
                        $element = '<input type="file" name="' . $name . '" value="' . $props['value'] . '" />';
                        break;
                    default:
                        $element = '<input type="text" name="' . $name . '" value="' . $props['value'] . '" />';
                        break;
                }
                echo '
                <div class="row">
                    <div class="col-md-6">
                        <label for="' . $name . '">' . $props['label'] . '</label>
                    </div>
                    <div class="col-md-6">
                        ' . $element . '
                    </div>
                </div>
                ';
            }
        }
        ?>
    </div>
</body>
</html>