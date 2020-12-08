<?php

namespace willow\context;

use willow\core\helper as h;
use willow;

class plugin {

	private
		$plugin = null // this
	;

	/**
	 * 
     */
    public function __construct( \willow\plugin $plugin ){

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
			! method_exists( 'willow\get\plugin', $method )
			|| ! is_callable([ 'willow\get\plugin', $method ])
		){

			w__log( 'e:>Class method is not callable: willow\get\plugin\\'.$method );

			return false;

		}

		// return \willow\get\post::$method;

		w__log( 'e:>Class method IS callable: willow\get\plugin\\'.$method );

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
		// w__log( $return );

		// kick back ##
		return $return;

	}


	
}
