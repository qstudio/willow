<?php

namespace q\willow\context;

use q\core\helper as h;
use q\get;
use q\willow;
use q\willow\context;
use q\willow\render; 

class media extends willow\context {


	public static function get( $args = null ){

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
			! method_exists( '\q\get\media', $method )
			|| ! is_callable([ '\q\get\media', $method ])
		){

			h::log( 'e:>Class method is not callable: q\get\media\\'.$method );

			return false;

		}

		// return \q\get\post::$method;

		// h::log( 'e:>Class method IS callable: q\get\media\\'.$method );

		// call method ##
		$return = call_user_func_array (
				array( '\\q\\get\\media', $method )
			,   array( $args )
		);

		// // test ##
		// h::log( $return );

		// kick back ##
		return $return;

	}


	/**
     * Src image - this requires a post_id / attachment_id to be bassed ##
     *
     * @param       Array       $args
     * @since       1.3.0
     * @return      String
     */
	/*
    public static function src( $args = null ) {

		return get\media::src( $args );

	}
	*/

	/**
     * lookup thumbnail image, this implies we are working with the current post
     *
     * @param       Array       $args
     * @since       1.3.0
     * @return      String
     */
	/*
    public static function thumbnail( $args = null ) {

		return get\media::thumbnail( $args );

	}
	*/


	/**
     * Get page Avatar style and placement
     *
     * @since       1.0.1
     * @return      Mixed       string HTML || Boolean false
     */
	/*
    public static function avatar( $args = array() )
    {

		return get\media::avatar( $args );

	}
	*/


}
