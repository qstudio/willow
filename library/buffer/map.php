<?php

namespace willow\buffer;

use willow\core;
use willow\core\helper as h;
use willow;
use willow\render;
use willow\buffer;

class map extends willow\buffer {

	/**
	 * Prepare output for Buffer
	 * 
	 * @since 4.1.0
	*/
    public static function prepare() {

		// sanity ##
		if ( 
			is_null( self::$buffer_map )
			|| ! is_array( self::$buffer_map )
			// || is_null( self::$buffer_map['0'] )
		){

			// log ##
			h::log( 'e:>$buffer_map is empty, so nothing to prepare.. stopping here.');

			// kick out ##
			return false;

		}

		// get orignal markup string ##
		$string = self::$markup_template;

		// h::log( self::$buffer_map );

		// pre format child willows, moving output into parent rows ##
		foreach( self::$buffer_map as $key => $value ){

			if( 
				// '0' == $key // skip first key, this contains the buffer markup ##
				// ||
				! $value['parent'] // skip rows without a parent value ( primary parsed elements ) ##
			){

				continue;

			}

			// // if $value['parent'] set, then take
			// if( $value['parent'] ){

			if ( 
				! $row = self::get_key_from_value( 'tag', $value['parent'] )
			){

				continue;

			}

			// h::log( 'Row: '.$value['hash'].' is a child to: '.self::$buffer_map[ $row ]['hash'] );

			// str_replace the value of "tag" in this key, in the "output" of the found key with "output" from this key... ##
			self::$buffer_map[ $row ]['output'] = str_replace( $value['tag'], $value['output'], self::$buffer_map[ $row ]['output'] );

		}

		// h::log( self::$buffer_map );
		// h::log( self::$buffer_log );
		// h::log( $string );
		// $return = '';

		// now, search and replace tags in parent with tags from buffer_map ##
		foreach( self::$buffer_map as $key => $value ){

			// skip first row or rows which do not have a parent ##
			if( 
				// '0' == $key 
				// || 
				$value['parent'] // skip rows with a parent value ##
				|| ! isset( $value['hash'] ) // skip rows without a hash ###
			){

				continue;

			}

			// check if we have string, so we can warm if not ##
			if( 
				strpos( $string, $value['tag'] ) === false
			){

				h::log( 'e:>'.$value['hash'].' -> Unable to locate: '.$value['tag'].' in buffer' );

				continue;

			}

			// replacement ##
			$string = str_replace( $value['tag'], $value['output'], $string );

		}

		// h::log( $string );

		// kick back ##
		return $string;

	}


	protected static function get_key_from_value( $key = null, $value = null ){

		// sanity ##
		if( 
			is_null( $key )
			|| is_null( $value )
		){

			h::log( 'e:>Error in passed arguments' );

			return false;

		}

		// h::log( 'searching for: '. $value.' in row: '.$key );

		foreach( self::$buffer_map as $key_map => $value_map ){

			if ( isset( $value_map[$key] ) && $value_map[$key] == $value ) {

				// h::log( 'key '.$key.' found in row: '.$key_map );

				return $key_map;

			}

		}

		// negative, if not found by now ##
		return false;

		/*
		$result = array_search( $value, array_column( self::$buffer_map, $key ) );
		$keys = array_keys(array_column( self::$buffer_map, $key ), $value );
		h::log( $keys );
		*/
		/*
		if( 
			! isset( self::$buffer_map[$result] )
		){

			h::log( 'e:>Error finding key: '.$result );

			return false;

		}

		// h::log( 'key found in row: '.$result );

		return $result;
		*/

	}

}
