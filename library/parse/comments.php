<?php

namespace Q\willow\parse;

use Q\willow;

class comments {

	private 
		$plugin = false,
		$parse_flags = false,

		$comment_hash, 
		$comment,
		$comment_match
	;

	private function reset(){
		$this->comment_hash = false; 
		$this->comment = false;
		$this->comment_match = false;
		$this->flags_comment = false;
	}

	public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

		// flags ##
		$this->parse_flags = new willow\parse\flags( $this->plugin );

	}

	/**
	 * Scan for comments in markup and convert to variables and $fields and also to error log ##
	 * 
	 * @since 4.1.0
	*/
	public function match( $args = null, $process = 'secondary' ){

		// local vars ##
		$_args = $this->plugin->get( '_args' );
		$_markup = $this->plugin->get( '_markup' );
		$_buffer_map = $this->plugin->get( '_buffer_map' );
		$_buffer_markup = $this->plugin->get( '_buffer_markup' );

		// sanity -- this requires ##
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

		// w__log('d:>'.$string);

		// get all comments, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( $this->plugin->tags->g( 'com_o' ) );
		$close = trim( $this->plugin->tags->g( 'com_c' ) );

		// w__log( 'open: '.$open. ' - close: '.$close );

		$regex_find = \apply_filters( 
			'willow/parse/comments/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
		);

		// w__log( 't:> allow for badly spaced tags around sections... whitespace flexible..' );
		if ( 
			preg_match_all( $regex_find, $string, $matches, PREG_OFFSET_CAPTURE ) 
		){

			// w__log( $matches[1] );

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

				// clear slate ##
				$this->reset();

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

				// w__log( 'd:>Searching for comments data...' );
				// self::$position = $matches[0][$match][1]; // take from first array ##
				// w__log( 'd:>position: '.$position );
				// w__log( 'd:>position from 1: '.$matches[0][$match][1] ); 
				
				// get a single comment ##
				$this->comment = core\method::string_between( $matches[0][$match][0], $open, $close );

				// return entire function string, including tags for tag swap ##
				$this->comment_match = core\method::string_between( $matches[0][$match][0], $open, $close, true );

				// look for flags ##
				// self::flags();
				$this->comment = $this->parse_flags->get( $this->comment, 'comment' );
				$_flags_comment = $this->plugin->get( '_flags_comment' );
				// w__log( $this->plugin->get( '_flags_comment') );

				// sanity ##
				if ( 
					! isset( $this->comment ) 
				){

					w__log( 'e:>Error in returned match function' );

					continue; 

				}

				// clean up ##
				$this->comment = trim( $this->comment );

				// test what we have ##
				// w__log( 'd:>comment: "'.$this->comment.'"' );

				// hash ##
				$this->comment_hash = 'comment__'.\rand();

				// w__log( 'd:>comment hash: "'.$this->comment_hash.'"' );

				// html comments are rendered on the UI, so require to add a variable tag to the markup ##
				if( 
					// isset( $_flags_comment['h'] )
					$_flags_comment
					&& is_array( $_flags_comment )
					&& (
						in_array( 'html', $_flags_comment )
						|| in_array( 'h', $_flags_comment ) // shortcut to 'html' ##
					)
				){

					// add data to buffer map ##
					$_buffer_map[] = [
						'hash'		=> self::$comment_hash,
						'tag'		=> self::$comment_match,
						'output'	=> '<!-- '.self::$comment.' -->',
						'parent'	=> false
					];

					// re-save _buffer_map ##
					$this->plugin->set( '_buffer_map', $_buffer_map ); 

				}
				
				// PHP log ##
				if ( 
					// isset( $_flags_comment['p'] )
					$_flags_comment
					&& is_array( $_flags_comment )
					&& (
						in_array( 'php', $_flags_comment )
						|| in_array( 'p', $_flags_comment ) // shortcut to 'php' ##
					)
				){

					// also, add a log entry ##
					w__log( 'd:>'.$this->comment );

				}

				// clear slate ##
				$this->reset();

			}

		}

		// clean up all tags ##
		// w__log( 't:>MOVED cleanup to after lookup, check if this does not trash other markups and apply to all parse lookups..' );
		// self::cleanup();

	}


	/***/
	public function cleanup( $args = null, $process = 'secondary' ){

		// local vars ##
		$_args = $this->plugin->get( '_args' );
		$_markup = $this->plugin->get( '_markup' );
		$_buffer_markup = $this->plugin->get( '_buffer_markup' );

		$open = trim( $this->plugin->tags->g( 'com_o' ) );
		$close = trim( $this->plugin->tags->g( 'com_c' ) );

		// strip all section blocks, we don't need them now ##
		$regex = \apply_filters( 
			'willow/parse/comments/cleanup/regex', 
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

					w__log( $count .' comment tags removed...' );

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
