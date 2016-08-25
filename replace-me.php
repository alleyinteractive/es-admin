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

namespace ES_Admin;

define( __NAMESPACE__ . '\PATH', __DIR__ );
define( __NAMESPACE__ . '\URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

// Custom autoloader
require_once( PATH . '/lib/autoload.php' );

// Singleton trait
require_once( PATH . '/lib/trait-singleton.php' );
