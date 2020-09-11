<?php

namespace willow\context;

use q\core\helper as h;
use q\get;
use willow;
use willow\context;
use willow\render; 

class media extends willow\context {


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
			! method_exists( '\willow\get\media', $method )
			|| ! is_callable([ '\willow\get\media', $method ])
		){

			h::log( 'e:>Class method is not callable: q\get\media\\'.$method );

			return false;

		}

		// return \willow\get\post::$method;

		// h::log( 'e:>Class method IS callable: q\get\media\\'.$method );

		// call method ##
		$return = call_user_func_array (
				array( '\\q\\get\\media', $method )
			,   array( $args )
		);

		// // test ##
		// h::log( $return );

		// kick back ##
		return $return;

	}


	/// ---- deprecated by get()


}
