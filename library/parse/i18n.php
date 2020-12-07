<?php

namespace Q\willow\parse;

use Q\willow;

class i18n {

	private 
		$plugin, // $this
		$return,
		$i18n,
		$i18n_match, // full string matched ##
		$arguments,
		$class,
		$method,
		$i18n_array
	;

	private static $textdomain;

	private function reset(){

		$return = false; 
		$i18n = false;
		$arguments = false;
		$class = false;
		$method = false;
		$i18n_array = false;

	}

	public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	public static function textdomain(){

		if( self::$textdomain ){ return self::$textdomain; }

		// load via filter -- variable textdomains are a no no.. but this could come from any theme or plugin... ?? ##
		return self::$textdomain = \apply_filters( 'willow/parse/i18n/textdomain', 'q-textdomain' );

	}
	
    /**
	 * Scan for translatable strings in markup and convert to \_e( 'string' ) functions and capture output
	 * 
	 * @since 4.1.0
	*/
    public function match( $args = null, $process = 'secondary' ){

		// local vars ##
		$_args = $this->plugin->get( '_args' );
		$_markup = $this->plugin->get( '_markup' );
		$_buffer_markup = $this->plugin->get( '_buffer_markup' );

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

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( $this->plugin->tags->g( 'i18n_o' ) );
		$close = trim( $this->plugin->tags->g( 'i18n_c' ) );

		// w__log( 'open: '.$open. ' - close: '.$close );

		$regex_find = \apply_filters( 
			'willow/parse/i18n/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
			// "/{{#(.*?)\/#}}/s" 
		);

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

				// w__log( $matches );

				// take match ##
				$match = $matches[0][$match][0];

				// pass match to function handler ##
				$this->format( $match, $process );

			}

		} else {

			// w__log( 'e:>No translation strings found' );

		}

	}
	
	/***/
	public function format( $match = null, $process = 'secondary' ){

		// sanity ##
		if(
			is_null( $match )
		){

			w__log( 'e:>No i18n match passed to format method' );

			return false;

		}

		// vars ##
		$_markup_template = $this->plugin->get( '_markup_template' );

		// tags ##
		$open = trim( $this->plugin->tags->g( 'i18n_o' ) );
		$close = trim( $this->plugin->tags->g( 'i18n_c' ) );

		// clear slate ##
		self::reset();

		// return entire function string, including tags for tag swap ##
		$this->i18n_match = willow\core\method::string_between( $match, $open, $close, true );
		$this->i18n = willow\core\method::string_between( $match, $open, $close );

		// w__log( '$i18n_match: '.$i18n_match );

		// look for flags ##
		// $i18n = flags::get( self::$function, 'i18n' );
		// $i18n = flags::get( $i18n, 'function' );
		// w__log( self::$flags_i18n );
		// w__log( $i18n );

		// clean up ##
		$this->i18n = trim( $this->i18n );

		// w__log( 'e:>i18n: '.$i18n );

		// sanity ##
		if ( 
			! $this->i18n
			|| ! isset( $this->i18n ) 
		){

			w__log( 'e:>Error in returned match i18n' );

			return false; 

		}

		// php_function tags ##
		// $return_open = $this->plugin->tags->g( 'php_fun_o' );
		// $return_close = $this->plugin->tags->g( 'php_fun_c' );

		// w__log( 'e:>$i18n: '.$i18n );

		// concat return string ##
		// $return = $return_open." [return] \__{+ ".$i18n." +}".$return_close;

		// gettext namespace ##
		// $textdomain = \apply_filters( 'willow/parse/i18n/textdomain', 'q-textdomain' );

		// run string via i18n callback ##
		$this->return = \__( $this->i18n, 'willow' ); // self::textdomain()

		// w__log( 'e:>'.$return );

		// function returns which update the template also need to update the buffer_map, for later find/replace ##
		// Seems like a potential pain-point ##
		$_markup_template = str_replace( $this->i18n_match, $this->return, $_markup_template );

		// store _markup_template ##
		$this->plugin->set( '_markup_template', $_markup_template );

		// update markup for willow parse ##
		$this->plugin->get( 'parse_markup' )->swap( $this->i18n_match, $this->return, 'i18n', 'string', $process );
		
		// clear slate ##
		$this->reset();

	}

	/**
	 * Check if passed string includes translateable strings 
	*/
	public function has( $string = null ){

		// @todo - sanity ##
		if(
			is_null( $string )
		){

			w__log( 'e:>No string passed to method' );

			return false;

		}

		// check for opening and closing tags
		if(
			strpos( $string, trim( $this->plugin->tags->g( 'i18n_o' )) ) !== false
			&& strpos( $string, trim( $this->plugin->tags->g( 'i18n_c' )) ) !== false
		){

			// $loo_o = strpos( $string, trim( $this->plugin->tags->g( 'i18n_o' )) );
			// $loo_c = strrpos( $string, trim( $this->plugin->tags->g( 'i18n_c' )) );

			// // w__log( 'd:>Found opening loo_o @ "'.$loo_o.'" and closing loo_c @ "'.$loo_c.'"'  ); 

			// // get string between opening and closing args ##
			// $return_string = substr( 
			// 	$string, 
			// 	( $loo_o + strlen( trim( $this->plugin->tags->g( 'loo_o' ) ) ) ), 
			// 	( $loo_c - $loo_o - strlen( trim( $this->plugin->tags->g( 'loo_c' ) ) ) ) ); 

			// $return_string = $this->plugin->tags->g( 'loo_o' ).$return_string.$this->plugin->tags->g( 'loo_c' );

			// w__log( 'e:>$string: "'.$return_string.'"' );

			return true;

		}

		// no ##
		return false;

	}

	/***/
	public function cleanup( $args = null, $process = 'secondary' ){

		// local vars ##
		$_args = $this->plugin->get( '_args' );
		$_markup = $this->plugin->get( '_markup' );
		$_buffer_markup = $this->plugin->get( '_buffer_markup' );

		$open = trim( $this->plugin->tags->g( 'i18n_o' ) );
		$close = trim( $this->plugin->tags->g( 'i18n_c' ) );

		// strip all function blocks, we don't need them now ##
		// // $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
		 	'q/render/parse/i18n/cleanup/regex', 
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

					w__log( 'd:>'.$count .' i18n tags removed...' );

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
