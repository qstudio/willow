<?php

namespace q\willow;

// use q\core;
// use q\core\helper as h;
use q\willow\core\helper as h;
// use q\render;
use q\willow;

// load it up ##
\q\willow\filter::run();

class filter extends \q_willow  { 

	/**
	 * Fire things up
	*/
	public static function run(){

		// load libraries ##
		\q\core\load::libraries( self::load() );

	}
	

    /**
    * Load Libraries
    *
    * @since        4.1.0
    */
    public static function load()
    {

		// h::log( 't:>TODO - filter extension method, like context registration...' );

		return $array = [

			// escape ##
			'escape' => h::get( 'filter/escape.php', 'return', 'path' ),

			// strip tags ##
			'strip' => h::get( 'filter/strip.php', 'return', 'path' ),

			// nl2br ##
			'nl2br' => h::get( 'filter/nl2br.php', 'return', 'path' ),
			
		];

	}
	


}
