<?php

// Global functions added by Willow, site outside of the namespace and are pluggable
// namespace willow\core;

// use willow\core;
// use willow\core\helper as h;

/** 
 * Willow API 
 *
 * @todo 
 */
if ( ! function_exists( 'willow' ) ) {

	function willow(){

		// sanity ##
		if(
			! class_exists( '\Q\willow\plugin' )
		){

			error_log( 'e:>Willow is not available to '.__FUNCTION__ );

			return false;

		}

		// cache ##
		$willow = \Q\willow\plugin::get_instance();

		// sanity - make sure willow instance returned ##
		if( 
			is_null( $willow )
			|| ! ( $willow instanceof \Q\willow\plugin ) 
		) {

			// get stored willow instance from filter ##
			$willow = \apply_filters( 'Q\willow/instance', NULL );

			// sanity - make sure willow instance returned ##
			if( 
				is_null( $willow )
				|| ! ( $willow instanceof \Q\willow\plugin ) 
			) {

				error_log( 'Error in object instance returned to '.__FUNCTION__ );

				return false;

			}

		}

		// return willow instance ## 
		return $willow;

	}

}

/**
 * Return first character of string 
 * 
 * @since	1.6.2
 * @return	Mixed
*/
if( ! function_exists( 'w__substr_first' ) ) {
	function w__substr_first( string $string = null ){

		// sanity ##
		if ( is_null( $string ) ){ return false; }

		return mb_substr( trim( $string ), 0, 1, 'utf-8' ); 

	}
}

/**
 * Return last character of string 
 * 
 * @since	1.6.2
 * @return	Mixed
*/
if( ! function_exists( 'w__substr_last' ) ) {
	function w__substr_last( string $string = null ){

		// sanity ##
		if ( is_null( $string ) ){ return false; }

		return mb_substr( trim( $string ), -1, 'utf-8' ); 

	}
}

