<?php

// Global functions added by Willow, site outside of the namespace and are pluggable
// namespace willow\core;

// use willow\core;
use Q\willow\core\log;

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

if ( ! function_exists( 'w__log' ) ) {

	/**
     * Write to WP Error Log
     *
     * @since       1.5.0
     * @return      void
     */
	function w__log( $args = null ){

		// shift callback level, as we added another level.. ##
		\add_filter( 
			'willow/core/log/backtrace/function', function () {
			return 4;
		});
		\add_filter( 
			'willow/core/log/backtrace/file', function () {
			return 3;
		});

		// pass to core\log::set();
		return log::set( $args );

	}

}

if ( ! function_exists( 'w__log_direct' ) ) {

	/**
     * Write to WP Error Log directly, not via core\log
     *
     * @since       4.1.0
     * @return      void
     */
	function w__log_direct( $args = null ){

		// error_log( $args );

		// sanity ##
		if ( is_null( $args ) ) { 
			
			// error_log( 'Nothing passed to log(), so bailing..' );

			return false; 
		
		}

		// $args can be a string or an array - so fund out ##
		if (  
			is_string( $args )
		) {

			// default ##
			$log = $args;

		} elseif ( 
			is_array( $args ) 
			&& isset( $args['log_string'] )	
		) {

			// error_log( 'log_string => from $args..' );
			$log = $args['string'];

		} else {
			
			$log = $args;

		} 

		// debugging is on in WP, so write to error_log ##
        if ( true === WP_DEBUG ) {

			// get caller ##
			$backtrace = Q\willow\core\method::backtrace();

            if ( is_array( $log ) || is_object( $log ) ) {

				log::error_log( print_r( $log, true ).' -> '.$backtrace, \WP_CONTENT_DIR."/debug.log" );
				
            } else {

				log::error_log( $log.' -> '.$backtrace, \WP_CONTENT_DIR."/debug.log" );
				
            }

		}
		
		// done ##
		return true;

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

