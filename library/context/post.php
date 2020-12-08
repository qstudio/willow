<?php

namespace willow\context;

use willow\core\helper as h;
use willow;

class post {

	private 
		$plugin = false
	;

	/**
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
			! method_exists( 'willow\get\post', $method )
			|| ! is_callable([ 'willow\get\post', $method ])
		){

			w__log( 'e:>Class method is not callable: willow\get\post\\'.$method );

			return false;

		}

		// w__log( 'e:>Class method IS callable: willow\get\post\\'.$method );

		// new object ##
		$post = new willow\get\post( $this->plugin );

		// return post method to 
		$return = $post->{$method}( $args );

		// call method ##
		/*
		$return = call_user_func_array (
				array( '\\willow\\get\\post', $method )
			,   array( $args )
		);
		*/

		// // test ##
		// w__log( $return );

		// kick back ##
		return $return;

	}


	/**
    * Get current post object - returned via post~this
	* 
	* @return		Array
    * @since        1.6.2
    */
    public function this( $args = null ){

		// new object ##
		$post = new willow\get\post( $this->plugin );

		return [ 'this' => $post->this( $args ) ];

	}


	
    /**
    * Render WP_Query
    *
    * @since       1.0.2
    */
    public function query( $args = [] ){

		// @todo -- add filter to return value and avoid Q check and get routine ##

		// Q needed to run get method ##
		// if ( ! class_exists( 'Q' ) ){ return false; }

		// w__log( self::$markup );
		// w__log( self::$args );

		// new object ##
		$query = new willow\get\query( $this->plugin );
		$navigation = new willow\get\navigation( $this->plugin );

		// build fields array with default values ##
		$return = ([
		// render\fields::define([
			'total' 		=> '0', // set to zero string value ##
			'pagination' 	=> null, // empty field.. ##
			'results' 		=> isset( self::$markup['default'] ) ? self::$markup['default'] : null // replace results with empty markup ##
		]);

        // pass to get_posts -- and validate that we get an array back ##
		if ( ! $array = $query->posts( $args ) ) {

			// log ##
			w__log( self::$args['task'].'~>n:query::posts did not return any data');

		}

		// validate what came back - it should include the WP Query, posts and totals ##
		if ( 
			! isset( $array['query'] ) 
			|| ! isset( $array['query']->posts ) 
			// || ! isset( $array['query']->posts ) 
		){

			// w__log( 'Error in data returned from query::posts' );

			// log ##
			w__log( self::$args['task'].'~>n:Error in data returned from query::posts');

		}
		
		// no posts.. so empty, set count to 0 and no pagination ##
		if ( 
			empty( $array['query']->posts )
			|| 0 == count( $array['query']->posts )
		){

			// w__log( 'No results returned from the_posts' );
			w__log( self::$args['task'].'~>n:No results returned from query::posts');

		// we have posts, so let's add some charm ##
		} else {

			// merge array into args ##
			$args = \q\core\method::parse_args( $array, $args );

			// w__log( $array['query']->found_posts );

			// define all required fields for markup ##
			// self::$fields = [
			$return = [
				'total' 		=> $array['query']->found_posts, // total posts ##
				'pagination'	=> $navigation->pagination( $args ), // get pagination, returns string ##
				'results'		=> $array['query']->posts // array of WP_Posts ##
			];

		}

		// ok ##
		return $return;

	}

}
