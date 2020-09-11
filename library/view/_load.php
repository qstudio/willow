<?php

namespace willow;

use willow\core;
use willow\core\helper as h;

// load it up ##
\willow\view::__run();

class view extends \willow {

	public static function __run(){

		self::load();

	}

	/**
	* Load Libraries
	*
	* @since        2.0.0
	*/
	public static function load()
	{

		// is methods ##
		require_once self::get_plugin_path( 'library/view/is.php' );

		// filters ##
		// 'view' => self::get_plugin_path( 'library/view/filter.php' ),

	}

}

