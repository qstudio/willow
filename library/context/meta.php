<?php

namespace willow\context;

use willow\core\helper as h;
use willow;

class meta {

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
			! method_exists( 'willow\get\meta', $method )
			|| ! is_callable([ 'willow\get\meta', $method ])
		){

			w__log( 'e:>Class method is not callable: willow\get\meta\\'.$method );

			return false;

		}

		// w__log( 'e:>Class method IS callable: willow\get\meta\\'.$method );

		// return callback ##
		$return = \willow()->meta->{$method}( $args );

		// test ##
		// w__log( $return );

		// kick back ##
		return $return;

	}

	/**
     * Get Meta field data via meta handler
     *
     * @param       Array       $args
     * @since       1.3.0
	 * @uses		define
     * @return      Array
     */
    public function field( $args = null ) {

		// return an array with the field "task" as the placeholder key and value

		return [ $args['task'] => $this->get->field( $args ) ];

	}


}
