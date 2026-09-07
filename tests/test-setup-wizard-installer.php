<?php
/**
 * Tests for the setup wizard plugin installer (step 3).
 *
 * Covers the successful installation path and key failure branches,
 * making sure a failed install is reported back as JSON instead of
 * blowing up with a fatal error.
 *
 * @package    feedzy-rss-feeds
 */
class Test_Setup_Wizard_Installer extends WP_UnitTestCase {

	/**
	 * Slug of the plugin faked by these tests.
	 *
	 * @var string
	 */
	const SLUG = 'optimole-wp';

	/**
	 * Path of the generated package.
	 *
	 * @var string
	 */
	private $package = '';

	/**
	 * Set up the admin user and the fake plugin package.
	 *
	 * @access public
	 */
	public function set_up() {
		parent::set_up();

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'pre_http_request', array( $this, 'block_http_request' ) );
		add_filter( 'wp_die_ajax_handler', array( $this, 'get_die_handler' ), 1, 1 );

		$this->package = $this->build_package();
	}

	/**
	 * Remove the installed plugin and the generated package.
	 *
	 * @access public
	 */
	public function tear_down() {
		deactivate_plugins( self::SLUG . '/' . self::SLUG . '.php', true );

		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'pre_http_request', array( $this, 'block_http_request' ) );
		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_die_handler' ), 1 );

		$this->delete_directory( WP_PLUGIN_DIR . '/' . self::SLUG );

		if ( $this->package && file_exists( $this->package ) ) {
			unlink( $this->package );
		}

		delete_option( 'feedzy_wizard_data' );

		parent::tear_down();
	}

	/**
	 * Die handler that throws instead of exiting, so wp_send_json() can be inspected.
	 *
	 * @access public
	 * @return callable
	 */
	public function get_die_handler() {
		return array( $this, 'throw_die_exception' );
	}

	/**
	 * Throw on wp_die().
	 *
	 * @access public
	 * @param string $message Die message.
	 * @throws WPDieException Always.
	 */
	public function throw_die_exception( $message ) {
		throw new WPDieException( $message );
	}

	/**
	 * A successful install returns a success status and puts the plugin on disk.
	 *
	 * @access public
	 */
	public function test_install_succeeds() {
		$response = $this->run_install_step();

		$this->assertEquals( 1, $response['status'] );
		$this->assertArrayNotHasKey( 'message', $response );
		$this->assertFileExists( WP_PLUGIN_DIR . '/' . self::SLUG . '/' . self::SLUG . '.php' );
		$this->assertTrue( is_plugin_active( self::SLUG . '/' . self::SLUG . '.php' ) );

		$wizard_data = get_option( 'feedzy_wizard_data', array() );
		$this->assertTrue( $wizard_data['enable_perfomance'] );
	}

	/**
	 * A failed download is reported as an error instead of raising a fatal.
	 *
	 * @access public
	 */
	public function test_failed_install_returns_error() {
		add_filter( 'upgrader_pre_download', array( $this, 'fail_download' ) );
		$response = $this->run_install_step();
		remove_filter( 'upgrader_pre_download', array( $this, 'fail_download' ) );

		$this->assertEquals( 0, $response['status'] );
		$this->assertEquals( 'Package could not be downloaded.', $response['message'] );
		$this->assertFalse( file_exists( WP_PLUGIN_DIR . '/' . self::SLUG . '/' . self::SLUG . '.php' ) );
	}

	/**
	 * An already installed plugin is not treated as a failure.
	 *
	 * @access public
	 */
	public function test_already_installed_plugin_is_not_an_error() {
		$this->run_install_step();

		// Second run hits the folder_exists error from the upgrader.
		$response = $this->run_install_step();

		$this->assertEquals( 1, $response['status'] );
	}

	/**
	 * An existing but incomplete plugin folder is not reported as a successful install.
	 *
	 * @access public
	 */
	public function test_incomplete_plugin_folder_is_not_a_success() {
		mkdir( WP_PLUGIN_DIR . '/' . self::SLUG );
		file_put_contents( WP_PLUGIN_DIR . '/' . self::SLUG . '/leftover.txt', 'leftover' );

		$response = $this->run_install_step();

		$this->assertEquals( 0, $response['status'] );
		$this->assertNotEmpty( $response['message'] );
		$this->assertFalse( is_plugin_active( self::SLUG . '/' . self::SLUG . '.php' ) );
	}

	/**
	 * An error next to a folder_exists error is still reported.
	 *
	 * @access public
	 */
	public function test_error_after_folder_exists_is_reported() {
		add_action( 'upgrader_process_complete', array( $this, 'add_skin_folder_exists_and_other_error' ) );
		$response = $this->run_install_step();
		remove_action( 'upgrader_process_complete', array( $this, 'add_skin_folder_exists_and_other_error' ) );

		$this->assertEquals( 0, $response['status'] );
		$this->assertEquals( 'Plugin could not be verified.', $response['message'] );
	}

	/**
	 * Errors collected by the skin are reported without calling missing methods on it.
	 *
	 * @access public
	 */
	public function test_skin_errors_are_reported() {
		add_action( 'upgrader_process_complete', array( $this, 'add_skin_error' ) );
		$response = $this->run_install_step();
		remove_action( 'upgrader_process_complete', array( $this, 'add_skin_error' ) );

		$this->assertEquals( 0, $response['status'] );
		$this->assertEquals( 'Plugin could not be verified.', $response['message'] );
	}

	/**
	 * A folder_exists error on the skin is ignored and the wizard moves on.
	 *
	 * @access public
	 */
	public function test_skin_folder_exists_error_is_ignored() {
		add_action( 'upgrader_process_complete', array( $this, 'add_skin_folder_exists_error' ) );
		$response = $this->run_install_step();
		remove_action( 'upgrader_process_complete', array( $this, 'add_skin_folder_exists_error' ) );

		$this->assertEquals( 1, $response['status'] );
	}

	/**
	 * An unreachable filesystem is reported as an error instead of a success.
	 *
	 * @access public
	 */
	public function test_unavailable_filesystem_returns_error() {
		add_filter( 'filesystem_method', array( $this, 'force_ftp_filesystem' ) );
		$response = $this->run_install_step();
		remove_filter( 'filesystem_method', array( $this, 'force_ftp_filesystem' ) );

		$this->assertEquals( 0, $response['status'] );
		$this->assertNotEmpty( $response['message'] );
		$this->assertFalse( file_exists( WP_PLUGIN_DIR . '/' . self::SLUG . '/' . self::SLUG . '.php' ) );
	}

	/**
	 * A missing slug is rejected before anything is installed.
	 *
	 * @access public
	 */
	public function test_missing_slug_is_rejected() {
		$response = $this->run_install_step( '' );

		$this->assertEquals( 0, $response['status'] );
		$this->assertEquals( 'No plugin specified.', $response['message'] );
	}

	/**
	 * A user without install capabilities cannot install anything.
	 *
	 * @access public
	 */
	public function test_user_without_capability_is_rejected() {
		$editor = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $editor );

		$response = $this->run_install_step();

		$this->assertEquals( 0, $response['status'] );
		$this->assertFalse( file_exists( WP_PLUGIN_DIR . '/' . self::SLUG . '/' . self::SLUG . '.php' ) );
	}

	/**
	 * The upgrader skin exposes its errors only through the WP_Error it returns.
	 *
	 * @access public
	 */
	public function test_upgrader_skin_error_api() {
		$ob_level = ob_get_level();
		$skin     = new WP_Ajax_Upgrader_Skin();

		$this->assertInstanceOf( 'WP_Error', $skin->get_errors() );
		$this->assertFalse( method_exists( $skin, 'get_error_code' ) );
		$this->assertFalse( method_exists( $skin, 'get_error_message' ) );

		while ( ob_get_level() > $ob_level ) {
			ob_end_clean();
		}
	}

	/**
	 * Keep the upgrade hooks from reaching wordpress.org.
	 *
	 * @access public
	 * @return array
	 */
	public function block_http_request() {
		return array(
			'headers'  => array(),
			'body'     => '{"plugins":{},"themes":{},"translations":[],"no_update":{}}',
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	/**
	 * Force the download to fail.
	 *
	 * @access public
	 * @return WP_Error
	 */
	public function fail_download() {
		return new WP_Error( 'download_failed', 'Package could not be downloaded.' );
	}

	/**
	 * Record an error on the skin of a finished upgrade.
	 *
	 * @access public
	 * @param WP_Upgrader $upgrader The upgrader that just ran.
	 */
	public function add_skin_error( $upgrader ) {
		$upgrader->skin->error( new WP_Error( 'verification_failed', 'Plugin could not be verified.' ) );
	}

	/**
	 * Record a folder_exists error on the skin of a finished upgrade.
	 *
	 * @access public
	 * @param WP_Upgrader $upgrader The upgrader that just ran.
	 */
	public function add_skin_folder_exists_error( $upgrader ) {
		$upgrader->skin->error( new WP_Error( 'folder_exists', 'Destination folder already exists.' ) );
	}

	/**
	 * Record a folder_exists error followed by another error.
	 *
	 * @access public
	 * @param WP_Upgrader $upgrader The upgrader that just ran.
	 */
	public function add_skin_folder_exists_and_other_error( $upgrader ) {
		$upgrader->skin->error( new WP_Error( 'folder_exists', 'Destination folder already exists.' ) );
		$upgrader->skin->error( new WP_Error( 'verification_failed', 'Plugin could not be verified.' ) );
	}

	/**
	 * Ask for a filesystem method that has no credentials available.
	 *
	 * @access public
	 * @return string
	 */
	public function force_ftp_filesystem() {
		return 'ftpext';
	}

	/**
	 * Serve the locally generated package instead of querying wordpress.org.
	 *
	 * @access public
	 * @return object
	 */
	public function fake_plugins_api() {
		return (object) array(
			'name'          => 'Optimole',
			'slug'          => self::SLUG,
			'version'       => '1.0.0',
			'download_link' => $this->package,
		);
	}

	/**
	 * Run step 3 of the wizard and return the decoded JSON response.
	 *
	 * @access private
	 * @param string $slug Plugin slug to install.
	 * @return array
	 */
	private function run_install_step( $slug = self::SLUG ) {
		add_filter( 'plugins_api', array( $this, 'fake_plugins_api' ) );

		$_POST     = array(
			'step'     => 'step_3',
			'slug'     => $slug,
			'security' => wp_create_nonce( FEEDZY_BASEFILE ),
		);
		$_REQUEST  = $_POST;
		$ob_level  = ob_get_level();
		$admin     = Feedzy_Rss_Feeds::instance()->get_admin();

		ob_start();
		try {
			$admin->feedzy_wizard_step_process();
		} catch ( WPDieException $e ) {
			// wp_send_json() ends the request.
		}
		$output = ob_get_clean();

		// The upgrader skin leaves its own buffers behind on failure.
		while ( ob_get_level() > $ob_level ) {
			$output .= ob_get_clean();
		}

		remove_filter( 'plugins_api', array( $this, 'fake_plugins_api' ) );

		$response = json_decode( $output, true );

		$this->assertIsArray( $response, 'Expected a JSON response, got: ' . $output );

		return $response;
	}

	/**
	 * Build a zip package holding a minimal plugin.
	 *
	 * @access private
	 * @return string Path to the package.
	 */
	private function build_package() {
		$path = get_temp_dir() . self::SLUG . '.zip';
		$zip  = new ZipArchive();
		$zip->open( $path, ZipArchive::CREATE | ZipArchive::OVERWRITE );
		$zip->addEmptyDir( self::SLUG );
		$zip->addFromString(
			self::SLUG . '/' . self::SLUG . '.php',
			"<?php\n/**\n * Plugin Name: Optimole\n * Version: 1.0.0\n */\n"
		);
		$zip->close();

		return $path;
	}

	/**
	 * Remove a directory and its content.
	 *
	 * @access private
	 * @param string $path Directory to remove.
	 */
	private function delete_directory( $path ) {
		if ( ! is_dir( $path ) ) {
			return;
		}

		foreach ( glob( $path . '/*' ) as $item ) {
			if ( is_dir( $item ) ) {
				$this->delete_directory( $item );
			} else {
				unlink( $item );
			}
		}

		rmdir( $path );
	}
}
