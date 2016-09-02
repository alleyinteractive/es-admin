<?php
/**
 * Plugin controller.
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * Base controller class. Sets up all the hooks etc.
 */
class Controller {
	use Singleton;

	/**
	 * Build the singleton.
	 */
	public function setup() {
		add_action( 'admin_menu', [ $this, 'add_search_page' ] );
	}

	/**
	 * Register the menu link.
	 */
	public function add_search_page() {
		add_menu_page( __( 'Search', 'es-admin' ), __( 'Search', 'es-admin' ), 'edit_posts', 'es-admin-search', [ $this, 'search_page' ], 'dashicons-search', '1.1' );
	}

	/**
	 * Output the search page.
	 */
	public function search_page() {
		$results = new Results_List_Table();
		$results->prepare_items();

		include( PATH . '/templates/header.php' );
		include( PATH . '/templates/search-bar.php' );
		if ( ! empty( $_GET['s'] ) ) {
			include( PATH . '/templates/main-results.php' );
		}
		include( PATH . '/templates/footer.php' );
	}
}
