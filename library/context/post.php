<?php

namespace q\willow\context;

use q\core\helper as h;
use q\get;
use q\willow;
use q\willow\context;
use q\willow\render; 

class post extends willow\context {


	/**
     * Generic H1 title tag
     *
     * @param       Array       $args
     * @since       1.3.0
     * @return      String
     */
    public static function title( $args = null ) {

		return get\post::title( $args );

    }



	/**
     * Post Meta details.. 
     *
     * @param       Array       $args
     * @since       1.4.1
     * @return      String
     */
	/*
    public static function meta_data( $args = null ) {

		return get\post::meta_data( $args );

	}
	*/



	/**
     * Post Meta comments 
     *
     * @param       Array       $args
     * @since       1.4.1
     * @return      String
     */
	/*
    public static function meta_comment( $args = null ) {

		return get\post::meta_comment( $args );

	}
	*/



	/**
	 * Helper Method to get_the_date
	 */
	/*
	public static function meta_date( $args = null ){

		return get\post::date( $args );

	}
	*/

	
    /**
    * Render WP_Query
    *
    * @since       1.0.2
    */
    public static function query( $args = [] )
    {

		// h::log( self::$markup );
		// h::log( self::$args );

		// h::log( 't:>PROBLEM - if markup is passed as single arg from willow, then other settings, like markup->default are lost, merge more gracefully..' );

		// build fields array with default values ##
		$return = ([
		// render\fields::define([
			'total' 		=> '0', // set to zero string value ##
			'pagination' 	=> null, // empty field.. ##
			'results' 		=> isset( self::$markup['default'] ) ? self::$markup['default'] : null // replace results with empty markup ##
		]);

        // pass to get_posts -- and validate that we get an array back ##
		if ( ! $array = get\query::posts( $args ) ) {

			// log ##
			h::log( self::$args['task'].'~>n:query::posts did not return any data');

		}

		// validate what came back - it should include the WP Query, posts and totals ##
		if ( 
			! isset( $array['query'] ) 
			|| ! isset( $array['query']->posts ) 
			// || ! isset( $array['query']->posts ) 
		){

			// h::log( 'Error in data returned from query::posts' );

			// log ##
			h::log( self::$args['task'].'~>n:Error in data returned from query::posts');

		}
		
		// no posts.. so empty, set count to 0 and no pagination ##
		if ( 
			empty( $array['query']->posts )
			|| 0 == count( $array['query']->posts )
		){

			// h::log( 'No results returned from the_posts' );
			h::log( self::$args['task'].'~>n:No results returned from query::posts');

		// we have posts, so let's add some charm ##
		} else {

			// merge array into args ##
			$args = \q\core\method::parse_args( $array, $args );

			// h::log( $array['query']->found_posts );

			// define all required fields for markup ##
			// self::$fields = [
			$return = [
				'total' 		=> $array['query']->found_posts, // total posts ##
				'pagination'	=> get\navigation::pagination( $args ), // get pagination, returns string ##
				'results'		=> $array['query']->posts // array of WP_Posts ##
			];

		}

		// ok ##
		return $return;

    }

	


	/**
	 * Helper Method to get the_excerpt
	 */
	public static function excerpt( $args = null ){

		return get\post::excerpt( $args );

	}




	/**
	 * Helper Method to get the_content
	 */
	public static function content( $args = null ){

		// get content - returns array with key 'content' ##
		return get\post::content( $args );

	}


}
