<?php

namespace q\willow;

use q\willow\core\helper as h;
use q\willow;

// load it up ##
\q\willow\filter::__run();

class filter extends \q_willow  { 

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

		// h::log( 't:>TODO - filter extension method, like context registration...' );

		// escape ##
		require_once self::get_plugin_path( 'library/filter/escape.php' );

		// strip tags ##
		require_once self::get_plugin_path( 'library/filter/strip.php' );

		// nl2br ##
		require_once self::get_plugin_path( 'library/filter/nl2br.php' );
			
	}
	


}
