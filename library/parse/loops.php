<?php

namespace willow;

use willow;
use willow\core;
use willow\core\helper as h;

/*
About Loops:

- Loops can be embedded in "primary" or "secondary" Willows - so either direclty in the main template or within a .willow file - but we only process Loops on the secondary process cycle ( parse )
- Loops must ALWAYS have a {: scope :} tag
	==> validated when parsing markup
- Loops contain {{ variables }} which can include [ flags ]
	==> each scope is manimpuated to create a unique hash 
- {: scopes :} can be re-used multiple times within the same {~ Willow ~}
	--> meaning, the source markup will show the same {: scope :} ( as this is data connector ) - but Willow needs to manipulate the markup to create a new unique {: scope :} reference and store a map to connect the data correctly
- {: scopes :} pull data from indexed arrays  - whose key matches the {: scope :} value	- returned via the Willow context lookup
	--> meaning, we need to manipulate future data ( as Loops are pared before data is gathered ) - this can be controlled by the scope_map array
- {{ variable }} data can be formatted differently within different {: scope :} loops, within the same {~ Willow ~}
	--> meaning we need to create seperate filter reference for each variable per scope
*/

class loops extends willow\parse {

	private static 
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

	private static function reset(){
		self::$loop_hash = false; 
		self::$loop_scope = false;
		self::$loop_scope_full = false;
		self::$loop_markup = false;
		self::$loop = false;
		self::$config_string = false;
		self::$return = false;
		self::$loop_arguments = false;
		self::$loop_variables = false;
	}


	/**
	 * Format single loop
	 * 
	 * @since 1.0.0
	*/
	public static function format( $match = null, $process = 'secondary', $args = null ){

		// sanity ##
		if(
			is_null( $match )
		){

			h::log( 'e:>No function match or postion passed to format method' );

			return false;

		}

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as variable whitespace is handled by the regex ##
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

		// get markup ##
		self::$loop_markup = core\method::string_between( $match, $scope_close, $loop_close );

		// get scope ##
		self::$loop_scope = self::scope( self::$loop_match );
		self::$loop_scope_full = self::scope( self::$loop_match, true );
		// h::log( 'tagless scope: '.self::$loop_scope );
		// h::log( 'full scope: '.self::$loop_scope_full );

		// h::log( $args['context'] );
		// h::log( $args['context'].' - '.self::$loop_scope.'<br />'.$match );

		// sanity ##
		if ( 
			! isset( self::$loop_scope ) 
			|| ! isset( self::$loop_markup ) 
		){

			h::log( 'e:>Error in returned match key or value' );

			return false; 

		}

		// clean up ##
		self::$loop_scope = trim(self::$loop_scope);
		self::$loop_markup = trim(self::$loop_markup);

		// h::log( self::$loop_markup );

		// look for {{ variables }} inside loop markup string ##
		if ( 
			self::$loop_variables = parse\markup::get( self::$loop_markup, 'variable' ) 
		) {
	
			// log ##
			// h::log( self::$args['task'].'~>d:>"'.count( $variables ) .'" variables found in string');
			// h::log( 'd:>"'.count( self::$loop_variables ) .'" variables found in string');
	
			// h::log( $variables );
	
			// remove any leftover variables in string ##
			foreach( self::$loop_variables as $key => $value ) {
	
				if(
				 	strpos( $value, trim( willow\tags::g( 'arg_o' )) ) !== false // open ##
				 	&& strrpos( $value, trim( willow\tags::g( 'arg_c' )) ) !== false // close ##
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
						// h::log( $arguments_array );

						// debug ##
						// h::log( 'variable "'.$value.'" has arguments: '.$arguments );

						// get fill arguments string - with tags ##
						$arguments_tag = core\method::string_between( $value, trim( tags::g( 'arg_o' )), trim( tags::g( 'arg_c' )), true );

						// store arguments for later use ##
						self::$args = core\method::parse_args( 
							self::$args, 
							$arguments_array
						);

						// h::log( self::$args );

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

		// update "{: scope :}" to  "{: scope__x :}" ##
		self::$loop_scope = self::$loop_scope.'__'.self::$loop_hash;

		// now, we need to edit the markup in two places -- or just one ??
		// create updated loop scope tag ##
		$loop_scope_tag = willow\tags::g( 'sco_o' ).self::$loop_scope.willow\tags::g( 'sco_c' );
		// h::log( 'New loop scope tag: '.$loop_scope_tag );
		// h::log( self::$loop_scope_full );

		// h::log( self::$markup_template );

		// replace markup in principle markup template ##
		self::$markup_template = render\method::str_replace_first( self::$loop_scope_full, $loop_scope_tag, self::$markup_template, 1 );

		// h::log( self::$markup_template );

		// h::log( \willow::$hash );

		// replace stored tag in parent Willow $hash ##
		\willow::$hash['tag'] = str_replace( self::$loop_scope_full, $loop_scope_tag, \willow::$hash['tag'] );

		// h::log( \willow::$hash );

		#*/

		// test what we have ##
		// h::log( self::$markup );
		// h::log( 'process: '.$process );
		// h::log( 'loop_markup: '.self::$loop_markup );
		// h::log( 'loop_scope: '.self::$loop_scope );
		// h::log( 'scope_count: '.self::$scope_count );
		// h::log( \willow::$hash['tag'] );
		// h::log( self::$args );
		// h::log( self::$willow_match );
		// h::log( self::$markup_template );
		// h::log( self::$scope_map );
		// h::log( 'd:>field: "'.self::$loop_scope.'"' );
		// h::log( 'd:>markup: "'.self::$loop_markup.'"' );
		// h::log( 'd:>match: "'.self::$loop_match.'"' );
		// h::log( 'd:>hash: "'.self::$loop_hash.'"' );
		// h::log( 'd:>position: "'.self::$position.'"' );

		// so, we can add a new field value to $args array based on the field name - with the markup as value
		self::$markup[self::$loop_scope] = self::$loop_markup;

		// generate a variable {{ $loop_scope }} ##
		$variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => self::$loop_scope, 'close' => 'var_c' ]);
		// parse\markup::set( $variable, self::$position, 'variable', $process ); // '{{ '.$field.' }}'

		// swap the entire {@ loop_match @} string for a single {{ variable }} matching the passed {: scope :} ##
		parse\markup::swap( self::$loop_match, $variable, 'loop', 'variable', $process ); 

		// h::log( 'd:>variable: "'.$variable.'"' );

		// iterate scope count ##
		// self::$scope_count ++ ;

		// clear slate ##
		self::reset();

	}



	/**
	 * Check if passed string includes a loop 
	*/
	public static function has( $string = null ){

		// sanity ##
		if(
			is_null( $string )
		){

			h::log( 'e:>No string passed to method' );

			return false;

		}

		// get loop tags ##
		$loo_o =  willow\tags::g( 'loo_o' );
		$loo_c =  willow\tags::g( 'loo_c' );

		// test string ##
		// h::log( $string );

		// the passed $string comes from a single Willow and might include one or multiple loops ##
		$loop_count_open = substr_count( $string, trim( $loo_o ) ); // loop openers ##
		$loop_count_close = substr_count( $string, trim( $loo_c ) ); // loop closers ##

		// check ##
		// h::log( 'Count Open: '.$loop_count_open.' ~ Count Close: '.$loop_count_close ); 

		// no loops, return false;
		if( 
			0 === $loop_count_open
			|| 0 === $loop_count_close
		){

			// h::log( 'd:>No loops in passed string, returning false.' );

			return false;

		}

		// if we have multiple loops and the loop open and close counts match, regex loop strings from $string ##

		// else, single loop, so get string between loo_o and loo_c - including tags ##
		if(
			// strpos( $string, trim( $loo_o ) ) !== false
			// && strpos( $string, trim( $loo_c ) ) !== false
			$loop_string = core\method::string_between( $string, trim( $loo_o ), trim( $loo_c ), true )
		){

			// h::log( $loop_string );

			/*
			$loo_o = strpos( $string, trim( $loo_o ) );
			$loo_c = strpos( $string, trim( $loo_c ) );

			h::log( 'd:>Found opening loo_o @ "'.$loo_o.'" and closing loo_c @ "'.$loo_c.'"'  ); 

			// get string between opening and closing args ##
			$return_string = substr( 
				$string, 
				( $loo_o + strlen( trim( $loo_o ) ) ), 
				( $loo_c - $loo_o - strlen( trim( $loo_c ) ) ) ); 

			$return_string = $loo_o.$return_string.$loo_c;
			*/

			// grab loop {: scope :} ##
			$scope = loops::scope( $loop_string );

			// h::log( 'scope: '.$scope );

			// add scope count ##
			// $scope = $scope.'_'.self::$loop_scope_count;

			// h::log( 'scope: '.$scope );

			// h::log( 'e:>$string: "'.$loop_string.'"' );

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
	public static function scope( $string = null, $inclusive = false ){

		// sanity ##
		if(
			is_null( $string )
		){

			h::log( 'e:>No string passed to method' );

			return false;

		}

		// h::log( '$string: '.$string  );

		// alternative method - get position of arg_o and position of LAST arg_c ( in case the string includes additional args )
		if(
			strpos( $string, trim( willow\tags::g( 'sco_o' )) ) !== false
			&& strpos( $string, trim( willow\tags::g( 'sco_c' )) ) !== false
			// @TODO --- this could be more stringent, testing ONLY the first + last 3 characters of the string ??
		){

			// $sco_o = strpos( $string, trim( willow\tags::g( 'sco_o' )) );
			// $sco_c = strrpos( $string, trim( willow\tags::g( 'sco_c' )) );

			// h::log( 'd:>Found opening sco_o & closing sco_c'  ); 

			$scope = core\method::string_between( $string, trim( willow\tags::g( 'sco_o' )), trim( willow\tags::g( 'sco_c' )), $inclusive );
			$scope = trim( $scope );

			/*
			// get string between opening and closing args ##
			$scope = substr( 
				$string, 
				( $sco_o + strlen( trim( willow\tags::g( 'sco_o' ) ) ) ), 
				( $sco_c - $sco_c - strlen( trim( willow\tags::g( 'sco_c' ) ) ) ) ); 

			// $return_string = willow\tags::g( 'loo_o' ).$return_string.$loo_c;
			*/

			// h::log( 'd:>$scope: "'.$scope.'"' );

			// kick back ##
			return $scope;

			// return true;

		}

		// no ##
		return false;

	}




	/**
	 * Scan for sections in markup and convert to variables and $fields
	 * 
	 * @since 4.1.0
	*/
	public static function prepare( $args = null, $process = 'secondary' ){

		// we do NOT need to parse Loops on the primary check
		if( 'primary' == $process ){

			return false;

		}

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

			h::log( 'e:>Error in stored $markup' );

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

		// sanity ##
		if (  
			! $string
			|| is_null( $string )
		){

			h::log( self::$args['task'].'~>e:>Error in $markup' );

			return false;

		}

		// h::log( $args );
		// h::log('d:>'.$string);

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$loop_open = trim( willow\tags::g( 'loo_o' ) );
		$loop_close = str_replace( '/', '\/', ( trim( willow\tags::g( 'loo_c' ) ) ) );

		$regex_find = \apply_filters( 
			'willow/render/markup/loop/regex/find', 
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
				// $position = $matches[0][$match][1]; 

				// take match ##
				$match = $matches[0][$match][0];

				// h::log( $match );

				// h::log( 'd:>position: '.$position );
				// h::log( 'd:>position from 1: '.$matches[0][$match][1] ); 

				// pass match to function handler ##
				self::format( $match, $process, $args );

			}

		}

	}



	public static function cleanup( $args = null, $process = 'secondary' ){

		$open = trim( willow\tags::g( 'loo_o' ) );
		// $close = trim( tags::g( 'sec_c' ) );
		// $end = trim( tags::g( 'sec_e' ) );
		$close = str_replace( '/', '\/', ( trim( willow\tags::g( 'loo_c' ) ) ) );

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

			h::log( 'e:>Error in stored $markup' );

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
