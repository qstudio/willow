<?php

namespace willow\context;

use willow\core\helper as h;
use willow;

class navigation {

	private
		$plugin = null // this
	;

	/**
	 * 
     */
    public function __construct(){

		// grab passed plugin object ## 
		$this->plugin = willow\plugin::get_instance();

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
			! method_exists( 'willow\get\navigation', $method )
			|| ! is_callable([ 'willow\get\navigation', $method ])
		){

			w__log( 'e:>Class method is not callable: willow\get\navigation\\'.$method );

			return false;

		}

		// w__log( 'e:>Class method IS callable: willow\get\navigation\\'.$method );

		// new object ##
		$navigation = new willow\get\navigation();

		// return callback ##
		$return = $navigation->{$method}( $args );

		// call method ##
		/*
		$return = call_user_func_array (
				array( '\\willow\\get\\navigation', $method )
			,   array( $args )
		);
		*/

		// // test ##
		// w__log( $return );

		// kick back ##
		return $return;

	}

}
