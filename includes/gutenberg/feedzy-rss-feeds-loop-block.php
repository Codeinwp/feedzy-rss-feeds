<?php
/**
 * Class for functionalities related to Loop block.
 *
 * Defines the functions that need to be used for Loop block,
 * and REST router.
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes/guteneberg
 * @author     Themeisle <friends@themeisle.com>
 */
class Feedzy_Rss_Feeds_Loop_Block {

	/**
	 * Magic tags whose value is a URL.
	 *
	 * @var list<string>
	 */
	const URL_KEYS = array( 'url', 'image', 'media' );

	/**
	 * Magic tags whose value is rich HTML.
	 *
	 * @var list<string>
	 */
	const HTML_KEYS = array( 'description', 'content', 'meta' );

	/**
	 * Elements a template may never contribute: scripts, document-level tags
	 * and SVG animations, which can rewrite other attributes such as href.
	 *
	 * @var list<string>
	 */
	const DENIED_ELEMENTS = array( 'script', 'style', 'meta', 'base', 'set', 'animate', 'animatemotion', 'animatetransform' );

	/**
	 * A reference to an instance of this class.
	 *
	 * @var Feedzy_Rss_Feeds_Loop_Block The one Feedzy_Rss_Feeds_Loop_Block instance.
	 */
	private static $instance;

	/**
	 * Instance of Feedzy_Rss_Feeds_Admin class.
	 *
	 * @var Feedzy_Rss_Feeds_Admin $admin The Feedzy_Rss_Feeds_Admin instance.
	 */
	private $admin;

	/**
	 * Feedzy RSS Feeds plugin version.
	 *
	 * @var string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Returns an instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Feedzy_Rss_Feeds_Loop_Block();
		}
		return self::$instance;
	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {
		$this->version = Feedzy_Rss_Feeds::get_version();
		$this->admin   = Feedzy_Rss_Feeds::instance()->get_admin();
		add_action( 'init', array( $this, 'register_block' ) );
		add_filter( 'feedzy_loop_item', array( $this, 'apply_magic_tags' ), 10, 3 );
	}

	/**
	 * Register Block
	 */
	public function register_block() {
		$metadata_file = trailingslashit( FEEDZY_ABSPATH ) . '/build/loop/block.json';
		register_block_type_from_metadata(
			$metadata_file,
			array(
				'render_callback' => array( $this, 'render_callback' ),
			)
		);

		wp_set_script_translations( 'feedzy-rss-feeds-loop-editor-script', 'feedzy-rss-feeds' );

		// Pass in REST URL.
		wp_localize_script(
			'feedzy-rss-feeds-loop-editor-script',
			'feedzyData',
			array(
				'imagepath'    => esc_url( FEEDZY_ABSURL . 'img/' ),
				'defaultImage' => esc_url( FEEDZY_ABSURL . 'img/feedzy.svg' ),
				'url'          => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( FEEDZY_BASEFILE ),
				'isPro'        => feedzy_is_pro(),
			)
		);

		wp_localize_script(
			'feedzy-rss-feeds-loop-editor-script',
			'feedzyConditionsData',
			apply_filters(
				'feedzy_conditions_data',
				array(
					'isPro'     => feedzy_is_pro(),
					'operators' => Feedzy_Rss_Feeds_Conditions::get_operators(),
				)
			)
		);
	}

	/**
	 * Render Callback
	 *
	 * @param array<string, mixed> $attributes The block attributes.
	 * @param string               $content The block content.
	 * @return string The block content.
	 */
	public function render_callback( $attributes, $content ) {
		$content    = empty( $content ) ? ( $attributes['innerBlocksContent'] ?? '' ) : $content;
		$is_preview = isset( $attributes['innerBlocksContent'] ) && ! empty( $attributes['innerBlocksContent'] );
		$feed_urls  = array();

		if ( isset( $attributes['feed']['type'] ) && 'group' === $attributes['feed']['type'] && isset( $attributes['feed']['source'] ) && is_numeric( $attributes['feed']['source'] ) ) {
			$group     = $attributes['feed']['source'];
			$value     = get_post_meta( $group, 'feedzy_category_feed', true );
			$value     = trim( $value );
			$feed_urls = ! empty( $value ) ? explode( ',', $value ) : array();
		}

		if ( isset( $attributes['feed']['type'] ) && 'url' === $attributes['feed']['type'] && isset( $attributes['feed']['source'] ) && is_array( $attributes['feed']['source'] ) ) {
			$feed_urls = $attributes['feed']['source'];
		}

		if ( empty( $feed_urls ) ) {
			return '<div>' . esc_html__( 'No feeds to display', 'feedzy-rss-feeds' ) . '</div>';
		}

		$column_count = isset( $attributes['layout'] ) && isset( $attributes['layout']['columnCount'] ) && ! empty( $attributes['layout']['columnCount'] ) ? $attributes['layout']['columnCount'] : 1;
		$referral_url = isset( $attributes['referral_url'] ) ? $attributes['referral_url'] : '';

		$default_query = array(
			'max'     => 5,
			'sort'    => 'default',
			'refresh' => '12_hours',
		);

		$query             = isset( $attributes['query'] ) ? wp_parse_args( $attributes['query'], $default_query ) : $default_query;
		$filters           = isset( $attributes['conditions'] ) ? $attributes['conditions'] : array();
		$thumb             = 'auto';
		$default_thumbnail = '';
	
		if ( isset( $attributes['thumb'] ) && ! empty( $attributes['thumb'] ) ) {
			$thumb = $attributes['thumb'];

			if (
				'yes' === $thumb &&
				isset( $attributes['fallbackImage'], $attributes['fallbackImage']['id'] ) &&
				! empty( $attributes['fallbackImage']['id'] )
			) {
				$image_id  = $attributes['fallbackImage']['id'];
				$media_img = wp_get_attachment_image_src( $image_id );

				if ( is_array( $media_img ) && ! empty( $media_img[0] ) ) {
					$default_thumbnail = $media_img[0];
				}
			}
		}

		$summary_length = '400';
		$title_length   = '';
		if ( isset( $query['summary_length'] ) ) {
			$summary_length = absint( $query['summary_length'] );
			$summary_length = $summary_length > 0 ? (string) $summary_length : '';
		}

		if ( isset( $query['title_length'] ) ) {
			$title_length = absint( $query['title_length'] );
			$title_length = $title_length > 0 ? (string) $title_length : '';
		}

		$options = array(
			'feeds'         => implode( ',', $feed_urls ),
			'max'           => $query['max'],
			'sort'          => $query['sort'],
			'offset'        => 0,
			'target'        => '_blank',
			'keywords_ban'  => '',
			'columns'       => '1',
			'thumb'         => $thumb,
			'default'       => $default_thumbnail,
			'title'         => $title_length,
			'meta'          => 'yes',
			'multiple_meta' => 'no',
			'summary'       => 'yes',
			'summarylength' => $summary_length,
			'filters'       => wp_json_encode( $filters ),
			'referral_url'  => $referral_url,
		);

		$sizes = array(
			'width'  => 300,
			'height' => 300,
		);

		$feed = $this->admin->fetch_feed( $feed_urls, $query['refresh'], $options );

		if ( isset( $feed->error ) && ! empty( $feed->error ) ) {
			return '<div>' . esc_html__( 'An error occurred while fetching the feed.', 'feedzy-rss-feeds' ) . '</div>';
		}

		$feed_items = apply_filters( 'feedzy_get_feed_array', array(), $options, $feed, implode( ',', $feed_urls ), $sizes );

		if ( empty( $feed_items ) ) {
			return '<div>' . esc_html__( 'No items to display.', 'feedzy-rss-feeds' ) . '</div>';
		}

		$content = $this->prepare_template( $content );
		$loop    = '';

		foreach ( $feed_items as $key => $item ) {
			$loop .= apply_filters( 'feedzy_loop_item', $content, $item, $attributes );
		}

		// Feed values may not add tags, attributes or URL schemes the template lacks.
		$loop = $this->kses( $loop, $this->get_allowed_html( $content ) );

		return sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes = get_block_wrapper_attributes(
				array(
					'class' => 'feedzy-loop-columns-' . $column_count,
				) 
			),
			$loop
		);
	}

	/**
	 * Prepare a template for KSES in one left-to-right pass that reads `<` the
	 * way the HTML tokenizer does, so nothing below can run past its real end:
	 * - script and style elements are removed with their body, since KSES would
	 *   drop the tags but print the body as text; their code no longer runs;
	 * - textarea, title, xmp and plaintext bodies are text: < and > in them are
	 *   encoded, which textarea and title display unchanged;
	 * - iframe, noembed, noframes and noscript bodies are fallback markup that
	 *   browsers read as raw text: each is prepared on its own up to its end tag;
	 * - comments, including those closed by --!> or left open, and bogus
	 *   comments (<!x>, <?x>, </3>) keep their place with < and > encoded and are
	 *   always closed; empty <!--> and <!---> are dropped;
	 * - < and > in quoted attribute values of start and end tags are encoded.
	 * Unclosed textarea, title, xmp, iframe, noembed, noframes and noscript
	 * elements are closed at the end of the template. Plaintext has no end tag,
	 * so it still runs to the end of the template and beyond, as in browsers.
	 * Magic tags inside text and fallback bodies are marked to insert plain text.
	 *
	 * @param string $template The inner blocks template.
	 *
	 * @return string The prepared template.
	 */
	private function prepare_template( string $template ): string {
		// Rest of a start tag: quotes open a value only after "=".
		$open    = '(?:(?>\s*=\s*(?:"[^"]*"|\'[^\']*\'))|[^>])*+>';
		$pattern = '#(?<removed><(?<rname>script|style)(?=[\s/>])' . $open . '.*?(?:</\k<rname>(?=[\s/>])[^>]*>|$))'
			. '|(?<sopen><(?<sname>textarea|title|xmp|iframe|noembed|noframes|noscript)(?=[\s/>])' . $open . ')(?<sbody>.*?)(?<sclose></\k<sname>(?=[\s/>])[^>]*>|$)'
			. '|(?<popen><plaintext(?=[\s/>])' . $open . ')(?<pbody>.*)'
			. '|(?<empty><!---?>)'
			. '|<!--(?<cbody>.*?)(?:--!?>|$)'
			. '|(?<bogus><(?:[!?]|/(?![a-zA-Z]))[^>]*)(?:>|$)'
			. '|</?[a-zA-Z]' . $open . '#is';

		return (string) preg_replace_callback(
			$pattern,
			function ( array $matches ): string {
				return $this->prepare_match( $matches );
			},
			$template
		);
	}

	/**
	 * Prepare one construct matched by prepare_template().
	 *
	 * @param array<int|string, string> $matches The pattern matches.
	 *
	 * @return string The prepared markup.
	 */
	private function prepare_match( array $matches ): string {
		$matches = array_merge(
			array(
				'removed' => '',
				'empty'   => '',
				'sname'   => '',
				'popen'   => '',
				'bogus'   => '',
			),
			$matches
		);

		if ( '' !== $matches['removed'] || '' !== $matches['empty'] ) {
			return '';
		}

		if ( '' !== $matches['sname'] ) {
			return $this->prepare_special_element( strtolower( $matches['sname'] ), $matches['sopen'], $matches['sbody'], $matches['sclose'] );
		}

		if ( '' !== $matches['popen'] ) {
			return $this->encode_attribute_values( $matches['popen'] ) . $this->encode_delimiters( $this->mark_plain_tags( $matches['pbody'] ) );
		}

		if ( 0 === strpos( $matches[0], '<!--' ) ) {
			return '<!--' . $this->encode_delimiters( $matches['cbody'] ) . '-->';
		}

		if ( '' !== $matches['bogus'] ) {
			return '<' . $this->encode_delimiters( substr( $matches['bogus'], 1 ) ) . '>';
		}

		return $this->encode_attribute_values( $matches[0] );
	}

	/**
	 * Prepare an element whose body the HTML tokenizer does not read as markup.
	 *
	 * @param string $name The lowercase element name.
	 * @param string $open The start tag.
	 * @param string $body The body up to the end tag.
	 * @param string $close The end tag, or empty when the element is unclosed.
	 *
	 * @return string The prepared element.
	 */
	private function prepare_special_element( string $name, string $open, string $body, string $close ): string {
		$body = $this->mark_plain_tags( $body );
		$body = in_array( $name, array( 'iframe', 'noembed', 'noframes', 'noscript' ), true ) ? $this->prepare_template( $body ) : $this->encode_delimiters( $body );

		return $this->encode_attribute_values( $open ) . $body . ( '' === $close ? '</' . $name . '>' : $close );
	}

	/**
	 * Mark magic tags so their values are inserted as plain text.
	 *
	 * @param string $markup The markup.
	 *
	 * @return string The markup with marked magic tags.
	 */
	private function mark_plain_tags( string $markup ): string {
		return (string) preg_replace( '/\{\{feedzy_([^}|]+)\}\}/', '{{feedzy_$1|plain}}', $markup );
	}

	/**
	 * Encode < and > in the quoted attribute values of a tag.
	 *
	 * @param string $tag The tag.
	 *
	 * @return string The tag with attribute delimiters encoded.
	 */
	private function encode_attribute_values( string $tag ): string {
		return (string) preg_replace_callback(
			'/=\s*("[^"]*"|\'[^\']*\')/',
			function ( array $value ): string {
				return $this->encode_delimiters( $value[0] );
			},
			$tag
		);
	}

	/**
	 * Encode < and > as character references.
	 *
	 * @param string $text The text.
	 *
	 * @return string The encoded text.
	 */
	private function encode_delimiters( string $text ): string {
		return str_replace( array( '<', '>' ), array( '&lt;', '&gt;' ), $text );
	}

	/**
	 * Build the KSES allowlist for a Loop template: post-safe HTML plus every
	 * tag and attribute the template itself uses, minus executable ones.
	 *
	 * @param string $template The inner blocks template.
	 *
	 * @return array<string, array<string, mixed>> The allowed HTML.
	 */
	private function get_allowed_html( string $template ): array {
		$allowed = wp_kses_allowed_html( 'post' );

		foreach ( $this->get_template_attributes( $template ) as $tag => $attributes ) {
			// KSES also accepts `true` for an element with no attributes.
			$existing        = isset( $allowed[ $tag ] ) && is_array( $allowed[ $tag ] ) ? $allowed[ $tag ] : array();
			$allowed[ $tag ] = array_merge( $existing, $attributes );
		}

		$allowed = array_diff_key( $allowed, array_flip( self::DENIED_ELEMENTS ) );

		foreach ( $allowed as $tag => $attributes ) {
			$allowed[ $tag ] = array_filter(
				is_array( $attributes ) ? $attributes : array(),
				function ( $attribute ): bool {
					return 0 !== strpos( (string) $attribute, 'on' ) && 'srcdoc' !== $attribute;
				},
				ARRAY_FILTER_USE_KEY
			);
		}

		return $allowed;
	}

	/**
	 * Collect the tag and attribute names used in a template.
	 *
	 * @param string $template The inner blocks template.
	 *
	 * @return array<string, array<string, true>> Attribute names keyed by tag name.
	 */
	private function get_template_attributes( string $template ): array {
		$tags = array();

		if ( ! preg_match_all( '/<([a-zA-Z][a-zA-Z0-9:-]*)((?:[^>"\']|"[^"]*"|\'[^\']*\')*)>/', $template, $matches, PREG_SET_ORDER ) ) {
			return $tags;
		}

		foreach ( $matches as $match ) {
			$tag          = strtolower( $match[1] );
			$tags[ $tag ] = $tags[ $tag ] ?? array();

			foreach ( array_keys( wp_kses_hair( $match[2], wp_allowed_protocols() ) ) as $attribute ) {
				$tags[ $tag ][ strtolower( (string) $attribute ) ] = true;
			}
		}

		return $tags;
	}

	/**
	 * Magic Tags Replacement.
	 *
	 * @param string               $content The content.
	 * @param array                $item The item.
	 * @param array<string, mixed> $attributes The block attributes.
	 *
	 * @return string The content.
	 */
	public function apply_magic_tags( $content, $item, $attributes ) {
		$pattern = '/\{\{feedzy_([^}]+)\}\}/';
		$content = str_replace(
			array(
				FEEDZY_ABSURL . 'img/feedzy.svg',
				'http://{{feedzy_url}}',
			),
			array(
				'{{feedzy_image}}',
				'{{feedzy_url}}',
			),
			$content
		);

		// Swap tags for inert tokens so KSES normalizes the template around them.
		$prefix   = 'feedzytoken' . substr( md5( uniqid( '', true ) ), 0, 12 );
		$keys     = array();
		$template = preg_replace_callback(
			$pattern,
			function ( array $matches ) use ( &$keys, $prefix ): string {
				$token          = $prefix . count( $keys ) . 'x';
				$keys[ $token ] = $matches[1];
				return $token;
			},
			$content
		);

		if ( empty( $keys ) ) {
			return $content;
		}

		$template = $this->kses( (string) $template, $this->get_allowed_html( $content ) );
		$values   = array();
		foreach ( $keys as $token => $tag ) {
			// prepare_template() marks tags in text and fallback bodies with "|plain".
			$parts            = explode( '|', $tag, 2 );
			$values[ $token ] = array(
				'key'   => $parts[0],
				'value' => (string) $this->get_raw_value( $parts[0], $item, $attributes ),
				'plain' => isset( $parts[1] ) && 'plain' === $parts[1],
			);
		}

		return (string) preg_replace_callback(
			'/<!--.*?-->|<[^>]*>|[^<]+/s',
			function ( array $matches ) use ( $values ): string {
				return $this->fill_tokens( $matches[0], $values );
			},
			$template
		);
	}

	/**
	 * Replace the tokens in one piece of KSES-normalized markup, escaping each
	 * value for where it sits: attribute value, comment or element content.
	 *
	 * @param string                                                        $part A tag, comment or text run.
	 * @param array<string, array{key: string, value: string, plain: bool}> $values Raw values keyed by token.
	 *
	 * @return string The filled markup.
	 */
	private function fill_tokens( string $part, array $values ): string {
		$context = 'text';
		if ( 0 === strpos( $part, '<!--' ) ) {
			$context = 'comment';
		} elseif ( 0 === strpos( $part, '<' ) ) {
			$context = 'attribute';
			$part    = (string) preg_replace_callback(
				'/=("[^"]*"|\'[^\']*\')/',
				function ( array $matches ) use ( $values ): string {
					return '=' . $this->replace_tokens( $matches[1], $values, 'attribute' );
				},
				$part
			);
		}

		// Tokens left in a tag are outside any attribute value, so they are dropped.
		return $this->replace_tokens( $part, $values, 'attribute' === $context ? 'drop' : $context );
	}

	/**
	 * Replace tokens with their values escaped for a context.
	 *
	 * @param string                                                        $markup The markup holding tokens.
	 * @param array<string, array{key: string, value: string, plain: bool}> $values Raw values keyed by token.
	 * @param string                                                        $context One of text, attribute, comment or drop.
	 *
	 * @return string The markup with tokens replaced.
	 */
	private function replace_tokens( string $markup, array $values, string $context ): string {
		$replacements = array();
		foreach ( $values as $token => $value ) {
			if ( false === strpos( $markup, $token ) ) {
				continue;
			}
			if ( 'drop' === $context ) {
				$replacements[ $token ] = '';
				continue;
			}
			$replacements[ $token ] = $this->escape_value( $value['key'], $value['value'], $value['plain'] ? 'plain' : $context );
		}

		return empty( $replacements ) ? $markup : strtr( $markup, $replacements );
	}

	/**
	 * Escape a raw feed value for a context.
	 *
	 * @param string $key The magic tag key.
	 * @param string $value The raw value.
	 * @param string $context One of text, attribute, comment or plain.
	 *
	 * @return string The escaped value.
	 */
	private function escape_value( string $key, string $value, string $context ): string {
		$is_html = in_array( $key, self::HTML_KEYS, true );

		// Comments and text-only bodies get plain text that cannot close them.
		if ( in_array( $context, array( 'comment', 'plain' ), true ) ) {
			return esc_html( $is_html ? wp_strip_all_tags( $value ) : $value );
		}

		if ( in_array( $key, self::URL_KEYS, true ) ) {
			return esc_url( $value );
		}

		if ( 'text' === $context ) {
			return $is_html ? wp_kses_post( $value ) : esc_html( $value );
		}

		return esc_attr( $is_html ? wp_strip_all_tags( $value ) : $value );
	}

	/**
	 * Run KSES with SVG link attributes protocol-checked as URLs.
	 *
	 * @param string                              $html The markup.
	 * @param array<string, array<string, mixed>> $allowed The allowed HTML.
	 *
	 * @return string The sanitized markup.
	 */
	private function kses( string $html, array $allowed ): string {
		add_filter( 'wp_kses_uri_attributes', array( $this, 'add_uri_attributes' ) );
		$html = wp_kses( $html, $allowed );
		remove_filter( 'wp_kses_uri_attributes', array( $this, 'add_uri_attributes' ) );

		return $html;
	}

	/**
	 * Add SVG link attributes to the KSES URI attribute list.
	 *
	 * @param string[] $attributes The URI attributes.
	 *
	 * @return string[] The URI attributes.
	 */
	public function add_uri_attributes( array $attributes ): array {
		$attributes[] = 'xlink:href';

		return $attributes;
	}

	/**
	 * Get Dynamic Value, escaped for element content.
	 *
	 * @param string               $key The key.
	 * @param array<string, mixed> $item Feed item.
	 * @param array<string, mixed> $attributes The block attributes.
	 *
	 * @return string The value.
	 */
	public function get_value( $key, $item, $attributes ) {
		return $this->escape_value( $key, (string) $this->get_raw_value( $key, $item, $attributes ), 'text' );
	}

	/**
	 * Get the unescaped feed value for a magic tag.
	 *
	 * @param string               $key The key.
	 * @param array<string, mixed> $item Feed item.
	 * @param array<string, mixed> $attributes The block attributes.
	 *
	 * @return string|null The value.
	 */
	private function get_raw_value( $key, $item, $attributes ) {
		switch ( $key ) {
			case 'title':
				return isset( $item['item_title'] ) ? $item['item_title'] : '';
			case 'url':
				return isset( $item['item_url'] ) ? $item['item_url'] : '';
			case 'date':
				$item_date = isset( $item['item_date'] ) ? wp_date( get_option( 'date_format' ), $item['item_date'] ) : '';
				return $item_date;
			case 'time':
				$item_date = isset( $item['item_date'] ) ? wp_date( get_option( 'time_format' ), $item['item_date'] ) : '';
				return $item_date;
			case 'datetime':
				$item_date = isset( $item['item_date'] ) ? wp_date( get_option( 'date_format' ), $item['item_date'] ) : '';
				$item_time = isset( $item['item_date'] ) ? wp_date( get_option( 'time_format' ), $item['item_date'] ) : '';
				/* translators: 1: date, 2: time */
				$datetime = sprintf( __( '%1$s at %2$s', 'feedzy-rss-feeds' ), $item_date, $item_time );
				return $datetime;
			case 'author':
				if ( isset( $item['item_author'] ) && is_string( $item['item_author'] ) ) {
					return $item['item_author'];
				} elseif ( isset( $item['item_author'] ) && is_object( $item['item_author'] ) ) {
					return $item['item_author']->get_name();
				}
				return '';
			case 'description':
				return isset( $item['item_description'] ) ? $item['item_description'] : '';
			case 'content':
				return isset( $item['item_content'] ) ? $item['item_content'] : '';
			case 'meta':
				return isset( $item['item_meta'] ) ? $item['item_meta'] : '';
			case 'categories':
				return isset( $item['item_categories'] ) ? $item['item_categories'] : '';
			case 'image':
				return $this->get_thumbnail( $item, $attributes );
			case 'media':
				return isset( $item['item_media']['src'] ) ? $item['item_media']['src'] : '';
			case 'price':
				return isset( $item['item_price'] ) ? $item['item_price'] : '';
			case 'source':
				return isset( $item['item_source'] ) ? $item['item_source'] : '';
			default:
				return '';
		}
	}

	/**
	 * Get Thumbnail of feed item.
	 * 
	 * Fallback to default thumbnail if not set. The Fallback image can be set in the block attributes or in the plugin settings.
	 *
	 * @param array<string, mixed> $item The feed item.
	 * @param array<string, mixed> $attributes The block attributes.
	 *
	 * @return string The thumbnail URL.
	 */
	private function get_thumbnail( $item, $attributes ) {
		$settings = apply_filters( 'feedzy_get_settings', array() );
		$thumb    = 'yes';
	
		if ( isset( $attributes['thumb'] ) && ! empty( $attributes['thumb'] ) ) {
			$thumb = $attributes['thumb'];
		}
	
		if ( 'no' === $thumb ) {
			return '';
		}
	
		if ( isset( $item['item_img_path'] ) && ! empty( $item['item_img_path'] ) ) {
			return $item['item_img_path'];
		} 
		
		if ( 'auto' === $thumb ) {
			return '';
		}

		// Try to find the fallback image.
		if (
			isset( $attributes['fallbackImage'], $attributes['fallbackImage']['url'] ) &&
			! empty( $attributes['fallbackImage']['url'] )
		) {
			$image_id  = $attributes['fallbackImage']['id'];
			$media_img = wp_get_attachment_image_src( $image_id );
			if ( is_array( $media_img ) && ! empty( $media_img[0] ) ) {
				return $media_img[0];
			}
		}
		
		if (
			isset( $settings, $settings['general'], $settings['general']['default-thumbnail-id'] ) &&
			! empty( $settings['general']['default-thumbnail-id'] )
		) {
			$media_img = wp_get_attachment_image_src( $settings['general']['default-thumbnail-id'], 'full' );
			if (
				is_array( $media_img ) && ! empty( $media_img[0] )
			) {
				return $media_img[0];
			}
		}
		
		return FEEDZY_ABSURL . 'img/feedzy.svg';
	}
}
