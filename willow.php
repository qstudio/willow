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
namespace Q\willow;

// import ##
use Q\willow;

// If this file is called directly, Bulk!
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// required bits to get set-up ##
require_once __DIR__ . '/library/api/function.php';
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/plugin.php';

// plugin activation hook to store current application and plugin state ##
\register_activation_hook( __FILE__, [ '\\Q\\willow\\plugin', 'activation_hook' ] );

// plugin deactivation hook - clear stored data ##
\register_deactivation_hook( __FILE__, [ '\\Q\\willow\\plugin', 'deactivation_hook' ] );

// get plugin instance ##
$plugin = plugin::get_instance();

// validate instance ##
if( ! ( $plugin instanceof willow\plugin ) ) {

	error_log( 'Error in Willow plugin instance' );

	// nothing else to do here ##
	return;

}

// fire hooks - build log, helper and config objects and translations ## 
// \add_action( 'init', [ $plugin, 'hooks' ], 0 );
\add_action( 'init', function() use( $plugin ){

	// build helper object ##
	$plugin->set( 'helper', new willow\core\helper( $plugin ) );

	// kick off config and store object ##
	$config = new willow\core\config( $plugin );
	$config->hooks();
	$plugin->set( 'config', $config );

	// kick off filter and store object ##
	$plugin->set( 'filter', new willow\core\filter( $plugin ) );
	// $this->filter->hooks();

	// kick off tags and store object ##
	$plugin->set( 'tags', new willow\core\tags( $plugin ) );
	// $this->tags->hooks();

	// kick off config and store object ##
	$extend = new willow\context\extend( $plugin );
	$extend->hooks(); // adds action hook 'willow/context/extend/register'
	$plugin->set( 'extend', $extend );

	// set text domain on init hook ##
	\add_action( 'init', [ $plugin, 'load_plugin_textdomain' ], 1 );
	
	// check debug settings ##
	\add_action( 'plugins_loaded', [ $plugin, 'debug' ], 11 );

});

// build output buffer ##
\add_action( 'init', [ new willow\buffer\output( $plugin ), 'hooks' ], 1 );
