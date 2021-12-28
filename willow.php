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
	\w__object( $config = new willow\core\config )->hooks();
	$plugin->set( 'config', $config );

	// extender ##
	$plugin->set( 'extend', new willow\context\extend() );

	// kick off filter and store object ##
	$plugin->set( 'filter', new willow\core\filter() );

	// build helper object ##
	$plugin->set( 'helper', new willow\core\helper() );

	// kick off tags and store object ##
	$plugin->set( 'tags', new willow\core\tags() );

	// build views - required in admin and front-end ##
	// @todo -- add filter to make views optional ##
	\w__object( new willow\core\view )->hooks();

	// updates ##
	\w__object( new willow\core\update )->hooks();

	// set text domain on init hook ##
	\add_action( 'init', [ $plugin, 'load_plugin_textdomain' ], 1 );
	
	// check debug settings ##
	\add_action( 'plugins_loaded', [ $plugin, 'debug' ], 11 );

	// Willow only needs to parse templates on the front-end ##
	if( ! \is_admin() ){ 

		// include __MAGIC__ context loader ##
		require_once $plugin->get_plugin_path( '/library/context/load.php' ); 

		// store an instance of the parser ##
		$plugin->set( 'parser', new willow\parse\prepare() );

		// create parse object, pushing in each individual parser object ##
		$parse = new \stdClass();
		// generic ##
		$parse->flags = new willow\parse\flags();
		$parse->arguments = new willow\parse\arguments();
		$parse->markup = new willow\parse\markup();
		$parse->cleanup = new willow\parse\cleanup();
		// tag specific ##
		$parse->partials = new willow\parse\partials();
		$parse->i18n = new willow\parse\i18n();
		$parse->php_functions = new willow\parse\php_functions();
		$parse->willows = new willow\parse\willows();
		$parse->loops = new willow\parse\loops();
		$parse->comments = new willow\parse\comments();
		$parse->variables = new willow\parse\variables();
		// store parsers ##
		$plugin->set( 'parse', $parse );

		// create render object, pushing in each individual render object instance ##
		$render = new \stdClass();
		$render->callback = new willow\render\callback();
		$render->format = new willow\render\format();
		$render->args = new willow\render\args();
		$render->markup = new willow\render\markup();
		$render->log = new willow\render\log();
		$render->output = new willow\render\output();
		$render->fields = new willow\render\fields();
		$plugin->set( 'render', $render );

		// context instance ##
		$plugin->set( 'context', new willow\context() );

		// get instances ##
		$plugin->set( 'media', new willow\get\media() );
		$plugin->set( 'group', new willow\get\group() );
		$plugin->set( 'meta', new willow\get\meta() );
		$plugin->set( 'navigation', new willow\get\navigation() );
		$plugin->set( 'plugin', new willow\get\plugin() );
		$plugin->set( 'query', new willow\get\query() );
		$plugin->set( 'post', new willow\get\post() );
		$plugin->set( 'taxonomy', new willow\get\taxonomy() );

		// create type object, pushing in each individual render object ##
		$type = new \stdClass();
		$type->get = new willow\type\get();
		$type->post = new willow\type\post();
		$type->author = new willow\type\author();
		$type->taxonomy = new willow\type\taxonomy();
		$type->media = new willow\type\media();
		$type->meta = new willow\type\meta();
		$plugin->set( 'type', $type );

		// set buffer ##
		$buffer = new \stdClass();
		$buffer->map = new willow\buffer\map();
		\w__object( $buffer->output = new willow\buffer\output )->hooks();
		$plugin->set( 'buffer', $buffer );

	}

}, 0 );
