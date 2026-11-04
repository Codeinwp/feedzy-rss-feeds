<?php
/**
 * Tests for the metadata Feedzy hands to the Themeisle SDK Support Hub.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Support_Hub extends WP_UnitTestCase {

	/**
	 * The filter returns the keys Feedzy opts into, and nothing else.
	 */
	public function test_metadata_has_the_agreed_keys() {
		$data = apply_filters( 'feedzy_rss_feeds_support_hub_metadata', array() );

		$this->assertIsArray( $data );
		$this->assertSame( array( 'is_pro', 'links' ), array_keys( $data ) );
		$this->assertArrayNotHasKey( 'setup_complete', $data, 'The setup nudge stays off for now.' );
	}

	/**
	 * The footer links point to Feedzy's docs and wordpress.org forum.
	 */
	public function test_links_point_to_feedzy() {
		$links = apply_filters( 'feedzy_rss_feeds_support_hub_metadata', array() )['links'];

		$this->assertSame(
			array(
				'https://docs.themeisle.com/collection/1569-feedzy-rss-feeds',
				'https://wordpress.org/support/plugin/feedzy-rss-feeds/',
			),
			wp_list_pluck( $links, 'url' )
		);
		foreach ( $links as $link ) {
			$this->assertNotEmpty( $link['icon'] );
			$this->assertNotEmpty( $link['text'] );
		}
	}

	/**
	 * Messages are tagged as Pro only while the Pro license is valid.
	 */
	public function test_is_pro_needs_a_valid_license() {
		add_filter( 'product_feedzy_license_status', array( $this, 'expired_license' ) );
		$this->assertFalse( apply_filters( 'feedzy_rss_feeds_support_hub_metadata', array() )['is_pro'] );
		remove_filter( 'product_feedzy_license_status', array( $this, 'expired_license' ) );

		add_filter( 'product_feedzy_license_status', array( $this, 'valid_license' ) );
		// test-import.php defines FEEDZY_PRO_ABSPATH for the rest of the run, so Pro may or may not be "installed" here.
		$this->assertSame( defined( 'FEEDZY_PRO_ABSPATH' ), apply_filters( 'feedzy_rss_feeds_support_hub_metadata', array() )['is_pro'] );
		remove_filter( 'product_feedzy_license_status', array( $this, 'valid_license' ) );
	}

	/**
	 * Reports a valid license.
	 *
	 * @return string
	 */
	public function valid_license() {
		return 'valid';
	}

	/**
	 * Reports an expired license.
	 *
	 * @return string
	 */
	public function expired_license() {
		return 'expired';
	}
}
