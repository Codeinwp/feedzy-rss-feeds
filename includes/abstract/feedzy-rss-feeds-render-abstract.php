<?php
/**
 * The Abstract class for render.
 *
 * @link       http://themeisle.com
 * @since      3.0.0
 *
 * @package    Feedzy_Rss_Feeds
 * @subpackage Feedzy_Rss_Feeds/includes/abstract
 */
/**
 * Abstract render class implements all routine stuff required for template
 * rendering.
 *
 * @package    Feedzy_Rss_Feeds
 * @subpackage Feedzy_Rss_Feeds/includes/abstract
 * @author     Themeisle <friends@themeisle.com>
 * @abstract
 */
abstract class Feedzy_Rss_Feeds_Render_Abstract {

	/**
	 * The storage of all data associated with this render.
	 *
	 * @since 3.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_data;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( $data = array() ) {
		$this->_data = $data;
	}

	/**
	 * Returns property associated with the render.
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 * @param string $name The name of a property.
	 * @return mixed Returns mixed value of a property or NULL if a property doesn't exist.
	 */
	public function __get( $name ) {
		return array_key_exists( $name, $this->_data ) ? $this->_data[ $name ] : null;
	}

	/**
	 * Checks whether the render has specific property or not.
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 * @param string $name The key name.
	 * @return boolean TRUE if the property exists, otherwise FALSE.
	 */
	public function __isset( $name ) {
		return array_key_exists( $name, $this->_data );
	}

	/**
	 * Associates the render with specific property.
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 * @param string $name The name of a property to associate.
	 * @param mixed  $value The value of a property.
	 */
	public function __set( $name, $value ) {
		$this->_data[ $name ] = $value;
	}

	/**
	 * Unassociates specific property from the render.
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 * @param string $name The name of the property to unassociate.
	 */
	public function __unset( $name ) {
		unset( $this->_data[ $name ] );
	}

	/**
	 * Renders template.
	 *
	 * @since 3.0.0
	 *
	 * @abstract
	 * @access protected
	 */
	protected abstract function _to_html();

	/**
	 * Builds template and return it as string.
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function to_html() {
		ob_start();
		$this->_to_html();
		return ob_get_clean();
	}

	/**
	 * Returns built template as string.
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 * @return type
	 */
	public function __toString() {
		return $this->to_html();
	}

	/**
	 * Renders the template.
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 */
	public function render() {
		$this->_to_html();
	}

}
