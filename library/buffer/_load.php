<?php

namespace q\willow;

use q\core;
use q\core\helper as h;
use q\willow\core\helper as wh;
use q\willow;
use q\willow\render;

\q\willow\buffer::run();

class buffer extends \q_willow {

	/**
	 * Check for view template and start OB, if correct
	*/
	public static function run(){

		// load libraries ##
		\q\core\load::libraries( self::load() );

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

		return $array = [

			// prepare map ##
			'output' => wh::get( 'buffer/output.php', 'return', 'path' ),

			// prepare map ##
			'map' => wh::get( 'buffer/map.php', 'return', 'path' ),

		];

	}


}
