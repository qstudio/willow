<?php

namespace Q\willow\context;

use Q\willow\core\helper as h;
use Q\willow;

class meta {

	private
		$plugin = null, // this
		$get = null 
	;

	/**
	 * 
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

		$this->get = new willow\get\meta( $this->plugin );

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
			! method_exists( 'Q\willow\get\meta', $method )
			|| ! is_callable([ 'Q\willow\get\meta', $method ])
		){

			h::log( 'e:>Class method is not callable: willow\get\meta\\'.$method );

			return false;

		}

		// return \willow\get\post::$method;

		// h::log( 'e:>Class method IS callable: willow\get\meta\\'.$method );

		// return callback ##
		$return = $this->get->{$method}( $args );

		// call method ##
		/*
		$return = call_user_func_array (
				array( '\\willow\\get\\meta', $method )
			,   array( $args )
		);
		*/

		// // test ##
		// h::log( $return );

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
