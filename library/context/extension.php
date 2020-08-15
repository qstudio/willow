<?php

namespace q\willow\context;

use q\willow\core\helper as h;
use q\willow;
// use q\willow\render;
// use q\willow\context;
use q\extension as extensions;

class extension extends willow\context {

	// to allow for external extensions ##
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
			! method_exists( '\q\get\extension', $method )
			|| ! is_callable([ '\q\get\extension', $method ])
		){

			h::log( 'e:>Class method is not callable: q\get\extension\\'.$method );

			return false;

		}

		// return \q\get\post::$method;

		// h::log( 'e:>Class method IS callable: q\get\post\\'.$method );

		// call method ##
		$return = call_user_func_array (
				array( '\\q\\get\\extension', $method )
			,   array( $args )
		);

		// // test ##
		// h::log( $return );

		// kick back ##
		return $return;

	}

}
