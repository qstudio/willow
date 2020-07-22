<?php

namespace q\willow\render;

use q\core;
use q\core\helper as h;
// use q\ui;
use q\plugin;
use q\get;
use q\view;
use q\asset;
use q\willow;

class method extends willow\render {


	/**
	 * Prepare $array to be rendered
	 *
	 */
	public static function prepare( $args = null, $array = null ) {

		// get calling method for filters ##
		$method = core\method::backtrace([ 'level' => 2, 'return' => 'function' ]);

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
			|| is_null( $array )
			|| ! is_array( $array )
			// || empty( $array )
		) {

			// log ##
			h::log( 'e~>'.$method.':>Error in passed $args or $array' );

			return false;

		}

		// empty results ##
		if (
			empty( $array )
		) {

			// log ##
			h::log( 'e~>'.$method.':>Returned $array is empty' );

			return false;

		}

		// h::log( 'd:>$method: '.$method );
		// h::log( $args );
		// h::log( $array );

		// if no markup sent.. ##
		if ( 
			! isset( $args['markup'] )
			&& is_array( $args ) 
		) {

			// default -- almost useless - but works for single values.. ##
			$args['markup'] = '%value%';

			foreach( $args as $k => $v ) {

				if ( is_string( $v ) ) {

					// take first string value in $args markup ##
					$args['markup'] = $v;

					break;

				}

			}

		}

		// no markup passed ##
		if ( ! isset( $args['markup'] ) ) {

			h::log( 'e~>'.$method.':Missing "markup", returning false.' );

			return false;

		}

		// last filter on array, before applying markup ##
		$array = \apply_filters( 'q/render/prepare/'.$method.'/array', $array, $args );

		// do markup ##
		$string = self::markup( $args['markup'], $array, $args );

		// filter $string by $method ##
		$string = \apply_filters( 'q/render/prepare/'.$method.'/string', $string, $args );

		// filter $array by method/template ##
		if ( $template = view\is::get() ) {

			// h::log( 'Filter: "q/theme/get/string/'.$method.'/'.$template.'"' );
			$string = \apply_filters( 'q/render/prepare/'.$method.'/string/'.$template, $string, $args );

		}

		// test ##
		// h::log( $string );

		// all render methods echo ##
		echo $string ;

		// optional logging to show removals and stats ##
        // render\log::render( $args );

		return true;

	}


	/**
	 * Search string for string passed to wp search query
	*/
	public static function search_the_content( Array $args = null ) {

		// sanity @todo ##
		if (
			is_null( $args )
			|| ! isset( $args['string'] )
		) {

			h::log( 'Error in passed params' );

			return false;

		}

		// get string ##
		$string = $args['string'];

		// get search term ##
		$search = \get_search_query();
		// h::log( $search );

        // $string = $args['string']; #\get_the_content();
        $keys = implode( '|', explode( ' ', $search ) );
		$string = preg_replace( '/(' . $keys .')/iu', '<mark>\0</mark>', $string );

		// get text length limit ##
		$length = isset( $args[ 'length' ] ) ? $args['length'] : 200 ;

		// get first occurance of search string ##
		$position = strpos($string, $search );

		// h::log( 'string pos: '.$position );

		if ( ( $length / 2 ) > $position ) {

			// h::log( 'first search term is less than 100 chars in, so return first 200 chars..' );

			$string = ( strlen( $string ) > 200 ) ? substr( $string,0,200 ).'...' : $string;

		} else {

			// move start point ##
			$string = '...'.substr( $string, $position - ( $length / 2 ), -1 );
			$string = ( strlen( $string ) > 200 ) ? substr( $string,0,200 ).'...' : $string;

		}

		// return ##
		return $string;

    }

	/**
	 * Extract keys and values from passed array
	 * 
	 * @since 4.1.0
	*/
	public static function extract( $array = null, $prefix = null, $return = null ){

		// @todo -- sanity ##
		if (
			is_null( $array )
			|| ! is_array( $array )
			|| is_null( $prefix )
		){

			h::log( 'e:>Error in passed params' );

			return false;

		}

		// return array ##
		$return_array = [];

		// category will be an array, so create category_title, permalink and slug fields ##
		foreach( $array as $k => $v ){

			$return_array[ $prefix.$k] = $v;

		}

		// how to return data ##
		if ( is_array( $return ) ){

			return array_merge( $return, $return_array );

		}

		// just retrn new values ##
		return $return_array;

	}



	/**
	 * Check if array contains other arrays
	 * 
	 * 
	 * @since 4.1.0
	*/
	public static function is_array_of_arrays( $array = null ) {

		// h::log( $array );

		// sanity ##
		if(
			is_null( $array )
			|| ! is_array( $array )
		){

			h::log( 'e:>Error in passed args or not array' );

			return false;

		}

		if (
			isset( $array[0] )
			&& is_array( $array[0] )
		){

			// h::log( 'd:>is_array' );

			return true;

		}

		// foreach ( $array as $key => $value ) {

		// 	if ( is_array( $value ) ) {

		// 		h::log( 'd:>is_array' );

		// 		return $key;

		// 	}
			  
		// }
		
		return false;
	  
	}



	public static function get_context(){

		// sanity ##
		if (
			! isset( self::$args )
			|| ! isset( self::$args['context'] )
			|| ! isset( self::$args['task'] )
		){

			h::log( 'd:>No context / task available' );

			return false;

		}

		return sprintf( 'Context: "%s" Task: "%s"', self::$args['context'], self::$args['task'] );

	}




}
