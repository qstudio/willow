<?php

namespace willow\filter;

use willow;
use willow\core\helper as h;

class method {

	/**
     * Plugin Instance
     *
     * @var     Object      $plugin
     */
	protected 
		$plugin
	;

	/**
	 * Class Constructer 
	*/
	function __construct(){

        // grab plugin instance ## 
		$this->plugin = \willow\plugin::get_instance();
		
	}

	/**
	* prepare passed flags before adding to $filter property
	*
	* formats 2 different filter groups - single letter "flag" types [a] and php "filters" [format:uppercase]
	* For filters, validates that filters have the correct key:value format
	*
	* @since 	1.3.0
	* @return	Mixed
	*/
	public function prepare( $args = null ){

		/*
		One or multiple filters, should be delimited into sets by "," comma

		strip_tags, esc_html
		esc_url
		sanitize_key, strtoupper
		*/

		// sanity ##
		if(
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['filters'] )
			// || ! isset( $args['use'] )
		){

			w__log( 'e:>Error. Missing required args' );

			return false;

		}

		// w__log( 'Filters: '.$args['filters'] );

		// explode at "," comma, into array of key:values ##
		$array = explode( ',', $args['filters'] );

		// clean up array ##
		$array = array_map( 'trim', $array );

		// clean up array ##
		$array = array_filter( $array );

		// w__log( $array );

		// kick back ##
		return $array;

	}

	/**
	* Apply assigned filter function
	* 
	* Filters function before calling, so can be replaced with alternative
	* Validates that assigned function in $filters, based on defined $filter value, exists and is callable
	*
	* @since 	1.3.0
	* @return	Mixed
	*/
	public function process( $args = null ) {

		// w__log( $args );

		// sanity ##
		if(
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['filters'] ) // array of filters
			|| ! is_array( $args['filters'] ) // note, this should be an array
			|| ! isset( $args['use'] ) // "tag" || "variable",
			|| ! isset( $args['string'] ) // string to apply filter to ##
		){

			w__log( 'e:>Missing required args' );

			return $args['string'];

		}

		// we need a string, but we've been passed an integer, let's cast it ##
		/*
		if( 
			filter_var( $args['string'], FILTER_VALIDATE_INT) !== false
		){

			w__log( 'e:>Passed $string is actually an integer: "'.$args['string'].'" Willow will cast it to a string value' );
			// w__log( $args['string'] );

			$args['string'] = (string) $args['string'];

		}
		*/

		// we need a string, so validate format ##
		if( 
			! is_string( $args['string'] ) // not a string 
			&& filter_var( $args['string'], FILTER_VALIDATE_INT) === false // && not an integer
		){

			w__log( 'e:>Passed $string is not in a valid string or integer format' );
			w__log( $args['string'] );

			return $args['string'];

		}

		// w__log( $args['string'] );

		/*
		// now, we need to prepare the flags, if any, from the passed string ##
		$filters = core\method::string_between( $args['string'], trim( $this->plugin->tags->g( 'fla_o' )), trim( $this->plugin->tags->g( 'fla_c' )) );

		w__log( $filters );

		$args['filters'] = self::prepare([ 'filters' => $filters ]);

		// if not flags -> no filters, return ##
		if(
			! $args['filters']
			|| ! is_array( $args['filters'] )
			|| empty( $args['filters'] )
		){

			w__log( 'd:>There are no flags in the string, returning.' );
			w__log( $args['string'] );

			return $args['string'];

		}
		*/

		// w__log( $args['filters'] );

		// get filters ##
		$_filters = $this->plugin->get( '_filters' );
			
		// load all stored filters, if filters_loaded is empty ##
		if( true !== $this->plugin->get( '_filters_filtered' ) ){

			$_filters = \apply_filters( 'willow/filters', $_filters );
			// $this->plugin->set( '_filters', $_filters );

			// update tracker, so we don't load again this life-cycle ##
			$this->plugin->set( '_filters_filtered', true );

		}

		/*
		allowed $filters are stored in an array, with the following format

		$filters 	= [
			'0' => 'esc_html',
			'1' => 'strtolower'
			'2' => 'etc'
		]
		*/

		// we are passed a string and will return a string ##
		$return = $args['string'];

		/*
		passed $args['filters'] contains an array in the following format:

		Array (
			'0' => 'esc_html',
			'1' => 'strtolower'
		)
		*/

		// w__log( $_filters );
		// w__log( $args['filters'] );

		// now, loop over each filter, allow it to be altered ( via apply_filters ) validate it exists and run it
		foreach( $args['filters'] as $function ) {

			// w__log( 'e:>Filter Function: '.$function.' -- use: '.$args['use'] );

			// check that requested function is in the allowed list - which has now passed by the load filter ##
			// @@@TODO -- this logic is messy, what are we skipping and why ?? ####
			if (
				! in_array( $function, $_filters )
			){

				// No need to warn about missing $flags ##
				if( 
					'tag' !== $args['use']
					&& 'variable' !== $args['use']
					&& ! in_array( $function, $this->plugin->get( '_flags' ) )
				) {

					// w__log( self::$flags );

					w__log( 'e:>Filter: "'.$function.'" is not available for use case: "'.$args['use'].'"' );

					// carry on.. ##
					continue;

				}

			}

			// get function value from $filters matching request ##
			// w__log( '$function: '.$function );

			// filter function - allows for replacement by use-case ( tag OR variable ) ##
			$function = \apply_filters( 'willow/filter/apply/'.$function.'/'.$args['use'], $function );

			// sanitize function name -- in case something funky was returned by filters or altered in the default list ##
			$function = willow\core\method::sanitize( $function, 'php_function' );

			// check clean function name ##
			// w__log( '$function: '.$function );

			// check if function exists ##
			if ( 
				! function_exists( $function ) 
				|| ! is_callable( $function ) 
			) {

				w__log( 'e:>Function "'.$function.'" does not exist or is not callable' );

				continue;

			}

			// apply filter function ##
			// note that functions run in passed sequence, updating the current variable state ##
			$return = $function( $return );

			// w__log( '$return: '.$return );

		}

		// kick it backm once complete ##
		return $return;

	}


}
