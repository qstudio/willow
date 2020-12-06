<?php

namespace Q\willow\context;

use Q\willow\core\helper as h;
use Q\willow;

class taxonomy {

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

			w__log( 'e:>Error in passed parameters' );

			return false;

		}

		// take task as method ##
		$method = $args['task'];

		if(
			! method_exists( 'Q\willow\get\taxonomy', $method )
			|| ! is_callable([ 'Q\willow\get\taxonomy', $method ])
		){

			w__log( 'e:>Class method is not callable: willow\get\taxonomy\\'.$method );

			return false;

		}

		// return \willow\get\post::$method;

		// w__log( 'e:>Class method IS callable: willow\get\taxonomy\\'.$method );

		// new object ##
		$taxonomy = new willow\get\taxonomy( $this->plugin );

		// return post method to 
		$return = $taxonomy->{$method}( $args );

		// call method ##
		/*
		$return = call_user_func_array (
				array( '\\willow\\get\\taxonomy', $method )
			,   array( $args )
		);
		*/

		// // test ##
		// w__log( $return );

		// kick back ##
		return $return;

	}


}
