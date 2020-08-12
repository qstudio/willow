<?php

namespace q\willow\context;

use q\core\helper as h;
// use q\ui;
use q\get;
use q\willow;
use q\willow\context;
use q\willow\render; 

class taxonomy extends willow\context {


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
			! method_exists( '\q\get\taxonomy', $method )
			|| ! is_callable([ '\q\get\taxonomy', $method ])
		){

			h::log( 'e:>Class method is not callable: q\get\taxonomy\\'.$method );

			return false;

		}

		// return \q\get\post::$method;

		// h::log( 'e:>Class method IS callable: q\get\taxonomy\\'.$method );

		// call method ##
		$return = call_user_func_array (
				array( '\\q\\get\\taxonomy', $method )
			,   array( $args )
		);

		// // test ##
		// h::log( $return );

		// kick back ##
		return $return;

	}


}
