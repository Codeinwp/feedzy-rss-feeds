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
		 * Extract magic tags.
		 *
		 * @return string
		 */
		public function extract_magic_tags() {
			preg_match_all( '/\[\[\{(.*)\}\]\]/U', $this->actions, $item_magic_tags, PREG_PATTERN_ORDER );
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
					foreach ( $jobs['replace_with'] as $job ) {
						$this->current_job = $job;
						$this->result      = $this->action_process( $job->tag );
						$this->result      = str_replace( $jobs['replace_to'], $this->result, $this->post_content );
					}
				}
			}
			return $this->result;
		}

		/**
		 * Action process.
		 *
		 * @param string $tag Magic tag.
		 * @return string
		 */
		public function action_process( $tag ) {
			switch ( $this->current_job->id ) {
				case 'trim':
					return $this->trim_content();
				case 'fz_translate':
					return $this->translate_content();
				case 'search_replace':
					return $this->search_replace();
				default:
					return;
			}
		}

		/**
		 * Get item content.
		 */
		private function item_content() {
			if ( ! empty( $this->result ) ) {
				return $this->result;
			}
			return $this->item['item_content'];
		}

		/**
		 * Trim content.
		 *
		 * @return string
		 */
		private function trim_content() {
			$content = call_user_func( array( $this, $this->current_job->tag ) );
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
		 * Translate content.
		 *
		 * @return string
		 */
		private function translate_content() {
			$content = call_user_func( array( $this, $this->current_job->tag ) );
			return apply_filters( 'feedzy_invoke_auto_translate_services', $content, '[#translated_content]', $this->translation_lang, $this->job, $this->language_code, $this->item );
		}

		/**
		 * Get full content.
		 */
		private function get_full_content() {
			$full_content = ! empty( $this->item['item_full_content'] ) ? $this->item['item_full_content'] : $this->item['item_content'];
			$post_content = apply_filters( 'feedzy_invoke_services', '[#item_full_content]', 'full_content', $full_content, $this->job );
			return $full_content;
		}
	}
}
