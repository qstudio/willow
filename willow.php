<?php

/**
 * Willow ~ WordPress Template Engine
 *
 * @package         willow
 * @author          Q Studio <social@qstudio.us>
 * @license         GPL-2.0+
 * @link            http://qstudio.us/
 * @copyright       2020 Q Studio
 *
 * @wordpress-plugin
 * Plugin Name:     Willow
 * Plugin URI:      https://www.qstudio.us
 * Description:     Willow is a Logic-less Template Engine built for WordPress
 * Version:         2.0.0
 * Author:          Q Studio
 * Author URI:      https://www.qstudio.us
 * License:         GPL
 * Requires PHP:    7.0 
 * Copyright:       Q Studio
 * Class:           willow
 * Text Domain:     willow
 * Domain Path:     /languages
 * GitHub Plugin URI: qstudio/willow
*/

// namespace plugin ##
namespace willow;

// import ##
use willow;

// If this file is called directly, Bulk!
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// plugin activation hook to store current application and plugin state ##
\register_activation_hook( __FILE__, [ '\\willow\\plugin', 'activation_hook' ] );

// plugin deactivation hook - clear stored data ##
\register_deactivation_hook( __FILE__, [ '\\willow\\plugin', 'deactivation_hook' ] );

// required bits to get set-up ##
require_once __DIR__ . '/library/api/function.php';
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/plugin.php';

// get plugin instance ##
$plugin = plugin::get_instance();

// validate instance ##
if( ! ( $plugin instanceof willow\plugin ) ) {

	error_log( 'Error in Willow plugin instance' );

	// nothing else to do here ##
	return;

}

// fire hooks - build log, helper and config objects and translations ## 
\add_action( 'plugins_loaded', function() use( $plugin ){

	// kick off config and store object ##
	$config = new willow\core\config( $plugin );
	$config->hooks();
	$plugin->set( 'config', $config );

	// build factory objects ##
	$plugin->factory( $plugin );

	// set text domain on init hook ##
	\add_action( 'init', [ $plugin, 'load_plugin_textdomain' ], 1 );
	
	// check debug settings ##
	\add_action( 'plugins_loaded', [ $plugin, 'debug' ], 11 );

}, 0 );

// Willow only needs to parse templates on the front-end ##
if( ! \is_admin() ){ 

	// build output buffer ##
	\add_action( 'plugins_loaded', [ new willow\buffer\output( $plugin ), 'hooks' ], 1 );

}
