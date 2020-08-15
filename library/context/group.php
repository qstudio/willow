<?php

namespace q\willow\context;

use q\willow\core\helper as h;
use q\willow;
use q\willow\render; 

class group extends willow\context {


	

	/**
     * Get group data via meta handler
     *
     * @param       Array       $args
     * @since       1.3.0
	 * @uses		define
     * @return      Array
     */
    public static function get( $args = null ) {

		$method = 'fields';

		// @todo -- add filter to return value and avoid Q check and get routine ##

		// Q needed to run get method ##
		if(
			! method_exists( '\q\get\group', $method )
			|| ! is_callable([ '\q\get\group', $method ])
		){

			h::log( 'e:>Class method is not callable: q\get\group\\'.$method );

			return false;

		}

		// method returns an array with 'data' and 'fields' ##
		if ( 
			$array = \q\get\group::fields( $args )
		){
			// h::log( $array );
			
			// "args->fields" are used for type and callback lookups ##
			self::$args['fields'] = $array['fields']; 

			// return ##
			return $array['data'];

		}

	}

}
