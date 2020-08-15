<?php

namespace q\willow;

use q\willow;
use q\willow\render;
use q\willow\core;
use q\willow\core\helper as h;

class willows extends willow\parse {

	private static 

		$willow_matches, // array of matches ##
		$willow,
		$willow_match, // full string matched ##
		$arguments,
		$class,
		$method,
		$willow_array,
		$config_string,
		$return
	
	;


	private static function reset(){

		self::$flags_willow = false;
		self::$willow = false;
		self::$arguments = []; // NOTE, this is now an empty array ##
		self::$class = false;
		self::$method = false;
		self::$willow_array = false;
		self::$willow_matches = false;
		self::$config_string = false;
		self::$return = false;

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
	public static function format( $match = null, $args = null, $process = 'internal', $position = null ){

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

		// look for flags ##
		self::$willow = flags::get( self::$willow, 'willow' );

		// clean up ##
		self::$willow = trim( self::$willow );

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

		}

		// go with it ##
		if ( 
			self::$config_string 
		){

			// check for loops ##
			if( $loop_markup = loops::has( self::$config_string ) ){

				h::log( self::$args['task'].'~>n:>HAS a loop so taking part of config string as markup' );
				// h::log( 'e:>HAS a loop so taking part of config string as markup' );
				
				if( flags::has( self::$config_string ) ) {

					h::log( self::$args['task'].'~>n:>FLAG set so take just loop_markup: '.$loop_markup );
					// h::log( 'e:>FLAG set so take just loop_markup: '.$loop_markup );

					self::$arguments = core\method::parse_args( 
						self::$arguments, 
						[ 'markup' => $loop_markup ]
					);

				} else {

					h::log( self::$args['task'].'~>n:>NO flags, so take whole string: '.self::$config_string );
					// h::log( 'e:>NO flags, so take whole string: '.self::$config_string );

					self::$arguments = core\method::parse_args( 
						self::$arguments, 
						[ 'markup' => self::$config_string ]
					);

				}

				$willow_explode = explode( trim( willow\tags::g( 'arg_o' )), self::$willow );
				self::$willow = trim( $willow_explode[0] );

			} 

			// parse arguments ##
			self::$arguments = core\method::parse_args( 
				self::$arguments, 
				willow\arguments::decode( self::$config_string )
			);

			$willow_explode = explode( trim( willow\tags::g( 'arg_o' )), self::$willow );
			self::$willow = trim( $willow_explode[0] );

			// if arguments are not in an array, take the whole string passed as the arguments ##
			if ( 
				! self::$arguments
				|| ! is_array( self::$arguments ) 
			) {

				h::log( self::$args['task'].'~>d:>No array arguments found in willow args, but perhaps we still have flags in the vars' );
				h::log( self::$args['task'].'~>d:>'.self::$config_string );

				self::$config_string = flags::get( self::$config_string, 'variable' );

				// clean up ## -- 
				self::$config_string = trim( self::$config_string ); // trim whitespace ##
				self::$config_string = trim( self::$config_string, '"' ); // trim leading and trailing double quote ##

				// assign string to markup - as this is the only argument we can find ##
				self::$arguments = [ 'markup' => self::$config_string ];

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
		self::$willow = str_replace( '~', '__', self::$willow ); // '::' ##
		
		// format namespace to q_willow::context ##
		self::$willow = '\\q\\willow\\context::'.self::$willow;

		// break function into class::method parts ##
		list( self::$class, self::$method ) = explode( '::', self::$willow ); // '::' ##

		// check ##
		if ( 
			! self::$class 
			|| ! self::$method 
		){

			h::log( 'e:>Error in passed function name, stopping here' );

			return false;

		}

		// clean up class name ##
		self::$class = core\method::sanitize( self::$class, 'php_class' );

		// clean up method name ##
		self::$method = core\method::sanitize( self::$method, 'php_function' );

		// h::log( 'class::method -- '.self::$class.'::'.self::$method );

		if ( 
			! class_exists( self::$class )
			// || ! method_exists( self::$class, self::$method ) // internal methods are found via callstatic lookup ##
			|| ! is_callable( self::$class, self::$method )
		){

			h::log( 'e:>Cannot find - class: '.self::$class.' - method: '.self::$method );

			return false;

		}	

		// make class__method an array ##
		self::$willow_array = [ self::$class, self::$method ];

		// context_class ##
		$willow_array = explode( '__', self::$method );

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
				]);

			}

		}

		// pass hash to buffer ##
		self::$arguments = core\method::parse_args( 
			self::$arguments, 
			[ 
				'config' 		=> [ 
					'process'	=> $process,
					'tag'		=> self::$willow_match,
					'parent'	=> $process == 'buffer' ? false : self::$args['config']['tag'],
				] 
			]
		);

		if( self::$flags_willow ) {

			// store flags in filter property ##
			if( ! isset( self::$filter[$willow_array[0]][$willow_array[1]] ) ) self::$filter[$willow_array[0]][$willow_array[1]] = [];

			self::$filter[$willow_array[0]][$willow_array[1]] = core\method::parse_args( 
				self::$filter[$willow_array[0]][$willow_array[1]],
				[
					'global'			=> self::$flags_willow,
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

		// collect current process state ##
		render\args::collect(); // <--- WHY @TODO ??
		
		// pass args, if set ##
		if( self::$arguments ){

			// h::log( 'passing args array to: '.self::$class.'::'.self::$method );
			// h::log( self::$arguments );
			self::$return = call_user_func_array( self::$willow_array, [ 0 => self::$arguments ] ); // 0 index is for static class args gatherer ##

		} else { 

			// h::log( 'NOT passing args array to: '.self::$class.'::'.self::$method );
			self::$return = call_user_func_array( self::$willow_array ); 

		}	

		// check return ##
		// h::log( self::$return );

		if ( 
			! isset( self::$return ) 
			|| ! self::$return
			|| ! is_array( self::$return )
		) {

			h::log( self::$args['task'].'~>n:>Willow "'.self::$willow_match.'" did not return a value, perhaps it is a hook.' );

			// done ##
			// return false; // REMOVED.. not doing anything I guess ??

		}

		// restore previous process state ##
		render\args::set(); // <--- WHY @TODO ??

		// clear slate ##
		self::reset();

	}




    /**
	 * Scan for willows in markup and add any required markup or call requested context method and capture output
	 * 
	 * @since 4.1.0
	*/
    public static function prepare( $args = null, $process = 'internal' ){ // type can be "buffer" or "internal"

		// h::log( '$process: '.$process );
		// h::log( $args );

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

		// get markup ##
		// $string = self::$markup['template'];
		// h::log( $string );

		// sanity ##
		if (  
			! $string
			|| is_null( $string )
		){

			h::log( self::$args['task'].'~>e:>Error in $markup' );

			return false;

		}

		// h::log( $args );

		// h::log('d:>'.$string);

		// get all willows, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( willow\tags::g( 'wil_o' ) );
		$close = trim( willow\tags::g( 'wil_c' ) );

		// h::log( 'open: '.$open. ' - close: '.$close. ' - end: '.$end );

		$regex_find = \apply_filters( 
			'q/willow/parse/willows/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
		);

		// h::log( $args );
		// h::log( self::$parse_args );

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

				// take position ##
				// h::log( 'position: '.$matches[0][$match][1] );
				$position = $matches[0][$match][1];

				// take match ##
				$match = $matches[0][$match][0];

				// store matches ##
				self::$willow_matches = $match;

				// h::log( $match );

				// h::log( $args );

				// pass match to function handler ##
				self::format( $match, $args, $process, $position );

			}

		}

	}



	public static function cleanup( $args = null, $process = 'internal' ){

		$open = trim( willow\tags::g( 'wil_o' ) );
		$close = trim( willow\tags::g( 'wil_c' ) );

		// strip all function blocks, we don't need them now ##
		// // $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
		 	'q/willow/parse/willows/cleanup/regex', 
		 	"/$open.*?$close/ms" 
		);
		
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

					h::log( $count .' willow tags removed...' );

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
