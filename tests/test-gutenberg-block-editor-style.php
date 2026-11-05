<?php
/**
 * Tests for the Classic block's editor styles.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Gutenberg_Block_Editor_Style extends WP_UnitTestCase {

	/**
	 * Script registry in place before the test.
	 *
	 * @var WP_Scripts|null
	 */
	private $original_scripts;

	/**
	 * Register the block, plus the script registry the iframe asset builder copies.
	 *
	 * @access public
	 */
	public function setUp(): void {
		parent::setUp();

		$this->original_scripts = $GLOBALS['wp_scripts'] ?? null;
		wp_scripts();
		Feedzy_Rss_Feeds_Gutenberg_Block::get_instance()->feedzy_register_block_type();
	}

	/**
	 * Undo the block registration and restore the script registry.
	 *
	 * @access public
	 */
	public function tearDown(): void {
		unregister_block_type( 'feedzy-rss-feeds/feedzy-block' );
		wp_deregister_style( 'feedzy-gutenberg-block-css' );
		wp_deregister_style( 'feedzy-block-css' );
		$GLOBALS['wp_scripts'] = $this->original_scripts;

		parent::tearDown();
	}

	/**
	 * The iframed editor must receive the block styles together with dashicons.
	 *
	 * @access public
	 */
	public function test_iframed_editor_assets_include_block_styles_and_dashicons(): void {
		$assets = _wp_get_iframed_editor_assets();

		$this->assertStringContainsString( "id='dashicons-css'", $assets['styles'] );
		$this->assertStringContainsString( "id='feedzy-block-css-css'", $assets['styles'] );
		$this->assertStringContainsString( "id='feedzy-gutenberg-block-css-css'", $assets['styles'] );
	}
}
