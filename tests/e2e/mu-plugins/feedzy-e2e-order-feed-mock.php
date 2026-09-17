<?php
/**
 * Plugin Name: Feedzy E2E Order Feed Mock
 * Description: Serves a canned feed whose items are deliberately not in chronological document order, so Feed Order tests can tell sorted output from the original feed order.
 *
 * @package feedzy-rss-feeds
 */

add_filter(
	'pre_http_request',
	function ( $preempt, $args, $url ) {
		if ( '/feedzy-e2e-order-feed.xml' !== wp_parse_url( $url, PHP_URL_PATH ) ) {
			return $preempt;
		}

		// Document order is shuffled on purpose: the middle item comes first, so
		// an unsorted import cannot produce either chronological order.
		$items = array(
			array( 'middle', 'Wed, 03 Jan 2024 10:00:00 +0000' ),
			array( 'oldest', 'Mon, 01 Jan 2024 10:00:00 +0000' ),
			array( 'newest', 'Fri, 05 Jan 2024 10:00:00 +0000' ),
			array( 'fourth', 'Tue, 02 Jan 2024 10:00:00 +0000' ),
			array( 'second', 'Thu, 04 Jan 2024 10:00:00 +0000' ),
		);

		$entries = '';
		foreach ( $items as $item ) {
			list( $slug, $date ) = $item;
			$entries            .= <<<XML
		<item>
			<title>Feedzy order {$slug}</title>
			<link>https://tests.example.com/feedzy-order-{$slug}</link>
			<guid isPermaLink="false">feedzy-order-{$slug}</guid>
			<description>Feedzy order {$slug} description.</description>
			<pubDate>{$date}</pubDate>
		</item>

XML;
		}

		$body = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
	<channel>
		<title>Feedzy E2E Order Feed</title>
		<link>https://tests.example.com/</link>
		<description>Feed with out-of-order publication dates.</description>
{$entries}	</channel>
</rss>
XML;

		return array(
			'headers'  => array(
				'content-type' => 'application/rss+xml; charset=UTF-8',
			),
			'body'     => $body,
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	},
	10,
	3
);
