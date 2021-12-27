<?php

namespace willow\parse;

use willow;

class flags {

	/**
	 * Construct
     */
    public function __construct(){

		// silence ##

	}

	/**
	 * Check if passed string contains flags
	*/
	public function has( $string = null ){

		// @todo - sanity ##
		if(
			is_null( $string )
		){

			w__log( 'e:>No string passed to method' );

			return false;

		}

		// alternative method - get position of arg_o and position of LAST arg_c ( in case the string includes additional args )
		if(
			strpos( $string, trim( \willow()->tags->g( 'fla_o' )) ) !== false
			&& strpos( $string, trim( \willow()->tags->g( 'fla_c' )) ) !== false
			// @TODO --- this could be more stringent, testing ONLY the first + last 3 characters of the string ??
		){

			// $fla_o = strpos( $string, trim( \willow()->tags->g( 'fla_o' )) );
			// $fla_c = strrpos( $string, trim( \willow()->tags->g( 'fla_c' )) );
			/*
			w__log( 'e:>Found opening loo_o @ "'.$loo_o.'" and closing loo_c @ "'.$loo_c.'"'  ); 

			// get string between opening and closing args ##
			$return_string = substr( 
				$string, 
				( $loo_o + strlen( trim( \willow()->tags->g( 'loo_o' ) ) ) ), 
				( $loo_c - $loo_o - strlen( trim( \willow()->tags->g( 'loo_c' ) ) ) ) ); 

			$return_string = \willow()->tags->g( 'loo_o' ).$return_string.\willow()->tags->g( 'loo_c' );

			// w__log( 'e:>$string: "'.$return_string.'"' );

			return $return_string;
			*/

			// w__log( 'd:>Found opening fla_o @ "'.$fla_o.'" and closing fla_c @ "'.$fla_c.'"'  ); 

			return true;

		}

		// no ##
		return false;

	}
	
	/*
	Decode flags passed in string

	Requirements: 

	[ esc_html, strip_tags ] = split, escape etc ##
	[ array ] = array
	*/
	public function get( $string = null, $use = 'willow' ){

		// sanity ##
		if(
			is_null( $string )
		){

			w__log( 'e:>Error in passed arguments.' );

			// odd, but ok ##
			return $string;

		}

		// w__log( $string );

		// sanity ##
		if(
			willow\core\strings::starts_with( 
				$string, 
				trim( \willow()->tags->g( 'fla_o' ) ) 
			)
			&& $flags = willow\core\strings::between( 
				$string, 
				trim( \willow()->tags->g( 'fla_o' ) ), 
				trim( \willow()->tags->g( 'fla_c' ) ) 
			)
		){

			// trim ##
			$flags = trim( $flags );

			// prepare flags / filters ##
			$flags_array = \willow()->filter->prepare([ 'filters' => $flags, 'use' => $use ] );
			
			// w__log( $flags_array );
			// w__log( 'use: '.$use );

			// assign filters based on use-case ##
			switch( $use ) {

				default :
				case "willow" :

					// @todo - validate that flags are allowed against self::$flags_willows ##

					// $this->flags_willow = $flags_array;
					\willow()->set( '_flags_willow', $flags_array );

				break ;

				case "php_function" :

					// @todo - validate that flags are allowed against $this->flags_php ##

					// $this->flags_php_function = $flags_array;
					\willow()->set( '_flags_php_function', $flags_array );

				break ;

				case "php_variable" :

					// @todo - validate that flags are allowed against $this->flags_php ##

					// $this->flags_php_variable = $flags_array;
					\willow()->set( '_flags_php_variable', $flags_array );

				break ;

				case "comment" :

					// @todo - validate that flags are allowed against $this->flags_comment ##

					// $this->flags_comment = $flags_array;
					\willow()->set( '_flags_comment', $flags_array );

				break ;

				case "variable" :

					// varialbe flags are validated just before they are applied ##

					// $this->flags_variable = $flags_array;
					\willow()->set( '_flags_variable', $flags_array );

				break ;

				case "argument" :

					// @todo - validate that flags are allowed again $this->flags_argument ##

					// $this->flags_argument = $flags_array;
					\willow()->set( '_flags_argument', $flags_array );

				break ;

			}

			// get entire string, with tags ##
			$flags_all = willow\core\strings::between( 
				$string, 
				trim( \willow()->tags->g( 'fla_o' ) ), 
				trim( \willow()->tags->g( 'fla_c' ) ), 
				true 
			);

			// remove flags from passed string ##
			$string = str_replace( $flags_all, '', $string );

			// kick it back ##
			return $string;

		}

		// w__log( 'd:>No flags found, returning passed string.' );

		// kick it back whole, as no flags found ##
		return $string;
		
	}

	public function cleanup( $args = null, $process = 'secondary' ){

		$open = trim( \willow()->tags->g( 'fla_o' ) );
		$close = trim( \willow()->tags->g( 'fla_c' ) );

		// w__log( self::$markup['template'] );

		// strip all function blocks, we don't need them now ##
		$regex = \apply_filters( 
		 	'willow/parse/flags/cleanup/regex', 
			"/\\$open.*?\\$close/"
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

					w__log( $count .' flags removed...' );

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
