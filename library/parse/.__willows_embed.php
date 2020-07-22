<?php

namespace q\willow;

use q\willow;
use q\willow\render;
use q\willow\core;
use q\core\helper as h;

class willows extends willow\parse {

	private static 

		$embed,
		$context,
		$task,
		$hash, 
		// $flags_willow,
		$willow,
		$willow_match, // full string matched ##
		$arguments,
		$class,
		$method,
		$willow_array,
		$config_string,
		$return
		// $level = '1'
		// $position
		// $is_global
	
	;


	private static function reset(){

		self::$embed = false;
		self::$hash = false; 
		self::$flags_willow = false;
		// self::$flags_argument = false;
		self::$willow = false;
		self::$arguments = []; // NOTE, this is now an empty array ##
		self::$class = false;
		self::$method = false;
		self::$willow_array = false;
		self::$config_string = false;
		self::$return = false;
		self::$context = false;
		self::$task = false;
		// self::$level = 0;
		// self::$position = false;
		// self::$is_global = false;

	}



	

	/**
	 * Check if passed string is a willow 
	*/
	public static function is( $string = null ){

		// @todo - sanity ##
		if(
			is_null( $string )
		){

			h::log( 'e:>No string passed to method' );

			return false;

		}

		// alternative method - get position of arg_o and position of LAST arg_c ( in case the string includes additional args )
		if(
			strpos( $string, trim( willow\tags::g( 'wil_o' )) ) !== false
			&& strrpos( $string, trim( willow\tags::g( 'wil_c' )) ) !== false
			// @TODO --- this could be more stringent, testing ONLY the first + last 3 characters of the string ??
		){

			/*
			$loo_o = strpos( $string, trim( willow\tags::g( 'loo_o' )) );
			$loo_c = strrpos( $string, trim( willow\tags::g( 'loo_c' )) );

			h::log( 'e:>Found opening loo_o @ "'.$loo_o.'" and closing loo_c @ "'.$loo_c.'"'  ); 

			// get string between opening and closing args ##
			$return_string = substr( 
				$string, 
				( $loo_o + strlen( trim( willow\tags::g( 'loo_o' ) ) ) ), 
				( $loo_c - $loo_o - strlen( trim( willow\tags::g( 'loo_c' ) ) ) ) ); 

			h::log( 'e:>$string: "'.$return_string .'"' );

			return $return_string;
			*/

			return true;

		}

		// no ##
		return false;

	}




	/**
	 * Format single willow
	 * 
	 * @since 4.1.0
	*/
	public static function format( $match = null, $args = null ){

		// sanity ##
		if(
			is_null( $match )
		){

			h::log( 'e:>No function match passed to format method' );

			return false;

		}

		// h::log( $args );

		$open = trim( willow\tags::g( 'wil_o' ) );
		$close = trim( willow\tags::g( 'wil_c' ) );

		// clear slate ##
		self::reset();

		// return entire function string, including tags for tag swap ##
		self::$willow_match = core\method::string_between( $match, $open, $close, true );
		self::$willow = core\method::string_between( $match, $open, $close );

		// h::log( '$willow_match: '.self::$willow_match );

		// look for flags ##
		self::$willow = flags::get( self::$willow, 'willow' );
		// h::log( self::$flags_willow );
		// h::log( 'd:>Willow: '.self::$willow );
		// h::log( 'd:>FORMAT --> Context: '.$args['context'].'~'.$args['task'] );

		// clean up ##
		self::$willow = trim( self::$willow );

		// h::log( 'willow: '.self::$willow ); 

		// sanity ##
		if ( 
			! self::$willow
			|| ! isset( self::$willow ) 
		){

			h::log( 'e:>Error in returned match function' );

			return false; 

		}

		if(
			false === strpos( self::$willow, '~' ) // '::' ##
		){

			h::log( 'e:>Error all willows must be in context::task format' );

			return false; 

		}

		// h::log( self::$args );

		// default args ##
		self::$hash = self::$willow; // set hash to entire function, in case this has no config and is not class_method format ##
		// h::log( 'hash set to: '.$hash );

		// $config_string = core\method::string_between( $value, '[[', ']]' )
		/*
		self::$config_string = core\method::string_between( 
			self::$willow, 
			trim( willow\tags::g( 'arg_o' )), 
			trim( willow\tags::g( 'arg_c' )) 
		);
		*/

		// alternative method - get position of arg_o and position of LAST arg_c ( in case the string includes additional args )
		if(
			strpos( self::$willow, trim( willow\tags::g( 'arg_o' )) ) !== false
			&& strrpos( self::$willow, trim( willow\tags::g( 'arg_c' )) ) !== false
		){

			$arg_o = strpos( self::$willow, trim( willow\tags::g( 'arg_o' )) );
			$arg_c = strrpos( self::$willow, trim( willow\tags::g( 'arg_c' )) );

			// h::log( 'e:>Found opening arg_o @ "'.$arg_o.'" and closing arg_c @ "'.$arg_c.'" for willow: '.self::$willow  ); 

			// get string between opening and closing args ##
			self::$config_string = substr( 
				self::$willow, 
				( $arg_o + strlen( trim( willow\tags::g( 'arg_o' ) ) ) ), 
				( $arg_c - $arg_o - strlen( trim( willow\tags::g( 'arg_c' ) ) ) ) ); 
			// h::log( 'e:>$string: "'.self::$config_string .'"' );

		}

		// go with it ##
		if ( 
			self::$config_string 
		){

			// sub tags - passed in arguments string ##

			// h::log( 'e:>config_string: '.self::$config_string );

			// check for loops ##
			if( loops::is( self::$config_string ) ){

				/*
				// get position ##
				if ( 
					strpos( self::$markup['template'], self::$config_string ) !== false 
				){

					$position = strpos( self::$markup['template'], self::$config_string );

					h::log( 'e:>IS a loop @ position: '.$position );

					// call loops ##
					loops::format( self::$config_string, $position );

				}
				*/

				// @TODO -- check for template... ?? perhaps a bad idea as it might be in the actual markup ##

				// pass markup, later when we merge args and config, we need to merge markup correctly ##
				// self::$arguments = [ 'markup' => self::$config_string ];

				self::$arguments = core\method::parse_args( 
					self::$arguments, 
					[ 'markup' => self::$config_string ]
				);

				$willow_explode = explode( trim( willow\tags::g( 'arg_o' )), self::$willow );
				// h::log( $willow_explode );
				self::$willow = trim( $willow_explode[0] );

				self::$hash = self::$willow; // update hash to take altered function name.. ##

			} 

			// clean up string -- remove all white space ##
			// $string = trim( $string );
			// $config_string = str_replace( ' ', '', $config_string );
			// h::log( 'd:> config_string: "'.self::$config_string ); 

			// pass to argument handler ##
			/*
			self::$arguments = 
				willow\arguments::decode([ 
					'string' 	=> self::$config_string, 
					// 'field' 	=> $field_name, 
					// 'value' 	=> self::$willow,
					// 'tag'		=> 'function'	
				]);
			*/

			// parse arguments ##
			self::$arguments = core\method::parse_args( 
				self::$arguments, 
				willow\arguments::decode( self::$config_string )
			);

			// h::log( self::$arguments );
			// h::log( self::$flags_argument );
			// h::log( 'function: '.$willow );
			// $willow = core\method::string_between( $willow, trim( tags::g( 'wil_o' )), trim( tags::g( 'arg_o' )) );
			$willow_explode = explode( trim( willow\tags::g( 'arg_o' )), self::$willow );
			// h::log( $willow_explode );
			self::$willow = trim( $willow_explode[0] );
			// $class = false;
			// $method = false;

			self::$hash = self::$willow; // update hash to take simpler function name.. ##
			// h::log( 'hash updated to: '.self::$hash );
			// h::log( 'willow: "'.self::$willow.'"' );

			// if we found a loop [l] flag in the function args, we should ask parse/loops to extract the data from the string
			// this should create required markup at $position of self::$willow in markup->template
			/*
			if( isset( self::$flags_argument['l'] ) ) {

				$loop_arguments = willow\loops::set([
					'func_args'	=> self::$arguments, 
				]);

				// if loops returned true, we can continue to next function, as this is done ##
				if( $loop_arguments ){

					// h::log( 'd:>loops returned true, we can continue to next function, as this is done' );
					self::$arguments = $loop_arguments; // empty ##

				}

			}
				*/
			// }

			// if arguments are not in an array, take the whole string passed as the arguments ##
			if ( 
				! self::$arguments
				|| ! is_array( self::$arguments ) 
			) {

				// h::log( 'e:>No array arguments found in willow args, but perhaps we still have flags in the vars??' );
				// h::log( 'e:>'.self::$config_string );

				self::$config_string = flags::get( self::$config_string, 'variable' );
				// h::log( self::$flags_variable );

				// h::log( 't:>VAR FLAGS, at this point we could find each var in string and loop over looking for flags and assign...' );
				// if ( 
					$argument_variables = parse\markup::get( self::$config_string, 'variable' );
				// ){
					/*
	
					// h::log( $argument_variables );

					foreach( $argument_variables as $arg_var_k => $arg_var_v ){

						// h::log( 'variable: '.$arg_var_v );

						variables::flags( $arg_var_v, self::$willow );

					}

				}
				*/

				// remove wrapping " quotation marks ## -- 
				// @todo, needs to be move elegant or based on if this was passed as a string argument from the template ##
				self::$config_string = trim( self::$config_string, '"' );

				// string value ##
				self::$arguments = self::$config_string;

			}
			
		}

		// function name might still contain opening and closing args brakets, which were empty - so remove them ##
		self::$willow = str_replace( [
				trim( willow\tags::g( 'arg_o' )), 
				trim( willow\tags::g( 'arg_c' )) 
			], '',
			self::$willow 
		);

		// format passed context~task to PHP class::method ##

		// h::log( 'WILLOW: '.self::$willow );
		@list( self::$context, self::$task ) = explode( '~', self::$willow );
		// h::log( 'context~task: '.self::$context.'~'.self::$task );

		self::$willow = str_replace( '~', '__', self::$willow ); // '::' ##
		
		// Q Willow->context class correction ##
		// if( 'q' == self::$class ) self::$class = '\\q\\context';

		// format namespace to q_willow::context ##
		self::$willow = '\\q\\willow\\context::'.self::$willow;

		// update hash? ##
		self::$hash = self::$willow;
		// h::log( 'Willow now: '.self::$willow );

		// h::log( 'function is class::method' );
		// break function into class::method parts ##
		list( self::$class, self::$method ) = explode( '::', self::$willow ); // '::' ##

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
		self::$willow_array = [ self::$class, self::$method ];

		// test what we have ##
		h::log( 'd:>Willow: "'.self::$willow.'"' );
		self::$hash = self::$hash.'.'.rand();
		// h::log( 'hash at end is...: '.self::$hash );
		// h::log( 'd:>FORMAT --> Context: '.$args['context'].'~'.$args['task'] );

		/*
		h::log( 'd:>self::$context: '.self::$context );
		h::log( 'd:>self::$task: '.self::$task );
		h::log( 'd:>args["context"]: '.$args["context"] );
		h::log( 'd:>args["task"]: '.$args["task"] );
		*/
		// if williw is embedded, capture data ##
		/*
		if(
			'buffer' != $args["context"]
			&& (
				self::$context != $args['context']
				|| self::$task != $args['task']
			)
		){

			h::log( 'd:>self::$context: '.self::$context );
			h::log( 'd:>self::$task: '.self::$task );
			h::log( 'd:>args["context"]: '.$args["context"] );
			h::log( 'd:>args["task"]: '.$args["task"] );

			// EMBEDDED WILLOW ##
			h::log( 'd:>Willow is embedded: '.self::$context.'~'.self::$task );

		}
		*/

		// context_class ##
		$willow_array = explode( '__', self::$method );

		// capture arguments ##
		if ( 
			$argument_variables = parse\markup::get( self::$config_string, 'variable' )
		){

			// h::log( $argument_variables );

			foreach( $argument_variables as $arg_var_k => $arg_var_v ){

				// h::log( 'variable: '.$arg_var_v );

				variables::flags([
					'variable' 	=> $arg_var_v, 
					'context' 	=> $willow_array[0], 
					'task'		=> $willow_array[1],
					'hash'		=> self::$hash 
				]);

			}

		}

		// h::log( self::$arguments );

		// -- define args, some from flags ##

		// pass hash to buffer ##
		self::$arguments = core\method::parse_args( 
			self::$arguments, 
			[ 
				'config' => [ 
					'hash' 	=> self::$hash,
					// 'sub'	=> true
				] 
			]
		);

		if( self::$flags_willow ) {

			// store flags in $filter property ##
			if( ! isset( self::$filter[$willow_array[0]][$willow_array[1]] ) ) self::$filter[$willow_array[0]][$willow_array[1]] = [];

			self::$filter[$willow_array[0]][$willow_array[1]] = core\method::parse_args( 
				self::$filter[$willow_array[0]][$willow_array[1]],
				[
					'global'			=> self::$flags_willow,
					'hash'				=> self::$hash
				], 
			);

		}

		// b = output buffer, collect return data which would render if not __NOT RECOMMENDED__ ##
		if( isset( self::$flags_willow['b'] ) ) {

			self::$arguments = core\method::parse_args( 
				self::$arguments, 
				[ 
					'config' => [ 
						'buffer' => true 
					] 
				]
			);
		}

		// if williw is embedded, capture data ##
		if(
			'buffer' != $args["context"]
			&& (
				self::$context != $args['context']
				|| self::$task != $args['task']
			)
		){

			// EMBEDDED WILLOW ##
			h::log( 'd:>Willow is embedded: '.self::$context.'~'.self::$task );

			/*
			h::log( 'd:>self::$context: '.self::$context );
			h::log( 'd:>self::$task: '.self::$task );
			h::log( 'd:>args["context"]: '.$args["context"] );
			h::log( 'd:>args["task"]: '.$args["task"] );
			*/

			self::$embed = [
				'in'			=>  [
									'context' => $args['context'],
									'task' => $args['task']
				],
				'markup'		=> 	self::$markup['template'],
				'context' 		=>	self::$context,
				'task'			=>	self::$task,
				'hash'			=>  self::$hash,
				'willow'		=>	self::$willow,
				'match'			=>  self::$willow_match,
				'flags'			=>	self::$flags_willow,
				'args'			=>	self::$arguments,
				// 'filters'		=>	isset( self::$flags_willow ) ? self::$filter[$willow_array[0]][$willow_array[1]] : null,
			];

			// h::log( self::$embed );

		}

		// collect current process state ##
		render\args::collect();

		// h::log( 'd:>Calling class_method: '.self::$class.'::'.self::$method );
		
		// h::log( $args );
		// if( isset( self::$buffer ) ) { h::log( 'd:>BUFFER IS SET for  '.self::$class.'::'.self::$method ); }

		// pass args, if set ##
		if( self::$arguments ){

			// h::log( 'passing args array to: '.self::$class.'::'.self::$method );
			// h::log( self::$arguments );
			// h::log( 'hash: '.self::$hash );

			// h::log( self::$class::{ self::$method }( [ 0 => self::$arguments ] ) );

			self::$return = call_user_func_array( 
				self::$willow_array, [ 0 => self::$arguments ] ); // 0 index is for static class args gatherer ##

			render\fields::define([
				self::$hash => 
					// self::$class::{ self::$method }( [ 0 => self::$arguments ] )
					self::$return
			]);

		} else { 

			// h::log( 'NOT passing args array to: '.self::$class.'::'.self::$method );
			self::$return = call_user_func_array( self::$willow_array ); 
			// self::$class::{ self::$method }();

			// add results to fields under $hash key
			render\fields::define([
			 	self::$hash => self::$return
			]);

			// global function returns can be pushed directly into buffer ##
			// NOT sure, basically, this skips all internal processing for external functions, which sounds right ??
			// self::$buffer[ self::$hash ] = self::$return; #self::$class::{ self::$method }();

		}

		// h::log( self::$markup );

		// restore previous process state ##
		render\args::set();

		// test what we have ##
		// h::log( 'd:>Willow: "'.self::$willow.'"' );
		// h::log( 'hash after args SET is...: '.self::$hash );

		if ( 
			! isset( self::$return ) 
			|| ! self::$return
		) {

			h::log( 'd:>Willow "'.self::$willow_match.'" did not return a value.' );

			// strip it from markup ##
			parse\markup::swap( self::$willow_match, '', 'willow', 'string' );

			// done ##
			return false;

		}

		// h::log( 't:>BAD code smell about that [r] flag.. perhaps we should add a o_buffer flag [b] also, try willow in template without R and perhaps apply default R to all secondary willows, found by parsing??' );
		// h::log( $args );

		// h::log( self::$markup );

		// replace tag with raw return value from function
		if( 
			(
				isset( $args['config']['embed'] )
				&& true === isset( $args['config']['embed'] )
			) 
			||
			isset( self::$flags_willow['r'] ) 
		){

			// h::log( 'e:>config->embed or [r] flag set' );

			// h::log( self::$args );

			// h::log( 'e:>Replacing willow: "'.self::$willow_match.'" with context function return value: '.self::$return );

			// get string ##
			$string = self::$return;

			// h::log( $string );
			// h::log( 'hash: '.self::$hash );
			// h::log( self::$fields );

			// replace in markup ##
			parse\markup::swap( self::$willow_match, $string, 'willow', 'string' ); // '{{ '.$field.' }}'

		} else {

			 h::log( 'hash: '.self::$hash );

			// finally -- add a variable "{{ $field }}" where the function tag block was in markup->template ##
			$variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => self::$hash, 'close' => 'var_c' ]);
			// variable::set( $variable, $position, 'variable' ); // '{{ '.$field.' }}'
			parse\markup::swap( self::$willow_match, $variable, 'willow', 'variable' ); // '{{ '.$field.' }}'

			h::log( 'e:>Replacing willow: "'.self::$willow_match.'" with new variable: '.$variable );

		}

		// h::log( self::$return );
		if( self::$embed ){

			// h::log( self::$embed );

			// h::log( 'd:>markup: '.self::$markup['template'] );
			// h::log( self::$buffer );
			// h::log( 'd:>markup: '.self::$markup['template'] );

			$embed_variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => self::$embed['hash'], 'close' => 'var_c' ]);

			// h::log( 'd:>$embed_variable: '.$embed_variable );

			// replace embed['match'] with embed['hash'] in self::$markup['template'] ##
			self::$markup['template'] = str_replace( self::$markup['template'], $embed_variable, self::$markup['template'] );

			// return true;
			
			// h::log( 'd:>'.self::$willow_match );
			// h::log( 'd:>markup: '.self::$markup['template'] );

		}

		h::log( 'd:>markup: '.self::$markup['template'] );

		// restore previous process state ##
		// render\args::set();

		// clear slate ##
		self::reset();

	}




    /**
	 * Scan for functions in markup and add any required markup or call requested functions and capture output
	 * 
	 * @since 4.1.0
	*/
    public static function prepare( $args = null ){

		// h::log( $args );

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
		// h::log( $string );

		// sanity ##
		if (  
			! $string
			|| is_null( $string )
		){

			h::log( self::$args['task'].'~>e:>Error in $markup' );

			return false;

		}

		// h::log( 'd:>PREPARE --> Context: '.$args['context'].'~'.$args['task'] );
		// h::log('d:>'.$string);

		// get all willows, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( willow\tags::g( 'wil_o' ) );
		$close = trim( willow\tags::g( 'wil_c' ) );

		// h::log( 'open: '.$open. ' - close: '.$close. ' - end: '.$end );

		$regex_find = \apply_filters( 
			'q/willow/parse/willows/regex/find', 
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

				// h::log( $match );

				// pass match to function handler ##
				self::format( $match, $args );

			}

		}

	}



	public static function cleanup( $args = null ){

		$open = trim( willow\tags::g( 'wil_o' ) );
		$close = trim( willow\tags::g( 'wil_c' ) );

		// strip all function blocks, we don't need them now ##
		// // $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
		 	'q/willow/parse/willows/cleanup/regex', 
		 	"/$open.*?$close/ms" 
		// 	// "/{{#.*?\/#}}/ms"
		);
		
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

					h::log( $count .' willow tags removed...' );

				}

				// return nothing for cleanup ##
				return "";

			}, 
			self::$markup['template'] 
		);

	}


}
