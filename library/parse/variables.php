<?php

namespace Q\willow\parse;

use Q\willow;
use Q\willow\core\helper as h;

class variables {

	private 

		$plugin = false,
		// $args = false,
		// $process = false,

		$arguments = false,
		$variable = false,
		$new_variable = false,
		$field = false,
		$field_array = false,
		$field_name = false,
		$field_type = false,
		$variable_config = false
	
	;


	private function reset(){

		$this->arguments = false; 
		$this->flags_variable = false;
		$this->variable = false;
		$this->new_variable = false;
		$this->variable_config = false;
		$this->field = false;
		$this->field_array = false;
		$this->field_name = false;
		$this->field_type = false;

	}

	/**
	 * Construct object from passed args
	 * 
	 * @since 2.0.0
	*/
	public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}
	
	
	/**
	 * Scan for arguments in variables and convert to $config->data
	 * 
	 * @since 4.1.0
	*/
	public function match( $args = null, $process = 'secondary' ){

		// local vars ##
		$_args = $this->plugin->get( '_args' );
		$_markup = $this->plugin->get( '_markup' );
		$_buffer_markup = $this->plugin->get( '_buffer_markup' );

		// sanity -- method requires requires ##
		if ( 
			(
				'secondary' == $process
				&& (
					! isset( $_markup )
					|| ! is_array( $_markup )
					|| ! isset( $_markup['template'] )
				)
			)
			||
			(
				'primary' == $process
				&& (
					! isset( $_buffer_markup )
				)
			)
		){

			w__log( 'e:>Error in stored $markup' );

			return false;

		}

		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "secondary" :

				// get markup ##
				$string = $_markup['template'];

			break ;

			case "primary" :

				// get markup ##
				$string = $_buffer_markup;

			break ;

		} 

		// sanity ##
		if (  
			! $string
			|| is_null( $string )
			|| ! is_string( $string )
		){

			// w__log( $_args['task'].'~>e:>Error in $markup' );
			// w__log( 'd:>Error in $markup' );

			// w__log( $string );

			return false;

		}

		// w__log('d:>'.$string);

		// get all {{ variables }} from markup string ##
		$parse_markup = new willow\parse\markup( $this->plugin );
        if ( 
            ! $variables = $parse_markup->get( $string, 'variable' ) 
        ) {

			// w__log( self::$args['task'].'~>d:>No variables found in $markup');
			// w__log( 'd:>No variables found in $markup: '.self::$args['task']);

			return false;

		}

		// log ##
		// w__log( self::$args['task'].'~>d:>"'.count( $variables ) .'" variables found in string');
		// w__log( 'd:>"'.count( $variables ) .'" variables found in string');

		// w__log( $variables );

		// remove any leftover variables in string ##
		foreach( $variables as $key => $value ) {

			// pass match to function handler ##
			$this->format( $value, $args, $process );

		}
		
		// clear slate ##
		$this->reset();

		// kick back ##
		return true;

	}

	/**
	 * Check if passed string is a variable 
	*/
	public function is( $string = null ){

		// @todo - sanity ##
		if(
			is_null( $string )
		){

			w__log( 'e:>No string passed to method' );

			return false;

		}

		// alternative method - get position of arg_o and position of LAST arg_c ( in case the string includes additional args )
		if(
			strpos( $string, trim( $this->plugin->tags->g( 'var_o' )) ) !== false
			&& strrpos( $string, trim( $this->plugin->tags->g( 'var_c' )) ) !== false
			// @TODO --- this could be more stringent, testing ONLY the first + last 3 characters of the string ??
		){

			return true;

		}

		// no ##
		return false;

	}

	public function flags( $args = null ){

		// sanity ##
		if(
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['variable'] )
		){

			w__log( 'e:>Error in $args passed to flags method' );

			return false ;

		}

		// clean up field name - remove variable tags ##
		$variable = str_replace( 
			[ 
				$this->plugin->tags->g( 'var_o' ), 
				$this->plugin->tags->g( 'var_c' ) 
			], 
			'', // with nada ##
			$args['variable'] 
		);
		$variable = trim( $variable );
		$variable_original = $variable;
		// w__log( '$variable: '.$variable );

		$this->plugin->set( '_flags_variable', false );

		// look for flags ##
		// $variable = flags::get( $variable, 'variable' );
		// $variable = trim( $variable );
		// w__log( '$variable, after flags: '.$variable );
		// w__log( self::$flags_variable );

		// $variable = flags::get( $variable, 'variable' );
		$variable = $this->plugin->parse->flags->get( $variable, 'variable' );
		// w__log( 'variable: '.trim( $variable ) );
		// w__log( 'whole variable: '.$args['variable'] );

		if(
			// $this->flags_variable
			$this->plugin->get( '_flags_variable' )
		){

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
	public function format( $match = null, $args = null, $process = 'secondary' ){

		// sanity ##
		if(
			is_null( $match )
		){

			w__log( 'e:>No variable match passed to format method' );

			return false;

		}

		// w__log( $args );

		// clear slate ##
		$this->reset();

		// return entire function string, including tags for tag swap ##
		$this->variable = $match;

		// clean up ##
		$this->variable = trim( $this->variable );

		// w__log( 'd:>$variable: '.$this->variable );

		// sanity ##
		if ( 
			! $this->variable
			|| ! isset( $this->variable ) 
		){

			w__log( 'e:>Error in returned match function' );

			return false; 

		}

		// store variable ##
		// $this->variable = $match;

		if ( 
			// $config_string = method::string_between( $value, '{+', '+}' )
			$this->variable_config = willow\core\method::string_between( 
				$this->variable, 
				trim( $this->plugin->tags->g( 'arg_o' )), 
				trim( $this->plugin->tags->g( 'arg_c' )) 
			)
		){

			// store variable ##
			// self::$variable = $value;

			// w__log( $matches[0] );

			// get field ##
			// w__log( 'value: '.$value );
			
			$this->field = str_replace( $this->variable_config, '', $this->variable );

			// clean up field data ## -- @TODO, move to core\method::sanitize();
			$this->field = preg_replace( "/[^A-Za-z0-9._]/", '', $this->field );

			// w__log( 'd:>field: '.$this->field );

			// check if field is sub field i.e: "field_name.src" ##
			if ( strpos( $this->field, '.' ) !== false ) {

				$this->field_array = explode( '.', $this->field );

				$this->field_name = $this->field_array[0]; // take first part ##
				$this->field_type = $this->field_array[1]; // take second part ##

			} else {

				$this->field_name = $this->field; // take first part ##
				$this->field_type = $this->field; // take second part ##

			}

			// we need field_name, so validate ##
			if (
				! $this->field_name
				|| ! $this->field_type
			){

				w__log( $this->plugin->get( '_args' )['task'].'~>e:>Error extracting $field_name or $field_type from variable: '.$this->variable );

				return false;

			}

			// create new variable for markup, based on $field value ##
			$this->new_variable = $this->plugin->tags->wrap([ 'open' => 'var_o', 'value' => $this->field, 'close' => 'var_c' ]);

			// test what we have ##
			// w__log( 'd:>variable: "'.$this->variable.'"' );
			// w__log( 'd:>new_variable: "'.$this->new_variable.'"' );
			// w__log( 'd:>field_name: "'.$this->field_name.'"' );
			// w__log( 'd:>field_type: "'.$this->field_type.'"' );

			// pass to argument handler -- returned value ##
			if ( 
				$this->arguments = $this->plugin->parse->arguments->decode( $this->variable_config ) // string containing arguments ##
			){

				// get args ##
				$_args = $this->plugin->get( '_args' );

				// merge in new args to args->field ##
				if ( ! isset( $this->plugin->get( '_args' )[$this->field_name] ) ) {
					
					$_args[$this->field_name] = [];

				}

				$_args[$this->field_name] = willow\core\method::parse_args( 
					$this->arguments, 
					$_args[$this->field_name] 
				);

				// set args ##
				$this->plugin->set( '_args', $_args );

			}

			// w__log( self::$args[$field_name] );

			// now, edit the variable, to remove the config ##
			$parse_markup = new willow\parse\markup( $this->plugin );
			$parse_markup->swap( $this->variable, $this->new_variable, 'variable', 'variable', $process );

		}

		// clean up ##
		self::reset();

		// ok ##
		return true;

	}







	/***/
	public function cleanup( $args = null, $process = 'secondary' ){

		// vars ##
		$_markup = $this->plugin->get( '_markup' );
		$_buffer_markup = $this->plugin->get( '_buffer_markup' );
		$open = trim( $this->plugin->tags->g( 'var_o' ) );
		$close = trim( $this->plugin->tags->g( 'var_c' ) );

		// strip all function blocks, we don't need them now ##
		$regex = \apply_filters( 
		 	'willow/parse/variables/cleanup/regex', 
			"~(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|\\$open\s+(.*?)\s+\\$close~"
		);

		// sanity -- method requires requires ##
		if ( 
			(
				'secondary' == $process
				&& (
					! isset( $_markup )
					|| ! is_array( $_markup )
					|| ! isset( $_markup['template'] )
				)
			)
			||
			(
				'primary' == $process
				&& (
					! isset( $_buffer_markup )
				)
			)
		){

			w__log( 'e:>Error in stored $markup' );

			return false;

		}

		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "secondary" :

				// get markup ##
				$string = $_markup['template'];

			break ;

			case "primary" :

				// get markup ##
				$string = $_buffer_markup;

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

					// w__log( $count .' variable tags removed...' );

				}

				// return nothing for cleanup ##
				return "";

			}, 
			$string
		);

		// w__log( $_markup['template'] );
				
		// find out which markup to affect ##
		switch( $process ){

			default : 
			case "secondary" :

				// set markup ##
				$_markup['template'] = $string;
				$this->plugin->set( '_markup', $_markup );

			break ;

			case "primary" :

				// set markup ##
				$_buffer_markup = $string;
				$this->plugin->set( '_buffer_markup', $_buffer_markup );

			break ;

		} 

	}



}
