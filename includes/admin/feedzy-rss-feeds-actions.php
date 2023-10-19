<?php
/**
 * The item content action chain process.
 *
 * @link       http://themeisle.com
 * @since      4.3
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/admin
 */

if ( ! class_exists( 'Feedzy_Rss_Feeds_Actions' ) ) {
	/**
	 * Singleton class for content action process.
	 */
	class Feedzy_Rss_Feeds_Actions {

		/**
		 * The main instance var.
		 *
		 * @var Feedzy_Rss_Feeds_Actions The one Feedzy_Rss_Feeds_Actions istance.
		 */
		private static $instance;

		/**
		 * Content actions.
		 *
		 * @var string $actions Content actions.
		 */
		private $actions;

		/**
		 * Setting options.
		 *
		 * @var string $settings Plugin setting.
		 */
		private $settings;

		/**
		 * Extract tags.
		 *
		 * @var string $extract_tags Extract tags.
		 */
		private $extract_tags;

		/**
		 * Check full content magic tag exists or not.
		 *
		 * @var bool $has_full_content
		 */
		public $has_full_content = false;

		/**
		 * Feed item.
		 *
		 * @var array $item
		 */
		public $item = array();

		/**
		 * Import job.
		 *
		 * @var object $job
		 */
		public $job = array();

		/**
		 * Action result.
		 *
		 * @var string $result
		 */
		public $result = '';

		/**
		 * Post content.
		 *
		 * @var string $post_content
		 */
		public $post_content = '';

		/**
		 * Language code.
		 *
		 * @var string $language_code
		 */
		public $language_code = '';

		/**
		 * Translation Language code.
		 *
		 * @var string $translation_lang
		 */
		public $translation_lang = '';

		/**
		 * Current action job.
		 *
		 * @var object $current_job
		 */
		private $current_job;

		/**
		 * Init the main singleton instance class.
		 *
		 * @return Feedzy_Rss_Feeds_Actions Return the instance class
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Feedzy_Rss_Feeds_Actions ) ) {
				self::$instance = new Feedzy_Rss_Feeds_Actions();
			}

			return self::$instance;
		}

		/**
		 * Run actions.
		 *
		 * @param string $actions Item content actions.
		 * @return string
		 */
		public function set_actions( $actions = '' ) {
			$this->actions = $actions;
			if ( empty( $this->actions ) ) {
				return $this->actions;
			}
			$this->extract_tags = $this->extract_magic_tags();
			return $this->extract_tags;
		}

		/**
		 * Set feedzy settings.
		 *
		 * @param string $options Setting option.
		 * @return void
		 */
		public function set_settings( $options ) {
			$this->settings = $options;
		}

		/**
		 * Extract magic tags.
		 *
		 * @return string
		 */
		public function extract_magic_tags() {
			preg_match_all( '/\[\[\{(.*)\}\]\]/U', $this->actions, $item_magic_tags, PREG_PATTERN_ORDER );
			$extract_tags = array();
			if ( ! empty( $item_magic_tags[0] ) ) {
				$extract_tags = array_map(
					function( $tag ) {
						$magic_tags = str_replace( array( '[[{"value":"', '"}]]' ), '', $tag );
						return array(
							'replace_to'   => $tag,
							'replace_with' => $magic_tags,
						);
					},
					$item_magic_tags[0]
				);
			}
			return $extract_tags;
		}

		/**
		 * Get magic tags.
		 */
		public function get_tags() {
			$replace_to   = array_column( $this->get_extract_tags(), 'replace_to' );
			$replace_with = array_column( $this->get_extract_tags(), 'replace_with' );
			return str_replace( $replace_to, $replace_with, $this->actions );
		}

		/**
		 * Get extract tags.
		 */
		public function get_extract_tags() {
			return $this->extract_tags;
		}

		/**
		 * Get actions.
		 */
		public function get_actions() {
			$replace_with = array_column( $this->get_extract_tags(), 'replace_with' );
			$actions      = array_map(
				function( $action ) {
					$replace_with = json_decode( $action );
					if ( $replace_with ) {
						return array(
							'replace_to'   => wp_json_encode( $replace_with ),
							'replace_with' => $replace_with,
						);
					}
					return false;
				},
				$replace_with
			);
			return array_filter( $actions );
		}

		/**
		 * Run action job.
		 *
		 * @param string $post_content Post content.
		 * @param string $import_translation_lang Translation language code.
		 * @param object $job Post object.
		 * @param string $language_code Feed language code.
		 * @param array  $item Feed item.
		 * @return string
		 */
		public function run_action_job( $post_content, $import_translation_lang, $job, $language_code, $item ) {
			$this->item             = $item;
			$this->job              = $job;
			$this->language_code    = $language_code;
			$this->translation_lang = $import_translation_lang;
			$this->post_content     = $post_content;
			$actions                = $this->get_actions();

			if ( ! empty( $actions ) ) {
				foreach ( $actions as $key => $jobs ) {
					if ( ! isset( $jobs['replace_with'] ) ) {
						continue;
					}
					$this->result = null;
					$replace_with = isset( $jobs['replace_with'] ) ? $jobs['replace_with'] : array();
					$replace_to   = isset( $jobs['replace_to'] ) ? $jobs['replace_to'] : '';
					foreach ( $replace_with as $job ) {
						$this->current_job = $job;
						$this->result      = $this->action_process();
					}
					$this->post_content = str_replace( $replace_to, $this->result, $this->post_content );
				}
			}
			return $this->post_content;
		}

		/**
		 * Action process.
		 *
		 * @return string
		 */
		public function action_process() {
			switch ( $this->current_job->id ) {
				case 'trim':
					return $this->trim_content();
				case 'fz_translate':
					return $this->translate_content();
				case 'search_replace':
					return $this->search_replace();
				case 'fz_paraphrase':
					return $this->paraphrase_content();
				case 'spinnerchief':
					return $this->spinnerchief_spin_content();
				case 'wordAI':
					return $this->word_ai_content();
				case 'chat_gpt_rewrite':
					return $this->chat_gpt_rewrite();
				case 'fz_summarize':
					return $this->summarize_content();
				default:
					return $this->default_content();
			}
		}

		/**
		 * Get item content.
		 */
		private function item_content() {
			if ( ! empty( $this->result ) ) {
				return $this->result;
			}
			return ! empty( $this->item['item_content'] ) ? $this->item['item_content'] : $this->item['item_description'];
		}

		/**
		 * Get full content.
		 */
		private function item_full_content() {
			if ( ! empty( $this->result ) ) {
				return $this->result;
			}
			$full_content = ! empty( $this->item['item_full_content'] ) ? $this->item['item_full_content'] : $this->item['item_content'];
			$post_content = apply_filters( 'feedzy_invoke_services', '[#item_full_content]', 'full_content', $full_content, $this->job );
			return $full_content;
		}

		/**
		 * Get item item_description.
		 */
		private function item_categories() {
			if ( ! empty( $this->result ) ) {
				return $this->result;
			}
			return ! empty( $this->item['item_categories'] ) ? $this->item['item_categories'] : '';
		}

		/**
		 * Get item item_description.
		 */
		private function item_description() {
			if ( ! empty( $this->result ) ) {
				return $this->result;
			}
			return ! empty( $this->item['item_description'] ) ? $this->item['item_description'] : '';
		}

		/**
		 * Trim content.
		 *
		 * @return string
		 */
		private function trim_content() {
			$content = call_user_func( array( $this, $this->current_job->tag ) );
			if ( $this->current_job->data->trimLength <= 0 ) {
				return $content;
			}
			$content = wp_strip_all_tags( $content );
			return wp_trim_words( $content, $this->current_job->data->trimLength );
		}

		/**
		 * Search and replace.
		 */
		private function search_replace() {
			$content = call_user_func( array( $this, $this->current_job->tag ) );
			return str_replace( $this->current_job->data->search, $this->current_job->data->searchWith, $content );
		}

		/**
		 * Paraphrase content with feedzy default service.
		 */
		private function paraphrase_content() {
			$content = call_user_func( array( $this, $this->current_job->tag ) );
			$content = apply_filters( 'feedzy_invoke_content_rewrite_services', $content, '[#content_feedzy_rewrite]', $this->job, $this->item );
			return $content;
		}

		/**
		 * Translate content.
		 *
		 * @return string
		 */
		private function translate_content() {
			$content = call_user_func( array( $this, $this->current_job->tag ) );
			return apply_filters( 'feedzy_invoke_auto_translate_services', $content, '[#translated_content]', $this->translation_lang, $this->job, $this->language_code, $this->item );
		}

		/**
		 * Spin content using spinnerchief.
		 *
		 * @return string
		 */
		private function spinnerchief_spin_content() {
			$content       = call_user_func( array( $this, $this->current_job->tag ) );
			$wordai_result = apply_filters( 'feedzy_invoke_services', '[#content_spinnerchief]', 'content', $content, $this->job );
			if ( ! empty( $wordai_result ) ) {
				$content = $wordai_result;
			}
			return $content;
		}

		/**
		 * Spin content using WordAI.
		 *
		 * @return string
		 */
		private function word_ai_content() {
			$content       = call_user_func( array( $this, $this->current_job->tag ) );
			$wordai_result = apply_filters( 'feedzy_invoke_services', '[#content_wordai]', 'content', $content, $this->job );
			if ( ! empty( $wordai_result ) ) {
				$content = $wordai_result;
			}
			return $content;
		}

		/**
		 * Get item default content.
		 *
		 * @return string
		 */
		private function default_content() {
			if ( ! method_exists( $this, $this->current_job->tag ) ) {
				return '';
			}
			return call_user_func( array( $this, $this->current_job->tag ) );
		}

		/**
		 * ChatGPT rewrite content.
		 *
		 * @return string
		 */
		private function chat_gpt_rewrite() {
			$content = call_user_func( array( $this, $this->current_job->tag ) );
			$content = wp_strip_all_tags( $content );
			$content = substr( $content, 0, apply_filters( 'feedzy_chat_gpt_content_limit', 3000 ) );
			$content = str_replace( array( '{content}' ), array( $content ), $this->current_job->data->ChatGPT );
			if ( ! class_exists( '\Feedzy_Rss_Feeds_Pro_Openai' ) ) {
				return $content;
			}
			$openai  = new \Feedzy_Rss_Feeds_Pro_Openai();
			$content = $openai->call_api( $this->settings, $content, '', array() );
			return $content;
		}

		/**
		 * Summarize item content.
		 *
		 * @return string
		 */
		private function summarize_content() {
			$content = call_user_func( array( $this, $this->current_job->tag ) );
			if ( ! class_exists( '\Feedzy_Rss_Feeds_Pro_Openai' ) ) {
				return $content;
			}
			$openai  = new \Feedzy_Rss_Feeds_Pro_Openai();
			$content = $openai->call_api( $this->settings, $content, 'summarize', array() );
			return $content;
		}
	}
}
