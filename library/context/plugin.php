<?php

namespace Q\willow\context;

use Q\willow\core\helper as h;
use Q\willow;

class plugin {

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
			! method_exists( 'Q\willow\get\plugin', $method )
			|| ! is_callable([ 'Q\willow\get\plugin', $method ])
		){

			h::log( 'e:>Class method is not callable: willow\get\plugin\\'.$method );

			return false;

		}

		// return \willow\get\post::$method;

		h::log( 'e:>Class method IS callable: willow\get\plugin\\'.$method );

		// new object ##
		$plugin = new willow\get\plugin( $this->plugin );

		// return post method to 
		$return = $plugin->{$method}( $args );

		// call method ##
		/*
		$return = call_user_func_array (
				array( '\\willow\\get\\plugin', $method )
			,   array( $args )
		);
		*/

		// // test ##
		// h::log( $return );

		// kick back ##
		return $return;

	}


	
}
