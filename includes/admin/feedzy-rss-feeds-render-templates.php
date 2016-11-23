<?php
/**
 * The Widget functionality of the plugin.
 *
 * @link       http://themeisle.com
 * @since      3.0.0
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
 */
/**
 * Media view template rendering class.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
 * @author     Themeisle <friends@themeisle.com>
 * @since 3.0.0
 */
class Feedzy_Rss_Feeds_Render_Templates extends Feedzy_Rss_Feeds_Render_Abstract {

	/**
	 * The array of template names.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var array
	 */
	private $_templates = array(
		'feedzy-library-feed',
		'feedzy-library-empty',
	);

	/**
	 * Renders concreate template and wraps it into script tag.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The name of a template.
	 * @param string $callback The name of the function to render a template.
	 */
	private function _renderTemplate( $id, $callback ) {
		echo '<script id="tmpl-', $id, '" type="text/html">';
		call_user_func( array( $this, $callback ) );
		echo '</script>';
	}

	/**
	 * Renders library-chart template.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderFeedzyLibraryFeed() {
		echo '<div class="feedzy-library-feed-footer feedzy-clearfix">';
		echo '<a class="feedzy-library-feed-action feedzy-library-feed-delete" href="javascript:;" title="', esc_attr__( 'Delete', 'feedzy_rss_translate' ), '"></a>';
		echo '<a class="feedzy-library-feed-action feedzy-library-feed-insert" href="javascript:;" title="', esc_attr__( 'Insert', 'feedzy_rss_translate' ), '"></a>';

		echo '<span class="feedzy-library-feed-shortcode" title="', esc_attr__( 'Click to select', 'feedzy_rss_translate' ), '">&nbsp;[feedzy id=&quot;{{data.id}}&quot;]&nbsp;</span>';
		echo '</div>';
	}

	/**
	 * Renders library-empty template.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderFeedzyLibraryEmpty() {
		echo '<div class="feedzy-library-feed">';
		echo '<div class="feedzy-library-feed-canvas feedzy-library-nofeed-canvas">';
		echo '<div class="feedzy-library-notfound">', esc_html__( 'No saved feeds found', 'feedzy_rss_translate' ), '</div>';
		echo '</div>';
		echo '<div class="feedzy-library-feed-footer feedzy-clearfix">';
		echo '<span class="feedzy-library-feed-action feedzy-library-nofeed-delete"></span>';
		echo '<span class="feedzy-library-feed-action feedzy-library-nofeed-insert"></span>';

		echo '<span class="feedzy-library-feed-shortcode">';
		echo '&nbsp;[Feedzy RSS Feeds]&nbsp;';
		echo '</span>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Renders templates.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _to_html() {
		foreach ( $this->_templates as $template ) {
			$callback = '_render' . str_replace( ' ', '', ucwords( str_replace( '-', ' ', $template ) ) );
			$this->_renderTemplate( $template, $callback );
		}
	}

}
