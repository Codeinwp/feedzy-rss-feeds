<?php
/**
 * Tests for the scripts enqueued on the Dashboard (support) page tabs.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Support_Page_Scripts extends WP_UnitTestCase {

	/**
	 * Admin class under test.
	 *
	 * @var Feedzy_Rss_Feeds_Admin
	 */
	private $admin;

	/**
	 * Request query vars saved before each test.
	 *
	 * @var array<string, mixed>
	 */
	private $original_get;

	/**
	 * Script registry saved before each test.
	 *
	 * @var WP_Scripts|null
	 */
	private $original_scripts;

	/**
	 * Save the globals the enqueue touches and open the Dashboard page.
	 *
	 * @access public
	 */
	public function set_up(): void {
		parent::set_up();

		$this->original_get     = $_GET;
		$this->original_scripts = isset( $GLOBALS['wp_scripts'] ) ? $GLOBALS['wp_scripts'] : null;
		$GLOBALS['wp_scripts']  = new WP_Scripts();

		$this->admin = new Feedzy_Rss_Feeds_Admin( 'feedzy-rss-feeds', 'latest' );

		set_current_screen( 'feedzy_page_feedzy-support' );
	}

	/**
	 * Restore the request, the screen and the script registry.
	 *
	 * @access public
	 */
	public function tear_down(): void {
		$_GET                      = $this->original_get;
		$GLOBALS['wp_scripts']     = $this->original_scripts;
		$GLOBALS['current_screen'] = null;

		parent::tear_down();
	}

	/**
	 * Enqueue the admin assets for a Dashboard tab.
	 *
	 * @param string $tab Tab slug.
	 */
	private function enqueue_for_tab( string $tab ): void {
		$_GET['tab'] = $tab;
		$this->admin->enqueue_styles_admin();
	}

	/**
	 * The License tab gets the license script but not the feedback app, which has no mount point there.
	 */
	public function test_license_tab_loads_license_script_without_feedback_app(): void {
		$this->enqueue_for_tab( 'license' );

		$this->assertTrue( wp_script_is( 'feedzy-rss-feeds_license', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'feedzy-rss-feeds_feedback', 'enqueued' ) );
	}

	/**
	 * The Help us improve tab loads the feedback app.
	 */
	public function test_improve_tab_loads_feedback_app(): void {
		$this->enqueue_for_tab( 'improve' );

		$this->assertTrue( wp_script_is( 'feedzy-rss-feeds_feedback', 'enqueued' ) );
	}

	/**
	 * Other Dashboard tabs load neither script.
	 */
	public function test_other_tabs_load_neither_script(): void {
		$this->enqueue_for_tab( 'help' );

		$this->assertFalse( wp_script_is( 'feedzy-rss-feeds_feedback', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'feedzy-rss-feeds_license', 'enqueued' ) );
	}
}
