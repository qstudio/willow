<?php

namespace willow;

use willow;
use willow\render;
use willow\core;
use willow\core\helper as h;

class willows extends willow\parse {

	private static 

		$willow_matches, // array of matches ##
		$willow,
		// $willow_match, // full string matched ##
		$arguments,
		$class,
		$method,
		$willow_array,
		$willow_hash, // moved creation of unique hash earlier, to track filters on as-yet unavailable data ##
		$argument_string,
		$return

	;

	// public static $willow_match; // full string matched ##

	// loop scope trackers - built and reset for each Willow routine ##
	// public
	// 	$scope_count = 0,
	// 	$scope_map = []
	// ;

	private static function reset(){

		self::$flags_willow = false;
		self::$willow_hash = false;
		self::$willow = false;
		self::$arguments = []; // NOTE, this is now an empty array ##
		self::$class = false;
		self::$method = false;
		self::$willow_array = false;
		self::$willow_matches = false;
		self::$willow_match = false; // ?? <<--
		self::$argument_string = false;
		self::$return = false;

		// reset loop_scope_count - as this accumulates on a per Willow basis ##
		// self::$scope_count = 0;
		self::$scope_map = [];

	}

	

	/**
	 * Check if passed string is a willow 
	*/
	public static function is( $string = null ){

		// sanity ##
		if(
			is_null( $string )
			|| ! is_string( $string )
		){

			h::log( 'e:>No string passed to method' );

			return false;

		}

		// alternative method - get position of arg_o and position of LAST arg_c ( in case the string includes additional args )
		if(
			strpos( $string, trim( willow\tags::g( 'wil_o' )) ) !== false // start ##
			&& strrpos( $string, trim( willow\tags::g( 'wil_c' )) ) !== false // end ##
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
	public static function format( $match = null, $args = null, $process = 'secondary', $position = null ){

		// sanity ##
		if(
			is_null( $match )
		){

			h::log( 'e:>No function match passed to format method' );

			return false;

		}

		// h::log( $args );

		// get Willow tags ##
		$open = trim( willow\tags::g( 'wil_o' ) );
		$close = trim( willow\tags::g( 'wil_c' ) );

		// clear slate ##
		self::reset();

		// return entire function string, including tags for tag swap ##
		self::$willow_match = core\method::string_between( $match, $open, $close, true );
		// h::log( self::$willow_match );

		// get Willow, without tags ##
		self::$willow = core\method::string_between( $match, $open, $close );

		// h::log( self::$willow );

		// look for Willow flags -- assigned to a filter for use late on, pre-rendering ##
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

		// check format ##
		if(
			false === strpos( self::$willow, '~' ) 
		){

			h::log( 'e:>Error: All Willows must be in "context~task" format' );

			return false; 

		}

		// h::log( $args );

		// get position of first arg_o and position of last arg_c ( in case the string includes additional args )
		if(
			strpos( self::$willow, trim( willow\tags::g( 'arg_o' )) ) !== false
			&& strrpos( self::$willow, trim( willow\tags::g( 'arg_c' )) ) !== false
		){

			$arg_o = strpos( self::$willow, trim( willow\tags::g( 'arg_o' )) );
			$arg_c = strrpos( self::$willow, trim( willow\tags::g( 'arg_c' )) );

			// h::log( 'e:>Found opening arg_o @ "'.$arg_o.'" and closing arg_c @ "'.$arg_c.'" for willow: '.self::$willow  ); 

			// get string between opening and closing args ##
			self::$argument_string = substr( 
				self::$willow, 
				( $arg_o + strlen( trim( willow\tags::g( 'arg_o' ) ) ) ), 
				( $arg_c - $arg_o - strlen( trim( willow\tags::g( 'arg_c' ) ) ) ) ); 

		}

		h::log( self::$argument_string  );

		// argument_string looks ok, so go with it ##
		if ( 
			self::$argument_string 
		){	

			// check for loops in argument string - might be one or multiple ##
			if( loops::has( self::$argument_string ) ){

				// h::log( $args['task'].'~>n:>HAS a loop so taking part of config string as markup' );
				// h::log( 'd:>HAS a loop so taking part of config string as markup' );

				// we need the entire markup, without the flags ##
				$decode_flags = willow\arguments::decode( self::$argument_string );
				// h::log( willow\arguments::decode( self::$argument_string ) );

				// check if string contains any [ flags ] -- technically filters -- ##
				if( 
					flags::has( self::$argument_string ) 
					&& $decode_flags
					&& isset( $decode_flags['markup']['template'] )
				) {

					// h::log( 'template -> '.$decode_flags['markup']['template'] );
					// h::log( $args['task'].'~>n:>FLAG set so take just loop markup: '.$loop['markup'] );
					// h::log( 'd:>Flags set, so take just loop markup: '.$loop['markup'] );

					self::$arguments = core\method::parse_args( 
						self::$arguments, 
						[ 
							'markup' 	=> $decode_flags['markup']['template'] // $loop['markup'] // markup ##
							// 'scope'		=> $loop['scope'] // {: scope :} <<-- doing nothing ##
						]
					);

				} else {

					// h::log( $args['task'].'~>n:>NO flags, so take whole string: '.self::$argument_string );
					// h::log( 'd:>No Flags, so take whole string: '.self::$argument_string );

					self::$arguments = core\method::parse_args( 
						self::$arguments, 
						[ 
							'markup' 	=> self::$argument_string //, whole string ##
							// 'scope'		=> $loop['scope'] {: scope :}
						]
					);

				}

				// take the first part of the passed string, before the arg_o tag as the {~ Willow ~} ##
				// --> REMOVED <-- //
				// $willow_explode = explode( trim( willow\tags::g( 'arg_o' )), self::$willow );
				// self::$willow = trim( $willow_explode[0] );

			} 

			// parse arguments ##
			self::$arguments = core\method::parse_args( 
				self::$arguments, 
				willow\arguments::decode( self::$argument_string )
			);

			// h::log( self::$arguments );

			// take the first part of the passed string, before the arg_o tag as the {~ Willow ~} ##
			$willow_explode = explode( trim( willow\tags::g( 'arg_o' )), self::$willow );
			self::$willow = trim( $willow_explode[0] );

			// if arguments are not in an array, take the whole string passed as the arguments ##
			if ( 
				! self::$arguments
				|| ! is_array( self::$arguments ) 
			) {

				h::log( $args['task'].'~>d:>No array arguments found in willow args, but perhaps we still have filters in the vars' );
				// h::log( $args['task'].'~>d:>'.self::$argument_string );

				// check for variable filters ##
				self::$argument_string = flags::get( self::$argument_string, 'variable' );	

				// clean up ## -- 
				self::$argument_string = trim( self::$argument_string ); // trim whitespace ##
				self::$argument_string = trim( self::$argument_string, '"' ); // trim leading and trailing double quote ##

				// assign string to markup - as this is the only argument we can find ##
				self::$arguments = [ 'markup' => self::$argument_string ];

			}
			
		}

		// function name might still contain opening and closing args brakets, which were empty - so remove them ##
		self::$willow = str_replace( [
				trim( willow\tags::g( 'arg_o' )), 
				trim( willow\tags::g( 'arg_c' )) 
			], '',
			self::$willow 
		);

		// format passed context~task to "$class__$method" ##
		self::$willow = str_replace( '~', '__', self::$willow ); // '::' ##

		// create hash ##
		self::$willow_hash = self::$willow.'.'.core\method::hash(); 
		// h::log( 'willow_hash: '.self::$willow_hash );
		
		// add escaped Willow namespace --- \willow\context:: ##
		self::$willow = '\\willow\\context::'.self::$willow;

		// break function into class::method parts ##
		list( self::$class, self::$method ) = explode( '::', self::$willow ); 

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

		// get all {{ variables }} in $argument_string and check for flags ##
		if ( 
			$argument_variables = parse\markup::get( self::$argument_string, 'variable' )
		){

			// h::log( $argument_variables );

			// loop each variable ##
			foreach( $argument_variables as $arg_var_k => $arg_var_v ){

				// h::log( 'variable: '.$arg_var_v );

				// check for variable filters ( formally flags  )##
				variables::flags([
					'variable' 	=> $arg_var_v, 
					'context' 	=> self::$class, 
					'task'		=> self::$method,
					'tag'		=> self::$willow_match,
					'hash'		=> self::$willow_hash // pass willow hash ##
				]);

			}

		}

		// h::log( self::$willow_match );

		// add hash, process, tag + parent values to arguments array ##
		self::$arguments = core\method::parse_args( 
			self::$arguments, 
			[ 
				'config' 		=> [ 
					'hash'		=> self::$willow_hash, // pass hash ##
					'process'	=> $process,
					'tag'		=> self::$willow_match,
					'parent'	=> $process == 'primary' ? false : self::$args['config']['tag'],
				] 
			]
		);

		// Does the willow have flags / filters ##
		if( self::$flags_willow ) {

			// store filters under willow hash - this avoids conflicts if Willows are re-used in the same template / view ##
			self::$filter[ self::$willow_hash ] = self::$flags_willow;

		}

		// buffer => output buffer, collect return data which would render if not  ##
		if( 
			self::$flags_willow // flags set ##
			&& is_array( self::$flags_willow ) // is an array 
			&& in_array( 'buffer', self::$flags_willow ) // output buffer defined ##
		) {

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
		render\args::collect();
		
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

			$task = isset( self::$args['task'] ) ? self::$args['task'] : $args['task'];

			h::log( $task.'~>n:>Willow "'.self::$willow_match.'" did not return a value, perhaps it is a hook.' );

		}

		// restore previous process state ##
		render\args::set();

		// clear slate for next run ##
		self::reset();

	}




    /**
	 * Scan for willows in markup and add any required markup or call requested context method and capture output
	 * 
	 * @since 4.1.0
	*/
    public static function prepare( $args = null, $process = 'secondary' ){ // type can be "primary" or "secondary"

		// h::log( '$process: '.$process );
		// h::log( $args );

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
			'willow/parse/willows/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces ##
		);

		// h::log( $args );
		// h::log( self::$parse_args );

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



	public static function cleanup( $args = null, $process = 'secondary' ){

		$open = trim( willow\tags::g( 'wil_o' ) );
		$close = trim( willow\tags::g( 'wil_c' ) );

		// strip all function blocks, we don't need them now ##
		// $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
		 	'willow/parse/willows/cleanup/regex', 
			"/(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|$open.*?$close/ms" // clean up with SKIP <code>tag</code> ##
			//  "/(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|{~.*?~}/ms" 
		);
		
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
