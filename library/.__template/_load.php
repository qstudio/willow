<?php

namespace q\willow;

use q\willow\core\helper as h;
use q\willow;

// load it up ##
\q\willow\template::__run();

class template extends \q_willow  { 

	/**
	 * Fire things up
	*/
	public static function __run(){

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

		return $array = [

			// reder ##
			'render' => h::get( 'template/render.php', 'return', 'path' ),

		];

	}
	


}
