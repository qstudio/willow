<?php

namespace willow;

use willow;
use willow\render;
use willow\core;
use willow\core\helper as h;

class i18n extends willow\parse {

	private static 

		$textdomain,
		$return,
		$i18n,
		$i18n_match, // full string matched ##
		$arguments,
		$class,
		$method,
		$i18n_array
		// $config_string
	
	;


	private static function reset(){

		$return = false; 
		// $flags_i18n = false;
		$i18n = false;
		$arguments = false;
		$class = false;
		$method = false;
		$i18n_array = false;
		// $config_string = false;

	}


	public static function textdomain(){

		if( self::$textdomain ){ return self::$textdomain; }

		// load via filter -- variable textdomains are a no no.. but this could come from any theme or plugin... ?? ##
		return self::$textdomain = \apply_filters( 'willow/parse/i18n/textdomain', 'q-textdomain' );

	}


	
	/**
	 * Check if passed string includes translateable strings 
	*/
	public static function has( $string = null ){

		// @todo - sanity ##
		if(
			is_null( $string )
		){

			h::log( 'e:>No string passed to method' );

			return false;

		}

		// check for opening and closing tags
		if(
			strpos( $string, trim( willow\tags::g( 'i18n_o' )) ) !== false
			&& strpos( $string, trim( willow\tags::g( 'i18n_c' )) ) !== false
		){

			// $loo_o = strpos( $string, trim( willow\tags::g( 'i18n_o' )) );
			// $loo_c = strrpos( $string, trim( willow\tags::g( 'i18n_c' )) );

			// // h::log( 'd:>Found opening loo_o @ "'.$loo_o.'" and closing loo_c @ "'.$loo_c.'"'  ); 

			// // get string between opening and closing args ##
			// $return_string = substr( 
			// 	$string, 
			// 	( $loo_o + strlen( trim( willow\tags::g( 'loo_o' ) ) ) ), 
			// 	( $loo_c - $loo_o - strlen( trim( willow\tags::g( 'loo_c' ) ) ) ) ); 

			// $return_string = willow\tags::g( 'loo_o' ).$return_string.willow\tags::g( 'loo_c' );

			// h::log( 'e:>$string: "'.$return_string.'"' );

			return true;

		}

		// no ##
		return false;

	}


	public static function format( $match = null, $process = 'secondary' ){

		// sanity ##
		if(
			is_null( $match )
		){

			h::log( 'e:>No i18n match passed to format method' );

			return false;

		}

		$open = trim( willow\tags::g( 'i18n_o' ) );
		$close = trim( willow\tags::g( 'i18n_c' ) );

		// clear slate ##
		self::reset();

		// return entire function string, including tags for tag swap ##
		$i18n_match = core\method::string_between( $match, $open, $close, true );
		$i18n = core\method::string_between( $match, $open, $close );

		// h::log( '$i18n_match: '.$i18n_match );

		// look for flags ##
		// $i18n = flags::get( self::$function, 'i18n' );
		// $i18n = flags::get( $i18n, 'function' );
		// h::log( self::$flags_i18n );
		// h::log( $i18n );

		// clean up ##
		$i18n = trim( $i18n );

		// h::log( 'e:>i18n: '.$i18n );

		// sanity ##
		if ( 
			! $i18n
			|| ! isset( $i18n ) 
		){

			h::log( 'e:>Error in returned match i18n' );

			return false; 

		}

		// php_function tags ##
		// $return_open = willow\tags::g( 'php_fun_o' );
		// $return_close = willow\tags::g( 'php_fun_c' );

		// h::log( 'e:>$i18n: '.$i18n );

		// concat return string ##
		// $return = $return_open." [return] \__{+ ".$i18n." +}".$return_close;

		// gettext namespace ##
		// $textdomain = \apply_filters( 'willow/parse/i18n/textdomain', 'q-textdomain' );

		// run string via i18n callback ##
		$return = \__( $i18n, 'willow' ); // self::textdomain()

		// h::log( 'e:>'.$return );

		// function returns which update the template also need to update the buffer_map, for later find/replace ##
		// Seems like a potential pain-point ##
		self::$markup_template = str_replace( $i18n_match, $return, self::$markup_template );

		// update markup for willow parse ##
		parse\markup::swap( $i18n_match, $return, 'i18n', 'string', $process );
		
		// clear slate ##
		self::reset();

	}




    /**
	 * Scan for translatable strings in markup and convert to \_e( 'string' ) functions and capture output
	 * 
	 * @since 4.1.0
	*/
    public static function prepare( $args = null, $process = 'secondary' ){

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

			h::log( 'e:>Error in stored $markup' );

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

		// sanity ##
		if (  
			! $string
			|| is_null( $string )
		){

			h::log( self::$args['task'].'~>e:>Error in $markup' );

			return false;

		}

		// h::log('d:>'.$string);

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( willow\tags::g( 'i18n_o' ) );
		$close = trim( willow\tags::g( 'i18n_c' ) );

		// h::log( 'open: '.$open. ' - close: '.$close );

		$regex_find = \apply_filters( 
			'willow/parse/i18n/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
			// "/{{#(.*?)\/#}}/s" 
		);

		if ( 
			preg_match_all( $regex_find, $string, $matches, PREG_OFFSET_CAPTURE ) 
		){

			// sanity ##
			if ( 
				! $matches
				|| ! isset( $matches[1] ) 
				|| ! $matches[1]
			){

				h::log( 'e:>Error in returned matches array' );

				return false;

			}

			foreach( $matches[1] as $match => $value ) {

				// position to add placeholder ##
				if ( 
					! is_array( $value )
					|| ! isset( $value[0] ) 
					|| ! isset( $value[1] ) 
					|| ! isset( $matches[0][$match][1] )
				) {

					h::log( 'e:>Error in returned matches - no position' );

					continue;

				}

				// h::log( $matches );

				// take match ##
				$match = $matches[0][$match][0];

				// pass match to function handler ##
				self::format( $match, $process );

			}

		} else {

			// h::log( 'e:>No translation strings found' );

		}

	}



	public static function cleanup( $args = null, $process = 'secondary' ){

		$open = trim( willow\tags::g( 'i18n_o' ) );
		$close = trim( willow\tags::g( 'i18n_c' ) );

		// strip all function blocks, we don't need them now ##
		// // $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
		 	'q/render/parse/i18n/cleanup/regex', 
		 	"/(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|$open.*?$close/ms" 
		// 	// "/{{#.*?\/#}}/ms"
		);

		// h::log( 'e:>Running Function Cleanup' );
		
		// self::$markup['template'] = preg_replace( $regex, "", self::$markup['template'] ); 

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

			h::log( 'e:>Error in stored $markup' );

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
				
				if( ! isset( $matches[1] )) {

					return "";

				}

				// h::log( $matches );

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					h::log( 'd:>'.$count .' i18n tags removed...' );

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
