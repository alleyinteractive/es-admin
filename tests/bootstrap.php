<?php
/**
 * PHPUnit bootstrap file
 *
 * @package ES Admin
 */

define( 'ES_ADMIN_TEST_ENV', true );

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

$_es_version = getenv( 'ES_VERSION' );
if ( ! defined( 'ES_VERSION' ) && $_es_version ) {
	define( 'ES_VERSION', $_es_version );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require_once( dirname( __DIR__ ) . '/es-admin.php' );

	// Load the ES integration. This can be disabled if needed by filtering
	// it to return false if you want to test with a different integration like
	// SearchPress.
	if ( apply_filters( 'es_admin_phpunit_use_default_integration', true ) ) {
		require_once( __DIR__ . '/es.php' );
	}

	if ( ! \ES_Admin\verify_es_is_running() ) {
		echo "\n\nFatal: bootstrap check failed!\n";
		exit( 1 );
	}

	add_filter( 'es_admin_adapter', function( $adapter ) {
		return 'ES_Admin\Adapters\Generic';
	} );
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/**
 * Load the phpunit adapter by default. This can be overridden as needed by
 * hooking into the filter at a later priority.
 *
 * @param  string $adapter_class Class name of the adapter, as a string.
 * @return string
 */
function _es_admin_phpunit_adapter( $adapter_class ) {
	return '\ES_Admin\Adapters\Generic';
}
tests_add_filter( 'es_admin_adapter', '_es_admin_phpunit_adapter' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// Load Test Case class.
require_once __DIR__ . '/class-es-admin-test-case.php';

// Load doubles.
require_once __DIR__ . '/doubles/class-search-spy.php';
require_once __DIR__ . '/doubles/class-mock-facet.php';
