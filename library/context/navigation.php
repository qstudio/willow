<?php

namespace Q\willow\context;

use Q\willow\core\helper as h;
use Q\willow;

class navigation {

	private
		$plugin = null // this
	;

	/**
	 * 
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	public function get( $args = null ){

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
			! method_exists( 'Q\willow\get\navigation', $method )
			|| ! is_callable([ 'Q\willow\get\navigation', $method ])
		){

			h::log( 'e:>Class method is not callable: willow\get\navigation\\'.$method );

			return false;

		}

		// new object ##
		$navigation = new willow\get\navigation( $this->plugin );

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
		// h::log( $return );

		// kick back ##
		return $return;

	}

}
