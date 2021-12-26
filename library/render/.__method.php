<?php

namespace willow\render;

use willow\core\helper as h;
use willow;

class method {

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
     * Markup object based on {{ placeholders }} and template
	 * This feature is not for formatting data, just applying markup to pre-formatted data
     *
     * @since    2.0.0
     * @return   Mixed
     */
    public static function markup( $markup = null, $data = null, $args = null ){

        // sanity ##
        if (
            is_null( $markup )
            || is_null( $data )
            || (
                ! is_array( $data )
                && ! is_object( $data )
            )
        ) {

            w__log( 'e:>missing parameters' );

            return false;

		}

		if (
			function_exists( 'willow' )
		){

			// variable replacement -- regex way ##
			$open = \willow()->tags->g( 'var_o' );
			$close = \willow()->tags->g( 'var_c' );

		} else {

			\w__log( 'e:>Willow Library Missing, using presumed variable tags {{ xxx }}' );

			$open = '{{ ';
			$close = ' }}';

		}
		
		// capture missing placeholders ##
		// $capture = [];

        // // w__log( $data );
		// w__log( $markup );
		// w__log( $data );
		// w__log( 't:>replace {{ with tag::var_o' );

		// empty ##
		$return = '';

        // format markup with translated data ##
        foreach( $data as $key => $value ) {

			if (
				is_array( $value )
			){

				// check on the value ##
				// w__log( 'd:>key: '.$key.' is array - going deeper..' );

				$return_inner = $markup;

				foreach( $value as $k => $v ) {

					// $string_inner = $markup;

					// check on the value ##
					// w__log( 'd:>key: '.$k.' / value: '.$v );

					// only replace keys found in markup ##
					if ( false === strpos( $return_inner, $open.$k.$close ) ) {

						// w__log( 'd:>skipping '.$k );
		
						continue ;
		
					}

					// template replacement ##
					$return_inner = str_replace( $open.$k.$close, $v, $return_inner );

				}

				$return .= $return_inner;

				continue;

			}

			// get new markup row ##
			$return .= $markup;

			// check on the value ##
			// w__log( 'd:>key: '.$key.' / value: '.$value );

            // only replace keys found in markup ##
            if ( false === strpos( $return, $open.$key.$close ) ) {

                // w__log( 'd:>skipping '.$key );

                continue ;

			}

			// template replacement ##
			$return = str_replace( $open.$key.$close, $value, $return );

		}

		// w__log( $return );

		// wrap string in defined string ?? ##
		if ( isset( $args['wrap'] ) ) {

			// w__log( 'd:>wrapping string before return: '.$args['wrap'] );

			// template replacement ##
			$return = str_replace( $open.'template'.$close, $return, $args['wrap'] );

		}

        // w__log( $return );

        // return markup ##
        return $return;

	}



	public static function str_replace_first( $find = null, $replace = null, $subject = null, $limit = 1 ) {

		// @todo - sanity ##

		// check is $find is in $ubject and return start position ##
		$pos = strpos( $subject, $find );

		// found ##
		if ( $pos !== false ) {

			return substr_replace( $subject, $replace, $pos, strlen( $find ) );

		}
		
		// kick it back ##
		return $subject;

	}


}
