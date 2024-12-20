<?php
/**
 * WordPress unit test conditions.
 *
 * @package     feedzy-rss-feeds
 * @subpackage  Tests
 * @copyright   Copyright (c) 2024, Hardeep Asrani
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0.0
 */
class Test_Feedzy_Conditions extends WP_UnitTestCase {

    /**
     * Conditions instance.
     *
     * @var Feedzy_Rss_Feeds_Conditions
     */
    private $conditions;

	/**
	 * Sets up the test methods.
	 */
	public function setUp(): void {
		parent::setUp();
		 // avoids error - readfile(/src/wp-includes/js/wp-emoji-loader.js): failed to open stream: No such file or directory
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        $this->conditions = new Feedzy_Rss_Feeds_Conditions();
	}

    public function test_migration() {
        $conditions = array(
			'keywords_inc'    => 'test',
			'keywords_exc'    => 'ban',
			'keywords_inc_on' => 'title',
			'keywords_exc_on' => 'title',
            'from_datetime'   => '2023-01-01',
            'to_datetime'     => '2023-12-31',
        );

        $expected = json_encode(array(
            'match' => 'all',
            'conditions' => array(
                array(
                    'field' => 'title',
                    'operator' => 'contains',
                    'value' => 'test',
                ),
                array(
                    'field' => 'title',
                    'operator' => 'not_contains',
                    'value' => 'ban',
                ),
                array(
                    'field' => 'date',
                    'operator' => 'greater_than',
                    'value' => '2023-01-01',
                ),
                array(
                    'field' => 'date',
                    'operator' => 'less_than',
                    'value' => '2023-12-31',
                ),
            ),
        ));

        $result = $this->conditions->migrate_conditions( $conditions );
        $this->assertJsonStringEqualsJsonString( $expected, $result );
    }

    public function test_is_condition_met_equals() {
        $condition = array(
            'operator' => 'equals',
            'value'    => 'test',
        );
        $this->assertTrue( $this->conditions->is_condition_met( $condition, 'test' ) );
    }

    public function test_is_condition_met_not_equals() {
        $condition = array(
            'operator' => 'not_equals',
            'value'    => 'test',
        );
        $this->assertFalse( $this->conditions->is_condition_met( $condition, 'test' ) );
    }

    public function test_is_condition_met_contains_single() {
        $condition = array(
            'operator' => 'contains',
            'value'    => 'test',
        );
        $this->assertTrue( $this->conditions->is_condition_met( $condition, 'this is a test' ) );
    }

    public function test_is_condition_met_contains_multiple() {
        $condition = array(
            'operator' => 'contains',
            'value'    => 'test, this',
        );
        $this->assertTrue( $this->conditions->is_condition_met( $condition, 'it is a test' ) );
    }

    public function test_is_condition_met_contains_plus() {
        $condition = array(
            'operator' => 'contains',
            'value'    => 'this+this',
        );
        $this->assertTrue( $this->conditions->is_condition_met( $condition, 'this is a test' ) );
    }

    public function test_is_condition_met_contains_plus_false() {
        $condition = array(
            'operator' => 'contains',
            'value'    => 'it+this',
        );
        $this->assertFalse( $this->conditions->is_condition_met( $condition, 'this is a test' ) );
    }

    public function test_is_condition_met_not_contains() {
        $condition = array(
            'operator' => 'not_contains',
            'value'    => 'test',
        );
        $this->assertFalse( $this->conditions->is_condition_met( $condition, 'this is a test' ) );
    }

    public function test_is_condition_met_greater_than() {
        $condition = array(
            'operator' => 'greater_than',
            'value'    => '5',
        );
        $this->assertTrue( $this->conditions->is_condition_met( $condition, '10' ) );
    }

    public function test_is_condition_met_less_than() {
        $condition = array(
            'operator' => 'less_than',
            'value'    => '10',
        );
        $this->assertTrue( $this->conditions->is_condition_met( $condition, '5' ) );
    }

    public function test_is_condition_met_regex() {
        $condition = array(
            'operator' => 'regex',
            'value'    => '/test/',
        );
        $this->assertTrue( $this->conditions->is_condition_met( $condition, 'this is a test' ) );


        $condition = array(
            'operator' => 'regex',
            'value'    => '\band\b',
        );
        $this->assertTrue( $this->conditions->is_condition_met( $condition, 'matt and tommy' ) );
    }
}
