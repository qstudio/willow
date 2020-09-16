<?php

namespace willow;

use willow;
use willow\core;
use willow\core\helper as h;
use willow\render;

class comments extends willow\parse {

	private static 

		$comment_hash, 
		$comment,
		$comment_match
	
	;


	private static function reset(){

		self::$comment_hash = false; 
		self::$comment = false;
		self::$comment_match = false;
		self::$flags_comment = false;

	}



	/**
	 * Scan for comments in markup and convert to variables and $fields and also to error log ##
	 * 
	 * @since 4.1.0
	*/
	public static function prepare( $args = null, $process = 'internal' ){

		// sanity -- this requires ##
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

			return false;

		}

		// h::log('d:>'.$string);

		// get all comments, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( willow\tags::g( 'com_o' ) );
		$close = trim( willow\tags::g( 'com_c' ) );

		// h::log( 'open: '.$open. ' - close: '.$close );

		$regex_find = \apply_filters( 
			'willow/parse/comments/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
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
				self::$comment = flags::get( self::$comment, 'comment' );
				// h::log( self::$flags_comment );

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
				self::$comment_hash = 'comment__'.\rand();

				// h::log( 'd:>comment hash: "'.self::$comment_hash.'"' );

				// html comments are rendered on the UI, so require to add a variable tag to the markup ##
				if( 
					// isset( self::$flags_comment['h'] )
					self::$flags_comment
					&& is_array( self::$flags_comment )
					&& (
						in_array( 'html', self::$flags_comment )
						|| in_array( 'h', self::$flags_comment ) // shortcut to 'html' ##
					)
				){

					// add data to buffer map ##
					self::$buffer_map[] = [
						'hash'		=> self::$comment_hash,
						'tag'		=> self::$comment_match,
						'output'	=> '<!-- '.self::$comment.' -->',
						'parent'	=> false
					];

				}
				
				// PHP log ##
				if ( 
					// isset( self::$flags_comment['p'] )
					self::$flags_comment
					&& is_array( self::$flags_comment )
					&& (
						in_array( 'php', self::$flags_comment )
						|| in_array( 'p', self::$flags_comment ) // shortcut to 'php' ##
					)
				){

					// also, add a log entry ##
					h::log( 'd:>'.self::$comment );

				}

				// clear slate ##
				self::reset();

			}

		}

		// clean up all tags ##
		// h::log( 't:>MOVED cleanup to after lookup, check if this does not trash other markups and apply to all parse lookups..' );
		// self::cleanup();

	}



	public static function cleanup( $args = null, $process = 'internal' ){

		$open = trim( willow\tags::g( 'com_o' ) );
		$close = trim( willow\tags::g( 'com_c' ) );

		// strip all section blocks, we don't need them now ##
		$regex = \apply_filters( 
			'willow/parse/comments/cleanup/regex', 
			"/(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|$open.*?$close/ms" 
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

			h::log( 'e:>Error in stored $markup: '.$process );

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

					h::log( $count .' comment tags removed...' );

				}

				// return nothing for cleanup ##
				return "";

			}, 
			$string
		);

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
