<?php
/**
 * Utility class for showing limited offers.
 *
 * @link       http://themeisle.com
 * @since      4.2.9
 *
 * @package    feedzy-rss-feeds
 * @subpackage feedzy-rss-feeds/includes
 */

/**
 * Class LimitedOffers
 */
class Feedzy_Rss_Feeds_Limited_Offers {

	/**
	 * Active deal.
	 *
	 * @var string
	 */
	private $active = '';

	/**
	 * The key for WP Options to disable the dashboard notification.
	 *
	 * @var string
	 */
	public $wp_option_dismiss_notification_key_base = 'dismiss_themeisle_notice_event_';

	/**
	 * Offer Links
	 *
	 * @var array<string>
	 */
	public $offer_metadata = array();

	/**
	 * LimitedOffers constructor.
	 */
	public function __construct() {
		try {
			if ( $this->is_deal_active( 'bf' ) ) {
				$this->activate_bff();
			}
		} catch ( Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( $e->getMessage() ); // phpcs:ignore
			}
		}
	}

	/**
	 * Load hooks for the dashboard.
	 *
	 * @return void
	 */
	public function load_dashboard_hooks() {
		add_filter( 'themeisle_products_deal_priority', array( $this, 'add_priority' ) );
		add_action( 'admin_notices', array( $this, 'render_dashboard_banner' ) );
		add_action( 'wp_ajax_dismiss_themeisle_sale_notice_feedzy', array( $this, 'disable_notification_ajax' ) );
	}

	/**
	 * Check if we have an active deal.
	 *
	 * @return bool True if the deal is active.
	 */
	public function is_active() {
		return ! empty( $this->active );
	}

	/**
	 * Activate the Black Friday deal.
	 *
	 * @return void
	 */
	public function activate_bff() {
		$this->active     = 'bf';

		$this->offer_metadata = array(
			'bannerUrl'     => FEEDZY_ABSURL . 'img/black-friday-banner.png', // TODO: change this based on product.
			'bannerAlt'     => 'Feedzy Black Friday Sale',
			'linkDashboard' => tsdk_utmify( 'https://themeisle.com/plugins/feedzy-rss-feeds/blackfriday', 'blackfridayltd23', 'dashboard' ),
			'linkGlobal'    => tsdk_utmify( 'https://themeisle.com/plugins/feedzy-rss-feeds/blackfriday', 'blackfridayltd23', 'globalnotice' ),
		);
	}

	/**
	 * Get the slug of the active deal.
	 *
	 * @return string Active deal.
	 */
	public function get_active_deal() {
		return $this->active;
	}

	/**
	 * Check if the deal is active with the given slug.
	 *
	 * @param string $slug Slug of the deal.
	 *
	 * @throws Exception When date is invalid.
	 */
	public function is_deal_active( $slug ) {

		if ( empty( $slug ) ) {
			return false;
		}

		if ( 'bf' === $slug ) {
			$start_date = '2023-10-10 00:00:00'; // TODO: Add the correct date after QA.
			$end_date   = '2023-11-27 23:59:00';
		} else {
			return false;
		}

		return $this->check_date_range( $start_date, $end_date );
	}

	/**
	 * Get the remaining time for the deal in a human readable format.
	 *
	 * @param string $slug Slug of the deal.
	 * @return string Remaining time for the deal.
	 */
	public function get_remaining_time_for_deal( $slug ) {
		if ( empty( $slug ) ) {
			return '';
		}

		if ( 'bf' === $slug ) {
			$end_date = '2023-11-27 23:59:00';
		} else {
			return '';
		}

		try {
			$end_date     = new DateTime( $end_date, new DateTimeZone( 'GMT' ) );
			$current_date = new DateTime( 'now', new DateTimeZone( 'GMT' ) );
			$diff         = $end_date->diff( $current_date );

			if ( $diff->days > 0 ) {
				return $diff->format( '%a days' );
			}

			if ( $diff->h > 0 ) {
				return $diff->format( '%h hours' );
			}

			if ( $diff->i > 0 ) {
				return $diff->format( '%i minutes' );
			}

			return $diff->format( '%s seconds' );
		} catch ( Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( $e->getMessage() ); // phpcs:ignore
			}
		}

		return '';
	}

	/**
	 * Check if the current date is in the range of the offer.
	 *
	 * @param string $start Start date.
	 * @param string $end   End date.
	 *
	 * @return bool True if the current date is in the range of the offer.
	 *
	 * @throws Exception When date is invalid.
	 */
	public function check_date_range( $start, $end ) {

		$start_date   = new DateTime( $start, new DateTimeZone( 'GMT' ) );
		$end_date     = new DateTime( $end, new DateTimeZone( 'GMT' ) );
		$current_date = new DateTime( 'now', new DateTimeZone( 'GMT' ) );

		return $start_date <= $current_date && $current_date <= $end_date;
	}

	/**
	 * Get the localized data for the plugin.
	 *
	 * @return array Localized data.
	 */
	public function get_localized_data() {
		return array_merge(
			array(
				'active'              => $this->is_active(),
				'dealSlug'            => $this->get_active_deal(),
				'remainingTime'       => $this->get_remaining_time_for_deal( $this->get_active_deal() ),
				'urgencyText'         => 'Hurry Up! Only ' . $this->get_remaining_time_for_deal( $this->get_active_deal() ) . ' left',
			),
			$this->offer_metadata
		);
	}

	/**
	 * Disable the notification via ajax.
	 *
	 * @return void
	 */
	public function disable_notification_ajax() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'dismiss_themeisle_sale_notice_feedzy' ) ) {
			wp_die( esc_html( __( 'Invalid nonce! Refresh the page and try again.', 'feedzy-rss-feeds' ) ) );
		}

		// We record the time and the plugin of the dismissed notification.
		update_option( $this->wp_option_dismiss_notification_key_base . $this->active, 'feedzy_' . $this->active . '_' . current_time( 'Y_m_d' ) );
		wp_die( 'success' );
	}

	/**
	 * Render the dashboard banner.
	 *
	 * @return void
	 */
	public function render_dashboard_banner() {

		if ( ! $this->has_priority() ) {
			return;
		}

		$message = 'Feedzy <strong>Black Friday Sale</strong> - Save big with a <strong>Lifetime License</strong> of Feedzy Agency Plan. <strong>Only 100 licenses</strong>, for a limited time!';

		?>
		<style>
			.themeisle-sale {
				padding: 10px 15px;

				display: flex;
				align-items: center;
			}
			.themeisle-sale svg {
				margin-right: 15px;
			}
			.themeisle-sale a {
				margin-left: 5px;
			}
			.themeisle-sale-error {
				color: red;
			}
		</style>
		<div class="themeisle-sale notice notice-info is-dismissible">
			<div class="notice-dismiss"></div>
			<!--- TODO: change this based on product. -->
			<svg width="24px" height="24px" viewBox="0 0 77 77" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				<!-- Generator: Sketch 52.6 (67491) - http://www.bohemiancoding.com/sketch -->
				<title>Combined Shape</title>
				<desc>Created with Sketch.</desc>
				<g id="Product-Page" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
					<g id="WordPress-plugins" transform="translate(-196.000000, -957.000000)" fill="#4268CF">
						<path d="M234.5,1034 C213.237037,1034 196,1016.76296 196,995.5 C196,974.237037 213.237037,957 234.5,957 C255.762963,957 273,974.237037 273,995.5 C273,1016.76296 255.762963,1034 234.5,1034 Z M238.389087,1003.61091 C236.241256,1001.46308 232.758851,1001.46297 230.610943,1003.61088 C228.463035,1005.75879 228.463021,1009.2412 230.610913,1011.38909 C232.758804,1013.53698 236.241149,1013.53703 238.389057,1011.38912 C240.536965,1009.24121 240.536979,1005.7588 238.389087,1003.61091 Z M251.199196,996.524269 C241.71601,988.013409 227.294143,988.004307 217.800859,996.524214 C217.240496,997.027079 217.222108,997.899777 217.75448,998.43215 L220.551879,1001.22955 C221.041594,1001.71926 221.829967,1001.75226 222.350408,1001.29537 C229.282401,995.21117 239.70281,995.198209 246.649546,1001.29541 C247.170047,1001.75225 247.95842,1001.71925 248.448075,1001.22959 L251.245465,998.432205 C251.777952,997.899834 251.759561,997.027136 251.199196,996.524269 Z M259.517481,988.062818 C245.754662,975.25391 224.312531,975.191374 210.482464,988.062873 C209.95096,988.557557 209.940845,989.396689 210.454222,989.910066 L213.185489,992.641333 C213.675569,993.131413 214.462824,993.141924 214.972622,992.672355 C226.281029,982.254786 243.720804,982.256415 255.027427,992.672311 C255.537167,993.141935 256.324422,993.131423 256.81456,992.641284 L259.545833,989.910011 C260.059097,989.396633 260.048984,988.557501 259.517481,988.062818 Z" id="Combined-Shape"></path>
					</g>
				</g>
			</svg>

			<span>
				<?php echo wp_kses_post( $message ); ?>
			</span>
			<a href="<?php echo esc_url( ! empty( $this->offer_metadata['linkGlobal'] ) ? $this->offer_metadata['linkGlobal'] : '' ); ?>" target="_blank" rel="external noreferrer noopener">
				<?php esc_html_e( 'Learn more', 'feedzy-rss-feeds' ); ?>
			</a>
			<span class="themeisle-sale-error"></span>
		</div>
		<script type="text/javascript">
			window.document.addEventListener( 'DOMContentLoaded', () => {
				const button = document.querySelector( '.themeisle-sale.notice .notice-dismiss' );
				button?.addEventListener( 'click', e => {
					e.preventDefault();
					fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						},
						body: new URLSearchParams({
							action: 'dismiss_themeisle_sale_notice_feedzy',
							nonce: '<?php echo esc_attr( wp_create_nonce( 'dismiss_themeisle_sale_notice_feedzy' ) ); ?>'
						})
					})
						.then(response => response.text())
						.then(response => {
							if ( ! response?.includes( 'success' ) ) {
								document.querySelector( '.themeisle-sale-error' ).innerHTML = response;
								return;
							}

							jQuery( '.themeisle-sale.notice' ).fadeOut()
						})
						.catch(error => {
							console.error( 'Error:', error );
							document.querySelector( '.themeisle-sale-error' ).innerHTML = error;
						});
				});
			});
		</script>
		<?php
	}

	/**
	 * Check if we can show the dashboard banner. Since it is shared between plugins, the user need only to dismiss it once.
	 *
	 * @return bool
	 */
	public function can_show_dashboard_banner() {
		return ! get_option( $this->wp_option_dismiss_notification_key_base . $this->active, false );
	}

	/**
	 * Add priority to the filter.
	 *
	 * @param array $products Registered products.
	 * @return array Array enhanced with Neve priority.
	 */
	public function add_priority( $products ) {
		$products['feedzy'] = 3; // TODO: change this based on product.
		return $products;
	}

	/**
	 * Check if the current product has priority.
	 *
	 * @return bool True if the current product has priority.
	 */
	public function has_priority() {
		$products = apply_filters( 'themeisle_products_deal_priority', array() );

		if ( empty( $products ) ) {
			return true;
		}

		$highest_priority = array_search( min( $products ), $products, true );
		return 'feedzy' === $highest_priority; // TODO: change this based on product.
	}
}
