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
 * Version:         2.1.0
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

// import namespace ##
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
// require_once __DIR__ . '/factory.php';

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

	// extender ##
	$plugin->set( 'extend', new willow\context\extend( $plugin ) );

	// kick off filter and store object ##
	$plugin->set( 'filter', new willow\core\filter( $plugin ) );

	// build helper object ##
	$plugin->set( 'helper', new willow\core\helper( $plugin ) );

	// kick off tags and store object ##
	$plugin->set( 'tags', new willow\core\tags( $plugin ) );

	// build views - required in admin and front-end ##
	// @todo -- add filter to make views optional ##
	$view = new willow\view\filter( $plugin );
	$view->hooks();

	// set text domain on init hook ##
	\add_action( 'init', [ $plugin, 'load_plugin_textdomain' ], 1 );
	
	// check debug settings ##
	\add_action( 'plugins_loaded', [ $plugin, 'debug' ], 11 );

	// Willow only needs to parse templates on the front-end ##
	if( ! \is_admin() ){ 

		// include __MAGIC__ context loader ##
		require_once $plugin->get_plugin_path( '/library/context/_load.php' ); 

		// build the parser ##
		$parser = new willow\parse\prepare( $plugin );

		// store an instance of the parser ##
		$plugin->set( 'parser', $parser );

		// create parse object, pushing in each individual parser object ##
		$parse = new \stdClass();

		// generic ##
		$parse->flags = new willow\parse\flags( $plugin );
		$parse->arguments = new willow\parse\arguments( $plugin );
		$parse->markup = new willow\parse\markup( $plugin );
		$parse->cleanup = new willow\parse\cleanup( $plugin );

		// tag specific ##
		$parse->partials = new willow\parse\partials( $plugin );
		$parse->i18n = new willow\parse\i18n( $plugin );
		$parse->php_functions = new willow\parse\php_functions( $plugin );
		$parse->willows = new willow\parse\willows( $plugin );
		$parse->loops = new willow\parse\loops( $plugin );
		$parse->comments = new willow\parse\comments( $plugin );
		$parse->variables = new willow\parse\variables( $plugin );

		// store parsers ##
		$plugin->set( 'parse', $parse );

		// create render object, pushing in each individual render object instance ##
		$render = new \stdClass();

		$render->callback = new willow\render\callback( $plugin );
		$render->format = new willow\render\format( $plugin );
		$render->args = new willow\render\args( $plugin );
		$render->markup = new willow\render\markup( $plugin );
		$render->log = new willow\render\log( $plugin );
		$render->output = new willow\render\output( $plugin );
		$render->fields = new willow\render\fields( $plugin );

		// store render objects ##
		$plugin->set( 'render', $render );
		$plugin->set( 'render_fields', new willow\render\fields( $plugin ) );

		// group instances ##
		$plugin->set( 'group', new willow\get\group( $plugin ) );

		// context instance ##
		$plugin->set( 'context', new willow\context( $plugin ) );

		// get instances ##
		$plugin->set( 'media', new willow\get\media( $plugin ) );
		$plugin->set( 'meta', new willow\get\meta( $plugin ) );
		$plugin->set( 'navigation', new willow\get\navigation( $plugin ) );
		$plugin->set( 'plugin', new willow\get\plugin( $plugin ) );
		$plugin->set( 'query', new willow\get\query( $plugin ) );
		$plugin->set( 'post', new willow\get\post( $plugin ) );
		$plugin->set( 'taxonomy', new willow\get\taxonomy( $plugin ) );

		// create type object, pushing in each individual render object ##
		$type = new \stdClass();
		
		// set types ##
		$type->get = new willow\type\get( $plugin );
		$type->post = new willow\type\post( $plugin );
		$type->author = new willow\type\author( $plugin );
		$type->taxonomy = new willow\type\taxonomy( $plugin );
		$type->media = new willow\type\media( $plugin );
		$type->meta = new willow\type\meta( $plugin );
		$plugin->set( 'type', $type );

		// set output ##
		$plugin->set( 'buffer_map', new willow\buffer\map( $plugin ) );

		// build output buffer ##
		$option = new willow\buffer\output( $plugin );
		$option->hooks();

	}

}, 0 );
