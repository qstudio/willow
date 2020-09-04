<?php

namespace q\willow;

use q\willow;
use q\willow\render;
use q\willow\core;
use q\willow\core\helper as h;

class php_functions extends willow\parse {

	private static 

		$return,
		$function_hash, 
		$function,
		$function_match, // full string matched ##
		$arguments,
		$class,
		$method,
		$function_array,
		$config_string
	
	;


	private static function reset(){

		self::$return = false; 
		self::$function_hash = false; 
		self::$flags_function = false;
		self::$function = false;
		self::$arguments = false;
		self::$class = false;
		self::$method = false;
		self::$function_array = false;
		self::$config_string = false;

	}


	public static function format( $match = null, $process = 'internal' ){

		// sanity ##
		if(
			is_null( $match )
		){

			h::log( 'e:>No function match passed to format method' );

			return false;

		}

		$open = trim( willow\tags::g( 'php_fun_o' ) );
		$close = trim( willow\tags::g( 'php_fun_c' ) );

		// clear slate ##
		self::reset();

		// return entire function string, including tags for tag swap ##
		self::$function_match = core\method::string_between( $match, $open, $close, true );
		self::$function = core\method::string_between( $match, $open, $close );

		// h::log( '$function_match: '.$function_match );

		// look for flags ##
		self::$function = flags::get( self::$function, 'php_function' );
		// h::log( self::$flags_function );
		// h::log( self::$function );

		// clean up ##
		self::$function = trim( self::$function );

		// h::log( 'Function: '.self::$function );

		// sanity ##
		if ( 
			! self::$function
			|| ! isset( self::$function ) 
		){

			h::log( 'e:>Error in returned match function' );

			return false; 

		}

		// default args ##
		self::$function_hash = self::$function; // set hash to entire function, in case this has no config and is not class_method format ##
		// h::log( 'hash set to: '.$function_hash );

		// $config_string = core\method::string_between( $value, '[[', ']]' )
		self::$config_string = core\method::string_between( 
			self::$function, 
			trim( willow\tags::g( 'arg_o' )), 
			trim( willow\tags::g( 'arg_c' )) 
		);

		// go with it ##
		if ( 
			self::$config_string 
		){

			// pass to argument handler ##
			self::$arguments = willow\arguments::decode( self::$config_string );

			$function_explode = explode( trim( willow\tags::g( 'arg_o' )), self::$function );
			self::$function = trim( $function_explode[0] );

			self::$function_hash = self::$function; // update hash to take simpler function name.. ##

			// if arguments are not in an array, take the whole string passed as the arguments ##
			if ( 
				! self::$arguments
				|| ! is_array( self::$arguments ) 
			) {

				// perhaps args is a simple csv, check and break ##
				if(
					false !== strpos( self::$config_string, ',' )
				){

					h::log('d:>Args are in csv: '.self::$config_string );

					$config_explode = explode( ',', self::$config_string );
					// $config_explode = array_map( trim, $config_explode );
					
					$config_explode = array_map( function( $item ) {
						return trim( $item, ' ' ); // trim whitespace, single and double quote ## ' \'"'
					}, $config_explode );

					// h::log( $config_explode );
					self::$arguments = $config_explode;

				} else {

					// h::log('d:>Args are not an array or csv, to taking the whole string');

					// remove wrapping " quotation marks ## -- 
					// @todo, needs to be move elegant or based on if this was passed as a string argument from the template ##
					self::$config_string = trim( self::$config_string, '"' );

					// create required array
					// self::$arguments = [ self::$config_string ];
					self::$arguments = self::$config_string;

				}

			}
			
		}

		// function name might still contain opening and closing args brakets, which were empty - so remove them ##
		self::$function = str_replace( [
				trim( willow\tags::g( 'arg_o' )), 
				trim( willow\tags::g( 'arg_c' )) 
			], '',
			self::$function 
		);

		// check if we are being passed a simple string function, or a class::method
		if(
			strpos( self::$function, '::' )
		){

			// h::log( 'function is class::method' );
			// break function into class::method parts ##
			list( self::$class, self::$method ) = explode( '::', self::$function );

			// update hash ##
			self::$function_hash = self::$method; 
			// h::log( 'hash updated again to: '.self::$function_hash );

			if ( 
				! self::$class 
				|| ! self::$method 
			){

				h::log( 'e:>Error in passed function name, stopping here' );

				return false;

			}

			// clean up class name @todo -- 
			self::$class = core\method::sanitize( self::$class, 'php_class' );

			// clean up method name --
			self::$method = core\method::sanitize( self::$method, 'php_function' );

			// h::log( 'class::method -- '.self::$class.'::'.self::$method );

			if ( 
				! class_exists( self::$class )
				|| ! method_exists( self::$class, self::$method ) // internal methods are found via callstatic lookup ##
				|| ! is_callable( self::$class, self::$method )
			){

				h::log( 'e:>Cannot find PHP Function --> '.self::$class.'::'.self::$method );

				return false;

			}	

			// make class__method an array ##
			self::$function_array = [ self::$class, self::$method ];

		// simple function string ##
		} else {

			// clean up function name ##
			self::$function = core\method::sanitize( self::$function, 'php_function' );

			// try to locate function directly in global scope ##
			if ( ! function_exists( self::$function ) ) {
					
				h::log( 'd:>Cannot find function: '.self::$function );

				return false;

			}

		}

		// final hash update ##
		self::$function_hash = self::$function_hash.'.'.rand();

		// class and method set -- so call ## 
		if ( self::$class && self::$method ) {

			// h::log( 'd:>Calling class_method: '.self::$class.'::'.self::$method );

			// pass args, if set ##
			if( self::$arguments ){

				// h::log( 'passing args array to: '.self::$class.'::'.self::$method );
				// h::log( self::$arguments );

				// global function returns are pushed directly into buffer ##
				self::$return = self::$class::{ self::$method }( self::$arguments );

			} else { 

				// h::log( 'NOT passing args array to: '.self::$class.'::'.self::$method );

				// global function returns are pushed directly into buffer ##
				self::$return = self::$class::{ self::$method }();

			}

		} else {

			// h::log( 'd:>Calling function: '.self::$function );

			// pass args, if set ##
			if( self::$arguments ){

				// h::log( 'passing args array to: '.self::$function );
				// h::log( self::$arguments );
				// self::$return = call_user_func( self::$function, self::$arguments );
				// if( ! is_array() )
				self::$return = call_user_func_array( self::$function, ( array )self::$arguments );

			} else {

				// h::log( 'NOT passing args array to: '.self::$function );

				// global functions skip internal processing and return their results directly to the buffer ##
				self::$return = call_user_func( self::$function ); // NOTE that calling this function directly was failing silently ##

			}

		}

		// escape ##
		h::log( 't:>NOTE, that escape is being called here, with old flag format.... and seems mixed html OR js ??' );
		if( 
			isset( self::$flags_function['e'] ) 
		){

			// JS or HTML ?? @todo ##
			self::$return = \esc_js( self::$return );

		}

		// h::log( self::$return );

		if ( ! isset( self::$return ) ) {

			h::log( 'd:>Function "'.self::$function_match.'" did not return a value, perhaps it is a hook or an action.' );

			parse\markup::swap( self::$function_match, '', 'php_function', 'string', $process );

			return false;

		}

		// we need to ensure $return is a string ##
		if(
			is_array( self::$return )
		){

			// h::log( self::$return );
			h::log( '"'.$open.' '.self::$function.' '.$close.'" returned an array, Willow will try to convert to a string' );

			self::$return = implode ( " ", array_values( self::$return ) );
			self::$return = trim( self::$return );
			// h::log( 'return: '.self::$return );

		}

		// return is still nt a string ##
		if(
			! is_string( self::$return )
			&& ! is_integer( self::$return )
		){

			h::log( 'Return from "'.self::$function.'" is not a string or integer, so rejecting' );
			// h::log( self::$return );

			parse\markup::swap( self::$function_match, '', 'php_function', 'string', $process );

			return false;

		}

		// add fields - perhaps we do not always need this -- perhaps based on [r] flag ##
		render\fields::define([
			self::$function_hash => self::$return
		]);

		// replace function tag with raw return value for willw parse ##
		if( 
			isset( self::$flags_function['r'] ) 
		){

			// h::log( 'e:>Replacing function: "'.self::$function_match.'" with function return value: '.self::$return );

			$string = self::$return;

			// function returns which update the template also need to update the buffer_map, for later find/replace ##
			// Seems like a potential pain-point ##
			self::$buffer_map[0] = str_replace( self::$function_match, $string, self::$buffer_map[0] );

			// update markup for willow parse ##
			parse\markup::swap( self::$function_match, $string, 'php_function', 'string', $process ); // '{{ '.$field.' }}'

		} else {

			// add data to buffer map ##
			self::$buffer_map[] = [
				'tag'		=> self::$function_match,
				'output'	=> self::$return,
				'parent'	=> false,
			];

		}
		
		// clear slate ##
		self::reset();

	}




    /**
	 * Scan for functions in markup and add any required markup or call requested functions and capture output
	 * 
	 * @since 4.1.0
	*/
    public static function prepare( $args = null, $process = 'internal' ){

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

			h::log( 'e:>Error in stored $markup' );

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
		$open = trim( willow\tags::g( 'php_fun_o' ) );
		$close = trim( willow\tags::g( 'php_fun_c' ) );

		// h::log( 'open: '.$open. ' - close: '.$close );

		$regex_find = \apply_filters( 
			'q/willow/parse/php_functions/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
			// "/{{#(.*?)\/#}}/s" 
		);

		// h::log( 't:> allow for badly spaced tags around sections... whitespace flexible..' );
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

				// take match ##
				$match = $matches[0][$match][0];

				// pass match to function handler ##
				self::format( $match, $process );

			}

		}

	}



	public static function cleanup( $args = null, $process = 'internal' ){

		$open = trim( willow\tags::g( 'php_fun_o' ) );
		$close = trim( willow\tags::g( 'php_fun_c' ) );

		// strip all function blocks, we don't need them now ##
		// // $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
		 	'q/render/parse/php_functions/cleanup/regex', 
		 	"/(?s)<pre[^<]*>.*?<\/pre>(*SKIP)(*F)|$open.*?$close/ms" 
		// 	// "/{{#.*?\/#}}/ms"
		);

		// h::log( 'e:>Running Function Cleanup' );
		
		// self::$markup['template'] = preg_replace( $regex, "", self::$markup['template'] ); 

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

			h::log( 'e:>Error in stored $markup' );

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
				
				if( ! isset( $matches[1] )) {

					return "";

				}

				// h::log( $matches );

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					h::log( 'd:>'.$count .' php function tags removed...' );

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
