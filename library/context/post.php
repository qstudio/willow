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
    public function __construct(){

		// grab passed plugin object ## 
		$this->plugin = willow\plugin::get_instance();

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
		$post = new willow\get\post();

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
		$post = new willow\get\post();

		return [ 'this' => $post->this( $args ) ];

	}

    /**
    * Render WP_Query
    *
    * @since       1.0.2
    */
    public function query( $args = [] ){

		// local vars ##
		$_markup = $this->plugin->get( '_markup' );
		$_args = $this->plugin->get( '_args' );

		// w__log( $_markup );
		// w__log( $_args );

		// new object ##
		$query = new willow\get\query();
		$navigation = new willow\get\navigation();

		// build fields array with default values ##
		$return = ([
			'total' 		=> '0', // set to zero string value ##
			'pagination' 	=> null, // empty field.. ##
			'results' 		=> isset( $_markup['default'] ) ? $_markup['default'] : null // replace results with empty markup ##
		]);

		// w__log( $args );

		// merge wp_query_args into args, if set ##
		// $args = isset( $args['wp_query_args'] ) ? $args['wp_query_args'] : $args ;

        // pass to get_posts -- and validate that we get an array back ##
		if ( ! $array = $query->posts( $args ) ) {

			// log ##
			w__log( $_args['task'].'~>n:query::posts did not return any data');

		}

		// validate what came back - it should include the WP Query, posts and totals ##
		if ( 
			! isset( $array['query'] ) 
			|| ! isset( $array['query']->posts ) 
			// || ! isset( $array['query']->posts ) 
		){

			w__log( 'Error in data returned from query::posts' );

			// log ##
			w__log( $_args['task'].'~>n:Error in data returned from query::posts');

		}
		
		// no posts.. so empty, set count to 0 and no pagination ##
		if ( 
			empty( $array['query']->posts )
			|| 0 == count( $array['query']->posts )
		){

			// w__log( 'No results returned from the_posts' );
			w__log( $_args['task'].'~>n:No results returned from query::posts');

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
