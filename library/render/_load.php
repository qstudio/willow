<?php

namespace q\willow;

// use q\core;
// use q\core\helper as h;
use q\willow\core\helper as h;
// use q\render;
use q\willow;

// load it up ##
\q\willow\render::run();

class render extends \q_willow  { 

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

		return $array = [

			// methods ##
			'method' => h::get( 'render/method.php', 'return', 'path' ),
			
			// validate and assign args ##
			'args' => h::get( 'render/args.php', 'return', 'path' ),

			// check callbacks on defined fields ## 
			'callback' => h::get( 'render/callback.php', 'return', 'path' ),

			// prepare and manipulate field data ##
			'fields' => h::get( 'render/fields.php', 'return', 'path' ), 

			// check format of each fields data and modify as required to markup ##
			'format' => h::get( 'render/format.php', 'return', 'path' ),

			// defined field types to generate field data ##
			'type' => h::get( 'render/type/_load.php', 'return', 'path' ),

			// prepare defined markup, search for and replace variables 
			'markup' => h::get( 'render/markup.php', 'return', 'path' ),

			// output string ##
			'output' => h::get( 'render/output.php', 'return', 'path' ),

			// log activity ##
			'log' => h::get( 'render/log.php', 'return', 'path' ),

		];

	}
	


}
