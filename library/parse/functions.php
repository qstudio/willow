<?php

namespace q\willow;

use q\willow;
use q\willow\render;
use q\willow\core;
use q\core\helper as h;

class functions extends willow\parse {

	private static 

		$return,
		$hash, 
		// $flags,
		$function,
		$function_match, // full string matched ##
		$arguments,
		$class,
		$method,
		$function_array,
		$config_string
		// $position
		// $is_global
	
	;


	private static function reset(){

		self::$return = false; 
		self::$hash = false; 
		self::$flags = false;
		self::$flags_args = false;
		self::$function = false;
		self::$arguments = false;
		self::$class = false;
		self::$method = false;
		self::$function_array = false;
		self::$config_string = false;
		// self::$position = false;
		// self::$is_global = false;

	}


	public static function format( $match = null ){

		// sanity ##
		if(
			is_null( $match )
		){

			h::log( 'e:>No function match passed to format method' );

			return false;

		}

		$open = trim( willow\tags::g( 'fun_o' ) );
		$close = trim( willow\tags::g( 'fun_c' ) );

		// clear slate ##
		self::reset();

		// return entire function string, including tags for tag swap ##
		self::$function_match = core\method::string_between( $match, $open, $close, true );
		self::$function = core\method::string_between( $match, $open, $close );

		// h::log( '$function_match: '.$function_match );

		// look for flags ##
		self::$function = flags::get( self::$function );
		// h::log( self::$flags );
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
		self::$hash = self::$function; // set hash to entire function, in case this has no config and is not class_method format ##
		// h::log( 'hash set to: '.$hash );

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

			// sub functions - functions passed in arguments string ##

			// clean up string -- remove all white space ##
			// $string = trim( $string );
			// $config_string = str_replace( ' ', '', $config_string );
			// h::log( 'd:> '.self::$config_string );

			// pass to argument handler ##
			self::$arguments = 
				willow\arguments::decode([ 
					'string' 	=> self::$config_string, 
					// 'field' 	=> $field_name, 
					// 'value' 	=> self::$function,
					// 'tag'		=> 'function'	
				]);

			// h::log( self::$arguments );
			// h::log( self::$flags_args );

			// h::log( $matches[0] );

			// $field = trim( core\method::string_between( $value, '{{ ', '[[' ) );
			// $function = str_replace( trim( tags::g( 'arg_o' )).$config_string.trim( tags::g( 'arg_c' )), '', $function );
			// h::log( 'function: '.$function );
			// $function = core\method::string_between( $function, trim( tags::g( 'fun_o' )), trim( tags::g( 'arg_o' )) );
			$function_explode = explode( trim( willow\tags::g( 'arg_o' )), self::$function );
			// h::log( $function_explode );
			self::$function = trim( $function_explode[0] );
			// $class = false;
			// $method = false;

			self::$hash = self::$function; // update hash to take simpler function name.. ##
			// h::log( 'hash updated to: '.$hash );
			// h::log( 'function: "'.self::$function.'"' );

			// if arguments are not in an array, take the whole string passed as the arguments ##
			if ( 
				! self::$arguments
				|| ! is_array( self::$arguments ) 
			) {

				// remove wrapping " quotation marks ## -- 
				// @todo, needs to be move elegant or based on if this was passed as a string argument from the template ##
				self::$config_string = trim( self::$config_string, '"' );

				// create required array structure + value
				// self::$arguments['markup']['template'] = self::$config_string;
				self::$arguments = self::$config_string;

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
			self::$hash = self::$method; 
			// h::log( 'hash updated again to: '.self::$hash );

			if ( 
				! self::$class 
				|| ! self::$method 
			){

				h::log( 'e:>Error in passed function name, stopping here' );

				return false;

			}

			// clean up class name @todo -- 
			self::$class = \q\core\method::sanitize( self::$class, 'php_class' );

			// clean up method name --
			self::$method = \q\core\method::sanitize( self::$method, 'php_function' );

			// h::log( 'class::method -- '.self::$class.'::'.self::$method );

			if ( 
				! class_exists( self::$class )
				// || ! method_exists( self::$class, self::$method ) // internal methods are found via callstatic lookup ##
				|| ! is_callable( self::$class, self::$method )
			){

				h::log( 'd:>Cannot find - class: '.self::$class.' - method: '.self::$method );

				return false;

			}	

			// make class__method an array ##
			self::$function_array = [ self::$class, self::$method ];

		// simple function string ##
		} else {

			// clean up function name ##
			self::$function = \q\core\method::sanitize( self::$function, 'php_function' );

			// try to locate function directly in global scope ##
			if ( ! function_exists( self::$function ) ) {
					
				h::log( 'd:>Cannot find function: '.self::$function );

				return false;

			}

		}

		// test what we have ##
		// h::log( 'd:>function: "'.self::$function.'"' );
		self::$hash = self::$hash.'.'.rand();
		// h::log( 'hash at end is...: '.self::$hash );

		// h::log( self::$arguments );

		// class and method set -- so call ## 
		if ( self::$class && self::$method ) {

			// h::log( 'd:>Calling class_method: '.self::$class.'::'.self::$method );

			// pass args, if set ##
			if( self::$arguments ){

				// h::log( 'passing args array to: '.self::$class.'::'.self::$method );
				// h::log( self::$arguments );

				// global function returns are pushed directly into buffer ##
				self::$return = self::$class::{ self::$method }( self::$arguments );
				// self::$buffer[ self::$hash ] = self::$return;

			} else { 

				// h::log( 'NOT passing args array to: '.self::$class.'::'.self::$method );

				// global function returns are pushed directly into buffer ##
				self::$return = self::$class::{ self::$method }();
				// self::$buffer[ self::$hash ] = self::$return;

			}

		} else {

			// h::log( 'd:>Calling function: '.self::$function );

			// pass args, if set ##
			if( self::$arguments ){

				// h::log( 'passing args array to: '.self::$function );
				// h::log( self::$arguments );

				self::$return = call_user_func( self::$function, self::$arguments );

				// render\fields::define([
				// 	self::$hash => self::$return
				// ]);

				// also, adding to buffer ## @TODO -- check this is ok ##
				// self::$buffer[ self::$hash ] = self::$return;

			} else {

				// h::log( 'NOT passing args array to: '.self::$function );

				// global functions skip internal processing and return their results directly to the buffer ##
				self::$return = call_user_func( self::$function ); // NOTE that calling this function directly was failing silently ##
				// self::$buffer[ self::$hash ] = self::$return;

			}

		}

		if ( ! isset( self::$return ) ) {

			h::log( 'd:>Function "'.self::$function_match.'" did not return a value, perhaps it is a hook or an action.' );

			willow\markup::swap( self::$function_match, '', 'function', 'string' );

			return false;

		}

		// we need to ensure $return is a string ##
		// h::log( 't:>Validate that $string is a string or integer.. if not, reject ??' );
		if(
			is_array( self::$return )
		){

			h::log( 'Return is in an array format, trying to convert array values to string' );

			self::$return = implode ( " ", array_values( self::$return ) );
			self::$return = trim( self::$return );

		}

		if(
			! is_string( self::$return )
			&& ! is_integer( self::$return )
		){

			h::log( 'Return is not a string or integer, so rejecting' );

			willow\markup::swap( self::$function_match, '', 'function', 'string' );

			return false;

		}

		// h::log( 'd:>'.self::$function.' -> '.self::$return );

		// add to buffer ##
		self::$buffer[ self::$hash ] = self::$return;

		// add fields - perhaps we do not always need this -- perhaps based on [r] flag ##
		render\fields::define([
			self::$hash => self::$return
		]);

		// replace tag with raw return value from function
		if( 
			(
				isset( $args['config']['embed'] )
				&& true === isset( $args['config']['embed'] )
			) 
			||
			isset( self::$flags['r'] ) 
		){


			// if ( ! isset( self::$return ) ) {

			// 	h::log( 'e:>Function "'.self::$function_match.'" did not return a value' );

			// 	return false;

			// }

			// h::log( 'e:>Replacing function: "'.self::$function_match.'" with function return value: '.self::$return );
			$string = self::$return;

			willow\markup::swap( self::$function_match, $string, 'function', 'string' ); // '{{ '.$field.' }}'

		} else {

			// finally -- add a variable "{{ $field }}" where the function tag block was in markup->template ##
			$variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => self::$hash, 'close' => 'var_c' ]);
			// variable::set( $variable, $position, 'variable' ); // '{{ '.$field.' }}'

			willow\markup::swap( self::$function_match, $variable, 'function', 'variable' ); // '{{ '.$field.' }}'

		}
		
		// clear slate ##
		self::reset();

	}




    /**
	 * Scan for functions in markup and add any required markup or call requested functions and capture output
	 * 
	 * @since 4.1.0
	*/
    public static function prepare( $args = null ){

		// h::log( 't:>TODO -- functions are always global, either function() or class::method format and must return data directly' );

		// h::log( $args['key'] );

		// sanity -- method requires requires ##
		if ( 
			! isset( self::$markup )
			|| ! is_array( self::$markup )
			|| ! isset( self::$markup['template'] )
		){

			h::log( 'e:>Error in stored $markup' );

			return false;

		}

		// get markup ##
		$string = self::$markup['template'];

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
		$open = trim( willow\tags::g( 'fun_o' ) );
		$close = trim( willow\tags::g( 'fun_c' ) );

		// h::log( 'open: '.$open. ' - close: '.$close. ' - end: '.$end );

		$regex_find = \apply_filters( 
			'q/willow/parse/functions/regex/find', 
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
				self::format( $match );

			}

		}

	}



	public static function cleanup( $args = null ){

		$open = trim( willow\tags::g( 'fun_o' ) );
		$close = trim( willow\tags::g( 'fun_c' ) );

		// strip all function blocks, we don't need them now ##
		// // $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
		 	'q/render/parse/function/cleanup/regex', 
		 	"/$open.*?$close/ms" 
		// 	// "/{{#.*?\/#}}/ms"
		);

		// h::log( 'e:>Running Function Cleanup' );
		
		// self::$markup['template'] = preg_replace( $regex, "", self::$markup['template'] ); 

		// use callback to allow for feedback ##
		self::$markup['template'] = preg_replace_callback(
			$regex, 
			function($matches) {
				
				if( ! isset( $matches[1] )) {

					return "";

				}

				// h::log( $matches );

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					h::log( 'd:>'.$count .' function tags removed...' );

				}

				// return nothing for cleanup ##
				return "";

			}, 
			self::$markup['template'] 
		);

	}


}
