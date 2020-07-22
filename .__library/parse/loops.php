<?php

namespace q\willow;

use q\willow;
use q\willow\core;
use q\core\helper as h;

use q\render; // TODO ##

class loops extends willow\parse {


	private static 

		$hash, 
		$loop,
		$loop_match, // full string matched ##
		$loop_field,
		$loop_markup,
		// $arguments,
		$config_string,
		$return,
		$position
	
	;


	private static function reset(){

		self::$hash = false; 
		// self::$flags = false;
		// self::$flags_args = false;
		self::$loop_field = false;
		self::$loop_markup = false;
		self::$loop = false;
		// self::$arguments = false;
		// self::$class = false;
		// self::$method = false;
		// self::$willow_array = false;
		self::$config_string = false;
		self::$return = false;
		self::$position = false;
		// self::$is_global = false;

	}


	/**
	 * Format single loop
	 * 
	 * @since 4.1.0
	*/
	public static function format( $match = null, $position = null, $process = 'internal' ){

		// sanity ##
		if(
			is_null( $match )
			|| is_null( $position )
		){

			h::log( 'e:>No function match or postion passed to format method' );

			return false;

		}

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$loop_open = trim( willow\tags::g( 'loo_o' ) );
		$loop_close = str_replace( '/', '\/', ( trim( willow\tags::g( 'loo_c' ) ) ) );

		// scope ## - self::$fields data key ##
		$scope_open = trim( willow\tags::g( 'sco_o' ) );
		$scope_close = trim( willow\tags::g( 'sco_c' ) );

		// clear slate ##
		self::reset();

		// return entire loop string, including tags for tag swap ##
		self::$loop_match = core\method::string_between( $match, $loop_open, $loop_close, true );
		self::$loop = core\method::string_between( $match, $loop_open, $loop_close );
		self::$position = $position;

		// get field + markup .... HMM ##, this will not work with embedded args
		self::$loop_field = core\method::string_between( $match, $scope_open, $scope_close );
		self::$loop_markup = core\method::string_between( $match, $scope_close, $loop_close );

		// sanity ##
		if ( 
			! isset( self::$loop_field ) 
			|| ! isset( self::$loop_markup ) 
		){

			h::log( 'e:>Error in returned match key or value' );

			return false; 

		}

		// clean up ##
		self::$loop_field = trim(self::$loop_field);
		self::$loop_markup = trim(self::$loop_markup);

		// $hash = 'section__'.\mt_rand();
		self::$hash = self::$loop_field;
		// $hash = $args['context'].'__'.$args['task'].'__'.rand();

		// test what we have ##
		// h::log( 'd:>field: "'.self::$loop_field.'"' );
		// h::log( 'd:>markup: "'.self::$loop_markup.'"' );
		// h::log( 'd:>hash: "'.self::$hash.'"' );

		// so, we can add a new field value to $args array based on the field name - with the markup as value
		// self::$args[$field] = $markup;
		self::$markup[self::$hash] = self::$loop_markup;

		// BREAK -- we might need to check for isset ( markup->field ) ++ markup-> template if not create -- template +

		// force hash ?? ##
		// self::$args['config']['hash'] = $hash;

		// h::log( 't:>INVERSION - No need for a whole new tag, just pass an "inversion/default" string in case of no results - perhaps defined with the class->method or picked up via a flag "*value"' );

		// finally -- add a variable "{{ $loop_field }}" before this block at $position to markup->template ##
		$variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => self::$hash, 'close' => 'var_c' ]);
		parse\markup::set( $variable, self::$position, 'variable', $process ); // '{{ '.$field.' }}'

		// clear slate ##
		self::reset();

	}



	/**
	 * Check if passed string is a loop 
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
			strpos( $string, trim( willow\tags::g( 'loo_o' )) ) !== false
			&& strrpos( $string, trim( willow\tags::g( 'loo_c' )) ) !== false
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
	 * Scan for sections in markup and convert to variables and $fields
	 * 
	 * @since 4.1.0
	*/
	public static function prepare( $args = null, $process = 'internal' ){

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
				// || ! is_array( self::$buffer_markup )
				// || ! isset( self::$buffer_markup['template'] )
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
		$loop_open = trim( willow\tags::g( 'loo_o' ) );
		$loop_close = str_replace( '/', '\/', ( trim( willow\tags::g( 'loo_c' ) ) ) );

		// arguments ##
		// $arg_open = trim( willow\tags::g( 'arg_o' ) );
		// $arg_close = trim( willow\tags::g( 'arg_c' ) );

		$regex_find = \apply_filters( 
			'q/render/markup/loop/regex/find', 
			"/$loop_open\s+(.*?)\s+$loop_close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
			// "/{{#(.*?)\/#}}/s" 
		);

		if ( 
			preg_match_all( $regex_find, $string, $matches, PREG_OFFSET_CAPTURE ) 
		){

			// h::log( $matches );

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

				// get position from first ( whole string ) match ##
				$position = $matches[0][$match][1]; 

				// take match ##
				$match = $matches[0][$match][0];

				
				// h::log( 'd:>position: '.$position );
				// h::log( 'd:>position from 1: '.$matches[0][$match][1] ); 

				// pass match to function handler ##
				self::format( $match, $position, $process );

				// h::log( 'd:>Searching for loop field and markup in: '.$matches[0][$match][0] );

				// $position = $matches[0][$match][1]; // take from first array ##
				// h::log( 'd:>position: '.$position );
				// h::log( 'd:>position from 1: '.$matches[0][$match][1] ); 
				/*
				$field = core\method::string_between( $matches[0][$match][0], $arg_open, $arg_close );
				$markup = core\method::string_between( $matches[0][$match][0], $arg_close, $loop_close );

				// sanity ##
				if ( 
					! isset( $field ) 
					|| ! isset( $markup ) 
				){

					h::log( 'e:>Error in returned match key or value' );

					continue; 

				}

				// clean up ##
				$field = trim($field);
				$markup = trim($markup);

				// $hash = 'section__'.\mt_rand();
				$hash = $field;
				// $hash = $args['context'].'__'.$args['task'].'__'.rand();

				// test what we have ##
				// h::log( 'd:>field: "'.$field.'"' );
				// h::log( "d:>markup: $markup" );
				// h::log( "d:>hash: $hash" );

				// so, we can add a new field value to $args array based on the field name - with the markup as value
				// self::$args[$field] = $markup;
				self::$markup[$hash] = $markup;

				// force hash ?? ##
				// self::$args['config']['hash'] = $hash;

				// h::log( 't:>INVERSION - No need for a whole new tag, just pass an "inversion/default" string in case of no results - perhaps defined with the class->method or picked up via a flag "*value"' );

				// finally -- add a variable "{{ $field }}" before this block at $position to markup->template ##
				$variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => $hash, 'close' => 'var_c' ]);
				willow\markup::set( $variable, $position, 'variable' ); // '{{ '.$field.' }}'
				*/

			}

		}

	}



	public static function cleanup( $args = null, $process = 'internal' ){

		$open = trim( willow\tags::g( 'loo_o' ) );
		// $close = trim( tags::g( 'sec_c' ) );
		// $end = trim( tags::g( 'sec_e' ) );
		$close = str_replace( '/', '\/', ( trim( willow\tags::g( 'loo_c' ) ) ) );

		// strip all section blocks, we don't need them now ##
		// $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
			'q/willow/parse/loops/regex/remove', 
			"/$open.*?$close/ms" 
			// "/{{#.*?\/#}}/ms"
		);
		// self::$markup['template'] = preg_replace( $regex_remove, "", self::$markup['template'] ); 

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
				// || ! is_array( self::$buffer_markup )
				// || ! isset( self::$buffer_markup['template'] )
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
				
				// h::log( $matches );
				if ( 
					! $matches 
					|| ! is_array( $matches )
					|| ! isset( $matches[1] )
				){

					return false;

				}

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					h::log( $count .' loop tags removed...' );

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
