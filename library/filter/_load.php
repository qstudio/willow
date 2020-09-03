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

		// filter methods ##
		require_once self::get_plugin_path( 'library/filter/method.php' );

		// apply filters ##
		require_once self::get_plugin_path( 'library/filter/apply.php' );

		// format string, such as lowercase, uppercase, nl2br etc ---> format:uppercase ##
		// require_once self::get_plugin_path( 'library/filter/format.php' );

		// sanitization functions to convert strings to safe keys etc ---> sanitize:key ##
		// require_once self::get_plugin_path( 'library/filter/sanitize.php' );

		// lowercase ##
		// require_once self::get_plugin_path( 'library/filter/lowercase.php' );

		// uppercase ##
		// require_once self::get_plugin_path( 'library/filter/uppercase.php' );

		// strip tags ##
		// require_once self::get_plugin_path( 'library/filter/strip.php' );

		// nl2br ##
		// require_once self::get_plugin_path( 'library/filter/nl2br.php' );
			
	}
	


}
