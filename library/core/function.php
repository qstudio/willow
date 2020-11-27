<?php

// Global functions added by Willow, site outside of the namespace and are pluggable
// namespace willow\core;

// use willow\core;
// use willow\core\helper as h;

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

