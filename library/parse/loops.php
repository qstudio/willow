<?php

namespace q\willow;

use q\willow;
use q\willow\core;
use q\core\helper as h;

use q\render; // TODO ##

class loops extends willow\parse {

	/*

	------ using config include ------

	{{{ ui::hello }}}

	{{# data }}
		<div class="col-12">You are {{ who }} and the time is {{ time }}</div>
	{{/#}}


	------ direct in template ------

	{{{ ui::hello (
		{{# data }}
			<div class="col-12">You are {{ who }} and the time is {{ time }}</div>
		{{/#}}
	) }}}


	------ preferred format, with inversion ------

	{{{ ui::hello ( [l]
		default = "Nothing found" &
		data = "<div class='col-12'>You are {{ who }} and the time is {{ time }}</div>"
	) }}}
	
	
	*/

	private static function reset(){

		$swap = false; 
		$func_args = false;
		$variable = false;
		$position = false;

	}


	/**
	 * Set loop markup in place of $function in markup 
	 * 
	 * @since 4.1.0
	*/
	public static function set( $args = null ){

		// h::log( $args );

		// sanity ##
		if(
			is_null( $args )
			|| ! is_array( $args )
			// || ! isset( $args['swap'] )
			|| ! isset( $args['func_args'] )
			// || ! isset( $args['position'] )
		){

			h::log( 'e:>Error in passed arguments' );

			return false;

		}

		// clear slate ##
		self::reset();
		
		// assign variables ##
		// $swap = $args['swap'];
		$func_args = $args['func_args'];
		// $position = $args['position'];

		// all loops require a "template" key, so ensure this is set ##
		if( ! isset( $func_args['template'] ) ) {

			h::log( 'e:>Loops require a "template" markup key, please add.' );

			return false;

		}

		// func_args might contain 2 keys:
		// 'default' = default value to add to markup.. which will get overwritten later.. somehow ?? perhaps needs to go in field data - HOW ?
		// 'XX' = 'markup'
		$array = []; // config = default:xx, lg:large
		// $array['markup']['template'] = '<div>{{ data }}</div>'; // @todo, this needs to be passed also

		// all loops require a "data" field, which is wrapped in the markup->template, add a basic version, if this is missing ##
		/*
		if( ! isset( $func_args['template'] ) ){

			$array['markup']['template'] = willow\tags::wrap([ 'open' => 'var_o', 'value' => 'data', 'close' => 'var_c' ]);

		}
		*/

		foreach( $func_args as $key => $value ){

			// // // default ##
			// if( 'default' != $key ){

			// // 	// .. todo
			// // 	// $string .= 'config->args = default : '.trim( $value, '"' );
			// // 	$array['markup'] = [ 
			// // 		'default' => trim( $value, '"' ) 
			// // 	];

			// } else {

				$array['markup'][$key] = trim( $value, '"' );
				// $field = $key;

			// }

		}

		// h::log( $array );
		// h::log( 'position: '.$position );
		// h::log( self::$args );

		// $field = core\method::string_between( $matches[0][$match][0], $open, $close );
		// $markup = core\method::string_between( $matches[0][$match][0], $close, $end );

		// sanity ##
		if ( 
			! isset( $array ) 
		){

			h::log( 'e:>Error in returned args string' );

			return false; 

		}

		// finally -- add a variable "{{ $field }}" before this block at $position to markup->template ##
		// $variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => $field, 'close' => 'var_c' ]);
		// willow\markup::set( $variable, $position, 'variable' ); // '{{ '.$field.' }}'

		// done, let functions know ##
		return $array;

		// return $array;

		// clean up ##
		/*
		$field = trim($field);
		$markup = trim($markup);

		$field = trim( $field, '"' );
		$markup = trim( $markup, '"' );

		// $hash = 'section__'.\mt_rand();
		$hash = $field;
		// $hash = $args['context'].'__'.$args['task'].'__'.rand();

		// test what we have ##
		h::log( 'd:>field: "'.$field.'"' );
		h::log( "d:>markup: $markup" );
		h::log( "d:>hash: $hash" );
		*/

		// so, we can add a new field value to $args array based on the field name - with the markup as value
		// self::$args[$field] = $markup;
		// self::$markup[$hash] = $markup;

		// h::log( 't:>INVERSION - No need for a whole new tag, just pass an "inversion/default" string in case of no results - perhaps defined with the class->method or picked up via a flag "*value"' );

		// get position of ??

		// finally -- add a variable "{{ $field }}" before this block at $position to markup->template ##
		// $variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => $hash, 'close' => 'var_c' ]);
		// willow\markup::swap( $swap, $variable ); // '{{ '.$field.' }}'
		// willow\markup::set( $variable, $position, 'variable' ); // '{{ '.$field.' }}'

		// done, let functions know ##
		// return $array;










	}




	/**
	 * Scan for sections in markup and convert to variables and $fields
	 * 
	 * @since 4.1.0
	*/
	/*
	public static function prepare( $args = null ){

		// h::log( $args );

		// sanity -- this requires ##
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
		$open = trim( willow\tags::g( 'sec_o' ) );
		$close = trim( willow\tags::g( 'sec_c' ) );
		$end = trim( willow\tags::g( 'sec_e' ) );
		$end_preg = str_replace( '/', '\/', ( trim( willow\tags::g( 'sec_e' ) ) ) );
		// $end = '{{\/#}}';

		// h::log( 'open: '.$open. ' - close: '.$close. ' - end: '.$end );

		$regex_find = \apply_filters( 
			'q/render/markup/section/regex/find', 
			"/$open\s+(.*?)\s+$end_preg/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
			// "/{{#(.*?)\/#}}/s" 
		);

		// h::log( 't:> allow for badly spaced tags around sections... whitespace flexible..' );
		if ( 
			preg_match_all( $regex_find, $string, $matches, PREG_OFFSET_CAPTURE ) 
		){

			// if ( is_null( self::$buffer ) ) self::cleanup();

			// // strip all section blocks, we don't need them now ##
			// // $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
			// $regex_remove = \apply_filters( 
			// 	'q/render/parse/section/regex/remove', 
			// 	"/$open.*?$end_preg/ms" 
			// 	// "/{{#.*?\/#}}/ms"
			// );
			// self::$markup['template'] = preg_replace( $regex_remove, "", self::$markup['template'] ); 
		
			// preg_match_all( '/%[^%]*%/', $string, $matches, PREG_SET_ORDER );
			// h::debug( $matches[1] );

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

				// h::log( 'd:>Searching for section field and markup...' );

				$position = $matches[0][$match][1]; // take from first array ##
				// h::log( 'd:>position: '.$position );
				// h::log( 'd:>position from 1: '.$matches[0][$match][1] ); 

				// foreach( $matches[1][0][0] as $k => $v ){
				// $delimiter = \apply_filters( 'q/render/markup/comments/delimiter', "::" );
				// list( $field, $markup ) = explode( $delimiter, $value[0] );
				// $field = method::string_between( $matches[0][$match][0], '{{#', '}}' );
				// $markup = method::string_between( $matches[0][$match][0], '{{# '.$field.' }}', '{{/#}}' );

				$field = core\method::string_between( $matches[0][$match][0], $open, $close );
				$markup = core\method::string_between( $matches[0][$match][0], $close, $end );

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
				// h::log( "d:>has: $hash" );

				// so, we can add a new field value to $args array based on the field name - with the markup as value
				// self::$args[$field] = $markup;
				self::$markup[$hash] = $markup;

				// force hash ?? ##
				// self::$args['config']['hash'] = $hash;

				h::log( 't:>INVERSION - No need for a whole new tag, just pass an "inversion/default" string in case of no results - perhaps defined with the class->method or picked up via a flag "*value"' );

				// finally -- add a variable "{{ $field }}" before this block at $position to markup->template ##
				$variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => $hash, 'close' => 'var_c' ]);
				willow\markup::set( $variable, $position, 'variable' ); // '{{ '.$field.' }}'

			}

		}

	}
	*/

	/*
	public static function cleanup( $args = null ){

		$open = trim( willow\tags::g( 'sec_o' ) );
		// $close = trim( tags::g( 'sec_c' ) );
		// $end = trim( tags::g( 'sec_e' ) );
		$end_preg = str_replace( '/', '\/', ( trim( willow\tags::g( 'sec_e' ) ) ) );

		// strip all section blocks, we don't need them now ##
		// $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
			'q/willow/parse/sections/regex/remove', 
			"/$open.*?$end_preg/ms" 
			// "/{{#.*?\/#}}/ms"
		);
		// self::$markup['template'] = preg_replace( $regex_remove, "", self::$markup['template'] ); 

		// use callback to allow for feedback ##
		self::$markup['template'] = preg_replace_callback(
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

					h::log( $count .' section tags removed...' );

				}

				// return nothing for cleanup ##
				return "";

			}, 
			self::$markup['template'] 
		);


	}
	*/


}
