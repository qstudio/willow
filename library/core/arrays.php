<?php

namespace willow\core;

use willow\core\helper as h;
use willow;

class arrays {

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

			w__log( 'e:>Error in passed params' );

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
	public static function is_array_of_arrays( $array = null ):bool {

		// w__log( $array );

		// sanity ##
		if(
			is_null( $array )
			|| ! is_array( $array )
		){

			// w__log( 'e:>Error in passed args or not an array' );

			return false;

		}

		if (
			isset( $array[0] )
			&& is_array( $array[0] )
		){

			// w__log( 'd:>is_array' );

			return true;

		}

		return false;
	  
	}

	public static function get_context(){

		// sanity ##
		if (
			null === \willow()
			|| null === \willow()->get( '_args' )
			|| ! isset( \willow()->get( '_args' )['context'] )
			|| ! isset( \willow()->get( '_args' )['task'] )
		){

			w__log( 'd:>No context / task available' );

			return false;

		}

		return sprintf( 
			'Context: "%s" Task: "%s"', 
			\willow()->get( '_args' )['context'], 
			\willow()->get( '_args' )['task'] 
		);

	}

	/**
	 * Attempt to convert an array to a string
	 * 
	 * @since 	2.0.1
	 * @return 	Mixed
	*/
    public static function to_string( $array = null ) {
        
        // sanity ##
		if(
			is_null( $array ) // nothing sent ##
			|| ! is_array( $array ) // not an array ##
			|| is_string( $array )// already a string ##
		){

			return $array; // Willow can try again ##

		}

		// split at space ##
		$string = implode ( " ", array_values( $array ) );

		// trim ##
		$string = trim( $string );

		// kick back ##
		return $string;

	}

	/**
	 * Convert an array to an object
	 * 
	 * @since 	1.5.0
	 * @return 	Mixed
	*/
    public static function to_object( $array ) {
        
        if ( ! is_array( $array ) ) {

            return $array;

        }
    
        $object = new \stdClass();

        if ( is_array( $array ) && count( $array ) > 0 ) {

            foreach ( $array as $name => $value ) {

                $name = strtolower( trim( $name ) );

                if ( ! empty( $name ) ) {

                    $object->$name = self::array_to_object( $value );

                }

            }

            return $object;

        } else {
          
            return false;
        
        }

	}

	/**
	 * Check is key_exists in MD array
	 * 
	 * @since 	1.2.0
	 * @return	Boolean
	*/
	public static function key_exists( array $array, $key ){

		// is in base array?
		if ( array_key_exists( $key, $array ) ) {
			return true;
		}
	
		// check arrays contained in this array
		foreach ( $array as $element ) {

			if ( is_array( $element ) ) {

				if ( self::key_exists( $element, $key ) ) {

					return true;

				}
			
			}
	
		}
	
		return false;
	}

	
	public static function search( $field = null, $value = null, $array = null ) {

		// sanity ##
		if (
			is_null( $field )
			|| is_null( $value )
			|| is_null( $array )
			|| ! is_array( $array )
		){

			w__log( 'e:>Error in passed params' );

			return false;

		}

        foreach ( $array as $key => $val ) {
        
            if ( $val[$field] === $value ) {
        
                return $key;
        
            }
        
        }
        
        return null;

	}

	
	
    /**
     * Recursive pass args 
     * 
     * @link    https://mekshq.com/recursive-wp-parse-args-wordpress-function/
     */
    public static function parse_args( &$args, $defaults ){

		// sanity ##
		if(
			! $defaults
		){

			// w__log( 'e:>No $defaults passed to method' );

			return $args; // ?? TODO, is this good ? 

		}

        $args = (array) $args;
        $defaults = (array) $defaults;
        $result = $defaults;
        
        foreach ( $args as $k => &$v ) {

            if ( 
				is_array( $v ) 
				&& $result
				&& is_array( $result )
				&& isset( $result[ $k ] ) 
				&& ! is_null( $result[ $k ] )
			) {
			
				$result[ $k ] = self::parse_args( $v, $result[ $k ] );
			
			} else {
			
				$result[ $k ] = $v;
			
			}

        }

        return $result;

	}

	public static function var_export_short( $data, $return = true ){

		$dump = var_export($data, true);

		$dump = preg_replace('#(?:\A|\n)([ ]*)array \(#i', '[', $dump); // Starts
		$dump = preg_replace('#\n([ ]*)\),#', "\n$1],", $dump); // Ends
		$dump = preg_replace('#=> \[\n\s+\],\n#', "=> [],\n", $dump); // Empties

		if (gettype($data) == 'object') { // Deal with object states
			$dump = str_replace('__set_state(array(', '__set_state([', $dump);
			$dump = preg_replace('#\)\)$#', "])", $dump);
		} else { 
			$dump = preg_replace('#\)$#', "]", $dump);
		}

		if ($return===true) {
			return $dump;
		} else {
			echo $dump;
		}

	}

}
