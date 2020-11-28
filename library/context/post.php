<?php

namespace willow\context;

use willow\core\helper as h;
use willow;
use willow\context;

class post extends willow\context {

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
			! method_exists( '\willow\get\post', $method )
			|| ! is_callable([ '\willow\get\post', $method ])
		){

			h::log( 'e:>Class method is not callable: willow\get\post\\'.$method );

			return false;

		}

		// return \willow\get\post::$method;

		// h::log( 'e:>Class method IS callable: q\get\post\\'.$method );

		// call method ##
		$return = call_user_func_array (
				array( '\\willow\\get\\post', $method )
			,   array( $args )
		);

		// // test ##
		// h::log( $return );

		// kick back ##
		return $return;

	}


	/**
    * Get current post object - returned via post~this
	* 
	* @return		Array
    * @since        1.6.2
    */
    public static function this( $args = null ){

		return [ 'this' => \willow\get\post::this( $args ) ];

	}


	
    /**
    * Render WP_Query
    *
    * @since       1.0.2
    */
    public static function query( $args = [] ){

		// @todo -- add filter to return value and avoid Q check and get routine ##

		// Q needed to run get method ##
		if ( ! class_exists( 'Q' ) ){ return false; }

		// h::log( self::$markup );
		// h::log( self::$args );

		// build fields array with default values ##
		$return = ([
		// render\fields::define([
			'total' 		=> '0', // set to zero string value ##
			'pagination' 	=> null, // empty field.. ##
			'results' 		=> isset( self::$markup['default'] ) ? self::$markup['default'] : null // replace results with empty markup ##
		]);

        // pass to get_posts -- and validate that we get an array back ##
		if ( ! $array = \willow\get\query::posts( $args ) ) {

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
				'pagination'	=> \willow\get\navigation::pagination( $args ), // get pagination, returns string ##
				'results'		=> $array['query']->posts // array of WP_Posts ##
			];

		}

		// ok ##
		return $return;

	}

}
