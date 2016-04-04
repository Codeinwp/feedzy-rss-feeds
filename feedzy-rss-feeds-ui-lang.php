<?php
/***************************************************************
 * SECURITY : Exit if accessed directly
***************************************************************/
if ( !defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed!' );
}


/***************************************************************
 * Translation for TinyMCE
 ***************************************************************/ 

if ( ! class_exists( '_WP_Editors' ) )
    require( ABSPATH . WPINC . '/class-wp-editor.php' );

function feedzy_tinymce_translation() {
    
	$strings = array(
		'plugin_title' 	=> __( 'Insert FEEDZY RSS Feeds Shortcode', 'feedzy_rss_translate' ),
		'feeds' 		=> __( 'The feed(s) URL (comma-separated list).', 'feedzy_rss_translate' ) . ' ' . __( 'If your feed is not valid, it won\'t work.', 'feedzy_rss_translate' ),
		'maximum' 		=> __( 'Number of items to display.', 'feedzy_rss_translate' ),
		'feed_title' 	=> __( 'Should we display the RSS title?', 'feedzy_rss_translate' ),
		'target' 		=> __( 'Links may be opened in the same window or a new tab.', 'feedzy_rss_translate' ),
		'title' 		=> __( 'Trim the title of the item after X characters.', 'feedzy_rss_translate' ),
		'meta' 			=> __( 'Should we display the date of publication and the author name?', 'feedzy_rss_translate' ),
		'summary' 		=> __( 'Should we display a description (abstract) of the retrieved item?', 'feedzy_rss_translate' ),
		'summarylength'	=> __( 'Crop description (summary) of the element after X characters.', 'feedzy_rss_translate' ),
		'thumb' 		=> __( 'Should we display the first image of the content if it is available?', 'feedzy_rss_translate' ),
		'defaultimg' 	=> __( 'Default thumbnail URL if no image is found.', 'feedzy_rss_translate' ),
		'size' 			=> __( 'Thumblails dimension. Do not include "px". Eg: 150', 'feedzy_rss_translate' ),
		'keywords_title'=> __( 'Only display item if title contains specific keyword(s) (comma-separated list/case sensitive).', 'feedzy_rss_translate' ),
		'text_default' 	=> __( 'Do not specify', 'feedzy_rss_translate' ),
		'text_no' 		=> __( 'No', 'feedzy_rss_translate' ),
		'text_yes' 		=> __( 'Yes', 'feedzy_rss_translate' ),
		'text_auto' 	=> __( 'Auto', 'feedzy_rss_translate' )
	);
    
	$locale = _WP_Editors::$mce_locale;
    $translated = 'tinyMCE.addI18n("' . $locale . '.feedzy_tinymce_plugin", ' . json_encode( $strings ) . ");\n";

     return $translated;
}

$strings = feedzy_tinymce_translation();