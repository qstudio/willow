<?php

namespace q\willow;

// use q\core;
use q\willow;
use q\willow\core;
use q\core\helper as h;
// use q\ui;
use q\render; // @TODO ##

class flags extends willow\parse {
	
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

		// use callback to allow for feedback ##
		self::$markup['template'] = preg_replace_callback(
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
			self::$markup['template'] 
		);
		
	}


}
