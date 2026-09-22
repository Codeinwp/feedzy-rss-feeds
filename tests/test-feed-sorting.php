<?php
/**
 * Tests for the custom feed item comparator.
 *
 * @package    feedzy-rss-feeds
 */

/**
 * A minimal stand-in for a SimplePie item with a title.
 */
class Feedzy_Test_Sortable_Item {

	/**
	 * The item title.
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Constructor.
	 *
	 * @param string $title The item title.
	 */
	public function __construct( $title ) {
		$this->title = $title;
	}

	/**
	 * The item title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}
}

class Test_Feed_Sorting extends WP_UnitTestCase {

	/**
	 * The comparator's static state, captured so the test cannot leak into other suites.
	 *
	 * @var array
	 */
	private $original_state = array();

	/**
	 * Set up test environment.
	 *
	 * @access public
	 */
	public function setUp(): void {
		parent::setUp();

		foreach ( array( 'sc', 'custom_sorting' ) as $property ) {
			$reflection = new ReflectionProperty( 'Feedzy_Rss_Feeds_Util_Feed', $property );
			$reflection->setAccessible( true );
			$this->original_state[ $property ] = $reflection->getValue();
		}
	}

	/**
	 * Clean up after tests.
	 *
	 * @access public
	 */
	public function tearDown(): void {
		foreach ( $this->original_state as $property => $value ) {
			$reflection = new ReflectionProperty( 'Feedzy_Rss_Feeds_Util_Feed', $property );
			$reflection->setAccessible( true );
			$reflection->setValue( null, $value );
		}

		parent::tearDown();
	}

	/**
	 * Sort a list of titles with the given sort token.
	 *
	 * @param string $sort The sort token.
	 * @param array  $titles The titles to sort.
	 *
	 * @return array The sorted titles.
	 */
	private function sort_titles( $sort, $titles ) {
		// The constructor is what arms the custom comparator.
		new Feedzy_Rss_Feeds_Util_Feed( array( 'sort' => $sort ) );

		$items = array_map(
			function ( $title ) {
				return new Feedzy_Test_Sortable_Item( $title );
			},
			$titles
		);

		usort( $items, array( 'Feedzy_Rss_Feeds_Util_Feed', 'sort_items' ) );

		return array_map(
			function ( $item ) {
				return $item->get_title();
			},
			$items
		);
	}

	/**
	 * Numeric titles sort by value, not character by character.
	 *
	 * @access public
	 */
	public function test_numeric_titles_sort_numerically() {
		$this->assertSame(
			array( '2', '10', '100' ),
			$this->sort_titles( 'title_asc', array( '100', '2', '10' ) )
		);

		$this->assertSame(
			array( '100', '10', '2' ),
			$this->sort_titles( 'title_desc', array( '10', '100', '2' ) )
		);
	}

	/**
	 * Textual titles sort alphabetically.
	 *
	 * @access public
	 */
	public function test_text_titles_sort_alphabetically() {
		$this->assertSame(
			array( 'Alpha', 'Beta', 'Gamma' ),
			$this->sort_titles( 'title_asc', array( 'Gamma', 'Alpha', 'Beta' ) )
		);

		$this->assertSame(
			array( 'Gamma', 'Beta', 'Alpha' ),
			$this->sort_titles( 'title_desc', array( 'Beta', 'Gamma', 'Alpha' ) )
		);
	}

	/**
	 * The comparator returns integers, never booleans.
	 *
	 * @access public
	 */
	public function test_comparator_returns_integers() {
		new Feedzy_Rss_Feeds_Util_Feed( array( 'sort' => 'title_asc' ) );

		$alpha = new Feedzy_Test_Sortable_Item( 'Alpha' );
		$beta  = new Feedzy_Test_Sortable_Item( 'Beta' );

		$this->assertSame( -1, Feedzy_Rss_Feeds_Util_Feed::sort_items( $alpha, $beta ) );
		$this->assertSame( 1, Feedzy_Rss_Feeds_Util_Feed::sort_items( $beta, $alpha ) );
		$this->assertSame( 0, Feedzy_Rss_Feeds_Util_Feed::sort_items( $alpha, $alpha ) );
	}
}
