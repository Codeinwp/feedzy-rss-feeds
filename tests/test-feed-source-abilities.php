<?php
/**
 * Feed source ability persistence and output tests.
 *
 * @package feedzy-rss-feeds
 */
class Test_Feed_Source_Abilities extends WP_UnitTestCase {

	private $feeds;
	private $requests;

	public function set_up(): void {
		parent::set_up();
		Feedzy_Rss_Feeds::instance()->get_admin()->register_admin_capabilities();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->feeds    = array(
			'https://93.184.215.14/feedzy-fixture/one',
			'https://93.184.215.14/feedzy-fixture/two',
			'https://93.184.215.14/feedzy-fixture/three',
		);
		$token = wp_generate_uuid4();
		foreach ( $this->feeds as &$feed ) {
			$feed .= '?test=' . $token;
		}
		unset( $feed );
		$this->requests = array();
		add_filter( 'pre_http_request', array( $this, 'mock_feed_request' ), 10, 3 );
	}

	public function tear_down(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_feed_request' ), 10 );
		wp_set_current_user( 0 );
		parent::tear_down();
	}

	public function mock_feed_request( $preempt, $args, $url ) {
		$this->requests[] = $url;
		if ( ! in_array( $url, $this->feeds, true ) ) {
			return new WP_Error( 'invalid_feed', 'Not a fixture feed.' );
		}
		return array(
			'headers'  => array( 'content-type' => 'application/rss+xml; charset=UTF-8' ),
			'body'     => file_get_contents( __DIR__ . '/fixtures/sample-feed.xml' ),
			'response' => array( 'code' => 200, 'message' => 'OK' ),
			'cookies'  => array(),
			'filename' => '',
		);
	}

	private function execute_ability( $name, $input ) {
		$ability = wp_get_ability( 'feedzy/' . $name );
		$this->assertInstanceOf( WP_Ability::class, $ability );
		$result = $ability->execute( $input );
		$this->assertNotWPError( $result );
		$this->assertTrue( $result['success'], wp_json_encode( $result ) );
		return $result['data'];
	}

	private function seed_feed_source( $value ) {
		global $wpdb;
		$id = self::factory()->post->create( array( 'post_type' => 'feedzy_categories', 'post_title' => 'Seeded feeds', 'post_status' => 'publish' ) );
		// Seed existing data without consuming the metadata validation hooks for this request.
		$wpdb->insert( $wpdb->postmeta, array( 'post_id' => $id, 'meta_key' => 'feedzy_category_feed', 'meta_value' => $value ) );
		return $id;
	}

	public function test_create_preserves_multiple_feeds(): void {
		$data = $this->execute_ability( 'create-feed-source', array( 'title' => 'Multiple feeds', 'feeds' => $this->feeds ) );
		$this->assertSame( $this->feeds, $data['feeds'] );
		$this->assertSame( implode( ', ', $this->feeds ), get_post_meta( $data['id'], 'feedzy_category_feed', true ) );
		$this->assertSame( $this->feeds, array_values( array_unique( $this->requests ) ) );
	}

	public function test_single_feed_and_draft_status_are_preserved(): void {
		$data = $this->execute_ability( 'create-feed-source', array( 'title' => 'Draft feed', 'feeds' => array( $this->feeds[0] ), 'status' => 'draft' ) );
		$this->assertSame( array( $this->feeds[0] ), $data['feeds'] );
		$this->assertSame( 'draft', $data['status'] );
	}

	public function test_update_replaces_multiple_feeds_and_reads_them_back(): void {
		$id      = $this->seed_feed_source( $this->feeds[0] );
		$input   = $this->feeds;
		$input[] = 'https://93.184.215.14/feedzy-fixture/invalid';
		$data    = $this->execute_ability( 'update-feed-source', array( 'id' => $id, 'feeds' => $input ) );
		$this->assertSame( $this->feeds, $data['feeds'] );
		$this->assertSame( implode( ', ', $this->feeds ), get_post_meta( $id, 'feedzy_category_feed', true ) );
		$get = $this->execute_ability( 'get-feed-source', array( 'id' => $id ) );
		$this->assertSame( $this->feeds, $get['feeds'] );
		$list = $this->execute_ability( 'list-feed-sources', array( 'search' => 'Seeded feeds' ) );
		$this->assertCount( 1, $list['feed_sources'] );
		$this->assertSame( $this->feeds, $list['feed_sources'][0]['feeds'] );
	}

	public function test_existing_comma_separated_groups_keep_feeds_on_title_update(): void {
		$id   = $this->seed_feed_source( implode( ', ', $this->feeds ) );
		$data = $this->execute_ability( 'update-feed-source', array( 'id' => $id, 'title' => 'Renamed group' ) );
		$this->assertSame( $this->feeds, $data['feeds'] );
		$this->assertSame( 'Renamed group', $data['title'] );
	}

	public function test_invalid_feed_is_filtered_individually(): void {
		$input   = $this->feeds;
		$input[] = 'https://93.184.215.14/feedzy-fixture/invalid';
		$data    = $this->execute_ability( 'create-feed-source', array( 'title' => 'Mixed feeds', 'feeds' => $input ) );
		$this->assertSame( $this->feeds, $data['feeds'] );
	}

	public function test_legacy_newline_metadata_is_read_without_rewriting(): void {
		$id   = $this->seed_feed_source( implode( "\r\n", $this->feeds ) );
		$data = $this->execute_ability( 'get-feed-source', array( 'id' => $id ) );
		$this->assertSame( $this->feeds, $data['feeds'] );
		$this->assertSame( implode( "\r\n", $this->feeds ), get_post_meta( $id, 'feedzy_category_feed', true ) );
	}

	public function test_get_reads_existing_comma_separated_groups(): void {
		$id   = $this->seed_feed_source( implode( ', ', $this->feeds ) );
		$data = $this->execute_ability( 'get-feed-source', array( 'id' => $id ) );
		$this->assertSame( $this->feeds, $data['feeds'] );
	}

	/** @expectedIncorrectUsage WP_Ability::execute */
	public function test_author_cannot_update_administrator_feed_source(): void {
		$id = $this->seed_feed_source( $this->feeds[0] );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'author' ) ) );
		$result = wp_get_ability( 'feedzy/update-feed-source' )->execute( array( 'id' => $id, 'feeds' => $this->feeds ) );
		$this->assertWPError( $result );
		$this->assertSame( $this->feeds[0], get_post_meta( $id, 'feedzy_category_feed', true ) );
		$this->assertEmpty( $this->requests );
	}

	/** @expectedIncorrectUsage WP_Ability::execute */
	public function test_subscriber_cannot_create_feed_sources(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$result = wp_get_ability( 'feedzy/create-feed-source' )->execute( array( 'title' => 'Forbidden', 'feeds' => $this->feeds ) );
		$this->assertWPError( $result );
		$this->assertEmpty( $this->requests );
	}
}
