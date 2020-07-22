<?php

namespace q\willow\context;

use q\core\helper as h;
// use q\ui;
use q\get;
use q\willow;
use q\willow\context;
use q\willow\render; 

// load it up ##
\q\willow\context\extend::run();

class extend extends willow\context {

	/**
	 * Fire things up
	*/
	public static function run(){

		// allow for class extensions ##
		\do_action( 'q/willow/context/extend/register', [ get_class(), 'register' ] );

	}


	public static function register( $args = null ){

		// test ##
		// h::log( $args );

		// sanity ###
		if (
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['context'] )
			|| ! isset( $args['class'] )
			|| ! isset( $args['methods'] )
			|| ! is_array( $args['methods'] )
		){

			h::log( 'e:>Error in passed params' );

			return false;

		}

		// store ##
		self::set( $args );

	}


	
    public static function set( $args = null ) {

		// sanity ###
		if (
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['context'] )
			|| ! isset( $args['class'] )
			|| ! isset( $args['methods'] )
			|| ! is_array( $args['methods'] )
		){

			h::log( 'e:>Error in passed params' );

			return false;

		}

		// we only want to get "public" methods -- in this case, listed without __FUNCTION at start ##
		$methods = [];
		foreach( $args['methods'] as $method ){

			// h::log( 'd:>checking method: '.$method );
			
			// skip quasi-private __METHODS ##
			if ( false !== strpos( $method, '__' ) ){ continue; } 
			
			// grab method ##
			$methods[] = $method;

		};

		// if methods is empty, don't store class ##
		if ( 
			! is_array( $methods )
			|| empty( $methods )
		){

			h::log( 'e:>Error in gathered methods' );

			return false;

		}

		return self::$extend[ $args['class'] ] = [
			'context' 	=> $args['context'],
			'class' 	=> $args['class'],
			'methods' 	=> $methods
		];

	}


	/**
	 * Get stored extension by context+task
	 *
	 */
	public static function get( $context = null, $task = null ) {

		// sanity ###
		if (
			is_null( $context )
			|| is_null( $task )
		){

			h::log( 'e:>Error in passed params' );

			return false;

		}

		// check ##
		// h::log( 'd:>Looking for extension: '.$context );
		// h::log( self::$extend );

		// is_array ##
		if (
			! is_array( self::$extend )
		){

			h::log( 'e:>Error in stored $extend' );

			return false;

		}

		foreach( self::$extend as $k => $v ){

			// h::log( 'checking class: '.$k );

			// check if $context match ##
			if ( $v['context'] == $context ){

				// now check if we have a matching method ##
				if ( false !== $key = array_search( $task, $v['methods'] ) ) {

					// h::log( 'found context: '.$v['class'] );

					// check if extension is callable ##
					if (
						! class_exists( $v['class'] )
						|| ! method_exists( $v['class'], $v['methods'][$key] )
						|| ! is_callable([ $v['class'], $v['methods'][$key] ])
					){

						// h::log( $v['class'].'::'.$v['methods'][$key].' is NOT available' );

						return false;

					}

					// h::log( $v['class'].'::'.$v['methods'][$key].' IS available' );

					// kick back ##
					return [ 'class' => $v['class'], 'method' => $v['methods'][$key] ];

				}

			}

		}

		// nada
		return false;

	}




	/**
	 * Get all stored extensions
	 *
	 */
	public static function get_all() {

		// h::log( self::$extend );

		// is_array ##
		if (
			! is_array( self::$extend )
		){

			h::log( 'e:>Error in stored $extend' );

			return false;

		}

		return self::$extend;

	}

     
}
