<?php

namespace q\willow;

use q\core;
use q\core\helper as h;
use q\willow\core\helper as wh;
use q\willow;
use q\willow\render;

\q\willow\buffer::__run();

class buffer extends \willow {

	/**
	 * Check for view template and start OB, if correct
	*/
	public static function __run(){

		// load libraries ##
		self::load();

		// start here ##
		self::$filter = [];

	}

	/**
    * Load Libraries
    *
    * @since        4.1.0
    */
    public static function load()
    {

		// prepare map ##
		require_once self::get_plugin_path( 'library/buffer/output.php' );

		// prepare map ##
		require_once self::get_plugin_path( 'library/buffer/map.php' );

	}


}
