<?php

namespace willow\context;

use willow\core\helper as h;
use willow\context;

// load it up ##
\willow\context\extend::__run();

class extend extends \willow\context {

	protected static $filtered = [];

	/**
	 * Fire things up
	*/
	public static function __run(){

		// allow for class extensions ##
		\do_action( 'willow/context/extend/register', [ get_class(), 'register' ] );

		// filter in context extensions ## 
		// \add_action( 'after_setup_theme', [ get_class(), 'filter' ], 2 );

		// merge filtered context extensions ## 
		// \add_action( 'after_setup_theme', [ get_class(), 'filter' ], 10 );

	}


	public static function filter(){

		// filter extensions ##
		$array = \apply_filters( 'willow/context/extend', [] );

		h::log( $array );

		// sanity ##
		if( 
			! $array
			|| ! is_array( $array ) 
		){

			h::log( 'e:>Not an Array' );

			return false;

		}

		// merge in - validate later ##
		self::$filtered = array_merge( $array, self::$filtered );

		h::log( self::$filtered );

		/*
		$merge = [];

		foreach( $array as $key => $value ){

			h::log( $key );

			$merge[ $key['class'] ] = $key;

		}

		h::log( $merge );

		// merge into class property ##
		self::$extend = array_merge(
			self::$extend, $merge
		);

		*/

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
			// || ! is_array( $args['methods'] )
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
			// || ! is_array( $args['methods'] )
		){

			h::log( 'e:>Error in passed params' );

			return false;

		}

		// reject invalid class objects ##
		if( 
			! class_exists( $args['class'] ) 
			// ! is_call( $args['class'] ) 
		){

			h::log( 'Invalid class: '.$args['class'] );

			return false;

		}

		// we only want to get "public" methods -- in this case, listed without __FUNCTION at start ##
		$methods = [];

		// methods can be passed as a string with value 'all' or 'public' ##
		if ( 
			is_string( $args['methods'] ) 
			&&  ( 
				'public' == $args['methods']
				|| 'all' == $args['methods']
			)
		){

			// switch methods ##
			switch( $args['methods'] ) {

				default :
				case "public" :

					$class = new \ReflectionClass( $args['class'] );
					$public_methods = $class->getMethods( \ReflectionMethod::IS_PUBLIC );
					// h::log( $public_methods );
					foreach( $public_methods as $key ){ 
						
						// match format returned by get_class_methods() ##
						$methods[] = $key->name; 
					
					} 

				break ;

				case "all" :

					$methods = get_class_methods( $args['class'] );

				break ;

			}

		} else {
		
			// grab method ##
			$methods = $args['methods'];

		}

		// remove quasi-private methods with __NAME ##
		foreach ( $methods as $key ) {

			// h::log( 'Checking method: '.$key );

			if( substr( $key, 0, 2, ) === '__' ) {

				// remove ##
				if ( ( $remove_key = array_search( $key, $methods )) !== false) {

					unset($methods[$remove_key]);

					// h::log( 'Removing method: '.$args['class'].'::'.$key );
					
				}

			}

		}

		// if methods is empty, don't store class ##
		if ( 
			! is_array( $methods )
			|| empty( $methods )
		){

			h::log( 'e:>Error in gathered methods' );

			return false;

		}

		// h::log( $methods );

		return self::$extend[ $args['class'] ] = [
			'context' 	=> $args['context'],
			'class' 	=> $args['class'],
			'methods' 	=> $methods,
			'lookup' 	=> isset( $args['lookup'] ) ? $args['lookup'] : false
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

			// h::log( 'checking class: '.$k.' for task: '.$task );

			// check if $context match ##
			if ( $v['context'] == $context ){

				// now check if we have a matching method ##
				if ( false !== $key = array_search( $task, $v['methods'] ) ) {

					// h::log( 'found task: '.$task );

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
