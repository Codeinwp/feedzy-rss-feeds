<?php
/**
 * Shared helpers for Feedzy Abilities: capability mapping, sanitization,
 * standardised WP_Error codes, and output shaping.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/abilities
 */

/**
 * Class Feedzy_Rss_Feeds_Ability_Helpers
 *
 * Static utility bag; no instances needed.
 */
class Feedzy_Rss_Feeds_Ability_Helpers {

	/**
	 * Check if the current user has permission to execute Feedzy abilities.
	 *
	 * @return bool
	 */
	public static function current_user_can() {
		return function_exists( 'feedzy_current_user_can' ) && feedzy_current_user_can();
	}

	/**
	 * Return a WP_Error if the current user cannot execute Feedzy abilities, or `true` if they can.
	 *
	 * @return true|WP_Error
	 */
	public static function permission_or_error() {
		if ( self::current_user_can() ) {
			return true;
		}

		return new WP_Error(
			'feedzy_forbidden',
			__( 'You do not have permission to perform this Feedzy operation.', 'feedzy-rss-feeds' ),
			array( 'status' => 403 )
		);
	}

	/**
	 * Sanitise arbitrary input according to the expected schema for Feedzy abilities.
	 *
	 * @param  mixed $input Raw input (scalar or array).
	 *
	 * @return mixed Sanitised value.
	 */
	public static function sanitize_input( $input ) {
		if ( is_array( $input ) ) {
			$out = array();
			foreach ( $input as $k => $v ) {
				$out[ sanitize_key( (string) $k ) ] = self::sanitize_input( $v );
			}
			return $out;
		}

		if ( is_string( $input ) ) {
			return sanitize_text_field( $input );
		}

		return $input;
	}

	/**
	 * Sanitise a string that may contain either a feed URL or a feed-group slug.
	 *
	 * @param  string $value Raw value.
	 *
	 * @return string
	 */
	public static function sanitize_url_or_slug( string $value ): string {
		$parts = explode( ',', $value );
		$parts = array_map(
			function ( $part ) {
				$part = trim( $part );
				return filter_var( $part, FILTER_VALIDATE_URL )
					? esc_url_raw( $part )
					: sanitize_text_field( $part );
			},
			$parts
		);
		return implode( ',', $parts );
	}

	/**
	 * Wrap a successful result in the standard response envelope.
	 *
	 * @param  mixed $data The payload.
	 *
	 * @return array{ success: true, data: mixed }
	 */
	public static function success( $data ) {
		return array(
			'success' => true,
			'data'    => $data,
		);
	}

	/**
	 * Wrap an error in the standard response envelope.
	 *
	 * @param  string $code    Machine-readable error code.
	 * @param  string $message Human-readable message (i18n-ready).
	 * @param  mixed  $data    Optional extra context.
	 *
	 * @return array{ success: false, error: array{ code: string, message: string, data?: mixed } }
	 */
	public static function error( string $code, string $message, $data = null ) {
		$err = array(
			'code'    => $code,
			'message' => $message,
		);
		if ( null !== $data ) {
			$err['data'] = $data;
		}
		return array(
			'success' => false,
			'error'   => $err,
		);
	}

	/**
	 * Convert a `WP_Error` to the standard error envelope.
	 *
	 * @param  WP_Error $wp_error The error.
	 *
	 * @return array<string, mixed> Standardised error response.
	 */
	public static function from_wp_error( WP_Error $wp_error ) {
		return self::error(
			$wp_error->get_error_code(),
			$wp_error->get_error_message(),
			$wp_error->get_error_data()
		);
	}

	/**
	 * Shape a `feedzy_categories` WP_Post for ability output.
	 *
	 * @param  WP_Post $post Raw post.
	 *
	 * @return array<string, mixed> Shaped feed source data.
	 */
	public static function shape_feed_source( WP_Post $post ) {
		$feeds_raw = get_post_meta( $post->ID, 'feedzy_category_feed', true );
		$feeds     = array();
		if ( ! empty( $feeds_raw ) ) {
			$feeds = array_filter( array_map( 'trim', explode( "\n", $feeds_raw ) ) );
			$feeds = array_values( $feeds );
		}

		return array(
			'id'         => $post->ID,
			'name'       => $post->post_name,
			'title'      => esc_html( $post->post_title ),
			'status'     => $post->post_status,
			'feeds'      => $feeds,
			'created_at' => $post->post_date_gmt,
			'updated_at' => $post->post_modified_gmt,
		);
	}

	/**
	 * All recognised import-job meta keys that abilities read/write.
	 *
	 * This is the single source of truth for the import schema; keep it in sync
	 * with `Feedzy_Rss_Feeds_Import::save_feedzy_import_feed_meta()`.
	 *
	 * @return string[]
	 */
	public static function import_meta_keys() {
		return array(
			// Core.
			'source',
			'import_post_type',
			'import_post_status',
			'import_post_term',
			'import_post_author',
			'import_feed_limit',
			// Mapping templates.
			'import_post_title',
			'import_post_content',
			'import_post_excerpt',
			'import_post_featured_img',
			'import_post_date',
			// Filtering.
			'inc_key',
			'exc_key',
			'inc_on',
			'exc_on',
			'from_datetime',
			'to_datetime',
			'filter_conditions',
			'import_remove_duplicates',
			'import_remove_html',
			'import_order',
			// Images.
			'import_use_external_image',
			'default_thumbnail_id',
			// Schedule.
			'fz_cron_schedule',
			// AI actions (Pro).
			'import_auto_translation',
			'import_auto_translation_lang',
			'language',
			// Author linking.
			'import_link_author_admin',
			'import_link_author_public',
			// Duplicate tagging.
			'mark_duplicate_tag',
			// Auto-delete (Pro).
			'import_feed_delete_days',
		);
	}

	/**
	 * Shape a `feedzy_import` WP_Post for ability output.
	 *
	 * @param  WP_Post $post Raw post.
	 *
	 * @return array<string, mixed> Shaped import job data.
	 */
	public static function shape_import( WP_Post $post ) {
		$meta = array();
		foreach ( self::import_meta_keys() as $key ) {
			$meta[ $key ] = get_post_meta( $post->ID, $key, true );
		}

		$custom_fields         = get_post_meta( $post->ID, 'imports_custom_fields', true );
		$meta['custom_fields'] = is_array( $custom_fields ) ? $custom_fields : array();

		return array(
			'id'         => $post->ID,
			'name'       => $post->post_name,
			'title'      => esc_html( $post->post_title ),
			'status'     => $post->post_status,
			'meta'       => $meta,
			'created_at' => $post->post_date_gmt,
			'updated_at' => $post->post_modified_gmt,
		);
	}


	/**
	 * All import-related meta keys that support Tagify action blocks.
	 *
	 * @return string[]
	 */
	public static function tagify_fields() {
		return array(
			'import_post_title',
			'import_post_content',
			'import_post_excerpt',
			'import_post_featured_img',
		);
	}

	/**
	 * Decode a Tagify meta value into an array of action payloads for the import runner.
	 *
	 * @param  string $tagify Tagify meta value from post meta.
	 *
	 * @return array<int, array<string, mixed>> Array of action payload maps.
	 */
	public static function decode_tagify_actions( string $tagify ) {
		if ( empty( $tagify ) || substr( $tagify, 0, 3 ) !== '[[{' ) {
			return array();
		}

		$outer = json_decode( $tagify, true );
		if ( ! is_array( $outer ) ) {
			return array();
		}

		$actions = array();
		foreach ( $outer as $sub_array ) {
			if ( ! is_array( $sub_array ) ) {
				continue;
			}
			foreach ( $sub_array as $item ) {
				if ( empty( $item['value'] ) || ! is_string( $item['value'] ) ) {
					continue;
				}
				$objects = json_decode( rawurldecode( $item['value'] ), true );
				if ( ! is_array( $objects ) ) {
					continue;
				}
				foreach ( $objects as $obj ) {
					if ( ! empty( $obj['id'] ) ) {
						$actions[] = array(
							'id'   => (string) $obj['id'],
							'data' => (array) ( $obj['data'] ?? array() ),
						);
					}
				}
			}
		}

		return $actions;
	}

	/**
	 * Build a Tagify block from one or more action objects for a single field.
	 *
	 * @param  string                           $tag     Feedzy magic-tag name.
	 * @param  array<int, array<string, mixed>> $actions Array of action payloads.
	 *
	 * @return string Tagify-format string ready for save_import_meta().
	 */
	public static function build_action_tagify( string $tag, array $actions ): string {
		$objects = array();
		foreach ( $actions as $action ) {
			$objects[] = array(
				'id'   => $action['id'],
				'tag'  => $tag,
				'data' => (object) $action['data'],
			);
		}
		$inner = wp_json_encode( $objects );
		return '[[{"value":"' . rawurlencode( $inner ) . '"}]]';
	}

	/**
	 * Get the list of available languages as code => name pairs.
	 *
	 * @return array<string, string>
	 */
	public static function get_available_languages() {
		if ( ! class_exists( 'Feedzy_Rss_Feeds' ) ) {
			return array();
		}
		$admin = Feedzy_Rss_Feeds::instance()->get_admin();
		if ( ! method_exists( $admin, 'get_lang_list' ) ) {
			return array();
		}
		return (array) $admin->get_lang_list();
	}

	/**
	 * Resolve a user-supplied language name or code into a valid language code, if possible.
	 *
	 * @param  string $input Raw language value from the action descriptor.
	 *
	 * @return string Resolved language code, or original value on no match.
	 */
	public static function resolve_lang_code( string $input ): string {
		if ( empty( $input ) ) {
			return $input;
		}

		$lang_list = self::get_available_languages();
		if ( empty( $lang_list ) ) {
			return $input;
		}

		// Case-insensitive match against localised language names.
		$lower = strtolower( trim( $input ) );
		foreach ( $lang_list as $code => $name ) {
			if ( strtolower( (string) $name ) === $lower ) {
				return (string) $code;
			}
		}

		return $input;
	}

	/**
	 * Get the mapping of AI action keys to their corresponding meta key and Tagify tag.
	 *
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function ai_action_map() {
		return array(
			'title_action'        => array( 'import_post_title', 'item_title' ),
			'content_action'      => array( 'import_post_content', 'item_content' ),
			'excerpt_action'      => array( 'import_post_excerpt', 'item_excerpt' ),
			'featured_img_action' => array( 'import_post_featured_img', 'item_image' ),
		);
	}

	/**
	 * Convert an action descriptor into the data structure expected by the import runner, based on the action type.
	 *
	 * @param  string               $action_type Action id.
	 * @param  array<string, mixed> $action      Caller-supplied descriptor.
	 *
	 * @return array<string, mixed> Data payload for the Tagify block.
	 */
	public static function build_action_data( string $action_type, array $action ) {
		switch ( $action_type ) {
			case 'chat_gpt_rewrite':
				$data = array( 'ChatGPT' => (string) ( $action['prompt'] ?? '' ) );
				if ( ! empty( $action['provider'] ) ) {
					$data['aiProvider'] = (string) $action['provider'];
				}
				if ( ! empty( $action['model'] ) ) {
					$data['aiModel'] = (string) $action['model'];
				}
				return $data;

			case 'fz_summarize':
				$data = array( 'fz_summarize' => true );
				if ( ! empty( $action['provider'] ) ) {
					$data['aiProvider'] = (string) $action['provider'];
				}
				if ( ! empty( $action['model'] ) ) {
					$data['aiModel'] = (string) $action['model'];
				}
				return $data;

			case 'fz_image':
				$data = array( 'generateOnlyMissingImages' => (bool) ( $action['only_missing'] ?? true ) );
				if ( ! empty( $action['prompt'] ) ) {
					$data['generateImagePrompt'] = (string) $action['prompt'];
				}
				if ( ! empty( $action['provider'] ) ) {
					$data['aiProvider'] = (string) $action['provider'];
				}
				if ( ! empty( $action['model'] ) ) {
					$data['aiModel'] = (string) $action['model'];
				}
				return $data;

			case 'trim':
				return array( 'trimLength' => (string) ( $action['length'] ?? '45' ) );

			case 'search_replace':
				return array(
					'search'     => (string) ( $action['search'] ?? '' ),
					'searchWith' => (string) ( $action['replace'] ?? '' ),
					'mode'       => (string) ( $action['mode'] ?? 'text' ),
				);

			case 'fz_translate':
			case 'translate':
				$raw_lang = (string) ( $action['lang'] ?? $action['language'] ?? '' );
				return array( 'lang' => self::resolve_lang_code( $raw_lang ) );

			case 'modify_links':
				$data = array();
				if ( isset( $action['remove_links'] ) ) {
					$data['remove_links'] = (bool) $action['remove_links'];
				}
				if ( ! empty( $action['target'] ) ) {
					$data['target'] = (string) $action['target'];
				}
				if ( ! empty( $action['follow'] ) ) {
					$data['follow'] = (string) $action['follow'];
				}
				return $data;

			case 'fz_paraphrase':
			case 'spinnerchief':
			case 'wordAI':
			case 'custom_html':
			default:
				return array();
		}
	}

	/**
	 * Resolve user-friendly action descriptors in the input meta into the Tagify format expected by the import runner, and merge with any existing actions for updates.
	 *
	 * @param  array<string, mixed> $meta    Meta array that may contain action descriptors.
	 * @param  int                  $post_id Existing job post ID (0 for new jobs — skips merge).
	 *
	 * @return array<string, mixed> Meta array with action keys replaced by Tagify import_post_* values.
	 */
	public static function resolve_ai_actions( array $meta, int $post_id = 0 ) {
		foreach ( self::ai_action_map() as $action_key => list( $meta_key, $tag ) ) {
			if ( empty( $meta[ $action_key ] ) ) {
				continue;
			}

			$descriptor = $meta[ $action_key ];

			if ( isset( $descriptor['type'] ) ) {
				$descriptor = array( $descriptor );
			}

			$new_actions = array();
			foreach ( $descriptor as $action ) {
				if ( empty( $action['type'] ) ) {
					continue;
				}
				$action_type = (string) $action['type'];
				if ( 'translate' === $action_type ) {
					$action_type = 'fz_translate';
				}
				$data          = self::build_action_data( $action_type, $action );
				$new_actions[] = array(
					'id'   => $action_type,
					'data' => $data,
				);
			}

			$existing_actions = array();
			if ( $post_id > 0 ) {
				$stored = get_post_meta( $post_id, $meta_key, true );
				if ( ! empty( $stored ) ) {
					$existing_actions = self::decode_tagify_actions( (string) $stored );
				}
			}

			$new_ids          = array_column( $new_actions, 'id' );
			$existing_actions = array_values(
				array_filter(
					$existing_actions,
					static function ( $a ) use ( $new_ids ) {
						return ! in_array( $a['id'], $new_ids, true );
					}
				)
			);

			$all_actions = array_merge( $existing_actions, $new_actions );

			if ( ! empty( $all_actions ) ) {
				$meta[ $meta_key ] = self::build_action_tagify( $tag, $all_actions );
			}
			unset( $meta[ $action_key ] );
		}
		return $meta;
	}

	/**
	 * Convert a value to the Feedzy Tagify JSON format used by the admin UI.
	 *
	 * @param  string $value Raw user input or existing stored value.
	 *
	 * @return string
	 */
	public static function to_tagify( string $value ): string {
		$value = trim( $value );
		if ( empty( $value ) ) {
			return $value;
		}

		// Already in Tagify JSON format — pass through unchanged.
		if ( substr( $value, 0, 3 ) === '[[{' ) {
			return $value;
		}

		$make_block = static function ( string $tag_name ): string {
			$inner = '[{"id":"","tag":"' . $tag_name . '","data":{}}]';
			return '[[{"value":"' . rawurlencode( $inner ) . '"}]]';
		};

		if ( preg_match( '/^\\[#([a-zA-Z0-9_:@\\-]+)\\]$/', $value, $m ) ) {
			return $make_block( $m[1] );
		}

		if ( strpos( $value, '[#' ) !== false ) {
			return preg_replace_callback(
				'/\\[#([a-zA-Z0-9_:@\\-]+)\\]/',
				static function ( array $m ) use ( $make_block ): string {
					return $make_block( $m[1] );
				},
				$value
			);
		}

		return $value;
	}

	/**
	 * Detect the feed source type for an import job.
	 *
	 * @param  string $source Comma-separated feed URL(s) or a source group slug.
	 *
	 * @return string 'amazon'|'xml'
	 */
	public static function detect_source_type( string $source ): string {
		if ( ! function_exists( 'feedzy_amazon_get_locale_hosts' ) ) {
			return 'xml';
		}
		$amazon_hosts = feedzy_amazon_get_locale_hosts();
		if ( empty( $amazon_hosts ) ) {
			return 'xml';
		}
		foreach ( array_map( 'trim', explode( ',', $source ) ) as $url ) {
			$host     = (string) wp_parse_url( $url, PHP_URL_HOST );
			$api_host = 'webservices.' . $host;
			if ( in_array( $api_host, $amazon_hosts, true ) ) {
				return 'amazon';
			}
		}
		return 'xml';
	}

	/**
	 * Resolve user-friendly taxonomy term descriptors into the {taxonomy}_{termId} format Feedzy expects, creating missing terms and passing magic tags through unchanged.
	 *
	 * @param  string|array<string> $value Raw input from the ability caller.
	 *
	 * @return string Resolved value ready for `update_post_meta()`.
	 */
	public static function resolve_import_post_term( $value ): string {
		if ( is_array( $value ) ) {
			$value = implode( ',', $value );
		}
		$value = trim( (string) $value );

		if ( empty( $value ) || 'none' === $value ) {
			return $value;
		}

		$resolved = array();

		foreach ( array_map( 'trim', explode( ',', $value ) ) as $token ) {
			if ( empty( $token ) ) {
				continue;
			}

			if ( substr( $token, 0, 2 ) === '[#' ) {
				$resolved[] = $token;
				continue;
			}

			$parts = explode( '_', $token );
			if ( count( $parts ) >= 2 && is_numeric( end( $parts ) ) && (int) end( $parts ) > 0 ) {
				$resolved[] = $token;
				continue;
			}

			if ( strpos( $token, ':' ) !== false ) {
				[ $taxonomy, $term_name ] = array_map( 'trim', explode( ':', $token, 2 ) );
				$taxonomy                 = sanitize_key( $taxonomy );
			} else {
				$taxonomy  = 'category';
				$term_name = $token;
			}

			$term_name = sanitize_text_field( $term_name );

			if ( empty( $term_name ) || empty( $taxonomy ) ) {
				continue;
			}

			$term = get_term_by( 'name', $term_name, $taxonomy );

			if ( ! $term ) {
				$inserted = wp_insert_term( $term_name, $taxonomy );
				if ( is_wp_error( $inserted ) ) {
					if ( 'term_exists' === $inserted->get_error_code() ) {
						$term_id = (int) $inserted->get_error_data( 'term_exists' );
					} else {
						continue;
					}
				} else {
					$term_id = (int) $inserted['term_id'];
				}
			} else {
				$term_id = (int) $term->term_id;
			}

			$resolved[] = $taxonomy . '_' . $term_id;
		}

		return implode( ',', $resolved );
	}

	/**
	 * Persist a set of import meta fields to a job post.
	 *
	 * @param  int                  $post_id Job post ID.
	 * @param  array<string, mixed> $meta    Key→value map; only known keys are accepted.
	 *
	 * @return void
	 */
	public static function save_import_meta( int $post_id, array $meta ) {
		$allowed       = array_flip( self::import_meta_keys() );
		$tagify_lookup = array_flip( self::tagify_fields() );

		if ( array_key_exists( 'custom_fields', $meta ) ) {
			self::save_custom_fields( $post_id, $meta['custom_fields'] );
		}

		foreach ( $meta as $key => $value ) {
			if ( ! isset( $allowed[ $key ] ) ) {
				continue;
			}
			if ( 'import_post_author' === $key && ! empty( $value ) ) {
				// The import runner expects a user ID, same as the import edit screen stores it.
				$author = is_numeric( $value ) ? get_user_by( 'ID', (int) $value ) : get_user_by( 'login', (string) $value );
				$value  = $author ? (string) $author->ID : '';
			}
			if ( 'import_feed_delete_days' === $key && '' !== $value && null !== $value ) {
				update_post_meta( $post_id, $key, (string) absint( $value ) );
				continue;
			}
			if ( '' === $value || null === $value ) {
				delete_post_meta( $post_id, $key );
				continue;
			}

			if ( 'import_post_term' === $key ) {
				$value = self::resolve_import_post_term( $value );
				update_post_meta( $post_id, $key, $value );
				continue;
			} elseif ( isset( $tagify_lookup[ $key ] ) ) {
				$value = self::to_tagify( (string) $value );
			} elseif ( is_array( $value ) ) {
				$value = implode( ',', array_map( 'sanitize_text_field', $value ) );
			} else {
				$value = sanitize_text_field( (string) $value );
			}

			update_post_meta( $post_id, $key, $value );
		}
	}

	/**
	 * Persist the custom fields map the same way the import edit screen stores it.
	 *
	 * @param  int   $post_id Job post ID.
	 * @param  mixed $fields  Map of meta key => value template.
	 *
	 * @return void
	 */
	public static function save_custom_fields( int $post_id, $fields ) {
		$custom_fields = array();
		if ( is_array( $fields ) ) {
			foreach ( $fields as $key => $value ) {
				$key = sanitize_text_field( (string) $key );
				if ( '' === $key || ! is_scalar( $value ) ) {
					continue;
				}
				$custom_fields[ $key ] = esc_html( (string) $value );
			}
		}

		if ( empty( $custom_fields ) ) {
			delete_post_meta( $post_id, 'imports_custom_fields' );
			delete_post_meta( $post_id, 'imports_custom_field_actions' );
			return;
		}

		update_post_meta( $post_id, 'imports_custom_fields', $custom_fields );
	}

	/**
	 * Whether the active license matches a plan type.
	 *
	 * @param  string $type One of: pro, business, agency.
	 *
	 * @return bool
	 */
	public static function has_plan( string $type ): bool {
		if ( ! function_exists( 'feedzy_is_pro' ) || ! feedzy_is_pro() ) {
			return false;
		}
		if ( 'pro' === $type ) {
			return true;
		}
		return (bool) apply_filters( 'feedzy_is_license_of_type', false, $type );
	}

	/**
	 * Minimum plan required by each action id. Actions not listed are available in Free.
	 *
	 * @return array<string, string>
	 */
	public static function action_plan_map() {
		return array(
			'modify_links'     => 'pro',
			'chat_gpt_rewrite' => 'business',
			'fz_summarize'     => 'business',
			'fz_paraphrase'    => 'business',
			'fz_image'         => 'business',
			'fz_translate'     => 'agency',
			'translate'        => 'agency',
			'spinnerchief'     => 'agency',
			'wordAI'           => 'agency',
		);
	}

	/**
	 * Validate caller-supplied import fields against the active edition.
	 *
	 * Mirrors the locks of the import edit screen so that a premium field is
	 * rejected with a clear error instead of being stored and silently ignored.
	 *
	 * @param  array<string, mixed> $input Caller-supplied fields (before defaults are applied).
	 *
	 * @return true|WP_Error
	 */
	public static function validate_edition( array $input ) {
		$required = array();

		foreach ( array( 'import_post_author', 'import_link_author_admin', 'import_link_author_public', 'mark_duplicate_tag', 'language', 'default_thumbnail_id', 'import_feed_delete_days' ) as $key ) {
			if ( ! empty( $input[ $key ] ) && 'no' !== $input[ $key ] ) {
				$required[ $key ] = 'pro';
			}
		}

		// The item count is locked in Free, except for legacy installs.
		if ( ! empty( $input['import_feed_limit'] ) && 10 !== (int) $input['import_feed_limit'] && ! ( function_exists( 'feedzy_is_legacyv5' ) && feedzy_is_legacyv5() ) ) {
			$required['import_feed_limit'] = 'pro';
		}

		if ( ! empty( $input['fz_cron_schedule'] ) && 'daily' !== $input['fz_cron_schedule'] ) {
			$required['fz_cron_schedule'] = 'pro';
		}

		$terms = isset( $input['import_post_term'] ) ? $input['import_post_term'] : '';
		$terms = is_array( $terms ) ? implode( ',', $terms ) : (string) $terms;
		if ( false !== strpos( $terms, '[#auto_categories]' ) ) {
			$required['import_post_term'] = 'pro';
		}

		if ( ! empty( $input['custom_fields'] ) ) {
			$required['custom_fields'] = 'business';
		}

		if ( ( isset( $input['import_auto_translation'] ) && 'yes' === $input['import_auto_translation'] ) ) {
			$required['import_auto_translation'] = 'agency';
		}

		foreach ( self::tagify_fields() as $key ) {
			if ( ! empty( $input[ $key ] ) && is_string( $input[ $key ] ) && false !== strpos( $input[ $key ], 'item_full_content' ) ) {
				$required[ $key ] = 'business';
			}
		}

		$action_plans = self::action_plan_map();
		foreach ( array_keys( self::ai_action_map() ) as $action_key ) {
			if ( empty( $input[ $action_key ] ) || ! is_array( $input[ $action_key ] ) ) {
				continue;
			}
			$descriptors = isset( $input[ $action_key ]['type'] ) ? array( $input[ $action_key ] ) : $input[ $action_key ];
			foreach ( $descriptors as $descriptor ) {
				$type = is_array( $descriptor ) && isset( $descriptor['type'] ) ? (string) $descriptor['type'] : '';
				if ( isset( $action_plans[ $type ] ) ) {
					$required[ $action_key . ':' . $type ] = $action_plans[ $type ];
				}
			}
		}

		$missing = array();
		foreach ( $required as $field => $plan ) {
			if ( ! self::has_plan( $plan ) ) {
				$missing[ $field ] = $plan;
			}
		}

		if ( empty( $missing ) ) {
			return true;
		}

		$is_pro = function_exists( 'feedzy_is_pro' ) && feedzy_is_pro();

		return new WP_Error(
			$is_pro ? 'feedzy_plan_required' : 'feedzy_pro_required',
			sprintf(
				/* translators: %s: comma separated list of input fields */
				__( 'These fields are not available with the current Feedzy plan: %s.', 'feedzy-rss-feeds' ),
				implode( ', ', array_keys( $missing ) )
			),
			array(
				'status' => 403,
				'fields' => $missing,
			)
		);
	}

	/**
	 * Validate the author of imported posts.
	 *
	 * @param  array<string, mixed> $input Caller-supplied fields.
	 *
	 * @return true|WP_Error
	 */
	public static function validate_author( array $input ) {
		if ( empty( $input['import_post_author'] ) ) {
			return true;
		}
		$value  = $input['import_post_author'];
		$author = is_numeric( $value ) ? get_user_by( 'ID', (int) $value ) : get_user_by( 'login', (string) $value );
		if ( $author ) {
			return true;
		}
		return new WP_Error(
			'feedzy_invalid_author',
			__( 'The author of the imported posts was not found.', 'feedzy-rss-feeds' )
		);
	}

	/**
	 * Build structured import-job status data from post metas.
	 *
	 * @param  WP_Post $post The job post.
	 *
	 * @return array<string, mixed>
	 */
	public static function build_import_status( WP_Post $post ) {
		$post_id     = $post->ID;
		$import_info = get_post_meta( $post_id, 'import_info', true );
		$last_run    = (int) get_post_meta( $post_id, 'last_run', true );

		$items = get_post_meta( $post_id, 'imported_items_hash', true );
		if ( empty( $items ) ) {
			$items = get_post_meta( $post_id, 'imported_items', true );
		}
		$cumulative = is_array( $items ) ? count( $items ) : 0;

		$last_total      = 0;
		$last_found      = 0;
		$last_duplicates = 0;
		if ( $import_info ) {
			$items_count = get_post_meta( $post_id, 'imported_items_count', true );
			$last_total  = (int) ( '' !== $items_count ? $items_count : 0 );
			if ( isset( $import_info['total'] ) && is_array( $import_info['total'] ) ) {
				$last_found = count( $import_info['total'] );
			}
			if ( isset( $import_info['duplicates'] ) && is_array( $import_info['duplicates'] ) ) {
				$last_duplicates = count( $import_info['duplicates'] );
			}
		}

		$import_errors = get_post_meta( $post_id, 'import_errors', true );
		$errors        = array();
		if ( ! empty( $import_errors ) ) {
			$errors = is_array( $import_errors ) ? array_values( $import_errors ) : array( $import_errors );
		}

		$pro_errors_raw = apply_filters( 'feedzy_run_status_errors', '', $post_id );
		if ( $pro_errors_raw ) {
			$errors[] = wp_strip_all_tags( $pro_errors_raw );
		}

		$per_feed = self::build_per_feed_status( $post_id );

		return array(
			'job_id'           => $post_id,
			'job_title'        => esc_html( $post->post_title ),
			'job_status'       => $post->post_status,
			'last_run'         => $last_run ? gmdate( 'c', $last_run ) : null,
			'last_run_found'   => $last_found,
			'last_run_total'   => $last_total,
			'last_duplicates'  => $last_duplicates,
			'cumulative_total' => $cumulative,
			'errors'           => $errors,
			'per_feed'         => $per_feed,
		);
	}

	/**
	 * Build the reference of an import run requested through the abilities.
	 *
	 * The reference is stateless: the import job ID, the time the run was requested and
	 * the limit passed to the cron hook.
	 *
	 * @param  int $post_id   Import job ID.
	 * @param  int $queued_at Unix timestamp of the request.
	 * @param  int $max       The import feed limit passed to the cron hook.
	 *
	 * @return string
	 */
	public static function encode_run_reference( int $post_id, int $queued_at, int $max ) {
		return $post_id . ':' . $queued_at . ':' . $max;
	}

	/**
	 * Decode a run reference built by `encode_run_reference()`.
	 *
	 * @param  string $reference The run reference.
	 *
	 * @return array{ post_id: int, queued_at: int, max: int }|WP_Error
	 */
	public static function decode_run_reference( string $reference ) {
		if ( ! preg_match( '/^([1-9]\d{0,18}):([1-9]\d{0,10}):([1-9]\d{0,5})$/', $reference, $parts ) ) {
			return new WP_Error(
				'feedzy_invalid_job_id',
				__( 'The job_id is not a valid import run reference.', 'feedzy-rss-feeds' )
			);
		}

		return array(
			'post_id'   => (int) $parts[1],
			'queued_at' => (int) $parts[2],
			'max'       => (int) $parts[3],
		);
	}

	/**
	 * Derive the state of a requested import run from the data the import runner stores on the job.
	 *
	 * The runner sets `last_run_id` and clears `import_errors` / `import_info` when a run starts,
	 * and writes `import_errors` again on every exit path (`import_info` only when items were processed).
	 *
	 * @param  int $post_id   Import job ID.
	 * @param  int $queued_at Unix timestamp of the request.
	 * @param  int $max       The import feed limit passed to the cron hook.
	 *
	 * @return array{ state: string, run_id: int, progress: array{ current: int, total: int, message: string } }
	 */
	public static function build_run_state( int $post_id, int $queued_at, int $max ) {
		$run_id = (int) get_post_meta( $post_id, 'last_run_id', true );

		if ( $run_id < $queued_at ) {
			$pending = false !== Feedzy_Rss_Feeds_Util_Scheduler::is_scheduled( 'feedzy_cron', array( $max, $post_id ) );
			$waiting = $pending || ( time() - $queued_at ) < 5 * MINUTE_IN_SECONDS;

			return array(
				'state'    => $waiting ? 'working' : 'failed',
				'run_id'   => 0,
				'progress' => array(
					'current' => 0,
					'total'   => 0,
					'message' => $waiting
						? __( 'Queued, waiting for the cron to start the import.', 'feedzy-rss-feeds' )
						: __( 'The import did not start: the queued event is gone. Check that WP-Cron is working.', 'feedzy-rss-feeds' ),
				),
			);
		}

		if ( metadata_exists( 'post', $post_id, 'import_errors' ) ) {
			$imported = (int) get_post_meta( $post_id, 'imported_items_count', true );
			$ran      = metadata_exists( 'post', $post_id, 'import_info' );

			return array(
				'state'    => $ran ? 'completed' : 'failed',
				'run_id'   => $run_id,
				'progress' => array(
					'current' => $imported,
					'total'   => $imported,
					'message' => $ran
						/* translators: %d: number of imported items */
						? sprintf( __( 'Import run completed. %d items imported.', 'feedzy-rss-feeds' ), $imported )
						: __( 'The import stopped before processing any item. See errors.', 'feedzy-rss-feeds' ),
				),
			);
		}

		$post_type = (string) get_post_meta( $post_id, 'import_post_type', true );
		if ( '' === $post_type || ! post_type_exists( $post_type ) ) {
			$post_type = 'any';
		}

		$query = new WP_Query(
			array(
				'post_type'              => $post_type,
				'post_status'            => 'any',
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'             => array(
					array(
						'key'   => 'feedzy_job',
						'value' => $post_id,
					),
					array(
						'key'   => 'feedzy_job_time',
						'value' => $run_id,
					),
				),
			)
		);

		$imported = (int) $query->found_posts;
		$stalled  = ( time() - $run_id ) > (int) apply_filters( 'feedzy_max_execution_time', 500 ) + MINUTE_IN_SECONDS;

		return array(
			'state'    => $stalled ? 'failed' : 'working',
			'run_id'   => $run_id,
			'progress' => array(
				'current' => $imported,
				'total'   => 0,
				'message' => $stalled
					? __( 'The import run did not finish within the maximum execution time.', 'feedzy-rss-feeds' )
					/* translators: %d: number of imported items */
					: sprintf( __( 'Import running. %d items imported so far.', 'feedzy-rss-feeds' ), $imported ),
			),
		);
	}

	/**
	 * Build per-feed status breakdown for multi-feed import jobs.
	 *
	 * @param  int $post_id Job post ID.
	 *
	 * @return array<int, array<string, mixed>> Array of per-feed status rows.
	 */
	public static function build_per_feed_status( int $post_id ) {
		$source = get_post_meta( $post_id, 'source', true );
		if ( empty( $source ) ) {
			return array();
		}

		$urls = self::resolve_source_to_urls( $source );
		if ( empty( $urls ) ) {
			return array();
		}

		$breakdown = array();
		foreach ( $urls as $url ) {
			$url_key     = sanitize_title( $url );
			$feed_errors = get_post_meta( $post_id, $url_key . '_errors', true );
			$feed_errors = ! empty( $feed_errors ) && is_array( $feed_errors ) ? $feed_errors : array();
			$normalized  = array();

			foreach ( $feed_errors as $entry ) {
				if ( is_string( $entry ) ) {
					$normalized[] = $entry;
				} elseif ( is_array( $entry ) && isset( $entry['message'] ) ) {
					$normalized[] = (string) $entry['message'];
				} elseif ( is_object( $entry ) && isset( $entry->message ) ) {
					$normalized[] = (string) $entry->message;
				} elseif ( is_array( $entry ) || is_object( $entry ) ) {
					$normalized[] = (string) wp_json_encode( $entry );
				}
			}

			$breakdown[] = array(
				'url'    => esc_url( $url ),
				'errors' => $normalized,
			);
		}

		return $breakdown;
	}

	/**
	 * Resolve a source value (slug or URL list) to an array of feed URLs.
	 *
	 * @param  string $source The `source` meta value from a job.
	 *
	 * @return string[]
	 */
	public static function resolve_source_to_urls( string $source ) {
		if ( empty( $source ) ) {
			return array();
		}

		if ( strpos( $source, 'http' ) !== false ) {
			return array_filter( array_map( 'trim', explode( ',', $source ) ) );
		}

		$posts = get_posts(
			array(
				'post_type'              => 'feedzy_categories',
				'name'                   => $source,
				'posts_per_page'         => 1,
				'post_status'            => 'publish',
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);
		if ( empty( $posts ) ) {
			return array();
		}
		$raw = get_post_meta( $posts[0]->ID, 'feedzy_category_feed', true );
		return array_filter( array_map( 'trim', explode( "\n", (string) $raw ) ) );
	}
}
