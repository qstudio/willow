<?php

namespace Q\willow;

use Q\willow;

// load it up ##
// \willow\render::__run();

class render { 

	/**
	 * Fire things up
	*/
	public static function __run(){

		// load libraries ##
		self::load();

	}
	

    /**
    * Load Libraries
    *
    * @since        4.1.0
    */
    public static function load(){

		// methods ##
		require_once self::get_plugin_path( 'library/render/method.php' );

		// validate and assign args ##
		require_once self::get_plugin_path( 'library/render/args.php' );

		// check callbacks on defined fields ## 
		require_once self::get_plugin_path( 'library/render/callback.php' );

		// prepare and manipulate field data ##
		require_once self::get_plugin_path( 'library/render/fields.php' ); 

		// check format of each fields data and modify as required to markup ##
		require_once self::get_plugin_path( 'library/render/format.php' );

		// defined field types to generate field data ##
		require_once self::get_plugin_path( 'library/render/type/_load.php' );

		// prepare defined markup, search for and replace variables 
		require_once self::get_plugin_path( 'library/render/markup.php' );

		// output string ##
		require_once self::get_plugin_path( 'library/render/output.php' );

		// template renderer ##
		require_once self::get_plugin_path( 'library/render/template.php' );

		// log activity ##
		require_once self::get_plugin_path( 'library/render/log.php' );

	}
	


}
