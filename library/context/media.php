<?php

namespace Q\willow\context;

use Q\willow\core\helper as h;
use Q\willow;

class media {

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
			! method_exists( 'Q\willow\get\media', $method )
			|| ! is_callable([ 'Q\willow\get\media', $method ])
		){

			h::log( 'e:>Class method is not callable: willow\get\media\\'.$method );

			return false;

		}

		// return \willow\get\post::$method;

		// h::log( 'e:>Class method IS callable: q\get\media\\'.$method );

		// new object ##
		$media = new willow\get\media( $this->plugin );

		// return post method to 
		$return = $media->{$method}( $args );

		// call method ##
		/*
		$return = call_user_func_array (
				array( '\\willow\\get\\media', $method )
			,   array( $args )
		);
		*/

		// // test ##
		// h::log( $return );

		// kick back ##
		return $return;

	}


	/// ---- deprecated by get()


}
