<?php

namespace willow\context;

use willow\core\helper as h;
use willow;

class media {

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
			! method_exists( 'willow\get\media', $method )
			|| ! is_callable([ 'willow\get\media', $method ])
		){

			w__log( 'e:>Class method is not callable: willow\get\media\\'.$method );

			return false;

		}

		// w__log( 'e:>Class method IS callable: willow\get\media\\'.$method );

		// new object ##
		$media = new willow\get\media( $this->plugin );

		// return call ## 
		$return = $media->{$method}( $args );

		// call method ##
		/*
		$return = call_user_func_array (
				array( '\\willow\\get\\media', $method )
			,   array( $args )
		);
		*/

		// // test ##
		// w__log( $return );

		// kick back ##
		return $return;

	}

}
