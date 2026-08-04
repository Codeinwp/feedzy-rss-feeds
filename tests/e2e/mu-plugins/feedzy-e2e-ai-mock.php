<?php
/**
 * Plugin Name: Feedzy E2E AI Mock
 * Description: Test-only mu-plugin. Stubs the Pro AI integrations and serves a long multibyte feed so e2e tests can cover the AI rewrite content handling (issue #1298). Never ship outside the e2e environment.
 *
 * @package feedzy-rss-feeds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Feedzy_Rss_Feeds_Pro_Openai' ) ) {
	/**
	 * Stub for the Pro OpenAI client (legacy BYOK integration).
	 */
	class Feedzy_Rss_Feeds_Pro_Openai {
		/**
		 * Record the request and return a fixed rewrite result.
		 *
		 * @param mixed  $settings Plugin settings.
		 * @param string $content Prompt content.
		 * @param string $type Request type.
		 * @param array  $additional Additional data.
		 * @return string
		 */
		public function call_api( $settings, $content, $type = '', $additional = array() ) {
			update_option(
				'feedzy_e2e_ai_byok_request',
				array(
					'bytes'      => strlen( $content ),
					'valid_utf8' => (bool) preg_match( '//u', $content ),
				),
				false
			);
			return 'BYOK_REWRITTEN_CONTENT';
		}
	}
}

if ( ! class_exists( 'Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager' ) ) {
	/**
	 * Stub for the Pro Managed AI quota manager.
	 */
	class Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager {

		const MANAGED_AI_OPTION_KEY = 'feedzy_e2e_managed_ai_enabled';

		/**
		 * Whether Managed AI is enabled (driven by an option so tests can toggle it).
		 *
		 * @return bool
		 */
		public function is_managed_ai_enabled() {
			return (bool) get_option( 'feedzy_e2e_managed_ai_enabled' );
		}

		/**
		 * Record the request and return a fixed result or a forced failure.
		 *
		 * @param string $type Workflow type.
		 * @param array  $args Workflow arguments.
		 * @param int    $job_id Import job ID.
		 * @return string|WP_Error
		 */
		public function run_feedzy_ai_workflow( $type, $args, $job_id ) {
			update_option(
				'feedzy_e2e_ai_managed_request',
				array(
					'action'     => $type,
					'text_bytes' => isset( $args['text'] ) ? strlen( $args['text'] ) : 0,
					'valid_utf8' => isset( $args['text'] ) && (bool) preg_match( '//u', $args['text'] ),
				),
				false
			);
			if ( get_option( 'feedzy_e2e_managed_ai_fail' ) ) {
				return new WP_Error( 'feedzy_e2e_forced_failure', 'Forced failure for e2e tests.' );
			}
			return 'MANAGED_REWRITTEN_CONTENT';
		}
	}
}

/**
 * Serve a mock RSS feed with one item whose content is well over 3,000 bytes
 * of multibyte text, so a byte-based cut would land mid code point.
 *
 * The mock URL lives on example.com because Feedzy's source validation
 * (wp_http_validate_url) resolves the host via DNS before any HTTP request
 * is made; a fake TLD would be dropped as an invalid source. The request
 * itself never leaves the site — this filter preempts it.
 */
add_filter(
	'pre_http_request',
	function ( $preempt, $args, $url ) {
		if ( false === strpos( $url, 'feedzy-e2e-long-feed' ) ) {
			return $preempt;
		}

		$paragraph = str_repeat( 'Șase săptămâni de știri importante — naïve café, 日本語のテキスト. ', 100 );
		$content   = '<p>' . $paragraph . '</p><p><strong>FEEDZY-E2E-END-MARKER</strong></p>';

		$body = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
<title>Feedzy E2E Long Feed</title>
<link>https://example.com</link>
<description>Long multibyte content feed</description>
<item>
<title>Long multibyte article</title>
<link>https://example.com/feedzy-e2e-long-feed/article-1</link>
<guid>https://example.com/feedzy-e2e-long-feed/article-1</guid>
<pubDate>' . gmdate( 'D, d M Y H:i:s' ) . ' GMT</pubDate>
<description>Short description</description>
<content:encoded><![CDATA[' . $content . ']]></content:encoded>
</item>
</channel>
</rss>';

		return array(
			'headers'       => array( 'content-type' => 'application/rss+xml; charset=UTF-8' ),
			'body'          => $body,
			'response'      => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'       => array(),
			'http_response' => null,
		);
	},
	10,
	3
);

/**
 * REST endpoints for the tests: configure the mock and read recorded requests.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'feedzy-e2e/v1',
			'/ai-mock',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => function () {
						return array(
							'byok'    => get_option( 'feedzy_e2e_ai_byok_request', null ),
							'managed' => get_option( 'feedzy_e2e_ai_managed_request', null ),
						);
					},
				),
				array(
					'methods'             => 'POST',
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => function ( $request ) {
						update_option( 'feedzy_e2e_managed_ai_enabled', (bool) $request->get_param( 'managed' ), false );
						update_option( 'feedzy_e2e_managed_ai_fail', (bool) $request->get_param( 'fail' ), false );
						delete_option( 'feedzy_e2e_ai_byok_request' );
						delete_option( 'feedzy_e2e_ai_managed_request' );
						return array( 'ok' => true );
					},
				),
			)
		);
	}
);
