<?php

namespace Q\willow\parse;

use Q\willow;

class loops {

	private 
		$plugin = false,
		// $args = false,
		// $process = false,

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

	public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/**
	 * Scan for sections in markup and convert to variables and $fields
	 * 
	 * @since 4.1.0
	*/
	public function match( $args = null, $process = 'secondary' ){

		// local vars ##
		$_args = $this->plugin->get( '_args' );
		$_markup = $this->plugin->get( '_markup' );
		$_buffer_markup = $this->plugin->get( '_buffer_markup' );

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
					! isset( self::$buffer_markup )
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

			w__log( self::$args['task'].'~>e:>Error in $markup' );

			return false;

		}

		// w__log( $args );
		// w__log('d:>'.$string);

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$loop_open = trim( $this->plugin->get( 'tags' )->g( 'loo_o' ) );
		$loop_close = str_replace( '/', '\/', ( trim( $this->plugin->get( 'tags' )->g( 'loo_c' ) ) ) );

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

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as variable whitespace is handled by the regex ##
		$loop_open = trim( $this->plugin->get( 'tags' )->g( 'loo_o' ) );
		$loop_close = str_replace( '/', '\/', ( trim( $this->plugin->get( 'tags' )->g( 'loo_c' ) ) ) );

		// scope ## - self::$fields data key ##
		$scope_open = trim( $this->plugin->get( 'tags' )->g( 'sco_o' ) );
		$scope_close = trim( $this->plugin->get( 'tags' )->g( 'sco_c' ) );

		// clear slate ##
		self::reset();

		// return entire loop string, including tags for tag swap ##
		self::$loop_match = core\method::string_between( $match, $loop_open, $loop_close, true );
		self::$loop = core\method::string_between( $match, $loop_open, $loop_close );

		// get markup ##
		self::$loop_markup = core\method::string_between( $match, $scope_close, $loop_close );

		// get scope ##
		self::$loop_scope = self::scope( self::$loop_match );
		self::$loop_scope_full = self::scope( self::$loop_match, true );
		// w__log( 'tagless scope: '.self::$loop_scope );
		// w__log( 'full scope: '.self::$loop_scope_full );

		// w__log( $args['context'] );
		// w__log( $args['context'].' - '.self::$loop_scope.'<br />'.$match );

		// sanity ##
		if ( 
			! isset( self::$loop_scope ) 
			|| ! isset( self::$loop_markup ) 
		){

			w__log( 'e:>Error in returned match key or value' );

			return false; 

		}

		// clean up ##
		self::$loop_scope = trim(self::$loop_scope);
		self::$loop_markup = trim(self::$loop_markup);

		// w__log( self::$loop_markup );

		// look for {{ variables }} inside loop markup string ##
		if ( 
			self::$loop_variables = parse\markup::get( self::$loop_markup, 'variable' ) 
		) {
	
			// log ##
			// w__log( self::$args['task'].'~>d:>"'.count( $variables ) .'" variables found in string');
			// w__log( 'd:>"'.count( self::$loop_variables ) .'" variables found in string');
	
			// w__log( $variables );
	
			// remove any leftover variables in string ##
			foreach( self::$loop_variables as $key => $value ) {
	
				if(
				 	strpos( $value, trim( $this->plugin->get( 'tags' )->g( 'arg_o' )) ) !== false // open ##
				 	&& strrpos( $value, trim( $this->plugin->get( 'tags' )->g( 'arg_c' )) ) !== false // close ##
				){

					// get arguments ##
					$arguments = core\method::string_between( $value, trim( tags::g( 'arg_o' )), trim( tags::g( 'arg_c' )) );

					// decode to array ##
					$arguments_array = willow\arguments::decode( $arguments );

					if( 
						$arguments_array 
						&& is_array( $arguments_array )
					) {

						// test array ##
						// w__log( $arguments_array );

						// debug ##
						// w__log( 'variable "'.$value.'" has arguments: '.$arguments );

						// get fill arguments string - with tags ##
						$arguments_tag = core\method::string_between( $value, trim( tags::g( 'arg_o' )), trim( tags::g( 'arg_c' )), true );

						// store arguments for later use ##
						self::$args = core\method::parse_args( 
							self::$args, 
							$arguments_array
						);

						// w__log( self::$args );

						// remove args from variable ##
						self::$loop_markup = str_replace( $arguments_tag, '', self::$loop_markup );

					}

				}
	
			}

		}

		// set loop hash ##
		self::$loop_hash = self::$loop_scope; // hash based only on scope value ## <<--- OLD SCOPE VALUE ##
		
		#/*
		// ## BREAKING CHANGE ##
		// make a hash and store it for this loop ##
		self::$loop_hash = core\method::hash();

		// store map of scopes + hashes ##
		if ( isset( self::$scope_map[self::$loop_scope] ) ) {

			self::$scope_map[self::$loop_scope][] = self::$loop_hash;

		} else {

			self::$scope_map[self::$loop_scope] = [];

			self::$scope_map[self::$loop_scope][] = self::$loop_hash;

		}

		// update "{: scope :}" to  "{: scope__$hash :}" ##
		self::$loop_scope = self::$loop_scope.'__'.self::$loop_hash;

		// w__log( self::$loop_markup ); 

		// now, we need to edit the markup in two places -- or just one ??
		// create updated loop scope tag ##
		$loop_scope_tag = $this->plugin->get( 'tags' )->g( 'sco_o' ).self::$loop_scope.$this->plugin->get( 'tags' )->g( 'sco_c' );
		// w__log( 'New loop scope tag: '.$loop_scope_tag );
		// w__log( self::$loop_scope_full );

		// w__log( self::$markup_template );

		// replace markup in principle markup template ##
		// self::$markup_template = str_replace( self::$loop_scope_full, $loop_scope_tag, self::$markup_template );
		self::$markup_template = render\method::str_replace_first( self::$loop_scope_full, $loop_scope_tag, self::$markup_template );

		// w__log( self::$markup_template );

		// w__log( \willow::$hash );

		// replace stored tag in parent Willow $hash ##
		// \willow::$hash['tag'] = str_replace( self::$loop_scope_full, $loop_scope_tag, \willow::$hash['tag'] );
		\willow::$hash['tag'] = render\method::str_replace_first( self::$loop_scope_full, $loop_scope_tag, \willow::$hash['tag'] );

		// w__log( \willow::$hash );

		#*/

		// test what we have ##
		// w__log( self::$markup );
		// w__log( 'process: '.$process );
		// w__log( 'loop_markup: '.self::$loop_markup );
		// w__log( 'loop_scope: '.self::$loop_scope );
		// w__log( 'scope_count: '.self::$scope_count );
		// w__log( \willow::$hash['tag'] );
		// w__log( self::$args );
		// w__log( self::$willow_match );
		// w__log( self::$markup_template );
		// w__log( self::$scope_map );
		// w__log( 'd:>field: "'.self::$loop_scope.'"' );
		// w__log( 'd:>markup: "'.self::$loop_markup.'"' );
		// w__log( 'd:>match: "'.self::$loop_match.'"' );
		// w__log( 'd:>hash: "'.self::$loop_hash.'"' );
		// w__log( 'd:>position: "'.self::$position.'"' );

		// so, we can add a new field value to $args array based on the loop scope ( including unique hash ) - with the loop_markup as value ##
		self::$markup[self::$loop_scope] = self::$loop_markup;

		// w__log( self::$markup );

		// generate a variable {{ $loop_scope }} ##
		$variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => self::$loop_scope, 'close' => 'var_c' ]);
		// parse\markup::set( $variable, self::$position, 'variable', $process ); // '{{ '.$field.' }}'

		// swap the entire {@ loop_match @} string for a single {{ variable }} matching the passed {: scope__$hash :} ##
		parse\markup::swap( self::$loop_match, $variable, 'loop', 'variable', $process ); 

		// w__log( 'd:>variable: "'.$variable.'"' );

		// iterate scope count ##
		// self::$scope_count ++ ;

		// clear slate ##
		self::reset();

	}



	/**
	 * Check if passed string includes a loop 
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
		$loo_o = $this->plugin->get( 'tags' )->g( 'loo_o' );
		$loo_c = $this->plugin->get( 'tags' )->g( 'loo_c' );

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
			$loop_string = willow\core\method::string_between( $string, trim( $loo_o ), trim( $loo_c ), true )
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
			strpos( $string, trim( $this->plugin->get( 'tags' )->g( 'sco_o' )) ) !== false
			&& strpos( $string, trim( $this->plugin->get( 'tags' )->g( 'sco_c' )) ) !== false
			// @TODO --- this could be more stringent, testing ONLY the first + last 3 characters of the string ??
		){

			// $sco_o = strpos( $string, trim( $this->plugin->get( 'tags' )->g( 'sco_o' )) );
			// $sco_c = strrpos( $string, trim( $this->plugin->get( 'tags' )->g( 'sco_c' )) );

			// w__log( 'd:>Found opening sco_o & closing sco_c'  ); 

			$scope = core\method::string_between( 
				$string, 
				trim( $this->plugin->get( 'tags' )->g( 'sco_o' )), 
				trim( $this->plugin->get( 'tags' )->g( 'sco_c' )), 
				$inclusive 
			);
			$scope = trim( $scope );

			/*
			// get string between opening and closing args ##
			$scope = substr( 
				$string, 
				( $sco_o + strlen( trim( $this->plugin->get( 'tags' )->g( 'sco_o' ) ) ) ), 
				( $sco_c - $sco_c - strlen( trim( $this->plugin->get( 'tags' )->g( 'sco_c' ) ) ) ) ); 

			// $return_string = $this->plugin->get( 'tags' )->g( 'loo_o' ).$return_string.$loo_c;
			*/

			// w__log( 'd:>$scope: "'.$scope.'"' );

			// kick back ##
			return $scope;

			// return true;

		}

		// no ##
		return false;

	}





	public function cleanup( $args = null, $process = 'secondary' ){

		$open = trim( $this->plugin->get( 'tags' )->g( 'loo_o' ) );
		// $close = trim( tags::g( 'sec_c' ) );
		// $end = trim( tags::g( 'sec_e' ) );
		$close = str_replace( '/', '\/', ( trim( $this->plugin->get( 'tags' )->g( 'loo_c' ) ) ) );

		// strip all section blocks, we don't need them now ##
		$regex = \apply_filters( 
			'willow/parse/loops/regex/remove', 
			"/(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|$open.*?$close/ms" 
		);
		// self::$markup['template'] = preg_replace( $regex_remove, "", self::$markup['template'] ); 

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
				// || ! is_array( self::$buffer_markup )
				// || ! isset( self::$buffer_markup['template'] )
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
				self::$markup['template'] = $string;

			break ;

			case "primary" :

				// set markup ##
				self::$buffer_markup = $string;

			break ;

		} 

	}


}
