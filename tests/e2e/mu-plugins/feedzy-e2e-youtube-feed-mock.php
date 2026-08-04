<?php
/**
 * Plugin Name: Feedzy E2E YouTube Feed Mock
 * Description: Serves a canned YouTube channel feed so e2e tests do not depend on live YouTube availability (runner IPs are frequently throttled).
 *
 * @package feedzy-rss-feeds
 */

add_filter(
	'pre_http_request',
	function ( $preempt, $args, $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		$path = wp_parse_url( $url, PHP_URL_PATH );

		if ( false === strpos( (string) $host, 'youtube.com' ) || '/feeds/videos.xml' !== $path ) {
			return $preempt;
		}

		$channel_id = 'UCSHmNs-_UuU1CfPhSbilTZQ';
		$query      = wp_parse_url( $url, PHP_URL_QUERY );
		if ( $query ) {
			parse_str( $query, $query_args );
			if ( ! empty( $query_args['channel_id'] ) ) {
				$channel_id = $query_args['channel_id'];
			}
		}

		$body = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015" xmlns:media="http://search.yahoo.com/mrss/" xmlns="http://www.w3.org/2005/Atom">
	<link rel="self" href="https://www.youtube.com/feeds/videos.xml?channel_id={$channel_id}"/>
	<id>yt:channel:{$channel_id}</id>
	<yt:channelId>{$channel_id}</yt:channelId>
	<title>Feedzy E2E Channel</title>
	<link rel="alternate" href="https://www.youtube.com/channel/{$channel_id}"/>
	<author>
		<name>Feedzy E2E</name>
		<uri>https://www.youtube.com/channel/{$channel_id}</uri>
	</author>
	<published>2024-01-01T00:00:00+00:00</published>
	<entry>
		<id>yt:video:dQw4w9WgXcQ</id>
		<yt:videoId>dQw4w9WgXcQ</yt:videoId>
		<yt:channelId>{$channel_id}</yt:channelId>
		<title>Feedzy E2E sample video one</title>
		<link rel="alternate" href="https://www.youtube.com/watch?v=dQw4w9WgXcQ"/>
		<author>
			<name>Feedzy E2E</name>
		</author>
		<published>2024-01-02T00:00:00+00:00</published>
		<updated>2024-01-02T00:00:00+00:00</updated>
		<media:group>
			<media:title>Feedzy E2E sample video one</media:title>
			<media:content url="https://www.youtube.com/v/dQw4w9WgXcQ?version=3" type="application/x-shockwave-flash" width="640" height="390"/>
			<media:thumbnail url="https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg" width="480" height="360"/>
			<media:description>Sample description one.</media:description>
		</media:group>
	</entry>
	<entry>
		<id>yt:video:9bZkp7q19f0</id>
		<yt:videoId>9bZkp7q19f0</yt:videoId>
		<yt:channelId>{$channel_id}</yt:channelId>
		<title>Feedzy E2E sample video two</title>
		<link rel="alternate" href="https://www.youtube.com/watch?v=9bZkp7q19f0"/>
		<author>
			<name>Feedzy E2E</name>
		</author>
		<published>2024-01-03T00:00:00+00:00</published>
		<updated>2024-01-03T00:00:00+00:00</updated>
		<media:group>
			<media:title>Feedzy E2E sample video two</media:title>
			<media:content url="https://www.youtube.com/v/9bZkp7q19f0?version=3" type="application/x-shockwave-flash" width="640" height="390"/>
			<media:thumbnail url="https://i.ytimg.com/vi/9bZkp7q19f0/hqdefault.jpg" width="480" height="360"/>
			<media:description>Sample description two.</media:description>
		</media:group>
	</entry>
</feed>
XML;

		return array(
			'headers'  => array(
				'content-type' => 'text/xml; charset=UTF-8',
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
