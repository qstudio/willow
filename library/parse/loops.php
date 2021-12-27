<?php

namespace willow\parse;

use willow;

class loops {

	private 
		$loop_hash, 
		$loop,
		$loop_match, // full string matched ##
		$loop_scope,
		$loop_scope_full,
		$loop_markup,
		$loop_arguments,
		$loop_variables,
		$config_string,
		$return
	;

	private function reset(){
		$this->loop_hash = false; 
		$this->loop_scope = false;
		$this->loop_scope_full = false;
		$this->loop_markup = false;
		$this->loop = false;
		$this->config_string = false;
		$this->return = false;
		$this->loop_arguments = false;
		$this->loop_variables = false;
	}

	/**
	 * Construct
     */
    public function __construct(){

		// silence ##

	}

	/**
	 * Scan for sections in markup and convert to variables and $fields
	 * 
	 * @since 4.1.0
	*/
	public function match( $args = null, $process = 'secondary' ){

		// local vars ##
		$_args = \willow()->get( '_args' );
		$_markup = \willow()->get( '_markup' );
		$_buffer_markup = \willow()->get( '_buffer_markup' );

		// we do NOT need to parse Loops on the primary check
		if( 'primary' == $process ){

			return false;

		}

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

			w__log( $_args['task'].'~>e:>Error in $markup' );

			return false;

		}

		// w__log( $args );
		// w__log('d:>'.$string);

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$loop_open = trim( \willow()->tags->g( 'loo_o' ) );
		$loop_close = str_replace( '/', '\/', ( trim( \willow()->tags->g( 'loo_c' ) ) ) );

		$regex_find = \apply_filters( 
			'willow/render/markup/loop/regex/find', 
			"/$loop_open\s+(.*?)\s+$loop_close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
			// "/{{#(.*?)\/#}}/s" 
		);

		if ( 
			preg_match_all( $regex_find, $string, $matches, PREG_OFFSET_CAPTURE ) 
		){

			// w__log( $matches );

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

				// get position from first ( whole string ) match ##
				// $position = $matches[0][$match][1]; 

				// take match ##
				$match = $matches[0][$match][0];

				// w__log( $match );

				// w__log( 'd:>position: '.$position );
				// w__log( 'd:>position from 1: '.$matches[0][$match][1] ); 

				// pass match to function handler ##
				$this->format( $match, $process, $args );

			}

		}

	}

	/**
	 * Format single loop
	 * 
	 * @since 1.0.0
	*/
	public function format( $match = null, $process = 'secondary', $args = null ){

		// sanity ##
		if(
			is_null( $match )
		){

			w__log( 'e:>No function match or postion passed to format method' );

			return false;

		}

		$_markup = \willow()->get( '_markup' );
		$_args = \willow()->get( '_args' );

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as variable whitespace is handled by the regex ##
		$loop_open = trim( \willow()->tags->g( 'loo_o' ) );
		$loop_close = str_replace( '/', '\/', ( trim( \willow()->tags->g( 'loo_c' ) ) ) );

		// scope ## - $_fields data key ##
		$scope_open = trim( \willow()->tags->g( 'sco_o' ) );
		$scope_close = trim( \willow()->tags->g( 'sco_c' ) );

		// clear slate ##
		$this->reset();

		// return entire loop string, including tags for tag swap ##
		$this->loop_match = willow\core\strings::between( $match, $loop_open, $loop_close, true );
		$this->loop = willow\core\strings::between( $match, $loop_open, $loop_close );

		// get markup ##
		$this->loop_markup = willow\core\strings::between( $match, $scope_close, $loop_close );
		// w__log( $this->loop_markup );

		// get scope ##
		$this->loop_scope = $this->scope( $this->loop_match );
		$this->loop_scope_full = $this->scope( $this->loop_match, true );
		// w__log( 'tagless scope: '.$this->loop_scope );
		// w__log( 'full scope: '.$this->loop_scope_full );

		// w__log( $args['context'] );
		// w__log( $args['context'].' - '.$this->loop_scope.'<br />'.$match );

		// sanity ##
		if ( 
			! isset( $this->loop_scope ) 
			|| ! isset( $this->loop_markup ) 
		){

			w__log( 'e:>Error in returned match key or value' );

			return false; 

		}

		// clean up ##
		$this->loop_scope = trim( $this->loop_scope );
		$this->loop_markup = trim( $this->loop_markup );

		// w__log( $this->loop_markup );

		// look for {{ variables }} inside loop markup string ##
		if ( 
			$this->loop_variables = \willow()->parse->markup->get( $this->loop_markup, 'variable' ) 
		) {
	
			// log ##
			// w__log( $_args['task'].'~>d:>"'.count( $variables ) .'" variables found in string');
			// w__log( 'd:>"'.count( $this->loop_variables ) .'" variables found in string');
	
			// w__log( $variables );
	
			// remove any leftover variables in string ##
			foreach( $this->loop_variables as $key => $value ) {
	
				if(
				 	strpos( $value, trim( \willow()->tags->g( 'arg_o' )) ) !== false // open ##
				 	&& strrpos( $value, trim( \willow()->tags->g( 'arg_c' )) ) !== false // close ##
				){

					// get arguments ##
					$arguments = willow\core\strings::between( 
						$value, 
						trim( \willow()->tags->g( 'arg_o' )), 
						trim( \willow()->tags->g( 'arg_c' )) 
					);

					// decode to array ##
					$arguments_array = \willow()->parse->arguments->decode( $arguments );

					if( 
						$arguments_array 
						&& is_array( $arguments_array )
					) {

						// test array ##
						// w__log( $arguments_array );

						// debug ##
						// w__log( 'variable "'.$value.'" has arguments: '.$arguments );

						// get fill arguments string - with tags ##
						$arguments_tag = willow\core\strings::between( 
							$value, 
							trim( \willow()->tags->g( 'arg_o' )), 
							trim( \willow()->tags->g( 'arg_c' )), 
							true 
						);

						// store arguments for later use ##
						$_args = willow\core\arrays::parse_args( 
							$_args, 
							$arguments_array
						);

						// w__log( 'e:>Setting args also' );

						// set value ##
						\willow()->set( '_args', $_args );

						// w__log( $_args );

						// remove args from variable ##
						$this->loop_markup = str_replace( $arguments_tag, '', $this->loop_markup );

					}

				}
	
			}

		}

		// set loop hash ##
		$this->loop_hash = $this->loop_scope; // hash based only on scope value ## <<--- OLD SCOPE VALUE ##
		
		// make a hash and store it for this loop ##
		$this->loop_hash = willow\core\strings::hash();

		// store map of scopes + hashes ##
		$_scope_map = \willow()->get( '_scope_map' );

		if ( isset( $_scope_map[ $this->loop_scope ] ) ) {

			$_scope_map[$this->loop_scope][] = $this->loop_hash;

		} else {

			$_scope_map[$this->loop_scope] = [];

			$_scope_map[$this->loop_scope][] = $this->loop_hash;

		}

		\willow()->set( '_scope_map', $_scope_map );

		// update "{: scope :}" to  "{: scope__$hash :}" ##
		$this->loop_scope = $this->loop_scope.'__'.$this->loop_hash;

		// w__log( $this->loop_markup ); 

		// now, we need to edit the markup in two places -- or just one ??
		// create updated loop scope tag ##
		$loop_scope_tag = \willow()->tags->g( 'sco_o' ).$this->loop_scope.\willow()->tags->g( 'sco_c' );
		// w__log( 'New loop scope tag: '.$loop_scope_tag );
		// w__log( $this->loop_scope_full );

		// w__log( self::$markup_template );

		// replace markup in principle markup template ##
		// self::$markup_template = str_replace( self::$loop_scope_full, $loop_scope_tag, self::$markup_template );
		$_markup_template = \willow()->get( '_markup_template' );
		$_markup_template = willow\core\strings::str_replace_first( $this->loop_scope_full, $loop_scope_tag, $_markup_template );
		\willow()->set( '_markup_template', $_markup_template );

		// w__log( $_markup_template );

		// w__log( \willow::$hash );

		// replace stored tag in parent Willow $hash ##
		// \willow::$hash['tag'] = str_replace( self::$loop_scope_full, $loop_scope_tag, \willow::$hash['tag'] );
		$_hash = \willow()->get( '_hash' );
		// w__log( $_hash );
		$_hash['tag'] = willow\core\strings::str_replace_first( $this->loop_scope_full, $loop_scope_tag, $_hash['tag'] );
		\willow()->set( '_hash', $_hash );

		// w__log( \willow::$hash );

		// test what we have ##
		// w__log( $_markup );
		// w__log( 'process: '.$process );
		// w__log( 'loop_markup: '.$this->loop_markup );
		// w__log( 'loop_scope: '.$this->loop_scope );
		// w__log( 'scope_count: '.$this->scope_count );
		// w__log( $_hash['tag'] );
		// w__log( $_args );
		// w__log( $this->willow_match );
		// w__log( $_markup_template );
		// w__log( $this->scope_map );
		// w__log( 'd:>field: "'.$this->loop_scope.'"' );
		// w__log( 'd:>markup: "'.$this->loop_markup.'"' );
		// w__log( 'd:>match: "'.$this->loop_match.'"' );
		// w__log( 'd:>hash: "'.$this->loop_hash.'"' );
		// w__log( 'd:>position: "'.$this->position.'"' );

		// so, we can add a new field value to $args array based on the loop scope ( including unique hash ) - with the loop_markup as value ##
		// self::$markup[self::$loop_scope] = self::$loop_markup;
		$_markup = \willow()->get( '_markup' );
		$_markup[ $this->loop_scope ] = $this->loop_markup;
		\willow()->set( '_markup', $_markup );

		// w__log( $_markup );

		// generate a variable {{ $loop_scope }} ##
		$variable = \willow()->tags->wrap([ 'open' => 'var_o', 'value' => $this->loop_scope, 'close' => 'var_c' ]);
		// w__log( $variable );
		// parse\markup::set( $variable, self::$position, 'variable', $process ); // '{{ '.$field.' }}'

		// swap the entire {@ loop_match @} string for a single {{ variable }} matching the passed {: scope__$hash :} ##
		\willow()->parse->markup->swap( $this->loop_match, $variable, 'loop', 'variable', $process ); 

		// w__log( 'd:>variable: "'.$variable.'"' );

		// clear slate ##
		$this->reset();

	}

	/**
	 * Check if passed string includes a loop 
	 * 
	 * @param	$string		String
	 * @since 	1.0.0
	 * @return	Boolean
	*/
	public function has( $string = null ){

		// sanity ##
		if(
			is_null( $string )
		){

			w__log( 'e:>No string passed to method' );

			return false;

		}

		// get loop tags ##
		$loo_o = \willow()->tags->g( 'loo_o' );
		$loo_c = \willow()->tags->g( 'loo_c' );

		// test string ##
		// w__log( $string );

		// the passed $string comes from a single Willow and might include one or multiple loops ##
		$loop_count_open = substr_count( $string, trim( $loo_o ) ); // loop openers ##
		$loop_count_close = substr_count( $string, trim( $loo_c ) ); // loop closers ##

		// check ##
		// w__log( 'Count Open: '.$loop_count_open.' ~ Count Close: '.$loop_count_close ); 

		// no loops, return false;
		if( 
			0 === $loop_count_open
			|| 0 === $loop_count_close
		){

			// w__log( 'd:>No loops in passed string, returning false.' );

			return false;

		}

		// if we have multiple loops and the loop open and close counts match, regex loop strings from $string ##

		// else, single loop, so get string between loo_o and loo_c - including tags ##
		if(
			// strpos( $string, trim( $loo_o ) ) !== false
			// && strpos( $string, trim( $loo_c ) ) !== false
			$loop_string = willow\core\strings::between( $string, trim( $loo_o ), trim( $loo_c ), true )
		){

			// w__log( $loop_string );

			/*
			$loo_o = strpos( $string, trim( $loo_o ) );
			$loo_c = strpos( $string, trim( $loo_c ) );

			w__log( 'd:>Found opening loo_o @ "'.$loo_o.'" and closing loo_c @ "'.$loo_c.'"'  ); 

			// get string between opening and closing args ##
			$return_string = substr( 
				$string, 
				( $loo_o + strlen( trim( $loo_o ) ) ), 
				( $loo_c - $loo_o - strlen( trim( $loo_c ) ) ) ); 

			$return_string = $loo_o.$return_string.$loo_c;
			*/

			// grab loop {: scope :} ##
			$scope = $this->scope( $loop_string );

			// w__log( 'scope: '.$scope );

			// add scope count ##
			// $scope = $scope.'_'.self::$loop_scope_count;

			// w__log( 'scope: '.$scope );

			// w__log( 'e:>$string: "'.$loop_string.'"' );

			// iterate loop count ##
			// self::$loop_scope_count ++ ;

			// return true;

			// return array with markup + scope ##
			return [ 
				'markup' 	=> $loop_string,
				'scope'		=> $scope
			];

		}

		// backup ##
		return false;

	}

	/**
	 * Check if passed string includes a {: scope :} 
	 * 
	 * @param 	$inclusive 	Boolean 	fallows to return whole scope string inside tags
	 * 
	*/
	public function scope( $string = null, $inclusive = false ){

		// sanity ##
		if(
			is_null( $string )
		){

			w__log( 'e:>No string passed to method' );

			return false;

		}

		// w__log( '$string: '.$string  );

		// alternative method - get position of arg_o and position of LAST arg_c ( in case the string includes additional args )
		if(
			strpos( $string, trim( \willow()->tags->g( 'sco_o' )) ) !== false
			&& strpos( $string, trim( \willow()->tags->g( 'sco_c' )) ) !== false
			// @TODO --- this could be more stringent, testing ONLY the first + last 3 characters of the string ??
		){

			// $sco_o = strpos( $string, trim( \willow()->tags->g( 'sco_o' )) );
			// $sco_c = strrpos( $string, trim( \willow()->tags->g( 'sco_c' )) );

			// w__log( 'd:>Found opening sco_o & closing sco_c'  ); 

			$scope = willow\core\strings::between( 
				$string, 
				trim( \willow()->tags->g( 'sco_o' )), 
				trim( \willow()->tags->g( 'sco_c' )), 
				$inclusive // option to return tag ##
			);
			$scope = trim( $scope );

			/*
			// get string between opening and closing args ##
			$scope = substr( 
				$string, 
				( $sco_o + strlen( trim( \willow()->tags->g( 'sco_o' ) ) ) ), 
				( $sco_c - $sco_c - strlen( trim( \willow()->tags->g( 'sco_c' ) ) ) ) ); 

			// $return_string = \willow()->tags->g( 'loo_o' ).$return_string.$loo_c;
			*/

			// w__log( 'd:>$scope: "'.$scope.'"' );

			// kick back ##
			return $scope;

			// return true;

		}

		// no ##
		return false;

	}

	/***/
	public function cleanup( $args = null, $process = 'secondary' ){

		// local vars ##
		$_args = \willow()->get( '_args' );
		$_markup = \willow()->get( '_markup' );
		$_buffer_markup = \willow()->get( '_buffer_markup' );

		$open = trim( \willow()->tags->g( 'loo_o' ) );
		$close = str_replace( '/', '\/', ( trim( \willow()->tags->g( 'loo_c' ) ) ) );

		// strip all section blocks, we don't need them now ##
		$regex = \apply_filters( 
			'willow/parse/loops/regex/remove', 
			"/(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|$open.*?$close/ms" 
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
				// || ! is_array( $_buffer_markup )
				// || ! isset( $_buffer_markup['template'] )
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

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					w__log( $count .' loop tags removed...' );

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
