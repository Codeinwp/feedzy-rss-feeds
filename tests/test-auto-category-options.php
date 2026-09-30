<?php
/**
 * Tests for the bounded category option list used by the
 * Auto Categories Mapping settings rows.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Auto_Category_Options extends WP_UnitTestCase {

	/**
	 * Remove the limit filter added by individual tests.
	 *
	 * @access public
	 */
	public function tearDown(): void {
		remove_all_filters( 'feedzy_post_taxonomy_limit' );
		remove_all_filters( 'feedzy_auto_categories_search_limit' );
		parent::tearDown();
	}

	/**
	 * Create categories and return their term IDs.
	 *
	 * @param int $count How many categories to create.
	 *
	 * @return array List of term IDs.
	 */
	private function make_categories( $count ) {
		$ids = array();
		for ( $i = 0; $i < $count; $i++ ) {
			$ids[] = $this->factory()->category->create( array( 'name' => 'Auto Cat ' . $i ) );
		}
		return $ids;
	}

	/**
	 * The option list is bounded by the taxonomy limit filter.
	 *
	 * @access public
	 */
	public function test_option_list_is_bounded_by_the_limit() {
		$this->make_categories( 8 );

		add_filter(
			'feedzy_post_taxonomy_limit',
			function () {
				return 5;
			}
		);

		$options = Feedzy_Rss_Feeds_Admin::get_auto_category_options( array() );

		$this->assertCount( 5, $options );
	}

	/**
	 * The limit filter receives the category taxonomy as its second argument.
	 *
	 * @access public
	 */
	public function test_limit_filter_receives_the_category_taxonomy() {
		$received = null;

		add_filter(
			'feedzy_post_taxonomy_limit',
			function ( $limit, $taxonomy ) use ( &$received ) {
				$received = $taxonomy;
				return $limit;
			},
			10,
			2
		);

		Feedzy_Rss_Feeds_Admin::get_auto_category_options( array() );

		$this->assertSame( 'category', $received );
	}

	/**
	 * A saved mapping pointing outside the limit stays selectable, so that
	 * re-saving the settings cannot silently drop the rule.
	 *
	 * @access public
	 */
	public function test_saved_category_outside_the_limit_is_included() {
		$ids  = $this->make_categories( 8 );
		$last = end( $ids );

		add_filter(
			'feedzy_post_taxonomy_limit',
			function () {
				return 2;
			}
		);

		$options = Feedzy_Rss_Feeds_Admin::get_auto_category_options(
			array(
				array(
					'keywords' => 'foo',
					'category' => $last,
				),
			)
		);

		$this->assertArrayHasKey( $last, $options );
		$this->assertSame( get_term( $last )->name, $options[ $last ] );
	}

	/**
	 * A saved category already inside the limit is not duplicated.
	 *
	 * @access public
	 */
	public function test_saved_category_inside_the_limit_is_not_duplicated() {
		$ids = $this->make_categories( 4 );

		$options = Feedzy_Rss_Feeds_Admin::get_auto_category_options(
			array(
				array(
					'keywords' => 'foo',
					'category' => $ids[0],
				),
				array(
					'keywords' => 'bar',
					'category' => $ids[0],
				),
			)
		);

		$this->assertSame( array_keys( $options ), array_unique( array_keys( $options ) ) );
	}

	/**
	 * Empty and non numeric mapping values are ignored.
	 *
	 * @access public
	 */
	public function test_empty_mapping_values_are_ignored() {
		$this->make_categories( 3 );

		$baseline = Feedzy_Rss_Feeds_Admin::get_auto_category_options( array() );

		$options = Feedzy_Rss_Feeds_Admin::get_auto_category_options(
			array(
				array(
					'keywords' => 'foo',
					'category' => '',
				),
				array(
					'keywords' => 'bar',
					'category' => 'not-a-term',
				),
				array( 'keywords' => 'baz' ),
			)
		);

		$this->assertSame( $baseline, $options );
	}

	/**
	 * Search reaches categories that fall outside the bounded initial list.
	 *
	 * @access public
	 */
	public function test_search_finds_category_outside_the_bound() {
		$this->factory()->category->create( array( 'name' => 'Needle Category' ) );
		$this->make_categories( 8 );

		add_filter(
			'feedzy_post_taxonomy_limit',
			function () {
				return 1;
			}
		);

		$options = Feedzy_Rss_Feeds_Admin::get_auto_category_options( array() );
		$this->assertNotContains( 'Needle Category', $options );

		$results = Feedzy_Rss_Feeds_Admin::get_auto_category_search_results( 'Needle' );
		$names   = wp_list_pluck( $results['categories'], 'name' );

		$this->assertContains( 'Needle Category', $names );
	}

	/**
	 * Search results are paginated and report whether another page exists.
	 *
	 * @access public
	 */
	public function test_search_results_are_paginated() {
		$this->make_categories( 7 );

		add_filter(
			'feedzy_auto_categories_search_limit',
			function () {
				return 3;
			}
		);

		$first = Feedzy_Rss_Feeds_Admin::get_auto_category_search_results( 'Auto Cat', 1 );
		$this->assertCount( 3, $first['categories'] );
		$this->assertTrue( $first['has_more'] );

		$second = Feedzy_Rss_Feeds_Admin::get_auto_category_search_results( 'Auto Cat', 2 );
		$this->assertCount( 3, $second['categories'] );

		$this->assertSame(
			array(),
			array_intersect(
				wp_list_pluck( $first['categories'], 'id' ),
				wp_list_pluck( $second['categories'], 'id' )
			)
		);

		$third = Feedzy_Rss_Feeds_Admin::get_auto_category_search_results( 'Auto Cat', 3 );
		$this->assertCount( 1, $third['categories'] );
		$this->assertFalse( $third['has_more'] );
	}

	/**
	 * A search with no match returns an empty result set rather than everything.
	 *
	 * @access public
	 */
	public function test_search_without_match_returns_nothing() {
		$this->make_categories( 3 );

		$results = Feedzy_Rss_Feeds_Admin::get_auto_category_search_results( 'zzz-no-such-category' );

		$this->assertSame( array(), $results['categories'] );
		$this->assertFalse( $results['has_more'] );
	}
}
