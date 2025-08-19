<?php
/**
 * The file that defines the conditions class
 *
 * A class definition that includes attributes and functions used across both the
 * import and block functionality of the plugin.
 *
 * @link       http://themeisle.com/plugins/feedzy-rss-feed/
 * @since      5.0.0
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes
 */
class Feedzy_Rss_Feeds_Conditions {

	/**
	 * Logical AND operator for conditions.
	 * Used to require all conditions to be met.
	 */
	const LOGIC_AND = 'all';

	/**
	 * Logical OR operator for conditions.
	 * Used to require any condition to be met.
	 */
	const LOGIC_OR = 'any';

	/**
	 * Operator to check if a value exists.
	 */
	const OPERATOR_HAS_VALUE = 'has_value';

	/**
	 * Operator to check if values are equal.
	 */
	const OPERATOR_EQUALS = 'equals';

	/**
	 * Operator to check if values are not equal.
	 */
	const OPERATOR_NOT_EQUALS = 'not_equals';

	/**
	 * Operator to check if a value is empty.
	 */
	const OPERATOR_EMPTY = 'empty';

	/**
	 * Operator to check if a value contains a substring.
	 */
	const OPERATOR_CONTAINS = 'contains';

	/**
	 * Operator to check if a value does not contain a substring.
	 */
	const OPERATOR_NOT_CONTAINS = 'not_contains';

	/**
	 * Operator to check if a value is greater than another value.
	 */
	const OPERATOR_GREATER_THAN = 'greater_than';

	/**
	 * Operator to check if a value is greater or equal than another value.
	 */
	const OPERATOR_GREATER_THAN_EQUALS = 'gte';

	/**
	 * Operator to check if a value is less than another value.
	 */
	const OPERATOR_LESS_THAN = 'less_than';

	/**
	 * Operator to check if a value is less or equals than another value.
	 */
	const OPERATOR_LESS_THAN_EQUALS = 'lte';

	/**
	 * Operator to check if a value matches a regular expression.
	 */
	const OPERATOR_REGEX = 'regex';

	/**
	 * Gets the supported operators.
	 *
	 * @return array<string> The supported operators.
	 */
	public static function get_operators(): array {
		return array(
			self::OPERATOR_HAS_VALUE           => __( 'Has Any Value', 'feedzy-rss-feeds' ),
			self::OPERATOR_EQUALS              => __( 'Equals', 'feedzy-rss-feeds' ),
			self::OPERATOR_NOT_EQUALS          => __( 'Not Equals', 'feedzy-rss-feeds' ),
			self::OPERATOR_EMPTY               => __( 'Is Empty', 'feedzy-rss-feeds' ),
			self::OPERATOR_CONTAINS            => __( 'Contains', 'feedzy-rss-feeds' ),
			self::OPERATOR_NOT_CONTAINS        => __( 'Not Contains', 'feedzy-rss-feeds' ),
			self::OPERATOR_GREATER_THAN        => __( 'Greater Than', 'feedzy-rss-feeds' ),
			self::OPERATOR_GREATER_THAN_EQUALS => __( 'Greater Than or Equals', 'feedzy-rss-feeds' ),
			self::OPERATOR_LESS_THAN           => __( 'Less Than', 'feedzy-rss-feeds' ),
			self::OPERATOR_LESS_THAN_EQUALS    => __( 'Less Than or Equals', 'feedzy-rss-feeds' ),
			self::OPERATOR_REGEX               => __( 'Matches Regular Expression', 'feedzy-rss-feeds' ),
		);
	}

	/**
	 * Migrate old conditions to a new format.
	 *
	 * This function takes an array of old conditions and converts them into a new format
	 * that includes logical operators and condition mappings.
	 *
	 * @param array $conditions The old conditions array.
	 *
	 * @return string The new conditions in JSON format.
	 */
	public function migrate_conditions( $conditions ): string {
		if ( ! is_array( $conditions ) ) {
			return '';
		}

		// In shortcodes and blocks, sometimes only the keywords are set, and the fields are not set.
		$default_field_mappings = array(
			'keywords_title' => 'keywords_inc_on',
			'keywords_ban'   => 'keywords_exc_on',
		);

		foreach ( $default_field_mappings as $old_key => $new_key ) {
			if ( isset( $conditions[ $old_key ] ) && ( ! isset( $conditions[ $new_key ] ) || empty( $conditions[ $new_key ] ) ) ) {
				$conditions[ $new_key ] = 'title';
			}
		}

		foreach ( array(
			'keywords_title' => 'keywords_inc',
			'keywords_ban'   => 'keywords_exc',
		) as $old_key => $new_key ) {
			if ( isset( $conditions[ $old_key ] ) ) {
				$conditions[ $new_key ] = $conditions[ $old_key ];
				unset( $conditions[ $old_key ] );
			}
		}

		$new_conditions = array(
			'match'      => self::LOGIC_AND,
			'conditions' => array(),
		);

		$mapping = array(
			'keywords_inc'  => self::OPERATOR_CONTAINS,
			'keywords_exc'  => self::OPERATOR_NOT_CONTAINS,
			'from_datetime' => self::OPERATOR_GREATER_THAN,
			'to_datetime'   => self::OPERATOR_LESS_THAN,
		);

		foreach ( $mapping as $key => $operator ) {
			if ( isset( $conditions[ $key ] ) && ! empty( $conditions[ $key ] ) ) {
				$field = 'from_datetime' === $key || 'to_datetime' === $key ? 'date' : ( isset( $conditions[ $key . '_on' ] ) ? $conditions[ $key . '_on' ] : '' );
				if ( ! empty( $field ) ) {
					array_push(
						$new_conditions['conditions'],
						array(
							'field'    => $field,
							'operator' => $operator,
							'value'    => $conditions[ $key ],
						)
					);
				}
			}
		}

		return wp_json_encode( $new_conditions );
	}

	/**
	 * Check if a condition is met.
	 *
	 * This function takes a condition and checks if it is met by the given value.
	 *
	 * @param array<string, string> $condition The condition to check.
	 * @param string                $value The value to check against.
	 *
	 * @return bool True if the condition is met, false otherwise.
	 */
	public function is_condition_met( $condition, $value ): bool {
		$operator        = $condition['operator'];
		$condition_value = trim( $condition['value'] ?? '' );
		$value           = trim( $value );

		switch ( $operator ) {
			case self::OPERATOR_HAS_VALUE:
				return ! empty( $value );
			case self::OPERATOR_EQUALS:
				return strtolower( $value ) === strtolower( $condition_value );
			case self::OPERATOR_NOT_EQUALS:
				return strtolower( $value ) !== strtolower( $condition_value );
			case self::OPERATOR_EMPTY:
				return empty( $value );
			case self::OPERATOR_CONTAINS:
				return $this->check_contains( strtolower( $value ), strtolower( $condition_value ) );
			case self::OPERATOR_NOT_CONTAINS:
				return ! $this->check_contains( strtolower( $value ), strtolower( $condition_value ) );
			case self::OPERATOR_GREATER_THAN:
			case self::OPERATOR_GREATER_THAN_EQUALS:
			case self::OPERATOR_LESS_THAN:
			case self::OPERATOR_LESS_THAN_EQUALS:
				// Check if the field type is date.
				if ( isset( $condition['field'] ) && 'date' === $condition['field'] ) {
					$condition_value = new DateTime( $condition_value, new DateTimeZone( 'UTC' ) );
					$condition_value = $condition_value->getTimestamp();
				} elseif ( isset( $condition['field'] ) && in_array( $condition['field'], array( 'title', 'description', 'fullcontent' ), true ) ) {
					// Check if the field type is title, description or fullcontent, we compare the length of the string.
					$value           = strlen( $value );
					$condition_value = (int) $condition_value;
				}

				switch ( $operator ) {
					case self::OPERATOR_GREATER_THAN:
						return $value > $condition_value;
					case self::OPERATOR_GREATER_THAN_EQUALS:
						return $value >= $condition_value;
					case self::OPERATOR_LESS_THAN:
						return $value < $condition_value;
					case self::OPERATOR_LESS_THAN_EQUALS:
						return $value <= $condition_value;
				}
				break;
			case self::OPERATOR_REGEX:
				if ( ! preg_match( '/^\/.*\/[imsxuADU]*$/', $condition_value ) ) {
					$condition_value = '/' . $condition_value . '/i';
				}
				return preg_match( $condition_value, $value ) === 1;
			default:
				// Default is OPERATOR_HAS_VALUE.
				return ! empty( $value );
		}
	}

	/**
	 * Evaluate conditions.
	 *
	 * This function evaluates a set of conditions against an item and returns whether they are met.
	 *
	 * @param bool                  $default_value The current return value.
	 * @param array<string, string> $attrs The attributes of the feed.
	 * @param SimplePie\Item        $item The item to evaluate.
	 * @param string                $feed_url The URL of the feed.
	 * @param int                   $index The index of the item.
	 *
	 * @return bool True if the conditions are met, false otherwise.
	 */
	public function evaluate_conditions( $default_value, $attrs, $item, $feed_url, $index ): bool {
		if ( ! isset( $attrs['filters'] ) || empty( $attrs['filters'] ) ) {
			return $default_value;
		}

		$filters = json_decode( $attrs['filters'], true );

		if ( ! is_array( $filters ) ) {
			return $default_value;
		}

		$conditions = $filters['conditions'] ?? array();
		$logic      = $filters['match'] ?? self::LOGIC_AND;

		if ( ! is_array( $conditions ) ) {
			return $default_value;
		}

		$default_value = self::LOGIC_AND === $logic;

		foreach ( $conditions as $condition ) {
			$field = $condition['field'] ?? '';
			$value = '';

			switch ( $field ) {
				case 'title':
					$value = wp_strip_all_tags( $item->get_title(), true );
					break;
				case 'description':
					$value = wp_strip_all_tags( $item->get_content(), true );
					break;
				case 'fullcontent':
					$content = $item->get_item_tags( SIMPLEPIE_NAMESPACE_ATOM_10, 'full-content' );
					$content = ! empty( $content[0]['data'] ) ? $content[0]['data'] : '';
					$value   = wp_strip_all_tags( $content, true );
					break;
				case 'author':
					$author = $item->get_author();
					$value  = $author ? $author->get_name() : '';
					break;
				case 'date':
					$value = strtotime( $item->get_date() );
					break;
				case 'featured_image':
					$instance = Feedzy_Rss_Feeds::instance();
					$admin    = $instance->get_admin();
					$image    = $admin->feedzy_retrieve_image( $item, $attrs );
					$value    = $image;
					break;
				case 'link':
					$value = $item->get_link();
					break;
				default:
					$value = '';
					break;
			}

			$condition_met = $this->is_condition_met( $condition, $value );

			if ( self::LOGIC_AND === $logic && ! $condition_met ) {
				$default_value = false;
				break;
			}

			if ( self::LOGIC_OR === $logic && $condition_met ) {
				$default_value = true;
				break;
			}
		}

		return $default_value;
	}

	/**
	 * Check if a value contains a condition value.
	 *
	 * This function checks if a value contains a condition value.
	 *
	 * @param string $value The value to check.
	 * @param string $condition_value The condition value to check for.
	 *
	 * @return bool True if the value contains the condition value, false otherwise.
	 */
	private function check_contains( $value, $condition_value ): bool {
		$or_conditions = preg_split( '/\s*,\s*/', $condition_value );
		foreach ( $or_conditions as $or_condition ) {
			$and_conditions           = preg_split( '/\s*\+\s*/', $or_condition );
			$all_and_conditions_match = true;
			foreach ( $and_conditions as $and_condition ) {
				if ( strpos( $value, trim( $and_condition ) ) === false ) {
					$all_and_conditions_match = false;
					break;
				}
			}
			if ( $all_and_conditions_match ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Convert a filter string to JSON.
	 *
	 * This function converts a filter string to a JSON object.
	 *
	 * @param string $filter_str The filter string to convert.
	 *
	 * @return string|false The JSON object.
	 */
	public function convert_filter_string_to_json( $filter_str ) {
		// Split into segments by semicolon.
		$segments     = explode( ';', $filter_str );
		$filter_array = array( 'conditions' => array() );

		foreach ( $segments as $segment ) {
			$segment = trim( $segment );

			// Check if this segment defines 'match'.
			if ( strpos( $segment, 'match=' ) === 0 ) {
				// Extract match value.
				list( , $match_val )   = explode( '=', $segment, 2 );
				$filter_array['match'] = trim( $match_val );
			} elseif ( strpos( $segment, 'condition=' ) === 0 ) {
				// Check if this segment defines a 'condition'.
				// Remove "condition=" prefix.
				$condition_str = substr( $segment, strlen( 'condition=' ) );
				$pairs         = explode( ',', $condition_str );
				$condition     = array();

				// Each pair is in the form key:value.
				foreach ( $pairs as $pair ) {
					$pair = trim( $pair );
					if ( strpos( $pair, ':' ) !== false ) {
						list( $key, $val )         = explode( ':', $pair, 2 );
						$condition[ trim( $key ) ] = trim( $val );
					}
				}

				if ( ! empty( $condition ) ) {
					$filter_array['conditions'][] = $condition;
				}
			}
		}

		// Encode the final array as JSON.
		return wp_json_encode( $filter_array );
	}
}
