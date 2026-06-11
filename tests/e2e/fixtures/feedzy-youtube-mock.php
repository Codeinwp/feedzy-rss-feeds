<?php
/**
 * Mock YouTube RSS feed for E2E testing.
 *
 * This mu-plugin intercepts WordPress HTTP requests to YouTube feed URLs
 * and returns a fixture YouTube RSS feed response, allowing E2E tests to
 * run without depending on external YouTube network access.
 */

if ( ! defined( 'E2E_TESTING' ) || ! E2E_TESTING ) {
	return;
}

add_filter( 'pre_http_request', 'feedzy_e2e_mock_youtube_feed', 10, 3 );

/**
 * Intercept YouTube feed requests and return a mock response.
 *
 * @param false|array|\WP_Error $preempt Whether to preempt the request.
 * @param array                 $args    Request arguments.
 * @param string                $url     The request URL.
 *
 * @return false|array
 */
function feedzy_e2e_mock_youtube_feed( $preempt, $args, $url ) {
	if ( strpos( $url, 'youtube.com/feeds/videos.xml' ) === false ) {
		return $preempt;
	}

	$xml = '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015"
      xmlns:media="http://search.yahoo.com/mrss/"
      xmlns="http://www.w3.org/2005/Atom">
  <link rel="self" href="https://www.youtube.com/feeds/videos.xml?channel_id=UCSHmNs-_UuU1CfPhSbilTZQ"/>
  <id>yt:channel:UCSHmNs-_UuU1CfPhSbilTZQ</id>
  <yt:channelId>UCSHmNs-_UuU1CfPhSbilTZQ</yt:channelId>
  <title>Test YouTube Channel</title>
  <link rel="alternate" href="https://www.youtube.com/channel/UCSHmNs-_UuU1CfPhSbilTZQ"/>
  <author>
    <name>Test Channel</name>
    <uri>https://www.youtube.com/channel/UCSHmNs-_UuU1CfPhSbilTZQ</uri>
  </author>
  <published>2024-01-01T00:00:00+00:00</published>
  <entry>
    <id>yt:video:abc123testid</id>
    <yt:videoId>abc123testid</yt:videoId>
    <yt:channelId>UCSHmNs-_UuU1CfPhSbilTZQ</yt:channelId>
    <title>Test YouTube Video Title</title>
    <link rel="alternate" href="https://www.youtube.com/watch?v=abc123testid"/>
    <author>
      <name>Test Channel</name>
      <uri>https://www.youtube.com/channel/UCSHmNs-_UuU1CfPhSbilTZQ</uri>
    </author>
    <published>2024-01-01T00:00:00+00:00</published>
    <updated>2024-01-01T00:00:00+00:00</updated>
    <media:group>
      <media:title>Test YouTube Video Title</media:title>
      <media:content url="https://www.youtube.com/v/abc123testid?version=3" type="application/x-shockwave-flash" width="640" height="390"/>
      <media:thumbnail url="https://i.ytimg.com/vi/abc123testid/hqdefault.jpg" width="480" height="360"/>
      <media:description>Test video description for E2E testing.</media:description>
    </media:group>
  </entry>
</feed>';

	return array(
		'headers'       => array( 'content-type' => 'application/atom+xml; charset=UTF-8' ),
		'body'          => $xml,
		'response'      => array(
			'code'    => 200,
			'message' => 'OK',
		),
		'cookies'       => array(),
		'http_response' => null,
	);
}
