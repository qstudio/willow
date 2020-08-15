<?php

namespace q\willow;

use q\willow;
use q\willow\core;
use q\willow\core\helper as h;

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

			
			$fla_o = strpos( $string, trim( willow\tags::g( 'fla_o' )) );
			$fla_c = strrpos( $string, trim( willow\tags::g( 'fla_c' )) );
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

			h::log( self::$args['task'].'~>n:>Found opening fla_o @ "'.$fla_o.'" and closing fla_c @ "'.$fla_c.'"'  ); 

			return true;

		}

		// no ##
		return false;

	}


	
	/*
	Decode flags passed in string

	Requirements: 

	[seg] = split, escape, global
	[a] = array
	*/
	public static function get( $string = null, $use = 'willow' ){

		// sanity ##
		if(
			is_null( $string )
		){

			h::log( 'e:>Error in passed arguments.' );

		}

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

			// h::log( 'd:>FOUND flags in string: '.$string );
			// h::log( $flags );

			// assign flags based on use-case ##
			switch( $use ) {

				default :
				case "willow" :

					// h::log( 'd:>Preparing flags for tag' );
					self::$flags_willow = str_split( $flags );
					self::$flags_willow = array_fill_keys( self::$flags_willow, true );
					// h::log( self::$flags );

				break ;

				case "php_function" :

					// h::log( 'd:>Preparing flags for tag' );
					self::$flags_function = str_split( $flags );
					self::$flags_function = array_fill_keys( self::$flags_function, true );
					// h::log( self::$flags );

				break ;

				case "comment" :

					// h::log( 'd:>Preparing flags for tag' );
					self::$flags_comment = str_split( $flags );
					self::$flags_comment = array_fill_keys( self::$flags_comment, true );
					// h::log( self::$flags );

				break ;

				case "variable" :

					// h::log( 'd:>Preparing flags for tag' );
					self::$flags_variable = str_split( $flags );
					self::$flags_variable = array_fill_keys( self::$flags_variable, true );
					// h::log( self::$flags );

				break ;

				case "argument" :

					// h::log( 'd:>Preparing flags_args for argument' );
					self::$flags_argument = str_split( $flags );
					self::$flags_argument = array_fill_keys( self::$flags_argument, true );
					// h::log( self::$flags );

				break ;

			}

			$flags_all = core\method::string_between( $string, trim( willow\tags::g( 'fla_o' ) ), trim( willow\tags::g( 'fla_c' ) ), true );

			// remove flags ##
			$string = str_replace( $flags_all, '', $string );

			// kick it back ##
			return $string;

		}

		// kick it back whole, as no flags found ##
		return $string;
		
	}




	public static function cleanup( $args = null, $process = 'internal' ){

		$open = trim( willow\tags::g( 'fla_o' ) );
		$close = trim( willow\tags::g( 'fla_c' ) );

		// h::log( self::$markup['template'] );

		// strip all function blocks, we don't need them now ##
		$regex = \apply_filters( 
		 	'q/willow/parse/flags/cleanup/regex', 
			"/\\$open.*?\\$close/"
		);

		// sanity -- method requires requires ##
		if ( 
			(
				'internal' == $process
				&& (
					! isset( self::$markup )
					|| ! is_array( self::$markup )
					|| ! isset( self::$markup['template'] )
				)
			)
			||
			(
				'buffer' == $process
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
			case "internal" :

				// get markup ##
				$string = self::$markup['template'];

			break ;

			case "buffer" :

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
			case "internal" :

				// set markup ##
				self::$markup['template'] = $string;

			break ;

			case "buffer" :

				// set markup ##
				self::$buffer_markup = $string;

			break ;

		} 
		
	}


}
