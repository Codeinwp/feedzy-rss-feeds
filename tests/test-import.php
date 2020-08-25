<?php
/**
 * WordPress unit test plugin.
 *
 * @package     feedzy-rss-feeds-pro
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Bogdan Preda
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */
class Test_Feedzy_Import extends WP_UnitTestCase {

	/**
	 * Sets up the test methods.
	 */
	public function setUp() {
		parent::setUp();
		 // avoids error - readfile(/src/wp-includes/js/wp-emoji-loader.js): failed to open stream: No such file or directory
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	}

	/**
	 * Test method to check Feedzy Imports
	 *
	 * @requires PHP 5.4
	 * @test
	 * @since   1.2.0
	 * @access  public
	 * @dataProvider importDataProvider
	 */
	public function test_feedzy_imports( $random_name1, $random_name2, $urls, $magic_tags = '[#item_content]', $use_filter = false ) {
		do_action( 'init' );

		$num_items = 1;
		$user_id       = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $user_id );
		$p = $this->factory->post->create_and_get(
			array(
				'post_title'  => $random_name1,
				'post_type'   => 'feedzy_categories',
				'post_author' => $user_id,
			)
		);

		$category_id    = wp_create_category( 'some name' );

		$_POST['post_type']                             = 'feedzy_categories';
		$_POST['feedzy_category_feed']                = $urls;
		$_POST['feedzy_category_meta_noncename']    = wp_create_nonce( FEEDZY_BASEFILE );

		// Test Create Feedzy Category
		do_action( 'save_post', $p->ID, $p );
		$post = get_post( $p->ID );
		$slug = $post->post_name;

		$user_id       = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $user_id );
		$p = $this->factory->post->create_and_get(
			array(
				'post_title'  => $random_name2,
				'post_type'   => 'feedzy_imports',
				'post_author' => $user_id,
				'feedzy_import_noncename' => wp_create_nonce( FEEDZY_BASEFILE ),
			)
		);

		$_POST['feedzy_import_noncename']    = wp_create_nonce( FEEDZY_BASEFILE );
		$_POST['post_type']                      = 'feedzy_imports';
		$_POST['feedzy_meta_data']['source']                           = $slug;
		$_POST['feedzy_meta_data']['import_post_type']                 = 'post';
		$_POST['feedzy_meta_data']['import_post_term']                 = 'category_' . $category_id;
		$_POST['feedzy_meta_data']['import_post_status']               = 'publish';
		$_POST['feedzy_meta_data']['inc_key']                          = '';
		$_POST['feedzy_meta_data']['exc_key']                          = '';
		$_POST['feedzy_meta_data']['import_post_title']                = '[#item_title]';
		$_POST['feedzy_meta_data']['import_post_date']                 = '[#item_date]';
		$_POST['feedzy_meta_data']['import_post_content']              = "{$magic_tags}";
		$_POST['feedzy_meta_data']['import_post_featured_img']         = '[#item_image]';
		$_POST['feedzy_meta_data']['import_feed_limit']         = $num_items;
		$_POST['custom_vars_key']                                    = array();
		$_POST['custom_vars_value']                                  = array();

		do_action( 'save_post_feedzy_imports', $p->ID, $p );
		$this->assertEquals( $p->post_title, $random_name2 );
		$this->assertEquals( $p->post_type, 'feedzy_imports' );

		$import_post_type     = get_post_meta( $p->ID, 'import_post_type', true );
		$import_post_term     = get_post_meta( $p->ID, 'import_post_term', true );
		$import_post_status   = get_post_meta( $p->ID, 'import_post_status', true );
		$source               = get_post_meta( $p->ID, 'source', true );
		$inc_key              = get_post_meta( $p->ID, 'inc_key', true );
		$exc_key              = get_post_meta( $p->ID, 'exc_key', true );
		$import_title         = get_post_meta( $p->ID, 'import_post_title', true );
		$import_date          = get_post_meta( $p->ID, 'import_post_date', true );
		$import_content       = get_post_meta( $p->ID, 'import_post_content', true );
		$import_featured_img  = get_post_meta( $p->ID, 'import_post_featured_img', true );
		$import_custom_fields = get_post_meta( $p->ID, 'imports_custom_fields', true );
		$import_feed_limit    = get_post_meta( $p->ID, 'import_feed_limit', true );

		$this->assertEquals( 'post', $import_post_type );
		$this->assertEquals( 'category_' . $category_id, $import_post_term );
		$this->assertEquals( 'publish', $import_post_status );
		$this->assertEquals( $slug, $source );
		$this->assertEquals( '', $inc_key );
		$this->assertEquals( '', $exc_key );
		$this->assertEquals( '[#item_title]', $import_title );
		$this->assertEquals( '[#item_date]', $import_date );
		$this->assertEquals( "{$magic_tags}", $import_content );
		$this->assertEquals( '[#item_image]', $import_featured_img );
		$this->assertEquals( '', $import_custom_fields );
		$this->assertEquals( $num_items, $import_feed_limit );

		$feed_src = str_replace( PHP_EOL, '', $urls );

		// Do Cron
		$options = array(
			'feeds'          => $source,
			// comma separated feeds url
			'max'            => $import_feed_limit,
			// number of feeds items (0 for unlimited)
			'feed_title'     => 'no',
			// display feed title yes/no
			'target'         => '_blank',
			// _blank, _self
			'title'          => '',
			// strip title after X char
			'meta'           => 'yes',
			// yes, no
			'summary'        => 'yes',
			// strip title
			'summarylength'  => '',
			// strip summary after X char
			'thumb'          => 'auto',
			// yes, no, auto
			'default'        => '',
			// default thumb URL if no image found (only if thumb is set to yes or auto)
			'size'           => '250',
			// thumbs pixel size
			'keywords_title' => $inc_key,
			'keywords_ban'   => $exc_key,
			'columns'        => 1,
			'offset'        => 0,
			'multiple_meta'        => 'no',
		);

		$expected_created_posts = $num_items;

		if ( $use_filter ) {
			$expected_created_posts = 0;
			add_filter(
				'feedzy_insert_post_args', function( $args ) {
					// empty title and content
					$args['post_title'] = '';
					$args['post_content'] = '';
					return $args;
				}, 10, 1
			);
		}

		$admin = new Feedzy_Rss_Feeds_Import( 'feedzy-rss-feeds', '1.2.0' );
		$test = $admin->get_job_feed( $options, $import_content );
		$this->assertGreaterThan( 0, count( $test ), sprintf( 'Problematic URLs: %s', print_r( $urls, true ) ) );

		do_action( 'feedzy_cron', '1' );

		$created = get_posts(
			array(
				'numberposts' => $num_items,
				'orderby' => 'date',
				'order' => 'DESC',
				'post_status' => 'publish',
			)
		);

		$this->assertEquals( $expected_created_posts, count( $created ) );

		// stop tests if the number of posts created (as expected) was 0.
		if ( $expected_created_posts === 0 ) {
			return;
		}

		// get categories of the post
		$categories = wp_get_post_categories(
			$created[0]->ID,
			array(
				'fields' => 'ids',
			)
		);
		$this->assertEquals( $test[0]['item_url_title'], $created[0]->post_title );
		$this->assertEquals( 'post', $created[0]->post_type );
		$this->assertNotContains( '[#item_full_content]', $created[0]->post_content );
		$item_content   = ! empty( $test[0]['item_content'] ) ? $test[0]['item_content'] : $test[0]['item_description'];
		if ( '[#item_categories]' === $magic_tags ) {
			$item_content   = ! empty( $test[0]['item_categories'] ) ? $test[0]['item_categories'] : '';
			$this->assertArrayNotHasKey( 'item_full_content', $test[0] );
			$this->assertEquals( strip_tags( $item_content ), strip_tags( $created[0]->post_content ) );
			$this->assertContains( 'Infrastructure (Public Works)', $created[0]->post_content );
			$this->assertContains( 'Newark Watershed Conservation and Development Corp', $created[0]->post_content );
		} else {
			$this->assertArrayNotHasKey( 'item_full_content', $test[0] );
			$this->assertEquals( strip_tags( $item_content ), strip_tags( $created[0]->post_content ) );
		}

		$this->assertEquals( 1, count( $categories ) );
		$this->assertEquals( $category_id, $categories[0] );
		$this->assertEquals( 1, get_post_meta( $created[0]->ID, 'feedzy', true ) );
		$this->assertNotEmpty( get_post_meta( $created[0]->ID, 'feedzy_item_url', true ) );

		// Check Post Delete
		$this->assertNotEquals( false, wp_delete_post( $p->ID ) );
		return $created[0];
	}


	/**
	 * Test canonical URLs.
	 *
	 * @requires PHP 5.4
	 * @test
	 * @depends test_feedzy_imports
	 * @access public
	 */
	public function test_canonical_url( $post ) {
		if ( ! $post ) {
			// we should not need this but because the depends annotation is not working, this is a workaround.
			$post = $this->test_feedzy_imports( $this->get_rand_name(), $this->get_rand_name(), $this->get_two_rand_feeds(), '[#item_content]' );
		}

		// switch off canonical urls.
		$settings = get_option( 'feedzy-settings' );
		update_option( 'feedzy-settings', $settings );

		$url    = get_permalink( $post->ID );
		$item_url = get_post_meta( $post->ID, 'feedzy_item_url', true );
		$links = $this->parse_html( $url, array( 'link' ) );
		$canonical_url = null;
		foreach ( $links as $link ) {
			if ( 'canonical' === $link['rel'] ) {
				$canonical_url = $link['href'];
				break;
			}
		}
		$this->assertNotEquals( $item_url, $canonical_url, 'Canonical URLs are equal' );

		// switch on canonical urls.
		$settings = get_option( 'feedzy-settings' );
		$settings['canonical'] = 1;
		update_option( 'feedzy-settings', $settings );

		$url    = get_permalink( $post->ID );
		$item_url = get_post_meta( $post->ID, 'feedzy_item_url', true );
		$links = $this->parse_html( $url, array( 'link' ) );
		$canonical_url = null;
		foreach ( $links as $link ) {
			if ( 'canonical' === $link['rel'] ) {
				$canonical_url = $link['href'];
				break;
			}
		}
		$this->assertEquals( $item_url, htmlspecialchars( $canonical_url ), 'Canonical URLs are not equal' );

	}


	/**
	 * Utility method to generate a random 5 char string.
	 *
	 * @since   1.2.0
	 * @access  private
	 * @return string
	 */
	private function get_rand_name() {
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$result     = '';
		for ( $i = 0; $i < 5; $i ++ ) {
			$result .= $characters[ mt_rand( 0, 61 ) ];
		}

		return $result;
	}

	/**
	 * Utility method to generate a random feed urls.
	 *
	 * @since   1.2.0
	 * @access  private
	 * @return string
	 */
	private function get_two_rand_feeds() {
		return 'http://s3.amazonaws.com/downloads.themeisle.com/sample-feed-import.xml';
	}

	/**
	 * Provide the data for testing
	 *
	 * @access public
	 */
	public function importDataProvider() {
		return array(
			array(
				$this->get_rand_name(),
				$this->get_rand_name(),
				$this->get_two_rand_feeds(),
				'<span></span>[#item_content]',
			),
			array(
				$this->get_rand_name(),
				$this->get_rand_name(),
				$this->get_two_rand_feeds(),
				'[#item_categories]',
			),
			array(
				$this->get_rand_name(),
				$this->get_rand_name(),
				$this->get_two_rand_feeds(),
				'<span></span>[#item_content]',
				true,
			),
		);
	}

	/**
	 * Parses the given XML and returns the nodes requested as key value pairs.
	 */
	private function parse_xml( $string, $key, $value ) {
		libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $string );

		$array = array();
		foreach ( $xml->channel->item as $item ) {
			$array[] = array(
				$key   => iconv( 'UTF-8', 'ASCII//IGNORE', trim( (string) $item->$key ) ),
				$value => iconv( 'UTF-8', 'ASCII//IGNORE', trim( (string) $item->$value ) ),
			);
		}
		return $array;
	}

	/**
	 * Generates the HTML source of the given link.
	 */
	private function get_page_source( $link ) {
		$html = '<html>';
		$this->go_to( $link );
		ob_start();
		do_action( 'wp_head' );
		$html .= '<head>' . ob_get_clean() . '</head>';

		ob_start();
		do_action( 'wp_footer' );
		$html .= '<body>' . /* somehow get the body too?? */ ob_get_clean() . '</body>';
		return $html . '</html>';
	}

	/**
	 * Creates the HTML of the given link and returns the nodes requested.
	 */
	private function parse_html( $link, $tags = array(), $debug = false ) {
		$html = $this->get_page_source( $link );
		if ( $debug ) {
			error_log( "$link === $html" );
		}
		return $this->parse_content( $html, $tags );
	}

	/**
	 * Parses the HTML of the given link and returns the nodes requested.
	 */
	private function parse_content( $html, $tags = array() ) {
		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$dom->loadHTML( $html );

		$array = array();
		foreach ( $tags as $tag ) {
			foreach ( $dom->getElementsByTagName( $tag ) as $node ) {
				$array[] = $this->get_node_as_array( $node );
			}
		}
		return $array;
	}

	// @codingStandardsIgnoreStart WordPress.NamingConventions.ValidVariableName.NotSnakeCase
	/**
	 * Extracts the node from the HTML source.
	 */
	private function get_node_as_array( $node ) {
		$array = false;

		if ( $node->hasAttributes() ) {
			foreach ( $node->attributes as $attr ) {
				$array[ $attr->nodeName ] = $attr->nodeValue;
			}
		}

		if ( $node->hasChildNodes() ) {
			if ( $node->childNodes->length == 1 ) {
				$array[ $node->firstChild->nodeName ] = $node->firstChild->nodeValue;
			} else {
				foreach ( $node->childNodes as $childNode ) {
					if ( $childNode->nodeType != XML_TEXT_NODE ) {
						$array[ $childNode->nodeName ][] = $this->get_node_as_array( $childNode );
					}
				}
			}
		}

		return $array;
	}
	// @codingStandardsIgnoreEnd WordPress.NamingConventions.ValidVariableName.NotSnakeCase
}
