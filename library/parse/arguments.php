<?php

namespace q\willow;

// use q\core;
use q\willow;
use q\willow\core;
use q\willow\core\helper as h;
// use q\ui;
// use q\render; // @TODO ##

class arguments extends willow\parse {

	private static 

		$string, 
		$array

	;


	protected static function reset(){

		self::$string = false; 
		self::$flags_argument = false;
		self::$array = false;

	}
	

	
	/*
	Decode arguments passed in string

	Requirements: 

	( new = test & config = debug:true, run:true )
	( config->debug = true & config->handle = sm:medium, lg:large )
	*/
	public static function decode( $string = null ){

		// h::log( $string );

		// sanity ##
		if(
			is_null( $string )
		){

			h::log( 'e:>Error in passed arguments' );

			return false;

		}

		// clear slate ##
		self::reset();
		
		// assign variables ##
		self::$string = $string;

		// trim string ##
		self::$string = trim( self::$string );

		// check for "<" at start and ">" at end ##
		// @todo - move to flags check for [a]
		self::$string = flags::get( self::$string, 'argument' );
		// h::log( self::$flags_argument );
		if( 
			! self::$flags_argument
			|| ! isset( self::$flags_argument ) // not an array
			|| ! is_array( self::$flags_argument )
		){

			// h::log( 'd:>Argument string "'.self::$string.'" does not contains any flag, so returning' );

			// done here ##
			return false;

		}

		// h::log( 'd:>string --> '.self::$string );

		// replace " with ' .... hmm ##
		// self::$string = str_replace( '"', "'", self::$string );

		// strip white spaces from data that is not passed inside double quotes ( "data" ) ##
		self::$string = preg_replace( '~"[^"]*"(*SKIP)(*F)|\s+~', "", self::$string );

		// h::log( 'd:>string --> '.self::$string );
		// h::log( self::$flags_argument );

		// extract data from string ##
		self::$array = core\method::parse_str( self::$string );

		// h::log( self::$array );

		// trim leading and ending double quotes ("..") from each value in array ##
		array_walk_recursive( self::$array, function( &$v ) { $v = trim( $v, '"' ); });

		// h::log( self::$array );

		// sanity ##
		if ( 
			// ! $config_string
			! self::$array
			|| ! is_array( self::$array )
			// || ! isset( $matches[0] ) 
			// || ! $matches[0]
		){

			h::log( self::$args['task'].'~>n:>No arguments found in string: '.self::$string ); // @todo -- add "loose" lookups, for white space '@s
			// h::log( 'd:>No arguments found in string: '.self::$string ); // @todo -- add "loose" lookups, for white space '@s''

			return false;

		}

		// clear slate ##
		// self::reset();

		// kick back to function handler - it should validate if an array was returned and then deal with it ##
		return self::$array;

	}





	/**
	 * Clean up left-over argument blocks
	 * 
	 * @since 4.1.0
	*/
	public static function cleanup( $args = null, $process = 'internal' ){

		$open = trim( willow\tags::g( 'arg_o' ) );
		$close = trim( willow\tags::g( 'arg_c' ) );

		// h::log( self::$markup['template'] );

		// strip all function blocks, we don't need them now ##
		$regex = \apply_filters( 
		 	'q/willow/parse/argument/cleanup/regex', 
			"~\\$open\s+(.*?)\s+\\$close~"
			// "~(?s)<pre[^<]*>.*?<\/pre>(*SKIP)(*F)|\\$open\s+(.*?)\s+\\$close~"
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

					h::log( $count .' argument tags removed...' );

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
