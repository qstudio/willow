<?php

namespace willow;

use willow;
use willow\core;
use willow\filter;
use willow\core\helper as h;

class flags extends willow\parse {


	/**
	 * Check if passed string contains flags
	*/
	public static function has( $string = null ){

		// @todo - sanity ##
		if(
			is_null( $string )
		){

			h::log( 'e:>No string passed to method' );

			return false;

		}

		// alternative method - get position of arg_o and position of LAST arg_c ( in case the string includes additional args )
		if(
			strpos( $string, trim( willow\tags::g( 'fla_o' )) ) !== false
			&& strpos( $string, trim( willow\tags::g( 'fla_c' )) ) !== false
			// @TODO --- this could be more stringent, testing ONLY the first + last 3 characters of the string ??
		){

			// $fla_o = strpos( $string, trim( willow\tags::g( 'fla_o' )) );
			// $fla_c = strrpos( $string, trim( willow\tags::g( 'fla_c' )) );
			/*
			h::log( 'e:>Found opening loo_o @ "'.$loo_o.'" and closing loo_c @ "'.$loo_c.'"'  ); 

			// get string between opening and closing args ##
			$return_string = substr( 
				$string, 
				( $loo_o + strlen( trim( willow\tags::g( 'loo_o' ) ) ) ), 
				( $loo_c - $loo_o - strlen( trim( willow\tags::g( 'loo_c' ) ) ) ) ); 

			$return_string = willow\tags::g( 'loo_o' ).$return_string.willow\tags::g( 'loo_c' );

			// h::log( 'e:>$string: "'.$return_string.'"' );

			return $return_string;
			*/

			// h::log( 'd:>Found opening fla_o @ "'.$fla_o.'" and closing fla_c @ "'.$fla_c.'"'  ); 

			return true;

		}

		// no ##
		return false;

	}


	
	/*
	Decode flags passed in string

	Requirements: 

	[ esc_html, strip_tags ] = split, escape etc ##
	[ array ] = array
	*/
	public static function get( $string = null, $use = 'willow' ){

		// sanity ##
		if(
			is_null( $string )
		){

			h::log( 'e:>Error in passed arguments.' );

		}

		// h::log( $string );

		// sanity ##
		if(
			core\method::starts_with( $string, trim( willow\tags::g( 'fla_o' ) ) )
			&& $flags = core\method::string_between( $string, trim( willow\tags::g( 'fla_o' ) ), trim( willow\tags::g( 'fla_c' ) ) )
		){

			$flags = trim(
				core\method::string_between( 
					$string, 
					trim( willow\tags::g( 'fla_o' ) ), 
					trim( willow\tags::g( 'fla_c' ) ) 
				)
			);

			// prepare flags / filters ##
			$flags_array = filter\method::prepare([ 'filters' => $flags, 'use' => $use ] );
			
			// h::log( $flags_array );
			// h::log( 'use: '.$use );

			// assign filters based on use-case ##
			switch( $use ) {

				default :
				case "willow" :

					// @todo - validate that flags are allowed against self::$flags_willows ##

					self::$flags_willow = $flags_array;

				break ;

				case "php_function" :

					// @todo - validate that flags are allowed against self::$flags_php ##

					self::$flags_php_function = $flags_array;

				break ;

				case "php_variable" :

					// @todo - validate that flags are allowed against self::$flags_php ##

					self::$flags_php_variable = $flags_array;

				break ;

				case "comment" :

					// @todo - validate that flags are allowed against self::$flags_comment ##

					self::$flags_comment = $flags_array;

				break ;

				case "variable" :

					// varialbe flags are validated just before they are applied ##

					self::$flags_variable = $flags_array;

				break ;

				case "argument" :

					// @todo - validate that flags are allowed again self::$flags_argument ##

					self::$flags_argument = $flags_array;

				break ;

			}

			// get entire string, with tags ##
			$flags_all = core\method::string_between( $string, trim( willow\tags::g( 'fla_o' ) ), trim( willow\tags::g( 'fla_c' ) ), true );

			// remove flags from passed string ##
			$string = str_replace( $flags_all, '', $string );

			// kick it back ##
			return $string;

		}

		// kick it back whole, as no flags found ##
		return $string;
		
	}




	public static function cleanup( $args = null, $process = 'secondary' ){

		$open = trim( willow\tags::g( 'fla_o' ) );
		$close = trim( willow\tags::g( 'fla_c' ) );

		// h::log( self::$markup['template'] );

		// strip all function blocks, we don't need them now ##
		$regex = \apply_filters( 
		 	'willow/parse/flags/cleanup/regex', 
			"/\\$open.*?\\$close/"
		);

		// sanity -- method requires requires ##
		if ( 
			(
				'secondary' == $process
				&& (
					! isset( self::$markup )
					|| ! is_array( self::$markup )
					|| ! isset( self::$markup['template'] )
				)
			)
			||
			(
				'primary' == $process
				&& (
					! isset( self::$buffer_markup )
				)
			)
		){

			h::log( 'e:>Error in stored $markup: '.$process );

			return false;

		}

		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "secondary" :

				// get markup ##
				$string = self::$markup['template'];

			break ;

			case "primary" :

				// get markup ##
				$string = self::$buffer_markup;

			break ;

		} 

		// use callback to allow for feedback ##
		$string = preg_replace_callback(
			$regex, 
			function($matches) {
				
				// h::log( $matches );
				if ( 
					! $matches 
					|| ! is_array( $matches )
					|| ! isset( $matches[1] )
				){

					return false;

				}

				// h::log( $matches );

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					h::log( $count .' flags removed...' );

				}

				// return nothing for cleanup ##
				return "";

			}, 
			$string
		);

		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "secondary" :

				// set markup ##
				self::$markup['template'] = $string;

			break ;

			case "primary" :

				// set markup ##
				self::$buffer_markup = $string;

			break ;

		} 
		
	}


}
