<?php

/**
 * WordPress unit test plugin.
 *
 * @package     Feedzy
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.10
 */
class Test_Feedzy extends WP_UnitTestCase {
	/**
	 * Test method to check Create | Update and Feed from Slug
	 *
	 * @since   3.0.12
	 * @access  public
	 */
	public function test_feedzy_category() {

		$random_name = $this->get_rand_name();
		$user_id     = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $user_id );
		$p = $this->factory->post->create_and_get(
			array(
				'post_title'  => $random_name,
				'post_type'   => 'feedzy_categories',
				'post_author' => $user_id,
			)
		);

		$urls = $this->get_two_rand_feeds();

		$_POST[ 'feedzy_categories' . '_noncename' ] = wp_create_nonce( FEEDZY_BASEFILE );
		$_POST['post_type']                          = 'feedzy_categories';
		$_POST['feedzy_category_feed']               = $urls;

		$this->assertClassHasStaticAttribute( 'instance', 'Feedzy_Rss_Feeds' );

		// Test Create Feedzy Category
		do_action( 'save_post', $p->ID, $p );
		$post_meta_urls = get_post_meta( $p->ID, 'feedzy_category_feed', true );
		$this->assertEquals( $p->post_title, $random_name );
		$this->assertEquals( $p->post_type, 'feedzy_categories' );
		$this->assertEquals( $urls, $post_meta_urls );

		// Test Update Feedzy Category
		$urls_changed                  = $this->get_two_rand_feeds();
		$_POST['feedzy_category_feed'] = $urls_changed;
		do_action( 'save_post', $p->ID, $p );
		$post_meta_urls = get_post_meta( $p->ID, 'feedzy_category_feed', true );
		$this->assertEquals( $urls_changed, $post_meta_urls );

		// Test Feed By Category slug
		$post           = get_post( $p->ID );
		$slug           = $post->post_name;
		$feed_from_slug = apply_filters( 'feedzy_process_feed_source', $slug );
		$this->assertEquals( str_replace( PHP_EOL, '', $urls_changed ), $feed_from_slug );

	}

	/**
	 * Test feeds (like google news) whose query arguments need to be smartly changed depending on the short code parameters.
	 *
	 * @dataProvider googleNewsDataProvider
	 * @access  public
	 */
	public function test_google_news_like_feeds( $feed, $query_arg, $max = 5 ) {
		$content = do_shortcode('[feedzy-rss feeds="' . $feed . '" max="' . $max . '" target="_blank" refresh="1_hours" title="120" meta="yes" summary="yes" summarylength="275" size="160"]' );

		$lists		= $this->parse_content( $content, array( 'li' ) );
		$this->assertGreaterThan( 0, count( $lists ) );

		// let's be sure to only extract those LIs that have rss_item as the class.
		$count		= 0;
		foreach ( $lists as $list ) {
			if ( isset( $list['class'] ) && 'rss_item' === $list['class'] ) {
				$count++;
			}
		}

		$this->assertEquals( $max, $count );
	}

	/**
	 * Provide google news like feeds.
	 *
	 * @access  public
	 * @return array
	 */
	public function googleNewsDataProvider() {
		return array(
			array( 'http://news.google.com/news?hl=en&gl=us&q=google&num=2&um=1&ie=UTF-8&output=rss', 'num' ),
		);
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
	 * Parses the HTML content and returns the nodes requested.
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

	/**
	 * Utility method to generate a random 5 char string.
	 *
	 * @since   3.0.12
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
	 * @since   3.0.12
	 * @access  private
	 * @return string
	 */
	private function get_two_rand_feeds() {
		$sections  = array( 'economics', 'china', 'europe', 'united-states', 'international', 'science-technology' );
		$feed_urls = '';
		$section   = $sections[ mt_rand( 0, 5 ) ];
		$feed_urls .= 'http://www.economist.com/sections/' . $section . '/rss.xml, ' . PHP_EOL;
		$section   = $sections[ mt_rand( 0, 5 ) ];
		$feed_urls .= 'http://www.economist.com/sections/' . $section . '/rss.xml' . PHP_EOL;

		return $feed_urls;
	}
}
