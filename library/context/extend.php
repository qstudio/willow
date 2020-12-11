<?php

namespace willow\context;

use willow\core\helper as h;
use willow\context;

class extend {

	private 
		$plugin = false,
		$_extend = [],
		$filtered = []
	;

	/**
     * Apply Markup changes to passed template
     * find all placeholders in self::$markup and replace with matching values in self::$fields
	 * most complex and most likely to clash go first, then simpler last ##
     * 
     */
    public function __construct(){

		// grab passed plugin object ## 
		$this->plugin = \willow\plugin::get_instance();

		// extend array ##
		$this->_extend = $this->plugin->get( '_extend' );

	}

	function hooks(){

		// allow for class extensions ##
		// \do_action( 'willow/context/extend/register', [ $this, 'register' ] );

		// allow for class extensions ##
		// \add_filter( 'after_setup_theme', [ $this, 'filter' ], 0, 1 );

		// filter in context extensions ## 
		// \add_action( 'after_setup_theme', [ get_class(), 'filter' ], 2 );

		// merge filtered context extensions ## 
		// \add_action( 'after_setup_theme', [ get_class(), 'filter' ], 10 );

	}

	/**
	 * @todo
	*/
	public function filter( $array = null ){

		// filter extensions ##
		$array = \apply_filters( 'willow/context/extend', $this->_extend );

		// h::log( $array );

		/*
		// sanity ##
		if( 
			! $array
			|| ! is_array( $array ) 
		){

			h::log( 'e:>Not an Array' );

			return false;

		}

		// merge in - validate later ##
		$this->filtered = array_merge( $array, $this->filtered );
		*/

		// h::log( self::$filtered );

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

	public function register( $args = null ){

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
		$this->set( $args );

	}

    protected function set( $args = null ) {

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

		// reject invalid classes ##
		if( 
			! class_exists( $args['class'] ) 
		){

			h::log( 'e:>Invalid class: '.$args['class'] );

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

		// @todo - check if any methods found ...
		if( empty( $methods ) ){

			return false;

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

		$_extend = $this->plugin->get( '_extend' );

		$_extend[ $args['class'] ] = [
			'context' 	=> $args['context'],
			'class' 	=> $args['class'],
			'methods' 	=> $methods,
			'lookup' 	=> $args['lookup'] ?? false
		];

		// store to object prop ##
		return $this->plugin->set( '_extend', $_extend );

		// h::log( $this->plugin->get( '_extend' ) );

		/*
		return self::$extend[ $args['class'] ] = [
			'context' 	=> $args['context'],
			'class' 	=> $args['class'],
			'methods' 	=> $methods,
			'lookup' 	=> isset( $args['lookup'] ) ? $args['lookup'] : false
		];
		*/

	}

	/**
	 * Get stored extension by context+task
	 *
	 */
	public function get( $context = null, $task = null ) {

		// sanity ###
		if (
			is_null( $context )
			|| is_null( $task )
		){

			h::log( 'e:>Error in passed params' );

			return false;

		}

		$_extend = $this->plugin->get( '_extend' );

		// check ##
		// h::log( 'd:>Looking for extension: '.$context );
		// h::log( $_extend );

		// is_array ##
		if (
			! is_array( $_extend )
		){

			h::log( 'e:>Error in stored $extend' );

			return false;

		}

		foreach( $_extend as $k => $v ){

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
	public function get_all() {

		// h::log( self::$extend );
		$_extend = $this->plugin->get( '_extend' );

		// is_array ##
		if (
			! is_array( $_extend )
		){

			h::log( 'e:>Error in stored $extend' );

			return false;

		}

		return $_extend;

	}

     
}
