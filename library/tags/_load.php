<?php

namespace q\willow;

// Q ##
use q\core;
use q\view;
use q\get;
use q\render;

// willow ##
use q\willow\core\helper as h;
use q\willow;

class tags extends \q_willow {

	// properties ##
	protected static 
		$filtered_tags = false
		// $tag_map = []
	;

	// public static function __callStatic( $function, $args ){	

	// 	self::g( $args );

	// }

	private static function map( $tag = null ){

		// sanity ##
		if ( 
			is_null( $tag )
		){

			h::log( 'e:>No tag passed to map');

			return false;

		}

		// load tags ##
		self::cache();

		// check for class property ##
		if (
			! self::$filtered_tags
		){

			h::log( 'e:>filtered_tags are not loaded..');

			return false;

		}

		// build map ##
		$tag_map = [
			'wil_o' => self::$filtered_tags['willow']['open'],
			'wil_c' => self::$filtered_tags['willow']['close'],

			'var_o' => self::$filtered_tags['variable']['open'],
			'var_c' => self::$filtered_tags['variable']['close'],
			
			'loo_o' => self::$filtered_tags['loop']['open'],
			'loo_c' => self::$filtered_tags['loop']['close'],
			
			'fun_o' => self::$filtered_tags['function']['open'],
			'fun_c' => self::$filtered_tags['function']['close'],
			
			'arg_o' => self::$filtered_tags['argument']['open'],
			'arg_c' => self::$filtered_tags['argument']['close'],

			'sco_o' => self::$filtered_tags['scope']['open'],
			'sco_c' => self::$filtered_tags['scope']['close'],
			
			'par_o' => self::$filtered_tags['partial']['open'],
			'par_c' => self::$filtered_tags['partial']['close'],
			
			'com_o' => self::$filtered_tags['comment']['open'],
			'com_c' => self::$filtered_tags['comment']['close'],
			
			'fla_o' => self::$filtered_tags['flag']['open'],
			'fla_c' => self::$filtered_tags['flag']['close'],
		];

		// full back, in case not requested via shortcode ##
		if ( ! isset( $tag_map[$tag] ) ){

			// return isset @todo...

		}

		// search for and return matching key, if found ##
		return $tag_map[$tag] ?: false ;

	}



	protected static function cache(){

		// check if we have already filtered load ##
		if ( self::$filtered_tags ){

			return self::$filtered_tags;

		}
		
		// per run filter on tags ##
		return self::$filtered_tags = \apply_filters( 'q/render/tags', self::$tags );

	}



	/**
	 * Wrap string in defined tags
	*/
	public static function wrap( $args = null ){

		// sanity ##
		if (
			! isset( $args )
			|| ! is_array( $args )
			|| ! isset( $args['open'] )
			|| ! isset( $args['value'] )
			|| ! isset( $args['close'] )
		){

			h::log( 'e:>Error in passed args' );

			return false;

		}

		// check ##
		if (
			! self::map( $args['open'] )
			|| ! self::map( $args['close'] )
		){

			h::log( 'e:>Error collecting open or close tags' );

			return false;

		}

		// gather data ##
		$string = self::map( $args['open'] ).$args['value'].self::map( $args['close'] );
		
		// replace method, white space aware ##
		if ( 
			isset( $args['replace'] )
		){

			$array = [];
			$array[] = self::map( $args['open'] ).$args['value'].self::map( $args['close'] );
			$array[] = trim(self::map( $args['open'] )).$args['value'].trim(self::map( $args['close'] )); // trim all spaces in tags
			$array[] = rtrim(self::map( $args['open'] )).$args['value'].self::map( $args['close'] ); // trim right on open ##
			$array[] = self::map( $args['open'] ).$args['value'].ltrim(self::map( $args['close'] )); // trim left on close ##

			h::log( $array );
			// h::log( 'value: "'.$args['value'].'"' );

			return $array;

		}

		// test ##
		// h::log( 'd:>'.$string );

		// return ##
		return $string;

	}

	
	/**
     * shortcut to get
	 * 
	 * @since 4.1.0
     */
    public static function g( $args = null ) {

		// we can pass shortcut ( mapped ) values -- i.e "var_o" ##
		return self::map( $args ) ?: false ;
 
	}

	
	/**
     * Get a single tag
	 * 
	 * @since 4.1.0
     */
    public static function get( $args = null ) {

		// sanity ##
		if (
			is_null( $args )
			|| ! isset( $args['tag'] )
			|| ! isset( $args['method'] )
		){

			h::log('e:> No args passed to method');

			return false;

		}

		// sanity ##
		if (
			! self::cache()
		){

			h::log('e:>Error in stored $tags');

			return false;

		}

		if (
			! isset( self::$filtered_tags[ $args['tag'] ][ $args['method'] ] )
		){

			h::log('e:>Cannot find tag: '.$args['tag'].'->'.$args['method'] );

			return false;

		}

		// h::log( self::cache() );

		// // get tags, with filter ##
		// $tags = self::cache();

		// looking for long form ##
		return self::$filtered_tags[ $args['tag'] ][ $args['method'] ] ;

	}



	/**
     * Get all tag definitions
	 * 
	 * @since 4.1.0
     */
    public static function get_all( $args = null ) {

		// sanity ##
		if (
			is_null( $args )
		){

			h::log('e:> No args passed to method');

			return false;

		}

		// sanity ##
		if (
			! isset( self::$tags )
			|| ! is_array( self::$tags )
		){

			h::log('e:>Error in stored $tags');

			return false;

		}

		// get tags, with filter ##
		$tags = self::cache();

		// looking for long form ##
		$return = 
			isset( $tags ) ?
			$tags :
			false ;

		return $return;

	}


    /**
     * Define tags on a global or per process basis
	 * 
	 * @since 4.1.0
     */
    public static function set( $args = null ) {

       // @todo ##

    }


}
