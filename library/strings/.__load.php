<?php

namespace willow;

use willow\core;
use willow\core\helper as h;

// load it up ##
\willow\strings::__run();

class strings extends \willow {

	
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
    public static function load()
    {

		// methods ##
		require_once self::get_plugin_path( 'library/strings/method.php' );

	}
	

}
