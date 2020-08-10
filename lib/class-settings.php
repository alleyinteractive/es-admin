<?php
/**
 * Admin search settings.
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * Settings screen for various search options.
 */
class Settings {
	use Singleton;

	/**
	 * The option_name for the settings options.
	 *
	 * @var string
	 */
	protected $option_name = 'es_admin_settings';

	/**
	 * The page slug for the settings screen.
	 *
	 * @var string
	 */
	protected $page_slug = 'es-admin-settings';

	/**
	 * Build the singleton.
	 */
	public function setup() {
		// All settings currently require ES_WP_Query, so we need it to be
		// present. In the future, this will likely change.
		if ( class_exists( 'ES_WP_Query' ) ) {
			add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
			add_action( 'admin_post_es_admin_settings', [ $this, 'process_form' ] );
		}
	}

	/**
	 * Register the menu link.
	 */
	public function add_settings_page() {
		add_options_page( __( 'Admin Search Settings', 'es-admin' ), __( 'Admin Search', 'es-admin' ), 'manage_options', $this->page_slug, [ $this, 'settings_page' ] );
	}

	/**
	 * Output the es-admin settings screen.
	 */
	public function settings_page() {
		include PATH . '/templates/settings.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
	}

	/**
	 * Get the stored settings, or optionally an individual setting.
	 *
	 * @param  string $key Optional. An individual setting to retrieve.
	 * @return mixed
	 */
	public function get_settings( $key = null ) {
		$settings = get_option( $this->option_name, [] );
		if ( $key ) {
			return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
		}
		return $settings;
	}

	/**
	 * Update the stored settings.
	 *
	 * @param  array $new_settings Settings to update.
	 * @return bool True if option value has changed, false if not or on failure.
	 */
	public function update_settings( $new_settings ) {
		$settings = $this->get_settings();
		return update_option( $this->option_name, array_merge( $settings, $new_settings ) );
	}

	/**
	 * Save settings.
	 */
	public function process_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'es-admin' ) );
		}

		if ( ! isset( $_POST['es_admin_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['es_admin_nonce'] ), 'es_admin_settings' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_die( esc_html__( 'You are not authorized to perform that action', 'es-admin' ) );
		}

		// Sanitize settings.
		$settings = [
			'enable_integration' => ! empty( $_POST['enable_integration'] ),
		];

		$this->update_settings( $settings );
		return $this->redirect( admin_url( 'options-general.php?saved=1&page=' . $this->page_slug ) );
	}

	/**
	 * Redirect and exit.
	 *
	 * @codeCoverageIgnore
	 * @param  string $location Url to which to redirect.
	 */
	protected function redirect( $location ) {
		wp_safe_redirect( $location );
		exit;
	}
}
