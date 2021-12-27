<?php

namespace willow\context;

use willow\core\helper as h;
use willow;

class taxonomy {

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
		$method = \sanitize_text_field( $args['task'] );

		if(
			! method_exists( 'willow\get\taxonomy', $method )
			|| ! is_callable([ 'willow\get\taxonomy', $method ])
		){

			w__log( 'e:>Class method is not callable: willow\get\taxonomy\\'.$method );

			return false;

		}

		// w__log( 'e:>Class method IS callable: willow\get\taxonomy\\'.$method );

		// return post method to 
		$return = \willow()->taxonomy->{$method}( $args );

		// // test ##
		// w__log( $return );

		// kick back ##
		return $return;

	}


}
