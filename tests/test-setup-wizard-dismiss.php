<?php
/**
 * Tests for the setup wizard "Go to dashboard" dismiss action.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Setup_Wizard_Dismiss extends WP_UnitTestCase {

	/**
	 * Admin class under test.
	 *
	 * @var Feedzy_Rss_Feeds_Admin
	 */
	private $admin;

	/**
	 * Request superglobals saved before each test.
	 *
	 * @var array{get: array<string, mixed>, post: array<string, mixed>, request: array<string, mixed>, request_uri: string|null}
	 */
	private $original_globals;

	/**
	 * Save the request globals and seed a fresh-install wizard state.
	 *
	 * @access public
	 */
	public function set_up(): void {
		parent::set_up();

		$this->original_globals = array(
			'get'         => $_GET,
			'post'        => $_POST,
			'request'     => $_REQUEST,
			'request_uri' => isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : null,
		);

		$this->admin = new Feedzy_Rss_Feeds_Admin( 'feedzy-rss-feeds', 'latest' );

		update_option( 'feedzy_fresh_install', '1' );
		update_option( 'feedzy_wizard_data', array( 'feed' => 'https://example.com/feed' ) );

		add_filter( 'wp_redirect', array( $this, 'throw_redirect' ) );
	}

	/**
	 * Restore the request globals and remove the seeded options.
	 *
	 * @access public
	 */
	public function tear_down(): void {
		remove_filter( 'wp_redirect', array( $this, 'throw_redirect' ) );
		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_die_handler' ) );

		$_GET     = $this->original_globals['get'];
		$_POST    = $this->original_globals['post'];
		$_REQUEST = $this->original_globals['request'];
		if ( null === $this->original_globals['request_uri'] ) {
			unset( $_SERVER['REQUEST_URI'] );
		} else {
			$_SERVER['REQUEST_URI'] = $this->original_globals['request_uri'];
		}

		delete_option( 'feedzy_fresh_install' );
		delete_option( 'feedzy_wizard_data' );

		parent::tear_down();
	}

	/**
	 * Stop the handler at its redirect, before it exits.
	 *
	 * @param string $location Redirect target.
	 * @throws Exception Always, carrying the redirect target.
	 */
	public function throw_redirect( string $location ): void {
		throw new Exception( $location );
	}

	/**
	 * Die handler that throws instead of exiting.
	 *
	 * @return callable(string): void
	 */
	public function get_die_handler(): callable {
		return array( $this, 'throw_die_exception' );
	}

	/**
	 * Throw the die message as an exception.
	 *
	 * @param string $message Die message.
	 * @throws WPDieException Always.
	 */
	public function throw_die_exception( string $message ): void {
		throw new WPDieException( $message );
	}

	/**
	 * Simulate a request to admin.php?action=feedzy_dismiss_wizard.
	 *
	 * @param array<string, string> $args Query args beyond the action.
	 */
	private function simulate_request( array $args ): void {
		$args = array_merge( array( 'action' => 'feedzy_dismiss_wizard' ), $args );

		$_GET                   = $args;
		$_REQUEST               = $args;
		$_SERVER['REQUEST_URI'] = add_query_arg( $args, '/wp-admin/admin.php' );
	}

	/**
	 * Run the admin action and return the redirect target, or the die message.
	 *
	 * @return array{redirect: string, died: string}
	 */
	private function dispatch(): array {
		$result = array(
			'redirect' => '',
			'died'     => '',
		);
		try {
			$this->admin->feedzy_dismiss_wizard();
		} catch ( WPDieException $e ) {
			$result['died'] = $e->getMessage();
		} catch ( Exception $e ) {
			$result['redirect'] = $e->getMessage();
		}
		return $result;
	}

	/**
	 * Assert the wizard state was left untouched.
	 */
	private function assert_wizard_untouched(): void {
		$this->assertSame( '1', get_option( 'feedzy_fresh_install' ) );
		$this->assertSame( array( 'feed' => 'https://example.com/feed' ), get_option( 'feedzy_wizard_data' ) );
	}

	/**
	 * A subscriber cannot dismiss the wizard, even with a valid nonce.
	 */
	public function test_subscriber_cannot_dismiss_wizard(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$this->simulate_request(
			array(
				'status'   => '0',
				'_wpnonce' => wp_create_nonce( 'feedzy_dismiss_wizard' ),
			)
		);

		$result = $this->dispatch();

		$this->assertSame( '', $result['redirect'] );
		$this->assertNotSame( '', $result['died'] );
		$this->assert_wizard_untouched();
	}

	/**
	 * An administrator cannot dismiss the wizard without a valid nonce.
	 */
	public function test_admin_cannot_dismiss_wizard_without_valid_nonce(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		foreach ( array( array( 'status' => '0' ), array( 'status' => '0', '_wpnonce' => 'invalid' ) ) as $args ) {
			$this->simulate_request( $args );

			$result = $this->dispatch();

			$this->assertSame( '', $result['redirect'] );
			$this->assertNotSame( '', $result['died'] );
			$this->assert_wizard_untouched();
		}
	}

	/**
	 * An administrator with a valid nonce dismisses the wizard and lands on the dashboard.
	 */
	public function test_admin_with_valid_nonce_dismisses_wizard_to_dashboard(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->simulate_request(
			array(
				'status'   => '0',
				'_wpnonce' => wp_create_nonce( 'feedzy_dismiss_wizard' ),
			)
		);

		$result = $this->dispatch();

		$this->assertSame( '', $result['died'] );
		$this->assertSame( '/wp-admin/admin.php?page=feedzy-support', $result['redirect'] );
		$this->assertSame( 0, get_option( 'feedzy_fresh_install' ) );
		$this->assertFalse( get_option( 'feedzy_wizard_data' ) );
	}

	/**
	 * The wizard's "Go to dashboard" link carries a valid nonce for the action.
	 */
	public function test_wizard_skip_link_carries_valid_nonce(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		ob_start();
		$this->admin->feedzy_setup_wizard_page();
		$html = ob_get_clean();

		$this->assertSame( 1, preg_match( '/href="([^"]*action=feedzy_dismiss_wizard[^"]*)"/', $html, $matches ) );
		wp_parse_str( wp_parse_url( html_entity_decode( $matches[1] ), PHP_URL_QUERY ), $query );
		$this->assertArrayHasKey( '_wpnonce', $query );
		$this->assertSame( 1, wp_verify_nonce( $query['_wpnonce'], 'feedzy_dismiss_wizard' ) );
	}

	/**
	 * An author with the block editor nonce cannot dismiss the wizard through the AJAX completion step.
	 */
	public function test_author_cannot_dismiss_wizard_via_ajax_completion(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'author' ) ) );
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', array( $this, 'get_die_handler' ) );

		$_POST    = array(
			'action'   => 'feedzy_wizard_step_process',
			'step'     => 'step_4',
			'security' => wp_create_nonce( FEEDZY_BASEFILE ),
		);
		$_REQUEST = $_POST;

		$died = '';
		ob_start();
		try {
			$this->admin->feedzy_wizard_step_process();
		} catch ( WPDieException $e ) {
			$died = $e->getMessage();
		}
		ob_end_clean();

		$this->assertSame( 'You do not have permission to do this.', $died );
		$this->assert_wizard_untouched();
	}
}
