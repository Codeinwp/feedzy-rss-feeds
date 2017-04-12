<?php

/**
 * Wordpress unit test plugin.
 *
 * @package     Feedzy
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.10
 */
class Test_Plugin extends WP_UnitTestCase {
	/**
	 * Test if plugin is active.
	 */
	function testPlugin() {
		$this->assertTrue( is_plugin_active( 'feedzy-rss-feeds/feedzy-rss-feeds.php' ) );
	}
}
