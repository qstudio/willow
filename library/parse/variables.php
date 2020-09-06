<?php

namespace q\willow;

use q\willow\core;
use q\willow\core\helper as h;
use q\willow;

class variables extends willow\parse {

	private static 

		$arguments,
		$variable,
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
			|| ! isset( $args['variable'] )
		){

			h::log( 'e:>Error in $args passed to flags method' );

			return false ;

		}

		// clean up field name - remove variable tags ##
		$variable = str_replace( [ tags::g( 'var_o' ), tags::g( 'var_c' ) ], '', $args['variable'] );
		$variable = trim( $variable );
		$variable_original = $variable;
		// h::log( '$variable: '.$variable );

		self::$flags_variable = false;

		// look for flags ##
		// $variable = flags::get( $variable, 'variable' );
		// $variable = trim( $variable );
		// h::log( '$variable, after flags: '.$variable );
		// h::log( self::$flags_variable );

		$variable = flags::get( $variable, 'variable' );
		// h::log( '$variable: '.trim( $variable ) );
		// h::log( 'whole variable: '.$args['variable'] );

		if(
			self::$flags_variable
		){

			// h::log( self::$flags_variable );

			// clean up variable ##
			// $variable = trim( $variable );

			// if we do find flags, we need to create a unique variable reference key, to avoid duplicate filters on re-used variables ##
			// but, at this point, we have no data -- so, we need to set a future-flag for use when filters are applied late on ##
			// h::log( self::$fields );
			// $variable_hash = $variable.'_'.core\method::hash();
			// h::log( 'Original: '.$variable_original.' --> $args: '.$args['variable'].' --> variable_hash: '.$variable_hash );

			// h::log( 'Willow Hash: '.$args['hash'] );

			// store variable flags ##
			// if( ! isset( self::$filter[ $args['context'] ][ $args['task']][ $variable_hash ] ) ) {
			// self::$filter[ $args['hash'] ]['variable'][ $variable_hash ] = self::$flags_variable;
			// }

			// add flags to filter list ##
			// self::$filter[ $args['context'] ][ $args['task'] ][ $variable_hash ] = self::$flags_variable; // [ $variable_hash ]

			// self::$fields_map[$args['context']][$args['task']]['variables'][ $variable ] = $variable_hash;

			// merge in new args to args->field ##
			// if ( ! isset( self::$fields_map[$args['context']][$args['task']]['variables'][ $variable ] ) ) {
			// 	self::$fields_map[$args['context']][$args['task']]['variables'][ $variable ] = [];
			// }

			// add field tracker ##
			// if ( ! isset( self::$fields_map[ $args['hash'] ][ $variable ] ) ){
				// self::$fields_map[ $args['hash'] ][ $variable ] = []; // empty array ##
			// }
			// self::$fields_map[ $args['hash'] ][ $variable ][] = $variable_hash; // add new variable hash as array value ##

			// h::log( self::$fields_map );

			// update self::$willow_match ##
			// $tag = $args['tag'];
			// alter willow_match ##
			// $variable_hash_replace = str_replace( $variable, $variable_hash, $args['variable'] ); 
			// $args['tag'] = str_replace( $args['variable'], $variable_hash_replace, $args['tag'] );
			// h::log( $args['tag'] );

			// variable replacement string ##
			// $variable_replace = str_replace( $variable, $variable_hash, $args['variable'] );
			// h::log( '$variable_replace: '.$variable_replace );			

			// alter buffer_map ##
			// self::$buffer_map[0] = str_replace( $args['variable'], $variable_replace, self::$buffer_map[0] );

			// h::log( 'MARKUP->> '.self::$markup['template'] );
			// h::log( self::$buffer_map[0] );
			// h::log( $args['tag'] );
			// h::log(  );
			// $args['tag'] = 'hello';

			// force markup->template update ##
			// self::$markup['template'] = $args['tag'];

			// kick back ##
			return true; // $args['tag'];

		}

		return false; //$args['tag'];

	}



	/**
	 * Format single variable
	 * 
	 * @since 4.1.0
	*/
	public static function format( $match = null, $args = null, $process = 'internal' ){

		// sanity ##
		if(
			is_null( $match )
		){

			h::log( 'e:>No variable match passed to format method' );

			return false;

		}

		// h::log( $args );

		// clear slate ##
		self::reset();

		// return entire function string, including tags for tag swap ##
		self::$variable = $match;

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

			// clean up field data ## -- @TODO, move to core\method::sanitize();
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
			parse\markup::swap( self::$variable, self::$new_variable, 'variable', 'variable', $process );

		}

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
		// h::log( self::$args['task'].'~>d:>"'.count( $variables ) .'" variables found in string');
		// h::log( 'd:>"'.count( $variables ) .'" variables found in string');

		// remove any leftover variables in string ##
		foreach( $variables as $key => $value ) {

			// pass match to function handler ##
			self::format( $value, $args, $process );

		}
		
		// clear slate ##
		self::reset();

		// kick back ##
		return true;

	}



	public static function cleanup( $args = null, $process = 'internal' ){

		$open = trim( willow\tags::g( 'var_o' ) );
		$close = trim( willow\tags::g( 'var_c' ) );

		// strip all function blocks, we don't need them now ##
		$regex = \apply_filters( 
		 	'q/willow/parse/variables/cleanup/regex', 
			"~(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|\\$open\s+(.*?)\s+\\$close~"
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

				// h::log( $matches );

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					// h::log( $count .' variable tags removed...' );

				}

				// return nothing for cleanup ##
				return "";

			}, 
			$string
		);

		// h::log( self::$markup['template'] );
				
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
