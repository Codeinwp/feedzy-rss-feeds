<?php
/**
 * Tests for the Feed Groups admin list columns.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Feed_Group_Columns extends WP_UnitTestCase {

	/**
	 * Columns WordPress passes to the Feed Groups list table.
	 *
	 * @var array{cb: string, title: string, date: string}
	 */
	private $core_columns = array(
		'cb'    => '<input type="checkbox" />',
		'title' => 'Title',
		'date'  => 'Date',
	);

	/**
	 * Restore the global post set while rendering rows.
	 *
	 * @access public
	 */
	public function tear_down(): void {
		unset( $GLOBALS['post'] );

		parent::tear_down();
	}

	/**
	 * The Source column is registered right before the Slug column.
	 */
	public function test_columns_include_source_before_slug(): void {
		$columns = apply_filters( 'manage_feedzy_categories_posts_columns', $this->core_columns );

		$this->assertSame( array( 'cb', 'title', 'source', 'slug', 'actions', 'date' ), array_keys( $columns ) );
		$this->assertSame( 'Source', $columns['source'] );
	}

	/**
	 * A group's saved feed URLs are listed in its row.
	 */
	public function test_row_lists_saved_source_urls(): void {
		$first   = 'https://www.wpbeginner.com/category/news/feed/';
		$second  = 'https://planet.wordpress.org/feed/category/news/';
		$post_id = $this->create_group( $first . ', ' . $second );

		$row = $this->render_row( $post_id );

		$this->assertStringContainsString( '<a href="' . $first . '" target="_blank" title="Click to view">' . $first . '</a>, <a href="' . $second . '"', $row );
		$this->assertStringNotContainsString( '...', $row );
	}

	/**
	 * A saved URL whose host no longer resolves is still listed.
	 */
	public function test_row_lists_unresolvable_source_url(): void {
		$post_id = $this->create_group( 'https://feeds.example.invalid/rss, https://wordpress.org/news/feed/' );

		$row = $this->render_row( $post_id );

		$this->assertStringContainsString( '<a href="https://feeds.example.invalid/rss" target="_blank" title="Click to view">https://feeds.example.invalid/rss</a>, <a href="https://wordpress.org/news/feed/"', $row );
	}

	/**
	 * Long source lists are cut off once the visible URLs pass the length limit.
	 */
	public function test_row_truncates_long_source_list(): void {
		$urls    = array(
			'https://www.wpbeginner.com/category/news/feed/',
			'https://planet.wordpress.org/feed/category/news/',
			'https://themeisle.com/blog/category/wordpress/feed/',
			'https://wpshout.com/feed/',
		);
		$post_id = $this->create_group( implode( ', ', $urls ) );

		$row = $this->render_row( $post_id );

		$this->assertStringContainsString( $urls[2] . '</a>, ...', $row );
		$this->assertStringNotContainsString( $urls[3], $row );
	}

	/**
	 * A group without saved feed URLs says so in its row.
	 */
	public function test_row_without_sources_shows_placeholder(): void {
		$row = $this->render_row( $this->create_group( '' ) );

		$this->assertStringContainsString( 'No Source Configured', $row );
	}

	/**
	 * Create a published feed group, storing its URLs without the live feed validation.
	 *
	 * @param string $feeds Comma-separated feed URLs.
	 *
	 * @return int
	 */
	private function create_group( string $feeds ): int {
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'feedzy_categories',
				'post_status' => 'publish',
			)
		);

		if ( '' !== $feeds ) {
			$admin = Feedzy_Rss_Feeds::instance()->get_admin();
			remove_filter( 'add_post_metadata', array( $admin, 'validate_category_feeds' ) );
			add_post_meta( $post_id, 'feedzy_category_feed', $feeds );
			add_filter( 'add_post_metadata', array( $admin, 'validate_category_feeds' ), 10, 5 );
		}

		return $post_id;
	}

	/**
	 * Render every registered column cell of a group's row, as the list table does.
	 *
	 * @param int $post_id Feed group post ID.
	 *
	 * @return string
	 */
	private function render_row( int $post_id ): string {
		$GLOBALS['post'] = get_post( $post_id );
		$columns         = apply_filters( 'manage_feedzy_categories_posts_columns', $this->core_columns );

		ob_start();
		foreach ( array_keys( $columns ) as $column ) {
			do_action( 'manage_feedzy_categories_posts_custom_column', $column, $post_id );
		}

		return (string) ob_get_clean();
	}
}
