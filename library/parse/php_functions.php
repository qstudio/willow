<?php

namespace willow\parse;

use willow;
use willow\core\helper as h;

class php_functions {

	private
		$return,
		$function_hash, 
		$function,
		$function_match, // full string matched ##
		$arguments,
		$class,
		$method,
		$function_array,
		$config_string
	;


	private function reset(){

		$this->return = false; 
		$this->function_hash = false; 
		\willow()->set( '_flags_php_function', false );
		$this->function = false;
		$this->arguments = false;
		$this->class = false;
		$this->method = false;
		$this->function_array = false;
		$this->config_string = false;

	}

	/**
	 * Construct
     */
    public function __construct(){

		// silence ##

	}
	
    /**
	 * Scan for functions in markup and add any required markup or call requested functions and capture output
	 * 
	 * @since 4.1.0
	*/
    public function match( $args = null, $process = 'secondary' ){

		// local vars ##
		$_args = \willow()->get( '_args' );
		$_markup = \willow()->get( '_markup' );
		$_buffer_markup = \willow()->get( '_buffer_markup' );

		// get parse task ##
		$_parse_task = $_args['task'] ?? \willow()->get( '_parse_task' );

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
		){

			w__log( $_parse_task.'~>e:>Error in $markup' );

			return false;

		}

		// w__log('d:>'.$string);

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( \willow()->tags->g( 'php_fun_o' ) );
		$close = trim( \willow()->tags->g( 'php_fun_c' ) );

		// w__log( 'open: '.$open. ' - close: '.$close );

		$regex_find = \apply_filters( 
			'willow/parse/php_functions/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces ##
		);

		// w__log( 't:> allow for badly spaced tags around sections... whitespace flexible..' );
		if ( 
			preg_match_all( $regex_find, $string, $matches, PREG_OFFSET_CAPTURE ) 
		){

			// sanity ##
			if ( 
				! $matches
				|| ! isset( $matches[1] ) 
				|| ! $matches[1]
			){

				w__log( 'e:>Error in returned matches array' );

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

					w__log( 'e:>Error in returned matches - no position' );

					continue;

				}

				// take match ##
				$match = $matches[0][$match][0];

				// pass match to function handler ##
				$this->format( $match, $process );

			}

		}

	}

	protected function format( $match = null, $process = 'secondary' ){

		// sanity ##
		if(
			is_null( $match )
		){

			w__log( 'e:>No function match passed to format method' );

			return false;

		}

		$open = trim( \willow()->tags->g( 'php_fun_o' ) );
		$close = trim( \willow()->tags->g( 'php_fun_c' ) );

		// clear slate ##
		self::reset();

		// return entire function string, including tags for tag swap ##
		$this->function_match = willow\core\strings::between( $match, $open, $close, true );
		$this->function = willow\core\strings::between( $match, $open, $close );

		// w__log( '$function_match: '.$function_match );

		// look for flags ##
		$this->function = \willow()->parse->flags->get( $this->function, 'php_function' );
		// w__log( \willow()->get( '_flags_php_function' ) );
		// w__log( $this->function );

		// clean up ##
		$this->function = trim( $this->function );

		// w__log( 'Function: '.$this->function );

		// sanity ##
		if ( 
			! $this->function
			|| ! isset( $this->function ) 
		){

			w__log( 'e:>Error in returned match function' );

			return false; 

		}

		// default args ##
		$this->function_hash = $this->function; // set hash to entire function, in case this has no config and is not class_method format ##
		// w__log( 'hash set to: '.$function_hash );

		// $config_string = core\strings::between( $value, '[[', ']]' )
		$this->config_string = willow\core\strings::between( 
			$this->function, 
			trim( \willow()->tags->g( 'arg_o' )), 
			trim( \willow()->tags->g( 'arg_c' )) 
		);

		// go with it ##
		if ( 
			$this->config_string 
		){

			// pass to argument handler ##
			$this->arguments = \willow()->parse->arguments->decode( $this->config_string );

			$function_explode = explode( trim( \willow()->tags->g( 'arg_o' )), $this->function );
			$this->function = trim( $function_explode[0] );

			$this->function_hash = $this->function; // update hash to take simpler function name.. ##

			// if arguments are not in an array, take the whole string passed as the arguments ##
			if ( 
				! $this->arguments
				|| ! is_array( $this->arguments ) 
			) {

				// perhaps args is a simple csv, check and break ##
				if(
					false !== strpos( $this->config_string, ',' )
				){

					// w__log('d:>Args are in csv: '.$this->config_string );

					$config_explode = explode( ',', $this->config_string );
					// $config_explode = array_map( trim, $config_explode );
					
					$config_explode = array_map( function( $item ) {
						return trim( $item, ' ' ); // trim whitespace, single and double quote ## ' \'"'
					}, $config_explode );

					// w__log( $config_explode );
					$this->arguments = $config_explode;

				} else {

					// w__log('d:>Args are not an array or csv, to taking the whole string');

					// remove wrapping " quotation marks ## -- 
					// @todo, needs to be move elegant or based on if this was passed as a string argument from the template ##
					$this->config_string = trim( $this->config_string, '"' );

					// create required array
					// $this->arguments = [ $this->config_string ];
					$this->arguments = $this->config_string;

				}

			}
			
		}

		// function name might still contain opening and closing args brakets, which were empty - so remove them ##
		$this->function = str_replace( [
				trim( \willow()->tags->g( 'arg_o' )), 
				trim( \willow()->tags->g( 'arg_c' )) 
			], '',
			$this->function 
		);

		// check if we are being passed a simple string function, or a class::method
		if(
			strpos( $this->function, '::' )
		){

			// w__log( 'function is class::method' );
			// break function into class::method parts ##
			list( $this->class, $this->method ) = explode( '::', $this->function );

			// update hash ##
			$this->function_hash = $this->method; 
			// w__log( 'hash updated again to: '.$this->function_hash );

			if ( 
				! $this->class 
				|| ! $this->method 
			){

				w__log( 'e:>Error in passed function name, stopping here' );

				return false;

			}

			// clean up class name @todo -- 
			$this->class = willow\core\sanitize::value( $this->class, 'php_class' );

			// clean up method name --
			$this->method = willow\core\sanitize::value( $this->method, 'php_function' );

			// w__log( 'class::method -- '.$this->class.'::'.$this->method );

			if ( 
				! class_exists( $this->class )
				// || ! method_exists( $this->class, $this->method ) // internal methods are found via callstatic lookup ##
				|| ! is_callable( $this->class, $this->method )
			){

				w__log( 'e:>Cannot find PHP Function --> '.$this->class.'::'.$this->method );

				return false;

			}	

			// make class__method an array ##
			$this->function_array = [ $this->class, $this->method ];

		// simple function string ##
		} else {

			// clean up function name ##
			$this->function = willow\core\sanitize::value( $this->function, 'php_function' );

			// try to locate function directly in global scope ##
			if ( ! function_exists( $this->function ) ) {
					
				w__log( 'd:>Cannot find function: '.$this->function );

				return false;

			}

		}

		// final hash update ##
		$this->function_hash = $this->function_hash.'.'.rand();

		// class and method set -- so call ## 
		if ( $this->class && $this->method ) {

			// w__log( 'd:>Calling class_method: '.$this->class.'::'.$this->method );

			// pass args, if set ##
			if( $this->arguments ){

				// w__log( 'passing args array to: '.$this->class.'::'.$this->method );
				// w__log( $this->arguments );

				// global function returns are pushed directly into buffer ##
				$this->return = $this->class::{ $this->method }( $this->arguments );

			} else { 

				// w__log( 'NOT passing args array to: '.$this->class.'::'.$this->method );

				// global function returns are pushed directly into buffer ##
				$this->return = $this->class::{ $this->method }();

			}

		} else {

			// w__log( 'd:>Calling function: '.$this->function );

			// pass args, if set ##
			if( $this->arguments ){

				// w__log( 'passing args array to: '.$this->function );
				// w__log( $this->arguments );
				// $this->return = call_user_func( $this->function, $this->arguments );
				// if( ! is_array() )
				$this->return = call_user_func_array( $this->function, ( array )$this->arguments );

			} else {

				// w__log( 'NOT passing args array to: '.$this->function );

				// global functions skip internal processing and return their results directly to the buffer ##
				$this->return = call_user_func( $this->function ); // NOTE that calling this function directly was failing silently ##

			}

		}

		// w__log( $this->return );

		if ( ! isset( $this->return ) ) {

			w__log( 'd:>Function "'.$this->function_match.'" did not return a value, perhaps it is a hook or an action?' );

			\willow()->parse->markup->swap( $this->function_match, '', 'php_function', 'string', $process );

			return false;

		}

		// we need to ensure $return is a string ##
		if(
			is_array( $this->return )
		){

			// w__log( $this->return );

			// attempt to convert the array to a string ##
			$this->return = \willow\core\arrays::to_string( $this->return );

			if ( is_string( $this->return ) ) {
				
				w__log( '"'.$open.' '.$this->function.' '.$close.'" returned an array, Willow converted this to a string: "'.$this->return.'"' );

			} else {

				w__log( '"'.$open.' '.$this->function.' '.$close.'" returned an array, Willow failed to convert this to a string.' );

			}
			// w__log( 'return: '.$this->return );

		}

		// filter ##
		// w__log( $this->flags_php_function );
		$_flags_php_function = \willow()->get( '_flags_php_function' );
		if( 
			$_flags_php_function
			&& is_array( $_flags_php_function )
		){

			// w__log( $_flags_php_function );
			// w__log( $this->return );
			// bounce to filter->process() ##
			$filter_return = \willow()->filter->process([ 
				'filters' 	=> $_flags_php_function, 
				'string' 	=> $this->return, 
				'use' 		=> 'php_function', // for filters ##
			]);

			// w__log( $filter_return );

			// check if filters changed value ##
			if( 
				$filter_return // return set ##
				&& '' != $filter_return // not empty ##
				&& $filter_return != $this->return // value chaged ##
			){

				// w__log( 'd:>php_function filters changed value: '.$filter_return );

				// update class property ##
				$this->return = $filter_return;

			}

		}

		// return is still not a string ##
		if(
			! is_string( $this->return )
			&& ! is_integer( $this->return )
		){

			w__log( 'Return from "'.$this->function.'" is not a string or integer, so Willow rejected it' );
			// w__log( $this->return );

			// remove php_function tag from markup ##
			\willow()->parse->markup->swap( $this->function_match, '', 'php_function', 'string', $process );

			// done on this pass ##
			return false;

		}

		// add fields - perhaps we do not always need this -- perhaps based on [null] flag status ##
		\willow()->render->fields->define([
			$this->function_hash => $this->return
		]);

		// replace function tag with raw return value for willw parse ##
		// w__log( $_flags_php_function );
		if( 
			$_flags_php_function
			&& is_array( $_flags_php_function )
			&& ( 
				in_array( 'null', $_flags_php_function ) // defined to 'null' return ##
				|| ! in_array( 'return', $_flags_php_function ) // OR 'return' defined - note that return superseeds null in all cases ##
			)
		){

			// add data returned from function to buffer map ##
			$_buffer_map = \willow()->get( '_buffer_map' );
			$_buffer_map[] = [
				'tag'		=> $this->function_match,
				'output'	=> $this->return,
				'parent'	=> false,
			];
			\willow()->set( '_buffer_map', $_buffer_map );

		} else {

			// w__log( $_flags_php_function );

			// w__log( 'e:>Replacing function: "'.$this->function_match.'" with function return value: '.$this->return );

			$string = $this->return;

			// function returns which update the template also need to update the buffer_map, for later find/replace ##
			// Seems like a potential pain-point ##
			$_markup_template = \willow()->get( '_markup_template' );
			$_markup_template = str_replace( $this->function_match, $string, $_markup_template );
			\willow()->set( '_markup_template', $_markup_template );

			// update markup for willow parse ##
			\willow()->parse->markup->swap( $this->function_match, $string, 'php_function', 'string', $process );

			// remove used flags ##
			/*
			if ( ( $filter_key = array_search( 'return', $_flags_php_function) ) !== false) {
				unset( $_flags_php_function[ $filter_key ] );
			}
			w__log( $_flags_php_function );
			*/

		}
		
		// clear slate ##
		$this->reset();

	}

	public function cleanup( $args = null, $process = 'secondary' ){

		// local vars ##
		$_args = \willow()->get( '_args' );
		$_markup = \willow()->get( '_markup' );
		$_buffer_markup = \willow()->get( '_buffer_markup' );

		$open = trim( \willow()->tags->g( 'php_fun_o' ) );
		$close = trim( \willow()->tags->g( 'php_fun_c' ) );

		// strip all function blocks, we don't need them now ##
		// // $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
		 	'q/render/parse/php_functions/cleanup/regex', 
		 	"/(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|$open.*?$close/ms" 
		// 	// "/{{#.*?\/#}}/ms"
		);

		// w__log( 'e:>Running Function Cleanup' );
		
		// self::$markup['template'] = preg_replace( $regex, "", self::$markup['template'] ); 

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
				
				if( ! isset( $matches[1] )) {

					return "";

				}

				// w__log( $matches );

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					w__log( 'd:>'.$count .' php function tags removed...' );

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
				$_markup['template'] = $string;
				\willow()->set( '_markup', $_markup );

			break ;

			case "primary" :

				// set markup ##
				$_buffer_markup = $string;
				\willow()->set( '_buffer_markup', $_buffer_markup );

			break ;

		} 

	}


}
