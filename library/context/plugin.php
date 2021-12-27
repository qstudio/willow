<?php

namespace willow\context;

use willow\core\helper as h;
use willow;

class plugin {

	/**
	 * Construct
     */
    public function __construct(){

		// silence ##

	}

	public function get( $args = null ){

		// sanity ##
		if(
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['context'] )
			|| ! isset( $args['task'] )
		){

			w__log( 'e:>Error in passed parameters' );

			return false;

		}

		// take task as method ##
		$method = $args['task'];

		if(
			! method_exists( 'willow\get\plugin', $method )
			|| ! is_callable([ 'willow\get\plugin', $method ])
		){

			w__log( 'e:>Class method is not callable: willow\get\plugin\\'.$method );

			return false;

		}

		// return \willow\get\post::$method;

		w__log( 'e:>Class method IS callable: willow\get\plugin\\'.$method );

		// return post method to 
		$return = \willow()->plugin->{$method}( $args );

		// // test ##
		// w__log( $return );

		// kick back ##
		return $return;

	}


	
}
