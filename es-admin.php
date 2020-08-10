<?php
/**
 * Plugin Name:     ES Admin
 * Plugin URI:      https://github.com/alleyinteractive/es-admin
 * Description:     Insanely powerful admin search, powered by Elasticsearch
 * Author:          Matthew Boynes
 * Author URI:      https://www.alleyinteractive.com/
 * Text Domain:     es-admin
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         ES Admin
 */

/*
	Copyright 2014-2016 Matthew Boynes, Alley Interactive

	The following code is a derivative work of code from the Alley Interactive
	plugins SearchPress and ES_WP_Query, which are licensed GPLv2. This code
	therefore is also licensed under the terms of the GNU Public License,
	version 2.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

namespace ES_Admin;

if ( is_admin() ) {
	define( __NAMESPACE__ . '\PATH', __DIR__ ); // phpcs:ignore WordPressVIPMinimum.Constants.ConstantString.NotCheckingConstantName
	define( __NAMESPACE__ . '\URL', trailingslashit( plugins_url( '', __FILE__ ) ) ); // phpcs:ignore WordPressVIPMinimum.Constants.ConstantString.NotCheckingConstantName

	// Custom autoloader.
	require_once PATH . '/lib/autoload.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

	// Singleton trait.
	require_once PATH . '/lib/trait-singleton.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

	// Assorted Functions.
	require_once PATH . '/lib/functions.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

	// Load the main controller class.
	add_action( 'after_setup_theme', [ '\ES_Admin\Controller', 'instance' ] );

	// Load the settings class.
	add_action( 'after_setup_theme', [ '\ES_Admin\Settings', 'instance' ] );

	// Load the integration class.
	add_action( 'after_setup_theme', [ '\ES_Admin\Integration', 'instance' ] );
}
