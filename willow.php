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

// If this file is called directly, Bulk!
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// required bits to get set-up ##
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/plugin.php';
require_once __DIR__ . '/library/core/function.php';

// plugin activation hook to store current application and plugin state ##
\register_activation_hook( __FILE__, [ '\\Q\\willow\\plugin', 'activation_hook' ] );

// plugin deactivation hook - clear stored data ##
\register_deactivation_hook( __FILE__, [ '\\Q\\willow\\plugin', 'deactivation_hook' ] );

// get plugin instance ##
$plugin = plugin::get_instance();

// validate instance ##
if( ! ( $plugin instanceof \Q\willow\plugin ) ) {

	error_log( 'Error in Willow plugin instance' );

	// nothing else to do here ##
	return;

}

// fire hooks - build log, helper and config objects and translations ## 
\add_action( 'init', [ $plugin, 'hooks' ], 0 );

// build output buffer ##
\add_action( 'init', [ new \Q\willow\buffer\output( $plugin ), 'hooks' ], 1 );
