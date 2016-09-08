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
		add_action( 'admin_print_styles-toplevel_page_es-admin-search', [ $this, 'assets' ] );
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
			include( PATH . '/templates/main-facets.php' );
			include( PATH . '/templates/main-results.php' );
		}
		include( PATH . '/templates/footer.php' );
	}

	/**
	 * Load custom CSS and JS files for the page.
	 */
	public function assets() {
		wp_enqueue_style( 'es-admin-css', URL . '/static/es-admin.css', [ 'buttons' ], '0.1' );
		wp_enqueue_script( 'es-admin-js', URL . '/static/es-admin.js', [ 'jquery' ], '0.1' );
	}
}
