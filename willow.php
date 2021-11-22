<?php

/**
 * Willow ~ WordPress Template Engine
 *
 * @package         willow
 * @author          Q Studio <social@qstudio.us>
 * @license         GPL-2.0+
 * @copyright       2020 Q Studio
 * @link			https://github.com/qstudio/willow/
 * @link            https://qstudio.us/
 *
 * @wordpress-plugin
 * Plugin Name:     Willow
 * Plugin URI:      https://qstudio.us/docs/willow/
 * Description:     Willow is a Logic-less Template Engine built for WordPress
 * Version:         2.0.6
 * Author:          Q Studio
 * Author URI:      https://qstudio.us
 * License:         GPL-2.0+
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
require_once __DIR__ . '/factory.php';

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
	$config = new willow\core\config();
	$config->hooks();
	$plugin->set( 'config', $config );

	// build factory objects ##
	$factory = new willow\factory();
	$factory->hooks();

	// build views - required in admin and front-end ##
	// @todo -- add filter to make views optional ##
	$view = new willow\view\filter();
	$view->hooks();

	// set text domain on init hook ##
	\add_action( 'init', [ $plugin, 'load_plugin_textdomain' ], 1 );
	
	// check debug settings ##
	\add_action( 'plugins_loaded', [ $plugin, 'debug' ], 11 );

	// Willow only needs to parse templates on the front-end ##
	if( ! \is_admin() ){ 

		// build output buffer ##
		$option = new willow\buffer\output();
		$option->hooks();

	}

}, 0 );
