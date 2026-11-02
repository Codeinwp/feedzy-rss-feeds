<?php
/**
 * Tests for saving and rendering the Proxy settings tab.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Proxy_Settings extends WP_UnitTestCase {

	/**
	 * Admin class under test.
	 *
	 * @var Feedzy_Rss_Feeds_Admin
	 */
	private $admin;

	/**
	 * Request superglobals saved before each test.
	 *
	 * @var array{post: array<string, mixed>, request: array<string, mixed>}
	 */
	private $original_globals;

	/**
	 * Save the request globals and log in as an administrator.
	 *
	 * @access public
	 */
	public function set_up(): void {
		parent::set_up();

		$this->original_globals = array(
			'post'    => $_POST,
			'request' => $_REQUEST,
		);

		$this->admin = new Feedzy_Rss_Feeds_Admin( 'feedzy-rss-feeds', 'latest' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	/**
	 * Restore the request globals and remove the saved settings.
	 *
	 * @access public
	 */
	public function tear_down(): void {
		$_POST    = $this->original_globals['post'];
		$_REQUEST = $this->original_globals['request'];

		delete_option( 'feedzy-settings' );

		parent::tear_down();
	}

	/**
	 * Submit the Proxy tab with the given port and return the rendered port field value.
	 *
	 * @param string $port Submitted port.
	 */
	private function save_proxy_port( string $port ): string {
		$_POST = array(
			'feedzy-settings-submit' => '1',
			'tab'                    => 'proxy',
			'nonce'                  => wp_create_nonce( 'proxy' ),
			'proxy-host'             => '',
			'proxy-port'             => $port,
			'proxy-user'             => '',
			'proxy-pass'             => '',
		);

		return $this->render_port_value();
	}

	/**
	 * Render the Proxy tab and return the port field's value attribute.
	 */
	private function render_port_value(): string {
		$_REQUEST = array( 'tab' => 'proxy' );

		ob_start();
		$this->admin->feedzy_settings_page();
		$html = (string) ob_get_clean();

		$this->assertSame( 1, preg_match( '/name="proxy-port"[^>]*value="([^"]*)"/', $html, $matches ) );

		return $matches[1];
	}

	/**
	 * An empty port is stored empty and the field renders empty.
	 */
	public function test_empty_port_is_saved_and_rendered_empty(): void {
		$value = $this->save_proxy_port( '' );

		$settings = get_option( 'feedzy-settings' );
		$this->assertSame( '', $settings['proxy']['port'] );
		$this->assertSame( '', $value );
	}

	/**
	 * A port stored as 0 renders as an empty field.
	 */
	public function test_stored_zero_port_renders_empty(): void {
		update_option(
			'feedzy-settings',
			array(
				'proxy' => array(
					'host' => '',
					'port' => 0,
					'user' => '',
					'pass' => '',
				),
			)
		);

		$this->assertSame( '', $this->render_port_value() );
	}

	/**
	 * Saving before the settings option exists raises no deprecation.
	 */
	public function test_first_save_raises_no_deprecation(): void {
		delete_option( 'feedzy-settings' );

		$deprecations = array();
		set_error_handler(
			static function ( int $errno, string $errstr ) use ( &$deprecations ): bool {
				$deprecations[] = $errstr;
				return true;
			},
			E_DEPRECATED
		);
		try {
			$this->save_proxy_port( '' );
		} finally {
			restore_error_handler();
		}

		$this->assertSame( array(), $deprecations );
		$this->assertIsArray( get_option( 'feedzy-settings' ) );
	}

	/**
	 * A submitted port is stored as an integer and rendered back.
	 */
	public function test_port_is_saved_as_integer(): void {
		$value = $this->save_proxy_port( '8080' );

		$settings = get_option( 'feedzy-settings' );
		$this->assertSame( 8080, $settings['proxy']['port'] );
		$this->assertSame( '8080', $value );
	}
}
