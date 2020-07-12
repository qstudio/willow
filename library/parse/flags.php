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
	public static function get( $string = null, $use = 'tag' ){

		// sanity ##
		if(
			is_null( $string )
		){

			h::log( 'e:>Error in passed arguments.' );

		}

		// sanity ##
		// h::log( 't:>make flags::get() method, make $flags a property of parse.. ' );

		if(
			// strstr( $string, trim( willow\tags::g( 'fla_o' ) ) )
			// && strstr( $string, trim( willow\tags::g( 'fla_c' ) ) )
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
				case "tag" :

					// h::log( 'd:>Preparing flags for tag' );
					self::$flags = str_split( $flags );
					self::$flags = array_fill_keys( self::$flags, true );
					// h::log( self::$flags );

				break ;

				case "argument" :

					// h::log( 'd:>Preparing flags_args for argument' );
					self::$flags_args = str_split( $flags );
					self::$flags_args = array_fill_keys( self::$flags_args, true );
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




	public static function cleanup( $args = null ){

		$open = trim( willow\tags::g( 'fla_o' ) );
		$close = trim( willow\tags::g( 'fla_c' ) );

		// h::log( self::$markup['template'] );

		// strip all function blocks, we don't need them now ##
		$regex = \apply_filters( 
		 	'q/willow/parse/flags/cleanup/regex', 
			 // "/$open.*?$close/ms" 
			//  "/$open\s+.*?\s+$close/s"
			"~\\$open\s+(.*?)\s+\\$close~"
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

					h::log( $count .' comment tags removed...' );

				}

				// return nothing for cleanup ##
				return "";

			}, 
			self::$markup['template'] 
		);
		
	}


}
