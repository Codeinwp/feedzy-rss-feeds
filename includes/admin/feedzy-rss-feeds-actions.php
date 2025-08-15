<?php
/**
 * The item content action chain process.
 *
 * @link       https://themeisle.com
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
		 * Serialized content actions. It can contain a mix of magic tags and simple text.
		 *
		 * @var string $raw_serialized_actions Content actions.
		 */
		private $raw_serialized_actions;

		/**
		 * Setting options.
		 *
		 * @var string $settings Plugin setting.
		 */
		private $settings;

		/**
		 * Extract tags.
		 *
		 * @var array $extract_tags Extract tags.
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
		 * The field content (title, description, post content, date, etc.)
		 *
		 * @var string $field_content
		 */
		public $field_content = '';

		/**
		 * Default value.
		 *
		 * @var string $default_value
		 */
		public $default_value = '';

		/**
		 * Action type.
		 *
		 * @var string $type
		 */
		public $type = '';

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
		 * @param string $raw_serialized_actions Item content actions.
		 * @return string|array
		 */
		public function set_raw_serialized_actions( $raw_serialized_actions = '' ) {
			$this->raw_serialized_actions = $raw_serialized_actions;
			if ( empty( $this->raw_serialized_actions ) ) {
				return $this->raw_serialized_actions;
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
		 * @return array|array[]
		 */
		public function extract_magic_tags() {
			/**
			 * Transform the serialized string of magic tags to array.
			 *
			 * Input(string): [[{"value":"[{"id":"chat_gpt_rewrite","tag":"item_title","data":{"ChatGPT":"Create a long description: {content}"}},{"id":"fz_summarize","tag":"item_title","data":{"fz_summarize":true}}]"}]] with a nice weather.
			 *
			 * Output:
			 * [
			 *  [
			 *    [replace_to]   => [[{"value":"[{"id":"chat_gpt_rewrite","tag":"item_title","data":{"ChatGPT":"Create a long description: {content}"}},{"id":"fz_summarize","tag":"item_title","data":{"fz_summarize":true}}]"}]]
			 *    [replace_with] => [{"id":"chat_gpt_rewrite","tag":"item_title","data":{"ChatGPT":"Create a long description: {content}"}},{"id":"fz_summarize","tag":"item_title","data":{"fz_summarize":true}}]
			 *  ]
			 * ]
			 */
			$can_process = preg_match_all( '/\[\[\{(.*)\}\]\]/U', $this->raw_serialized_actions, $item_magic_tags, PREG_PATTERN_ORDER );
			if ( ! $can_process ) {
				return array();
			}

			$extract_tags = array();
			if ( ! empty( $item_magic_tags[0] ) ) {
				$extract_tags = array_map(
					function ( $tag ) {
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
		 * Get the extracted serialized actions from the Tagify tags. The actions can be a mix of Tagify tags and simple text.
		 *
		 * @return string The serialized actions.
		 */
		public function get_serialized_actions() {
			if ( ! is_array( $this->get_extract_tags() ) ) {
				return '';
			}

			$replace_to   = array_column( $this->get_extract_tags(), 'replace_to' );
			$replace_with = array_column( $this->get_extract_tags(), 'replace_with' );
			return str_replace( $replace_to, $replace_with, $this->raw_serialized_actions );
		}

		/**
		 * Get extract tags.
		 */
		public function get_extract_tags() {
			return $this->extract_tags;
		}

		/**
		 * Get actions. Return pairs of serialized actions and their deserialized versions.
		 *
		 * Deserialized version is used to run the action job. While serialized version is used to replace the job result in the input content.
		 *
		 * @return array
		 */
		public function get_actions() {
			if ( ! is_array( $this->get_extract_tags() ) ) {
				return array();
			}

			$replace_with = array_column( $this->get_extract_tags(), 'replace_with' );
			$actions      = array_map(
				function ( $serialized_actions ) {
					$job_actions = json_decode( $serialized_actions );
					if ( $job_actions ) {
						return array(
							'serialized_actions' => $serialized_actions,
							'job_actions'        => $job_actions,
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
		 * @param string $field_content Field content. It can contain a mix of magic tags and simple text.
		 * @param string $import_translation_lang Translation language code.
		 * @param object $job Post object.
		 * @param string $language_code Feed language code.
		 * @param array  $item Feed item.
		 * @param string $default_value Default value.
		 * @return string
		 */
		public function run_action_job( $field_content, $import_translation_lang, $job, $language_code, $item, $default_value = '' ) {
			$this->item             = $item;
			$this->job              = $job;
			$this->language_code    = $language_code;
			$this->translation_lang = $import_translation_lang;
			$this->field_content    = $field_content;
			$this->default_value    = $default_value;
			$actions                = $this->get_actions();

			if ( ! empty( $actions ) ) {
				foreach ( $actions as $key => $jobs ) {
					if ( ! isset( $jobs['job_actions'] ) ) {
						continue;
					}

					$this->result = null;
					$jobs_actions = $jobs['job_actions'];
					$replace_to   = isset( $jobs['serialized_actions'] ) ? $jobs['serialized_actions'] : '';
					foreach ( $jobs_actions as $job ) {
						$this->current_job = $job;
						$this->result      = $this->action_process();
					}
					if ( 'item_image' === $this->type ) {
						$this->field_content = str_replace( $replace_to, $this->result, wp_json_encode( $jobs_actions ) );
					} else {
						$this->field_content = str_replace( $replace_to, $this->result, $this->field_content );
					}
				}
			}
			if ( empty( $actions ) && 'item_image' === $this->type ) {
				return $default_value;
			}
			return $this->field_content;
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
				case 'fz_image':
					return $this->generate_image();
				case 'modify_links':
					return $this->modify_links();
				default:
					return $this->default_content();
			}
		}

		/**
		 * Get translation language.
		 */
		public function get_translation_lang() {
			return $this->translation_lang;
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
		 * Get item item_title.
		 */
		private function item_title() {
			if ( ! empty( $this->result ) ) {
				return $this->result;
			}
			return ! empty( $this->item['item_title'] ) ? $this->item['item_title'] : '';
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
			$search  = $this->current_job->data->search;
			$replace = $this->current_job->data->searchWith;
			$mode    = isset( $this->current_job->data->mode ) ? $this->current_job->data->mode : 'text';

			switch ( $mode ) {
				case 'wildcard':
					$pattern = preg_quote( $search, '/' );
					$pattern = str_replace( '\\*', '.*', $pattern );
					return preg_replace( '/' . $pattern . '/i', $replace, $content );
				case 'regex':
					if ( ! preg_match( '/^\/.\/[imsxuADU]$/', $search ) ) {
						$pattern = '/' . $search . '/i';
					}

					return preg_replace( $pattern, $replace, $content );
				default:
					return str_replace( $search, $replace, $content );
			}
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
			$content                = call_user_func( array( $this, $this->current_job->tag ) );
			$this->translation_lang = ! empty( $this->current_job->data->lang ) ? $this->current_job->data->lang : $this->translation_lang;
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
				return $this->default_value;
			}
			return call_user_func( array( $this, $this->current_job->tag ) );
		}

		/**
		 * ChatGPT rewrite content.
		 *
		 * @return string
		 */
		private function chat_gpt_rewrite() {
			// Return prompt content if openAI class doesn't exist.
			if ( ! class_exists( '\Feedzy_Rss_Feeds_Pro_Openai' ) ) {
				return $this->current_job->data->ChatGPT;
			}

			$content        = call_user_func( array( $this, $this->current_job->tag ) );
			$content        = wp_strip_all_tags( $content );
			$content        = substr( $content, 0, apply_filters( 'feedzy_chat_gpt_content_limit', 3000 ) );
			$prompt_content = $this->current_job->data->ChatGPT;

			$additional_data = array(
				'ai_provider' => 'openai',
			);

			if ( isset( $this->current_job->data ) && isset( $this->current_job->data->aiProvider ) ) {
				$additional_data['ai_provider'] = $this->current_job->data->aiProvider;
			}

			if (
				'openai' === $additional_data['ai_provider'] &&
				isset( $this->current_job->data ) && isset( $this->current_job->data->aiModel )
			) {
				$additional_data['ai_model'] = $this->current_job->data->aiModel;
			}

			$content         = str_replace( array( '{content}' ), array( $content ), $prompt_content );
			$openai          = new \Feedzy_Rss_Feeds_Pro_Openai();
			$rewrite_content = $openai->call_api( $this->settings, $content, '', $additional_data );
			// Replace prompt content string for specific cases.
			$rewrite_content = str_replace( explode( '{content}', $prompt_content ), '', trim( $rewrite_content, '"' ) );
			return $rewrite_content;
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
			if ( isset( $this->current_job->data->fz_summarize ) ) {
				unset( $this->current_job->data->fz_summarize );
			}
			// Summarizes the content using the `Rewrite with AI` action, ensuring backward compatibility.
			$this->current_job->data->ChatGPT = 'Summarize this article {content} for better SEO.';
			return $this->chat_gpt_rewrite();
		}

		/**
		 * Generate item image using OpenAI.
		 * Return default value if OpenAI is not available or `Generate only for missing images` option is enabled and feed has image.
		 *
		 * @return string Image URL to download.
		 */
		private function generate_image() {

			if ( ! class_exists( '\Feedzy_Rss_Feeds_Pro_Openai' ) ) {
				return isset( $this->default_value ) ? $this->default_value : '';
			}

			$feed_has_image = false !== filter_var( $this->default_value, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED );
			if ( ( ! isset( $this->current_job->data->generateOnlyMissingImages ) || ! empty( $this->current_job->data->generateOnlyMissingImages ) ) && $feed_has_image ) {
				return isset( $this->default_value ) ? $this->default_value : '';
			}

			$prompt = call_user_func( array( $this, 'item_title' ) );
			if ( ! empty( $this->current_job->data->generateImagePrompt ) ) {
				$prompt .= "\r\n" . $this->current_job->data->generateImagePrompt;
			}
			$openai = new \Feedzy_Rss_Feeds_Pro_Openai();
			return $openai->call_api( $this->settings, $prompt, 'image', array() );
		}

		/**
		 * Modify links.
		 *
		 * @return string Item content.
		 */
		private function modify_links() {
			$content = call_user_func( array( $this, $this->current_job->tag ) );
			// Returns item content because it has no HTML tags.
			if ( wp_strip_all_tags( $content ) === $content ) {
				return $content;
			}
			// Pro version is required to perform this action.
			if ( ! feedzy_is_pro() ) {
				return $content;
			}
			// converts all special characters to utf-8.
			$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );

			$dom = new DOMDocument( '1.0', 'utf-8' );
			libxml_use_internal_errors( true );
			$dom->loadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
			$xpath = new DOMXPath( $dom );
			libxml_clear_errors();
			// Get all anchors tags.
			$nodes = $xpath->query( '//a' );

			foreach ( $nodes as $node ) {
				if ( ! empty( $this->current_job->data->remove_links ) ) {
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$node->parentNode->removeChild( $node );
					continue;
				}
				if ( ! empty( $this->current_job->data->target ) ) {
					$node->setAttribute( 'target', $this->current_job->data->target );
				}
				if ( ! empty( $this->current_job->data->follow ) && 'yes' === $this->current_job->data->follow ) {
					$node->setAttribute( 'rel', 'nofollow' );
				}
			}
			if ( ! empty( $this->current_job->data->follow ) && 'yes' === $this->current_job->data->follow ) {
				add_filter(
					'wp_targeted_link_rel',
					function () {
						return 'nofollow';
					}
				);
			}
			return $dom->saveHTML();
		}

		/**
		 * Get custom field value.
		 */
		private function custom_field() {
			if ( ! empty( $this->result ) ) {
				return $this->result;
			}
			return $this->default_value;
		}
	}
}
