<?php

namespace willow\context;

use willow\core\helper as h;
use willow\render; 

class group extends \willow\context {


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

		// Q needed to run get method ##
		if(
			! method_exists( '\willow\get\group', $method )
			|| ! is_callable([ '\willow\get\group', $method ])
		){

			h::log( 'e:>Class method is not callable: willow\get\group\\'.$method );

			return false;

		}

		// method returns an array with 'data' and 'fields' ##
		if ( 
			$array = \willow\get\group::fields( $args )
		){
			// h::log( $array );
			
			// "args->fields" are used for type and callback lookups ##
			self::$args['fields'] = $array['fields']; 

			// return ##
			return $array['data'];

		}

	}

}
