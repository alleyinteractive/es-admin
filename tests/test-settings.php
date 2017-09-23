<?php
/**
 * Class Test_Settings
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * Settings test case.
 */
class Test_Settings extends \WP_UnitTestCase {
	protected $settings;

	public function setUp() {
		parent::setUp();
		$this->settings = Settings::instance();

		// Convert redirects to catchable exceptions.
		add_filter( 'wp_redirect', 'wp_die' );
	}

	public function tearDown() {
		if ( isset( $GLOBALS['_parent_pages']['es-admin-settings'] ) ) {
			unset( $GLOBALS['_parent_pages']['es-admin-settings'] );
		}
		parent::tearDown();
	}

	/**
	 * Prevent the admin methods from redirecting and exiting.
	 *
	 * This leverages `wp_die()`, which is overridden in phpunit to be an
	 * exception with the message of whatever is passed to wp_die().
	 *
	 * @param  string $location URL to which to redirect.
	 */
	public function prevent_redirect( $location ) {
		wp_die( $location );
	}

	public function test_get_settings() {
		$value = [
			'enable_integration' => '1',
		];
		update_option( 'es_admin_settings', $value );
		$this->assertSame( $value, $this->settings->get_settings() );
		$this->assertSame( '1', $this->settings->get_settings( 'enable_integration' ) );
	}

	public function test_update_settings() {
		$value = [
			'enable_integration' => '0',
		];
		$this->assertEmpty( get_option( 'es_admin_settings' ) );
		$this->settings->update_settings( $value );
		$this->assertSame( $value, get_option( 'es_admin_settings' ) );

		$value['addition'] = 456;
		$this->settings->update_settings( [ 'addition' => 456 ] );
		$this->assertSame( $value, get_option( 'es_admin_settings' ) );
	}

	public function test_process_form() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$_POST = [
			'es_admin_nonce' => wp_create_nonce( 'es_admin_settings' ),
			'enable_integration' => '1',
		];

		$this->assertEmpty( get_option( 'es_admin_settings' ) );

		try {
			$this->settings->process_form();
			$this->fail( 'Failed to process form' );
		} catch ( \WPDieException $e ) {
			// Verify the redirect url.
			$this->assertSame( admin_url( 'options-general.php?saved=1&page=es-admin-settings' ), $e->getMessage() );
		}

		$this->assertTrue( $this->settings->get_settings( 'enable_integration' ) );
	}

	/**
	 * @expectedException WPDieException
	 */
	public function test_process_form_no_access() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$_POST = [
			'es_admin_nonce' => wp_create_nonce( 'es_admin_settings' ),
			'enable_integration' => '1',
		];
		$this->settings->process_form();
	}

	/**
	 * @expectedException WPDieException
	 */
	public function test_process_form_missing_nonce() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$_POST = [
			'enable_integration' => '1',
		];
		$this->settings->process_form();
	}

	/**
	 * @expectedException WPDieException
	 */
	public function test_process_form_invalid_nonce() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$_POST = [
			'es_admin_nonce' => wp_create_nonce( 'wrong-nonce-key' ),
			'enable_integration' => '1',
		];
		$this->settings->process_form();
	}

	public function test_process_form_through_admin_post() {
		$this->settings->setup( true );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$_POST = [
			'es_admin_nonce' => wp_create_nonce( 'es_admin_settings' ),
			'enable_integration' => '1',
		];

		$this->assertEmpty( get_option( 'es_admin_settings' ) );

		try {
			do_action( 'admin_post_es_admin_settings' );
			$this->fail( 'Failed to process form' );
		} catch ( \WPDieException $e ) {
			// Verify the redirect url.
			$this->assertSame( admin_url( 'options-general.php?saved=1&page=es-admin-settings' ), $e->getMessage() );
		}

		$this->assertTrue( $this->settings->get_settings( 'enable_integration' ) );
	}

	public function test_settings_html() {
		$html = get_echo( [ $this->settings, 'settings_page' ] );
		$this->assertRegExp( '#<input name="enable_integration"([^>](?!checked))+>#i', $html );

		$this->settings->update_settings( [ 'enable_integration' => true ] );

		$html = get_echo( [ $this->settings, 'settings_page' ] );
		$this->assertRegExp( '#<input name="enable_integration"[^>]+\schecked=(["\'])checked\1\s*/>#i', $html );
	}

	public function test_add_menu_page() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertTrue( empty( $GLOBALS['_parent_pages']['es-admin-settings'] ) );
		$this->settings->add_settings_page();
		$this->assertFalse( empty( $GLOBALS['_parent_pages']['es-admin-settings'] ) );
	}

	public function test_add_menu_page_no_access() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertTrue( empty( $GLOBALS['_parent_pages']['es-admin-settings'] ) );
		$this->settings->add_settings_page();
		$this->assertTrue( empty( $GLOBALS['_parent_pages']['es-admin-settings'] ) );
	}
}
