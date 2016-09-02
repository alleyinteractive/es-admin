<?php
/**
 * Autoloader.
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * Autoload classes.
 *
 * @param  string $cls Class name.
 */
function autoload( $cls ) {
	$cls = ltrim( $cls, '\\' );
	if ( strpos( $cls, 'ES_Admin\\' ) !== 0 ) {
		return;
	}

	$cls = strtolower( str_replace( [ 'ES_Admin\\', '_' ], [ '', '-' ], $cls ) );
	$dirs = explode( '\\', $cls );
	$cls = array_pop( $dirs );

	require_once( PATH . rtrim( '/lib/' . implode( '/', $dirs ), '/' ) . '/class-' . $cls . '.php' );
}
spl_autoload_register( '\ES_Admin\autoload' );
