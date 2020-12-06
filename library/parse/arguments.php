<?php

namespace Q\willow\parse;

// use q\core;
use Q\willow;

class arguments {

	private 

		$plugin,
		$string, 
		$array

	;

	protected function reset(){

		$this->string = false; 
		$this->plugin->set( '_flags_argument', false );
		$this->array = false;

	}
	
	public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/*
	Decode arguments passed in string

	Requirements: 

	( new = test & config = debug:true, run:true )
	( config->debug = true & config->handle = sm:medium, lg:large )
	*/
	public function decode( $string = null ){

		// w__log( $string );

		// sanity ##
		if(
			is_null( $string )
		){

			w__log( 'e:>Error in passed arguments' );

			return false;

		}

		// clear slate ##
		$this->reset();
		
		// assign variables ##
		$this->string = $string;

		// trim string ##
		$this->string = trim( $this->string );

		// flags check for [array]
		$parse_flags = new willow\parse\flags( $this->plugin );
		$this->string = $parse_flags->get( $this->string, 'argument' );

		// get flags locally ##
		$_flags_argument = $this->plugin->get( '_flags_argument' );

		// w__log( $_flags_argument );

		if( 
			! $_flags_argument
			|| ! isset( $_flags_argument ) // not an array
			|| ! is_array( $_flags_argument )
		){

			// w__log( 'd:>Argument string "'.self::$string.'" does not contains any flag, so returning' );

			// done here ##
			return false;

		}

		// w__log( 'd:>string --> '.self::$string );

		// replace " with ' .... hmm ##
		// self::$string = str_replace( '"', "'", self::$string );

		// strip white spaces from data that is not passed inside double quotes ( "data" ) ##
		$this->string = preg_replace( '~"[^"]*"(*SKIP)(*F)|\s+~', "", $this->string );

		// w__log( 'd:>string --> '.$this->string );
		// w__log( $_flags_argument );

		// extract data array from string ##
		$this->array = core\method::parse_str( $this->string );

		// w__log( $this->array );

		// trim leading and ending double quotes ("..") from each value in array ##
		array_walk_recursive( $this->array, function( &$v ) { $v = trim( $v, '"' ); });

		// w__log( $this->array );

		// sanity ##
		if ( 
			// ! $config_string
			! $this->array
			|| ! is_array( $this->array )
			|| empty( $this->array ) // added empty check ##
			// || ! isset( $matches[0] ) 
			// || ! $matches[0]
		){

			w__log( $this->plugin-get( '_args')['task'].'~>n:>No arguments found in string: '.$this->string ); // @todo -- add "loose" lookups, for white space '@s
			// w__log( 'd:>No arguments found in string: '.$this->string ); // @todo -- add "loose" lookups, for white space '@s''

			return false;

		}

		// clear slate ##
		// self::reset();

		// kick back to function handler - it should validate if an array was returned and then deal with it ##
		return $this->array;

	}

	/**
	 * Clean up left-over argument blocks
	 * 
	 * @since 4.1.0
	*/
	public function cleanup( $args = null, $process = 'secondary' ){

		$open = trim( willow\tags::g( 'arg_o' ) );
		$close = trim( willow\tags::g( 'arg_c' ) );

		// w__log( self::$markup['template'] );

		// strip all function blocks, we don't need them now ##
		$regex = \apply_filters( 
		 	'willow/parse/argument/cleanup/regex', 
			"~\\$open\s+(.*?)\s+\\$close~"
			// "~(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|\\$open\s+(.*?)\s+\\$close~"
		);

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

			w__log( 'e:>Error in stored $markup: '.$process );

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
				
				// w__log( $matches );
				if ( 
					! $matches 
					|| ! is_array( $matches )
					|| ! isset( $matches[1] )
				){

					return false;

				}

				// w__log( $matches );

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					w__log( $count .' argument tags removed...' );

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
