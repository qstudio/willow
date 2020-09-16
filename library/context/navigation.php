<?php

namespace willow\context;

use q\core\helper as h;
// use q\ui;
use q\get;
use willow;
use willow\context;
use willow\render; 

class navigation extends willow\context {

	public static function get( $args = null ){

		// sanity ##
		if(
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['context'] )
			|| ! isset( $args['task'] )
		){

			h::log( 'e:>Error in passed parameters' );

			return false;

		}

		// take task as method ##
		$method = $args['task'];

		if(
			! method_exists( '\willow\get\navigation', $method )
			|| ! is_callable([ '\willow\get\navigation', $method ])
		){

			h::log( 'e:>Class method is not callable: willow\get\navigation\\'.$method );

			return false;

		}

		// return \willow\get\post::$method;

		// h::log( 'e:>Class method IS callable: q\get\post\\'.$method );

		// call method ##
		$return = call_user_func_array (
				array( '\\willow\\get\\navigation', $method )
			,   array( $args )
		);

		// // test ##
		// h::log( $return );

		// kick back ##
		return $return;

	}

}
