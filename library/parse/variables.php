<?php

namespace q\willow;

use q\willow\core;
use q\core\helper as h;
use q\willow;

use q\render; // TODO

class variables extends willow\parse {

	private static 

		$arguments,
		// $flags_variable,
		$variable,
		// $variable_match, // full string matched ##
		$new_variable,
		$field,
		$field_array,
		$field_name,
		$field_type,
		$variable_config
	
	;


	private static function reset(){

		self::$arguments = false; 
		self::$flags_variable = false;
		self::$variable = false;
		self::$new_variable = false;
		// self::$variable_match = false;
		self::$variable_config = false;
		self::$field = false;
		self::$field_array = false;
		self::$field_name = false;
		self::$field_type = false;

	}


	
	/**
	 * Check if passed string is a variable 
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
			strpos( $string, trim( willow\tags::g( 'var_o' )) ) !== false
			&& strrpos( $string, trim( willow\tags::g( 'var_c' )) ) !== false
			// @TODO --- this could be more stringent, testing ONLY the first + last 3 characters of the string ??
		){

			return true;

		}

		// no ##
		return false;

	}



	public static function flags( $args = null ){

		// sanity ##
		if(
			is_null( $args )
			|| ! is_array( $args )
		){

			h::log( 'e:>Error in $args passed to flags method' );

			return false;

		}

		// clean up field name - remove variable tags ##
		$variable = str_replace( [ tags::g( 'var_o' ), tags::g( 'var_c' ) ], '', $args['variable'] );
		$variable = trim( $variable );
		// h::log( '$variable: '.$variable );

		self::$flags_variable = false;

		// look for flags ##
		$variable = flags::get( $variable, 'variable' );
		$variable = trim( $variable );
		// h::log( self::$flags_variable );

		if(
			self::$flags_variable
		){

			h::log( 'd:>$variable: '.$variable.' has flags..' );
			// h::log( self::$flags_variable );
			/*
			// empty ##
			$filters = [];

			// e = escape --- escape html ##
			if( isset( self::$flags_variable['e'] ) ) {

				$filters = core\method::parse_args( 
					$filters, 
					[ 
						// 'config' => [ 
							'escape' => true 
						// ] 
					]
				);
			}

			// s = strip --- strip html / php tags ##
			if( isset( self::$flags_variable['s'] ) ) {

				$filters = core\method::parse_args( 
					$filters, 
					[ 
						// 'config' => [ 
							'strip' => true 
						// ] 
					]
				);
			}
			*/

			// get "field_name" from variable ##
			// $field_name = trim( str_replace( [ tags::g( 'arg_o' ), tags::g( 'arg_c' ) ], '', $variable ) );
			// $field_name = !is_null( $hash ) ? $hash : trim( $variable ) ;
			// h::log( '$field_name: '. $field_name );
			// h::log( '$willow: '.$willow );
			// h::log( 'context: '.self::$parse_context );
			// h::log( 'task: '.self::$parse_task );

			// if(
			// 	$filters
			// 	&& is_array( $filters )
			// 	&& ! empty( $filters )
			// ) {

				// merge in new args to args->field ##
				// if ( 
					// ! isset( self::$filter[$field_name] ) 
				// ) {
					if( ! isset( self::$filter[$args['context']][$args['task']] ) ) self::$filter[$args['context']][$args['task']] = [];

					self::$filter[$args['context']][$args['task']] = core\method::parse_args( 
						self::$filter[$args['context']][$args['task']],
						[
							'variables'		=> [
								$variable	=> self::$flags_variable,
							],
							'hash'			=> $args['hash'],
						]						
					);
				// }

				// self::$filter[$field_name] = core\method::parse_args( 
				// 	$arguments, 
				// 	self::$filter[$field_name]['options'] 
				// );

				/*
				// merge in new args to args->field ##
				if ( ! isset( self::$args['process'][$field_name] ) ) self::$args['process'][$field_name] = [];

				self::$args['process'][$field_name] = core\method::parse_args( 
					$arguments, 
					self::$args['process'][$field_name] 
				);
				*/

				// h::log( self::$args );
				// h::log( self::$args['process'][$field_name] );

				// return true;

			// }

			return true;

		}

		return false;

	}



	/**
	 * Format single variable
	 * 
	 * @since 4.1.0
	*/
	public static function format( $match = null, $args = null ){

		// sanity ##
		if(
			is_null( $match )
		){

			h::log( 'e:>No variable match passed to format method' );

			return false;

		}

		// h::log( $args );

		// $open = trim( willow\tags::g( 'var_o' ) );
		// $close = trim( willow\tags::g( 'var_c' ) );

		// clear slate ##
		self::reset();

		// return entire function string, including tags for tag swap ##
		self::$variable = $match;
		// self::$variable = core\method::string_between( $match, $open, $close );

		// h::log( 'd:>$variable: '.self::$variable );

		// look for flags ##
		// self::$variable = flags::get( self::$variable, 'variable' );
		// h::log( self::$flags );

		/*
		if(
			self::$flags_variable
		){

			h::log( 'd:>$variable: '.self::$variable.' has flags..' );
			h::log( self::$flags_variable );

		}
		*/

		// clean up ##
		self::$variable = trim( self::$variable );

		// h::log( 'd:>$variable: '.self::$variable );

		// sanity ##
		if ( 
			! self::$variable
			|| ! isset( self::$variable ) 
		){

			h::log( 'e:>Error in returned match function' );

			return false; 

		}

		// store variable ##
		// self::$variable = $match;

		if ( 
			// $config_string = method::string_between( $value, '{+', '+}' )
			self::$variable_config = core\method::string_between( self::$variable, trim( tags::g( 'arg_o' )), trim( tags::g( 'arg_c' )) )
		){

			// store variable ##
			// self::$variable = $value;

			// h::log( $matches[0] );

			// get field ##
			// h::log( 'value: '.$value );
			
			self::$field = str_replace( self::$variable_config, '', self::$variable );

			// clean up field data ## -- @TODO, move to \Q::sanitize();
			self::$field = preg_replace( "/[^A-Za-z0-9._]/", '', self::$field );

			// h::log( 'd:>field: '.self::$field );

			// check if field is sub field i.e: "field_name.src" ##
			if ( strpos( self::$field, '.' ) !== false ) {

				self::$field_array = explode( '.', self::$field );

				self::$field_name = self::$field_array[0]; // take first part ##
				self::$field_type = self::$field_array[1]; // take second part ##

			} else {

				self::$field_name = self::$field; // take first part ##
				self::$field_type = self::$field; // take second part ##

			}

			// we need field_name, so validate ##
			if (
				! self::$field_name
				|| ! self::$field_type
			){

				h::log( self::$args['task'].'~>e:>Error extracting $field_name or $field_type from variable: '.self::$variable );

				return false;

			}

			// create new variable for markup, based on $field value ##
			self::$new_variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => self::$field, 'close' => 'var_c' ]);

			// test what we have ##
			// h::log( 'd:>variable: "'.self::$variable.'"' );
			// h::log( 'd:>new_variable: "'.self::$new_variable.'"' );
			// h::log( 'd:>field_name: "'.self::$field_name.'"' );
			// h::log( 'd:>field_type: "'.self::$field_type.'"' );

			// pass to argument handler -- returned value ##
			if ( 
				self::$arguments = willow\arguments::decode( self::$variable_config ) // string containing arguments ##
			){

				// merge in new args to args->field ##
				if ( ! isset( self::$args[self::$field_name] ) ) self::$args[self::$field_name] = [];

				self::$args[self::$field_name] = core\method::parse_args( 
					self::$arguments, 
					self::$args[self::$field_name] 
				);

			}

			// h::log( self::$args[$field_name] );

			// now, edit the variable, to remove the config ##
			parse\markup::swap( self::$variable, self::$new_variable, 'variable', 'variable' );

		}

		/*
		// e = escape --- escape html ##
		if( isset( self::$flags_variable['e'] ) ) {

			self::$arguments = core\method::parse_args( 
				self::$arguments, 
				[ 
					'config' => [ 
						'escape' => true 
					] 
				]
			);
		}

		// s = strip --- strip html / php tags ##
		if( isset( self::$flags_variable['s'] ) ) {

			self::$arguments = core\method::parse_args( 
				self::$arguments, 
				[ 
					'config' => [ 
						'strip' => true 
					] 
				]
			);
		}

		// merge in new args to args->field ##
		if ( ! isset( self::$args[self::$field_name] ) ) self::$args[self::$field_name] = [];

		self::$args[self::$field_name] = core\method::parse_args( 
			self::$arguments, 
			self::$args[self::$field_name] 
		);
		*/

		// clean up ##
		self::reset();

		// ok ##
		return true;

	}





	/**
	 * Scan for arguments in variables and convert to $config->data
	 * 
	 * @since 4.1.0
	*/
	public static function prepare( $args = null ){

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
			// || ! isset( $args['key'] )
			// || ! isset( $args['value'] )
			// || ! isset( $args['string'] )
		){

			h::log( self::$args['task'].'~>e:>Error in $markup' );
			// h::log( 'd:>Error in $markup' );

			return false;

		}

		// h::log('d:>'.$string);

		// get all {{ variables }} from markup string ##
        if ( 
            ! $variables = parse\markup::get( $string, 'variable' ) 
        ) {

			// h::log( self::$args['task'].'~>d:>No variables found in $markup');
			// h::log( 'd:>No variables found in $markup: '.self::$args['task']);

			return false;

		}

		// log ##
		h::log( self::$args['task'].'~>d:>"'.count( $variables ) .'" variables found in string');
		// h::log( 'd:>"'.count( $variables ) .'" variables found in string');

		h::log( 't:>VARIABLE flags, such as escape or strip...' );

		// remove any leftover variables in string ##
		foreach( $variables as $key => $value ) {

			// take match ##
			// $match = $matches[0][$match][0];

			// pass match to function handler ##
			self::format( $value, $args );

			// clear slate ##
			// self::reset();

			// h::log( self::$args['task'].'~>d:>'.$value );
			// h::log( 'd:>variable: "'.$value.'"' );

			// now, we need to look for the config pattern, defined as field(setting:value;) and try to handle any data found ##
			// $regex_find = \apply_filters( 'q/render/markup/config/regex/find', '/[[(.*?)]]/s' );
			
			// if ( 
			// 	preg_match( $regex_find, $value, $matches ) 
			// ){

			/*
			if ( 
				// $config_string = method::string_between( $value, '[[', ']]' )
				$config_string = core\method::string_between( $value, trim( tags::g( 'arg_o' )), trim( tags::g( 'arg_c' )) )
			){

				// store variable ##
				$variable = $value;

				// h::log( $matches[0] );

				// get field ##
				// h::log( 'value: '.$value );
				
				// $field = trim( method::string_between( $value, '{{ ', '[[' ) );
				$field = str_replace( $config_string, '', $value );

				// clean up field data ## -- @TODO, move to \Q::sanitize();
				$field = preg_replace( "/[^A-Za-z0-9._]/", '', $field );

				// h::log( 'field: '.$field );

				// check if field is sub field i.e: "post__title" ##
				if ( false !== strpos( $field, '__' ) ) {

					$field_array = explode( '__', $field );

					$field_name = $field_array[0]; // take first part ##
					$field_type = $field_array[1]; // take second part ##

				} else {

					$field_name = $field; // take first part ##
					$field_type = $field; // take second part ##

				}

				// we need field_name, so validate ##
				if (
					! $field_name
					|| ! $field_type
				){

					h::log( self::$args['task'].'~>e:>Error extracting $field_name or $field_type from variable: '.$variable );

					continue;

				}

				// create new variable for markup, based on $field value ##
				$new_variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => $field, 'close' => 'var_c' ]);

				// test what we have ##
				// h::log( 'd:>variable: "'.$value.'"' );
				// h::log( 'd:>new_variable: "'.$new_variable.'"' );
				// h::log( 'd:>field_name: "'.$field_name.'"' );
				// h::log( 'd:>field_type: "'.$field_type.'"' );

				// pass to argument handler -- returned value ##
				if ( 
					self::$arguments = willow\arguments::decode([ 
						'string' 	=> $config_string, // string containing arguments ##
					])
				){

					// merge in new args to args->field ##
					if ( ! isset( self::$args[$field_name] ) ) self::$args[$field_name] = [];

					self::$args[$field_name] = core\method::parse_args( self::$arguments, self::$args[$field_name] );

				}

				// h::log( self::$args[$field_name] );

				// now, edit the variable, to remove the config ##
				willow\markup::swap( $variable, $new_variable, 'variable', 'variable' );

			}
			*/
		
		}
		
		// clear slate ##
		self::reset();

		// kick back ##
		return true;

	}



	public static function cleanup( $args = null ){

		$open = trim( willow\tags::g( 'var_o' ) );
		$close = trim( willow\tags::g( 'var_c' ) );

		// h::log( self::$markup['template'] );

		// strip all function blocks, we don't need them now ##
		// // $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
		 	'q/willow/parse/variables/cleanup/regex', 
			 // "/$open.*?$close/ms" 
			//  "/$open\s+.*?\s+$close/s"
			"~\\$open\s+(.*?)\s+\\$close~"
		);

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

				// h::log( $matches );

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					// h::log( $count .' variable tags removed...' );

				}

				// return nothing for cleanup ##
				return "";

			}, 
			self::$markup['template'] 
		);

		// h::log( self::$markup['template'] );
		
		// self::$markup['template'] = preg_replace( $regex, "", self::$markup['template'] ); 

	}



}
