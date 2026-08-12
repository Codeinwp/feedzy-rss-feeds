<?php
/**
 * Tests for the AI rewrite content handling in Feedzy_Rss_Feeds_Actions.
 *
 * Covers issue #1298: the "Rewrite with AI" action silently truncated the
 * source content to 3,000 bytes (splitting UTF-8 code points) for every
 * provider, and returned preprocessed content on Managed AI failures.
 *
 * @package    feedzy-rss-feeds
 */

if ( ! class_exists( 'Feedzy_Rss_Feeds_Pro_Openai' ) ) {
	/**
	 * Test double for the Pro OpenAI client (legacy BYOK integration).
	 */
	class Feedzy_Rss_Feeds_Pro_Openai {

		/**
		 * The last content string passed to call_api().
		 *
		 * @var string|null
		 */
		public static $last_content = null;

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
			self::$last_content = $content;
			return 'BYOK_REWRITTEN';
		}
	}
}

if ( ! class_exists( 'Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager' ) ) {
	/**
	 * Test double for the Pro Managed AI quota manager.
	 */
	class Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager {

		const MANAGED_AI_OPTION_KEY = 'feedzy_test_managed_ai';

		/**
		 * Whether Managed AI is enabled for the test.
		 *
		 * @var bool
		 */
		public static $enabled = false;

		/**
		 * The response returned by run_feedzy_ai_workflow().
		 *
		 * @var mixed
		 */
		public static $response = 'MANAGED_REWRITTEN';

		/**
		 * The last args passed to run_feedzy_ai_workflow().
		 *
		 * @var array|null
		 */
		public static $last_args = null;

		/**
		 * Whether Managed AI is enabled.
		 *
		 * @return bool
		 */
		public function is_managed_ai_enabled() {
			return self::$enabled;
		}

		/**
		 * Record the request and return the configured response.
		 *
		 * @param string $type Workflow type.
		 * @param array  $args Workflow arguments.
		 * @param int    $job_id Import job ID.
		 * @return mixed
		 */
		public function run_feedzy_ai_workflow( $type, $args, $job_id ) {
			self::$last_args = $args;
			return self::$response;
		}
	}
}

/**
 * Test the AI rewrite content handling.
 */
class Test_Ai_Actions extends WP_UnitTestCase {

	/**
	 * Reset the test doubles before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		Feedzy_Rss_Feeds_Pro_Openai::$last_content         = null;
		Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager::$enabled    = false;
		Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager::$response   = 'MANAGED_REWRITTEN';
		Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager::$last_args  = null;
	}

	/**
	 * Run a chained AI action against the given item content.
	 *
	 * @param string $item_content The feed item content.
	 * @param string $action_id The action id (chat_gpt_rewrite or fz_summarize).
	 * @return string The action result.
	 */
	private function run_ai_action( $item_content, $action_id = 'chat_gpt_rewrite' ) {
		$data       = 'fz_summarize' === $action_id ? '{"fz_summarize":true}' : '{"ChatGPT":"Rewrite this: {content}"}';
		$serialized = '[[{"value":"[{"id":"' . $action_id . '","tag":"item_content","data":' . $data . '}]"}]]';

		$job     = new stdClass();
		$job->ID = 123;

		// Mirror Feedzy_Rss_Feeds_Import::get_actions_runner() + run_action_job() usage.
		$actions       = Feedzy_Rss_Feeds_Actions::instance();
		$actions->type = 'item_content';
		$actions->set_raw_serialized_actions( $serialized );

		return $actions->run_action_job( $actions->get_serialized_actions(), '', $job, '', array( 'item_content' => $item_content ) );
	}

	/**
	 * Legacy BYOK requests are still capped, but the cut never splits a UTF-8 code point.
	 */
	public function test_byok_truncation_is_utf8_safe() {
		// 1 ASCII byte followed by 2,000 two-byte characters: byte 3,000 lands mid-character.
		$content = 'a' . str_repeat( 'ă', 2000 );

		$result   = $this->run_ai_action( $content );
		$received = Feedzy_Rss_Feeds_Pro_Openai::$last_content;

		$this->assertSame( 'BYOK_REWRITTEN', $result );
		$this->assertNotNull( $received );

		$sent_content = str_replace( 'Rewrite this: ', '', $received );
		$this->assertLessThanOrEqual( 3000, strlen( $sent_content ) );
		// A blind substr() would leave 3,000 bytes ending in half a code point.
		$this->assertSame( 2999, strlen( $sent_content ) );
		$this->assertTrue( (bool) preg_match( '//u', $sent_content ), 'Truncated content must be valid UTF-8.' );
		// Byte-level check on purpose — no mbstring dependency in the tests either.
		$this->assertSame( 'ă', substr( $sent_content, -2 ) );
	}

	/**
	 * The cut is also safe when it lands inside a 4-byte (supplementary plane) character.
	 */
	public function test_byok_truncation_is_safe_for_four_byte_characters() {
		// 2 ASCII bytes then 4-byte emoji: byte 3,000 lands two bytes into an emoji.
		$content = 'ab' . str_repeat( '💩', 1000 );

		$this->run_ai_action( $content );
		$sent_content = str_replace( 'Rewrite this: ', '', Feedzy_Rss_Feeds_Pro_Openai::$last_content );

		// 2 + 749 * 4 = 2998: the two dangling emoji bytes are dropped.
		$this->assertSame( 2998, strlen( $sent_content ) );
		$this->assertTrue( (bool) preg_match( '//u', $sent_content ), 'Truncated content must be valid UTF-8.' );
		$this->assertSame( '💩', substr( $sent_content, -4 ) );
	}

	/**
	 * The feedzy_chat_gpt_content_limit filter still controls the BYOK cap.
	 */
	public function test_byok_truncation_filter_still_applies() {
		add_filter( 'feedzy_chat_gpt_content_limit', $set_limit = function () {
			return 10;
		} );

		$this->run_ai_action( str_repeat( 'x', 50 ) );
		$sent_content = str_replace( 'Rewrite this: ', '', Feedzy_Rss_Feeds_Pro_Openai::$last_content );

		remove_filter( 'feedzy_chat_gpt_content_limit', $set_limit );

		$this->assertSame( 10, strlen( $sent_content ) );
	}

	/**
	 * BYOK content at or below the limit is passed through untouched.
	 */
	public function test_byok_short_content_is_not_modified() {
		$content = 'Short multibyte content: ăâîșț.';
		$this->run_ai_action( $content );

		$this->assertSame( 'Rewrite this: ' . $content, Feedzy_Rss_Feeds_Pro_Openai::$last_content );
	}

	/**
	 * Managed AI receives the complete source content beyond 3,000 bytes.
	 */
	public function test_managed_ai_receives_full_content() {
		Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager::$enabled = true;

		$paragraph = str_repeat( 'Șase săptămâni de știri importante. ', 200 );
		$content   = '<p>' . $paragraph . '</p>';

		$result = $this->run_ai_action( $content );

		$this->assertSame( 'MANAGED_REWRITTEN', $result );
		$this->assertSame(
			wp_strip_all_tags( $content ),
			Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager::$last_args['text']
		);
		$this->assertGreaterThan( 3000, strlen( Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager::$last_args['text'] ) );
	}

	/**
	 * A failed Managed AI rewrite returns the original source byte-for-byte, HTML intact.
	 */
	public function test_managed_ai_failure_returns_original_content() {
		Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager::$enabled  = true;
		Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager::$response = new WP_Error( 'ai_failed', 'Workflow failed.' );

		$content = '<p>' . str_repeat( 'Lungă poveste fără sfârșit. ', 200 ) . '</p><p><strong>END-MARKER</strong></p>';

		$result = $this->run_ai_action( $content );

		$this->assertSame( $content, $result );
	}

	/**
	 * The summarize action routes through the same rewrite path and gets the full content too.
	 */
	public function test_summarize_managed_ai_receives_full_content() {
		Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager::$enabled = true;

		$content = str_repeat( 'Zusammenfassung für längere Artikel über 3000 Bytes. ', 150 );
		$result  = $this->run_ai_action( $content, 'fz_summarize' );

		$this->assertSame( 'MANAGED_REWRITTEN', $result );
		$this->assertGreaterThan( 3000, strlen( Feedzy_Rss_Feeds_Pro_Ai_Quota_Manager::$last_args['text'] ) );
	}
}
