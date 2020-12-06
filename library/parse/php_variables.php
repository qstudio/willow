<?php

namespace willow;

use willow;
use willow\render;
use willow\core;
use willow\core\helper as h;

class php_variables extends willow\parse {

	private static 

		$return,
		// $php_var_hash, 
		$php_var,
		$php_var_match, // full string matched ##
		$arguments,
		$class,
		$method,
		$php_var_array,
		$config_string
	
	;


	private static function reset(){

		$return = false; 
		// $php_var_hash = false; 
		$flags_php_variable = false;
		$php_var = false;
		$arguments = false;
		$class = false;
		$method = false;
		$php_var_array = false;
		$config_string = false;

	}


	public static function format( $match = null, $process = 'secondary' ){

		// sanity ##
		if(
			is_null( $match )
		){

			w__log( 'e:>No php variable match passed to format method' );

			return false;

		}

		$open = trim( willow\tags::g( 'php_var_o' ) );
		$close = trim( willow\tags::g( 'php_var_c' ) );

		// clear slate ##
		self::reset();

		// return entire function string, including tags for tag swap ##
		$php_var_match = core\method::string_between( $match, $open, $close, true );
		$php_var = core\method::string_between( $match, $open, $close );

		// w__log( '$php_var_match: '.$php_var_match );

		// look for flags ##
		$php_var = flags::get( self::$function, 'php_variable' );
		// $php_var = flags::get( $php_var, 'function' );
		// w__log( self::$flags_php_variable );
		// w__log( $php_var );

		// clean up ##
		$php_var = trim( $php_var );

		// w__log( 'e:>PHP Var: '.$php_var );

		// sanity ##
		if ( 
			! $php_var
			|| ! isset( $php_var ) 
		){

			w__log( 'e:>Error in returned match php variable' );

			return false; 

		}

		// $php_var_hash = $php_var; 
		// w__log( 'e:>VAR: '.$_GET['test'] );

		// $_GET ##
		if( false !== strpos( $php_var, '$_GET' ) ){

			// w__log( 'e:>IS a getter..' );

			if( 
				$argument = 
					false !== strpos( $php_var, '"' ) ? 
					core\method::string_between( $php_var, "\"", "\"" ) :
					core\method::string_between( $php_var, "\'", "\'" )
			){

				// w__log( 'Get argument: '.$argument );

				// sanitize ##
				$argument = core\method::sanitize( $argument );

				// w__log( 'Clean argument: '.$argument );

				if( $return = isset( $_GET[$argument] ) ? $_GET[$argument] : false  ){

					// sanitize ##
					$return = core\method::sanitize( $return );

					// w__log( 'RETURN: '.$return );

				}

			}

		// $_POST ##
		} elseif( false !== strpos( $php_var, '$_POST' ) ){

			w__log( 'e:>Willow does not return $_POST data to templates, use a PHP controller instead.' );

		// $_REQUEST ?? needed? ##
		} elseif( false !== strpos( $php_var, '$_REQUEST' ) ){

			w__log( 'e:>Willow does not return $_REQUEST data to templates, use a PHP controller instead.' );

		}

		if( $return ) {

			// filter ##
			// w__log( self::$flags_php_variable );
			if( 
				self::$flags_php_variable
				&& is_array( self::$flags_php_variable )
			){

				// w__log( self::$flags_php_variable );
				// w__log( self::$return );
				// bounce to filter::apply() ##
				$filter_return = filter\method::apply([ 
					'filters' 	=> self::$flags_php_variable, 
					'string' 	=> self::$return, 
					'use' 		=> 'php_function', // for filters ##
				]);

				// w__log( $filter_return );

				// check if filters changed value ##
				if( 
					$filter_return // return set ##
					&& '' != $filter_return // not empty ##
					&& $filter_return != self::$return // value chaged ##
				){

					w__log( 'd:>php_function fitlers changed value: '.$filter_return );

					// update class property ##
					self::$return = $filter_return;

				}

			}

			// w__log( 'hash set to: '.$php_var_hash );

			// w__log( 'e:>Replacing PHP variable: "'.$php_var_match.'" with value: '.$return );

			// $string = $return;

			// function returns which update the template also need to update the buffer_map, for later find/replace ##
			// Seems like a potential pain-point ##
			self::$markup_template = str_replace( $php_var_match, $return, self::$markup_template );

			// update markup for willow parse ##
			parse\markup::swap( $php_var_match, $return, 'php_variable', 'string', $process ); 

		}
		
		// clear slate ##
		self::reset();

	}




    /**
	 * Scan for functions in markup and add any required markup or call requested functions and capture output
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

			w__log( 'e:>Error in stored $markup' );

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

			w__log( self::$args['task'].'~>e:>Error in $markup' );

			return false;

		}

		// w__log('d:>'.$string);

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( willow\tags::g( 'php_var_o' ) );
		$close = trim( willow\tags::g( 'php_var_c' ) );

		// w__log( 'open: '.$open. ' - close: '.$close );

		$regex_find = \apply_filters( 
			'willow/parse/php_variables/regex/find', 
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

				w__log( 'e:>Error in returned matches array' );

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

					w__log( 'e:>Error in returned matches - no position' );

					continue;

				}

				// w__log( $matches );

				// take match ##
				$match = $matches[0][$match][0];

				// pass match to function handler ##
				self::format( $match, $process );

			}

		} else {

			// w__log( 'e:>No PHP Vars found' );

		}

	}



	public static function cleanup( $args = null, $process = 'secondary' ){

		$open = trim( willow\tags::g( 'php_var_o' ) );
		$close = trim( willow\tags::g( 'php_var_c' ) );

		// strip all function blocks, we don't need them now ##
		// // $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
		 	'q/render/parse/php_variable/cleanup/regex', 
		 	"/(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|$open.*?$close/ms" 
		// 	// "/{{#.*?\/#}}/ms"
		);

		// w__log( 'e:>Running Function Cleanup' );
		
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

			w__log( 'e:>Error in stored $markup' );

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

				// w__log( $matches );

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					w__log( 'd:>'.$count .' php variable tags removed...' );

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
