<?php

namespace q\willow\core;

use q\core;
use q\core\helper as h;
// use q\ui;
use q\plugin;
use q\get;
use q\view;
use q\asset;

class method extends \q_willow {


	/**
	 * Get string between two placeholders
	 * 
	 * @link 	https://stackoverflow.com/questions/5696412/how-to-get-a-substring-between-two-strings-in-php
	 * @since 4.1.0
	*/
	public static function string_between( $string, $start, $end, $inclusive = false ){ 
		
		$string = " ".$string; 
		$ini = strpos( $string, $start ); 
		
		if ($ini == 0) {
			return ""; 
		}
		
		if ( ! $inclusive ) {

			$ini += strlen( $start ); 
		
		}
		
		$len = strpos( $string, $end, $ini ) - $ini; 
		
		if ( $inclusive ) {
			
			$len += strlen( $end ); 
		
		}

		$string = substr( $string, $ini, $len ); 

		// trim white spaces ##
		$string = trim( $string );

		// h::log( 'string: '.$string );

		// kick back ##
		return $string;
	
	} 


	/**
	 * 
	 * 
	 * @link https://stackoverflow.com/questions/27078259/get-string-between-find-all-occurrences-php/27078384#27078384
	*/
	public static function strings_between( $str, $startDelimiter, $endDelimiter ) {

		$contents = array();
		$startDelimiterLength = strlen($startDelimiter);
		$endDelimiterLength = strlen($endDelimiter);
		$startFrom = $contentStart = $contentEnd = 0;

		while (false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {

			$contentStart += $startDelimiterLength;
			$contentEnd = strpos($str, $endDelimiter, $contentStart);
			
			if (false === $contentEnd) {
				break;
			}

			$contents[] = substr($str, $contentStart, $contentEnd - $contentStart);
			$startFrom = $contentEnd + $endDelimiterLength;

		}
	  
		return $contents;

	}



	/**
	 * Check if a string starts with a specific string
	 * 
	 * @since 4.1.0
	*/
	public static function starts_with( $haystack = null, $needle = null ){

		// sanity ##
		if (
			is_null( $haystack )
			|| is_null( $needle )
		){
			
			h::log('e:>Error in passed params');

			return false;

		}

		$length = strlen( $needle );
		
		return ( substr( $haystack, 0, $length ) === $needle );
	 
	}



	/**
	 * Check if a string ends with a specific string
	 * 
	 * @since 4.1.0
	*/
	public static function ends_with( $haystack = null, $needle = null ){

		// sanity ##
		if (
			is_null( $haystack )
			|| is_null( $needle )
		){
			
			h::log('e:>Error in passed params');

			return false;

		}

	    $length = strlen( $needle );
		
		if ( $length == 0 ) {

        	return true;
		
		}

		return ( substr( $haystack, -$length ) === $needle );

	}



	
	/**
	 * 
	 * @link https://www.php.net/manual/en/function.parse-str.php
	*/
	public static function parse_str( $string = null ) {

		# result array
		$array = array();
	  
		# split on outer delimiter
		$pairs = explode( '&', $string );
	  
		# loop through each pair
		foreach ( $pairs as $i ) {

			# split into name and value
			list( $key, $value ) = explode( '=', $i, 2 );

			// what about array values ##
			// example -- sm:medium, lg:large
			if( false !== strpos( $value, ':' ) ){

				// temp array ##
				$value_array = [];	

				// split value into an array at "," ##
				$value_pairs = explode( ',', str_replace( ' ', '', $value ) );

				// h::log( $value_pairs );

				# loop through each pair
				foreach ( $value_pairs as $v_pair ) {

					// h::log( $v_pair ); // 'sm:medium'

					# split into name and value
					list( $value_key, $value_value ) = explode( ':', $v_pair, 2 );

					$value_array[ $value_key ] = $value_value;

				}

				// check if we have an array ##
				if ( is_array( $value_array ) ){

					$value = $value_array;

				}

			}
		 
			// $key might be in part__part format, so check ##
			if( false !== strpos( $key, '->' ) ){

				// explode, max 2 parts ##
				$md_key = explode( '->', $key, 2 );

				# if name already exists
				if( isset( $array[ $md_key[0] ][ $md_key[1] ] ) ) {

					# stick multiple values into an array
					if( is_array( $array[ $md_key[0] ][ $md_key[1] ] ) ) {
					
						$array[ $md_key[0] ][ $md_key[1] ][] = $value;
					
					} else {
					
						$array[ $md_key[0] ][ $md_key[1] ] = array( $array[ $md_key[0] ][ $md_key[1] ], $value );
					
					}

				# otherwise, simply stick it in a scalar
				} else {

					$array[ $md_key[0] ][ $md_key[1] ] = $value;

				}

			} else {

				# if name already exists
				if( isset($array[$key]) ) {

					# stick multiple values into an array
					if( is_array($array[$key]) ) {
					
						$array[$key][] = $value;
					
					} else {
					
						$array[$key] = array($array[$key], $value);
					
					}

				# otherwise, simply stick it in a scalar
				} else {

					$array[$key] = $value;

				}
			  
			}
		}
	  
		# return result array
		return $array;

	  }
	  


}
