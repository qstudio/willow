<?php

namespace q\willow\context;

use q\core\helper as h;
use q\get;
use q\willow;
use q\willow\core;
use q\willow\context;
use q\willow\render; 

class partial extends willow\context {


	/**
     * Generic Getter - looks for properties in config matching context->task
	 * can be loaded as a string in context/ui file
     *
     * @param       Array       $args
     * @since       1.4.1
	 * @uses		render\fields::define
     * @return      Array
     */
    public static function get( $args = null ) {

		// // look for property "args->task" in config ##
		// if ( 
		// 	$config = core\config::get([ 'context' => $args['context'], 'task' => $args['task'] ])
		// ){
			// h::log( $config );
			
			// "args->fields" are used for type and callback lookups ##
			// self::$args['fields'] = $array['fields']; 

			// define "fields", passing returned data ##
			render\fields::define(
				core\config::get([ 'context' => $args['context'], 'task' => $args['task'] ])
			);

		// }

	}


}
