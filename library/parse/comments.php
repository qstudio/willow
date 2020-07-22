<?php

namespace q\willow;

use q\willow;
use q\willow\core;
use q\core\helper as h;
use q\willow\render;

class comments extends willow\parse {

	private static 

		$comment_hash, 
		$comment,
		$comment_match
		// $flags_comment
		// $flags
		// $position
	
	;


	private static function reset(){

		self::$comment_hash = false; 
		self::$comment = false;
		self::$comment_match = false;
		self::$flags_comment = false;
		// self::$position = false;

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
				self::$comment = flags::get( self::$comment, 'comment' );
				// h::log( self::$flags_comment );
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
				self::$comment_hash = 'comment__'.\rand();

				// h::log( 'd:>comment hash: "'.self::$comment_hash.'"' );

				// no escaping.. yet
				// $config = [ 'config' => [ 'escape' => false ] ];
				// h::log( $config );
				// if ( ! isset( self::$args[$comment_hash] ) ) self::$args[$comment_hash] = [];
				// self::$args[$comment_hash] = \q\core\method::parse_args( $config, self::$args[$comment_hash] );
				// h::log( self::$args );

				// html comments are rendered on the UI, so require to add a variable tag to the markup ##
				if ( 
					isset( self::$flags_comment['h'] )
				){

					// so, we can add a new field value to $args array based on the field name - with the comment as value
					render\fields::define([
						self::$comment_hash 		=> '<!-- '.self::$comment.' -->',
					]);

					// add data to buffer map ##
					self::$buffer_map[] = [
						'output'	=> '<!-- '.self::$comment.' -->',
						'tag'		=> self::$comment_match,
						'master'	=> false,
					];

					// self::$buffer_fields[ self::$comment_hash ] = '<!-- '.self::$comment.' -->';

					// add a variable "{{ $field }}" before this comment block to markup->template ##
					$variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => self::$comment_hash, 'close' => 'var_c' ]);
					parse\markup::swap( self::$comment_match, $variable, 'comment', 'variable', $process ); 

					// update match string ##
					// self::$comment_match = $variable;

				}
				
				// PHP log ##
				if ( 
					isset( self::$flags_comment['p'] )
				){

					// also, add a log entry ##
					h::log( 'd:>'.self::$comment );

					// remove from markup ##
					parse\markup::swap( self::$comment_match, '', 'comment', 'string', $process ); 

				}

				// default is a "silent" comment - also indicated with flag 's', if set ##
				// this comment is not rendered in html or PHP and the willow tag is removed ##
				// this goes last, as we might need the match reference for previous replacements ##
				if ( 
					empty( self::$flags_comment ) 
					|| isset( self::$flags_comment['s'] )
				){

					// h::log( 'Silent comment, we need to remove the tag' );

					// find out which markup to affect ##
					switch( $process ){

						default : 
						case "internal" :

							// get markup ##
							self::$markup['template'] = parse\markup::remove( self::$comment_match, self::$markup['template'], 'comment' );

						break ;

						case "buffer" :

							// get markup ##
							// $string = self::$buffer_markup;
							self::$buffer_markup = parse\markup::remove( self::$comment_match, self::$buffer_markup, 'comment' );

						break ;

					} 

				}

				self::$buffer_fields[ self::$comment_hash ] = '<!-- '.self::$comment.' -->';

				// clear slate ##
				self::reset();

			}

		}

		// clean up all tags ##
		// h::log( 't:>MOVED cleanup to after lookup, check if this does not trash other markups and apply to all parse lookups..' );
		self::cleanup();

	}



	public static function cleanup( $args = null, $process = 'internal' ){

		$open = trim( willow\tags::g( 'com_o' ) );
		$close = trim( willow\tags::g( 'com_c' ) );

		// strip all section blocks, we don't need them now ##
		// $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
			'q/willow/parse/comments/cleanup/regex', 
			"/$open.*?$close/ms" 
			// "/{{#.*?\/#}}/ms"
		);
		// self::$markup['template'] = preg_replace( $regex_remove, "", self::$markup['template'] ); 

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
				// || ! is_array( self::$buffer_markup )
				// || ! isset( self::$buffer_markup['template'] )
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
