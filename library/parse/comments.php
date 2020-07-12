<?php

namespace q\willow;

use q\willow;
use q\willow\core;
use q\core\helper as h;
use q\willow\render;

class comments extends willow\parse {

	private static 

		$hash, 
		$comment,
		$comment_match
		// $flags
		// $position
	
	;


	private static function reset(){

		self::$hash = false; 
		self::$comment = false;
		self::$comment_match = false;
		self::$flags = false;
		// self::$position = false;

	}



	/**
	 * Scan for comments in markup and convert to variables and $fields and also to error log ##
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
		){

			h::log( self::$args['task'].'~>e:>Error in $markup' );

			return false;

		}

		// h::log('d:>'.$string);

		// get all comments, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( willow\tags::g( 'com_o' ) );
		$close = trim( willow\tags::g( 'com_c' ) );

		// h::log( 'open: '.$open. ' - close: '.$close );

		$regex_find = \apply_filters( 
			'q/willow/parse/comments/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
			// "/{{#(.*?)\/#}}/s" 
		);

		// h::log( 't:> allow for badly spaced tags around sections... whitespace flexible..' );
		if ( 
			preg_match_all( $regex_find, $string, $matches, PREG_OFFSET_CAPTURE ) 
		){

			// h::log( $matches[1] );

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

				// clear slate ##
				self::reset();

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

				// h::log( 'd:>Searching for comments data...' );

				// self::$position = $matches[0][$match][1]; // take from first array ##
				// h::log( 'd:>position: '.$position );
				// h::log( 'd:>position from 1: '.$matches[0][$match][1] ); 
				
				// get a single comment ##
				self::$comment = core\method::string_between( $matches[0][$match][0], $open, $close );

				// return entire function string, including tags for tag swap ##
				self::$comment_match = core\method::string_between( $matches[0][$match][0], $open, $close, true );

				// look for flags ##
				// self::flags();
				self::$comment = flags::get( self::$comment );
				// h::log( self::$flags );
				/*
				if(
					strstr( self::$comment, trim( willow\tags::g( 'fla_o' ) ) )
					&& strstr( self::$comment, trim( willow\tags::g( 'fla_c' ) ) )
				){

					h::log( 'd:>FOUND flags...' );

					$flags = trim(
						core\method::string_between( 
							self::$comment, 
							trim( willow\tags::g( 'fla_o' ) ), 
							trim( willow\tags::g( 'fla_c' ) ) 
						)
					);

					self::$flags = str_split( $flags );
					self::$flags = array_fill_keys( self::$flags, true );
					// h::log( self::$flags );

					// remove flags ##
					self::$comment = str_replace( $flags, '', self::$comment );

				}
				*/

				// sanity ##
				if ( 
					! isset( self::$comment ) 
				){

					h::log( 'e:>Error in returned match function' );

					continue; 

				}

				// clean up ##
				self::$comment = trim(self::$comment);

				// test what we have ##
				// h::log( 'd:>comment: "'.self::$comment.'"' );

				// hash ##
				self::$hash = 'comment__'.\mt_rand();

				// no escaping.. yet
				// $config = [ 'config' => [ 'escape' => false ] ];
				// h::log( $config );
				// if ( ! isset( self::$args[$hash] ) ) self::$args[$hash] = [];
				// self::$args[$hash] = \q\core\method::parse_args( $config, self::$args[$hash] );
				// h::log( self::$args );

				// default is an html comment - also indicated with flag 'h', if set ##
				if ( 
					empty( self::$flags ) 
					|| isset( self::$flags['h'] )
				){

					// so, we can add a new field value to $args array based on the field name - with the comment as value
					render\fields::define([
						self::$hash 		=> '<!-- '.self::$comment.' -->',
					]);

					// add a variable "{{ $field }}" before this comment block to markup->template ##
					$variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => self::$hash, 'close' => 'var_c' ]);
					willow\markup::swap( self::$comment_match, $variable, 'comment', 'variable' ); // '{{ '.$field.' }}'

				}
				
				if ( 
					isset( self::$flags['p'] )
				){

					// also, add a log entry ##
					h::log( 'd:>'.self::$comment );

				}

				// clear slate ##
				self::reset();

			}

		}

	}



	/*
	public static function flags( $string = null ){

		// sanity ##
		h::log( 't:>make flags::prepare() method, make $flags a property of parse.. ' );

		if(
			// strstr( self::$comment, trim( willow\tags::g( 'fla_o' ) ) )
			// && strstr( self::$comment, trim( willow\tags::g( 'fla_c' ) ) )
			core\method::starts_with( self::$comment, trim( willow\tags::g( 'fla_o' ) ) )
			&& $flags = core\method::string_between( self::$comment, trim( willow\tags::g( 'fla_o' ) ), trim( willow\tags::g( 'fla_c' ) ) )
		){

			h::log( 'd:>FOUND flags...' );

			$flags = trim(
				core\method::string_between( 
					self::$comment, 
					trim( willow\tags::g( 'fla_o' ) ), 
					trim( willow\tags::g( 'fla_c' ) ) 
				)
			);

			self::$flags = str_split( $flags );
			self::$flags = array_fill_keys( self::$flags, true );
			// h::log( self::$flags );

			$flags_all = core\method::string_between( self::$comment, trim( willow\tags::g( 'fla_o' ) ), trim( willow\tags::g( 'fla_c' ) ), true );

			// remove flags ##
			self::$comment = str_replace( $flags_all, '', self::$comment );

			// kick it back ##
			// return $string

		}

	}
	*/


	public static function cleanup( $args = null ){

		$open = trim( willow\tags::g( 'com_o' ) );
		$close = trim( willow\tags::g( 'com_c' ) );

		// strip all section blocks, we don't need them now ##
		// $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
			'q/render/parse/comment/cleanup/regex', 
			"/$open.*?$close/ms" 
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

				// h::log( $matches );

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					h::log( $count .' comment tags removed...' );

				}

				// return nothing for cleanup ##
				return "";

			}, 
			self::$markup['template'] 
		);

	}




}
