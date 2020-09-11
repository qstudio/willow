<?php

namespace willow\context;

use willow\core\helper as h;
use willow\get;
use willow\context;

class taxonomy extends \willow\context {


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
			! method_exists( '\willow\get\taxonomy', $method )
			|| ! is_callable([ '\willow\get\taxonomy', $method ])
		){

			h::log( 'e:>Class method is not callable: willow\get\taxonomy\\'.$method );

			return false;

		}

		// return \willow\get\post::$method;

		// h::log( 'e:>Class method IS callable: willow\get\taxonomy\\'.$method );

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
