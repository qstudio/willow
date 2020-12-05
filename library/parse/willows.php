<?php

namespace Q\willow\parse;

use Q\willow;

class willows {

	private 
		$plugin = false,

		$willow_context,
		$willow_task,
		$willow_matches, // array of matches ##
		$willow,
		// $willow_match, // full string matched ##
		$arguments,
		$class,
		$method,
		$willow_array,
		$willow_hash, // moved creation of unique hash earlier, to track filters on as-yet unavailable data ##
		$argument_string,
		$return
	;

	private function reset(){

		$this->willow_context = false;
		$this->willow_task = false;
		$this->flags_willow = false;
		$this->willow_hash = false;
		$this->willow = false;
		$this->arguments = []; // NOTE, this is now an empty array ##
		$this->class = false;
		$this->method = false;
		$this->willow_array = false;
		$this->willow_matches = false;
		$this->willow_match = false; // ?? <<--
		$this->argument_string = false;
		$this->return = false;

		// reset loop_scope_count - as this accumulates on a per Willow basis ##
		$this->plugin->set( '_scope_map', [] );

	}

	/**
	 * Construct object from passed args
	 * 
	 * @since 2.0.0
	*/
	public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/**
	 * Scan for willows in markup and add any required markup or call requested context method and capture output
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

			$this->plugin->log( 'e:>Error in stored $markup' );

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

		// get markup ##
		// $string = self::$markup['template'];
		// $this->plugin->log( $string );

		// sanity ##
		if (  
			! $string
			|| is_null( $string )
		){

			$this->plugin->log( $_args['task'].'~>e:>Error in $markup' );

			return false;

		}

		// $this->plugin->log( $args );

		// $this->plugin->log('d:>'.$string);

		// get all willows, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( $this->plugin->get( 'tags')->g( 'wil_o' ) );
		$close = trim( $this->plugin->get( 'tags')->g( 'wil_c' ) );

		// $this->plugin->log( 'open: '.$open. ' - close: '.$close. ' - end: '.$end );

		$regex_find = \apply_filters( 
			'willow/parse/willows/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces ##
		);

		// $this->plugin->log( $args );
		// $this->plugin->log( self::$parse_args );

		if ( 
			preg_match_all( $regex_find, $string, $matches, PREG_OFFSET_CAPTURE ) 
		){

			// sanity ##
			if ( 
				! $matches
				|| ! isset( $matches[1] ) 
				|| ! $matches[1]
			){

				$this->plugin->log( 'e:>Error in returned matches array' );

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

					$this->plugin->log( 'e:>Error in returned matches - no position' );

					continue;

				}

				// take position ##
				// $this->plugin->log( 'position: '.$matches[0][$match][1] );
				$position = $matches[0][$match][1];

				// take match ##
				$match = $matches[0][$match][0];

				// store matches ##
				$this->willow_matches = $match;

				// $this->plugin->log( $match );

				// $this->plugin->log( $args );

				// pass match to function handler ##
				$this->format( $match, $args, $process, $position );

			}

		}

	}
	
	/**
	 * Format single willow
	 * 
	 * @since 4.1.0
	*/
	public function format( $match = null, $args = null, $process = 'secondary', $position = null ){

		// sanity ##
		if(
			is_null( $match )
		){

			$this->plugin->log( 'e:>No function match passed to format method' );

			return false;

		}

		// $this->plugin->log( $args );

		// get Willow tags ##
		$open = trim( $this->plugin->get( 'tags' )->g( 'wil_o' ) );
		$close = trim( $this->plugin->get( 'tags' )->g( 'wil_c' ) );

		// clear slate ##
		$this->reset();

		// return entire function string, including tags for tag swap ##
		$this->willow_match = willow\core\method::string_between( $match, $open, $close, true );
		// $this->plugin->log( $this->willow_match );

		// get Willow, without tags ##
		$this->willow = willow\core\method::string_between( $match, $open, $close );

		// $this->plugin->log( $this->willow );

		// look for Willow flags -- assigned to a filter for use late on, pre-rendering ##
		$parse_flags = new willow\parse\flags( $this->plugin );
		$this->willow = $parse_flags->get( $this->willow, 'willow' );
		// $this->plugin->log( self::$flags_willow );

		$parse_arguments = new willow\parse\arguments( $this->plugin );

		// clean up ##
		$this->willow = trim( $this->willow );

		// sanity ##
		if ( 
			! $this->willow
			|| ! isset( $this->willow ) 
		){

			$this->plugin->log( 'e:>Error in returned match function' );

			return false; 

		}

		// check format ##
		if(
			false === strpos( $this->willow, '~' ) 
		){

			$this->plugin->log( 'e:>Error: All Willows must be in "context~task" format' );

			return false; 

		}

		// grab context and task for debugging ##
		$this->willow_context = explode( '~', $this->willow, 2 )[0];
		$this->willow_task = explode( '~', $this->willow, 2 )[1];

		// $this->plugin->log( 'Willow Context: '.$this->willow_context.' // Task: '.$this->willow_task );

		// $this->plugin->log( $args );

		// get position of first arg_o and position of last arg_c ( in case the string includes additional args )
		if(
			strpos( $this->willow, trim( $this->plugin->get( 'tags' )->g( 'arg_o' )) ) !== false
			&& strrpos( $this->willow, trim( $this->plugin->get( 'tags' )->g( 'arg_c' )) ) !== false
		){

			$arg_o = strpos( $this->willow, trim( $this->plugin->get( 'tags' )->g( 'arg_o' )) );
			$arg_c = strrpos( $this->willow, trim( $this->plugin->get( 'tags' )->g( 'arg_c' )) );

			// $this->plugin->log( 'e:>Found opening arg_o @ "'.$arg_o.'" and closing arg_c @ "'.$arg_c.'" for willow: '.$this->willow  ); 

			// get string between opening and closing args ##
			$this->argument_string = substr( 
				$this->willow, 
				( $arg_o + strlen( trim( $this->plugin->get( 'tags' )->g( 'arg_o' ) ) ) ), 
				( $arg_c - $arg_o - strlen( trim( $this->plugin->get( 'tags' )->g( 'arg_c' ) ) ) ) 
			); 

		}

		// $this->plugin->log( $this->argument_string  );

		// argument_string looks ok, so go with it ##
		if ( 
			$this->argument_string 
		){	

			// check for loops in argument string - might be one or multiple ##
			$parse_loops = new willow\parse\loops( $this->plugin );
			if( $loops = $parse_loops->has( $this->argument_string ) ){

				// $this->plugin->log( $this->willow_task.'~>n:>HAS a loop so taking part of config string as markup' );
				// $this->plugin->log( 'd:>HAS a loop so taking part of config string as markup' );

				// we need the entire markup, without the flags ##
				$decode_flags = $parse_arguments->decode( $this->argument_string );
				// $this->plugin->log( $decode_flags );
				// $this->plugin->log( willow\arguments::decode( $this->argument_string ) );

				// check if string contains any [ flags ] -- technically filters -- ##
				if( 
					$parse_flags->has( $this->argument_string ) 
					&& $decode_flags
					&& isset( $decode_flags['markup']['template'] )
				) {

					// $this->plugin->log( 'template -> '.$decode_flags['markup']['template'] );
					// $this->plugin->log( $this->willow_task.'~>n:>FLAG set so take just loop markup: '.$loop['markup'] );
					// $this->plugin->log( 'd:>Flags set, so take just loop markup: '.$loop['markup'] );

					$this->arguments = core\method::parse_args( 
						$this->arguments, 
						[ 
							'markup' 	=> $decode_flags['markup']['template']  ## // $loops['markup'] // 
							// 'scope'		=> $loop['scope'] // {: scope :} <<-- doing nothing ##
						]
					);

				} else {

					// $this->plugin->log( $this->willow_task.'~>n:>NO flags, so take whole string: '.$this->argument_string );
					// $this->plugin->log( 'd:>No Flags, so take whole string: '.$this->argument_string );

					$this->arguments = core\method::parse_args( 
						$this->arguments, 
						[ 
							'markup' 	=> $this->argument_string //, whole string ##
							// 'scope'		=> $loop['scope'] {: scope :}
						]
					);

				}

				// take the first part of the passed string, before the arg_o tag as the {~ Willow ~} ##
				// --> REMOVED <-- //
				// $willow_explode = explode( trim( $this->plugin->get( 'tags')->g( 'arg_o' )), $this->willow );
				// $this->willow = trim( $willow_explode[0] );

			} 

			// parse arguments ##
			$this->arguments = willow\core\method::parse_args( 
				$this->arguments, 
				$parse_arguments->decode( $this->argument_string )
			);

			// $this->plugin->log( $this->arguments );

			// take the first part of the passed string, before the arg_o tag as the {~ Willow ~} ##
			$willow_explode = explode( trim( $this->plugin->get( 'tags')->g( 'arg_o' )), $this->willow );
			$this->willow = trim( $willow_explode[0] );

			// if arguments are not in an array, take the whole string passed as the arguments ##
			if ( 
				! $this->arguments
				|| ! is_array( $this->arguments ) 
			) {

				$this->plugin->log( $this->willow_task.'~>d:>No array arguments found in willow args, but perhaps we still have filters in the vars' );
				// $this->plugin->log( $args['task'].'~>d:>'.$this->argument_string );

				// check for variable filters ##
				$this->argument_string = $parse_flags->get( $this->argument_string, 'variable' );	

				// clean up ## -- 
				$this->argument_string = trim( $this->argument_string ); // trim whitespace ##
				$this->argument_string = trim( $this->argument_string, '"' ); // trim leading and trailing double quote ##

				// assign string to markup - as this is the only argument we can find ##
				$this->arguments = [ 'markup' => $this->argument_string ];

			}
			
		}

		// function name might still contain opening and closing args brakets, which were empty - so remove them ##
		$this->willow = str_replace( [
				trim( $this->plugin->get( 'tags' )->g( 'arg_o' )), 
				trim( $this->plugin->get( 'tags' )->g( 'arg_c' )) 
			], '',
			$this->willow 
		);

		// format passed context~task to "$class__$method" ##
		$this->willow = str_replace( '~', '__', $this->willow ); // '::' ##

		// create hash ##
		$this->willow_hash = $this->willow.'.'.willow\core\method::hash(); 
		// $this->plugin->log( 'willow_hash: '.$this->willow_hash );
		
		// add escaped Willow namespace --- Q\willow\context:: ##
		// $this->willow = '\\Q\\willow\\context::'.$this->willow;

		// break function into class::method parts ##
		// list( $this->class, $this->method ) = explode( '::', $this->willow ); 

		// add escaped Willow namespace --- Q\willow\context\\XXX ##
		$this->class = '\\Q\\willow\\context\\'.$this->willow_context;

		// break function into class::method parts ##
		$this->method = $this->willow_task;

		// check ##
		if ( 
			! $this->class 
			|| ! $this->method 
		){

			$this->plugin->log( 'e:>Error in passed function name, stopping here' );

			return false;

		}

		// clean up class name ##
		$this->class = willow\core\method::sanitize( $this->class, 'php_class' );

		// clean up method name ##
		$this->method = willow\core\method::sanitize( $this->method, 'php_function' );

		// $this->plugin->log( 'class->method -- '.$this->class.'::'.$this->method );

		if ( 
			! class_exists( $this->class )
			// || ! method_exists( $this->class, $this->method ) // methods are found via MAIGC lookup ##
			|| ! is_callable( $this->class, $this->method )
		){

			$this->plugin->log( 'e:>Cannot find - class: '.$this->class.' - method: '.$this->method );

			return false;

		}	

		// make class__method an array ##
		$this->willow_array = [ $this->class, $this->method ];

		// context_class ##
		$willow_array = explode( '__', $this->method );

		// get all {{ variables }} in $argument_string and check for flags ##
		$parse_markup = new willow\parse\markup( $this->plugin );

		$parse_variables = new willow\parse\variables( $this->plugin );
		if ( 
			$argument_variables = $parse_markup->get( $this->argument_string, 'variable' )
		){

			// $this->plugin->log( $argument_variables );

			// loop each variable ##
			foreach( $argument_variables as $arg_var_k => $arg_var_v ){

				// $this->plugin->log( 'variable: '.$arg_var_v );

				// check for variable filters ( formally flags  )##
				$parse_variables->flags([
					'variable' 	=> $arg_var_v, 
					'context' 	=> $this->class, 
					'task'		=> $this->method,
					'tag'		=> $this->willow_match,
					'hash'		=> $this->willow_hash // pass willow hash ##
				]);

			}

		}

		// $this->plugin->log( $this->willow_match );

		// add hash, process, tag + parent values to arguments array ##
		$this->arguments = willow\core\method::parse_args( 
			$this->arguments, 
			[ 
				'config' 		=> [ 
					'hash'		=> $this->willow_hash, // pass hash ##
					'process'	=> $process,
					'tag'		=> $this->willow_match,
					'parent'	=> $process == 'primary' ? false : $this->plugin->get('_args')['config']['tag'],
				] 
			]
		);

		// get willow flags ##
		$_flags_willow = $this->plugin->get( '_flags_willow' );

		// Does the willow have flags / filters ##
		if( $_flags_willow ) {

			// store filters under willow hash - this avoids conflicts if Willows are re-used in the same template / view ##
			$_fllters = $this->plugin->get( '_filters' );
			$_filters[ $this->willow_hash ] = $_flags_willow;
			$this->plugin->set( '_filters', $_filters );

		}

		// buffer => output buffer, collect return data which would render if not  ##
		if( 
			$_flags_willow // flags set ##
			&& is_array( $_flags_willow ) // is an array 
			&& in_array( 'buffer', $_flags_willow ) // output buffer defined ##
		) {

			self::$arguments = core\method::parse_args( 
				self::$arguments, 
				[ 
					'config' => [ 
						'buffer' => true 
					] 
				]
			);
		}

		// debug => output debug data for this single Willow ##
		if( 
			$_flags_willow // flags set ##
			&& is_array( $_flags_willow ) // is an array 
			&& in_array( 'debug', $_flags_willow ) // debug defined ##
		) {

			self::$arguments = core\method::parse_args( 
				self::$arguments, 
				[ 
					'config' => [ 
						'debug' => true 
					] 
				]
			);
		}

		// collect current process state ##
		// render\args::collect();
		$render_args = new willow\render\args( $this->plugin );
		$render_args->collect();
		
		// instantiate new class ##
		$object = new \Q\willow\context( $this->plugin );

		// pass args, if set ##
		if( $this->arguments ){

			$this->plugin->log( $this->willow_task.'~>n:>Passing args array to: '.$this->class.'::'.$this->method );

			// $this->plugin->log( $this->arguments );
			
			$this->return = $object->{$this->willow}( $this->arguments );

		} else { 

			$this->plugin->log( $this->willow_task.'~>n:>NOT passing args array to: '.$this->class.'::'.$this->method );
			// $this->return = call_user_func_array( $this->willow_array ); 
			$this->return = $object->{$this->willow};

		}	

		// check return ##
		// $this->plugin->log( $this->return );

		if ( 
			! isset( $this->return ) 
			|| ! $this->return
			|| false === $this->return
			|| ! is_array( $this->return )
		) {

			$task = isset( $args['task'] ) ? $args['task'] : $args['task'];

			$this->plugin->log( $task.'~>n:>Willow "'.$this->willow_match.'" did not return a value, perhaps it is a hook.' );

		}

		// restore previous process state ##
		// render\args::set();
		$render_args->restore();

		// clear local props for next run ##
		$this->reset();

	}

	/**
	 * Check if passed string includes a Willow 
	*/
	public function has( $string = null ){

		// sanity ##
		if(
			is_null( $string )
		){

			$this->plugin->log( 'e:>No string passed to method' );

			return false;

		}

		// get loop tags ##
		$wil_o =  $this->plugin->get( 'tags' )->g( 'wil_o' );
		$wil_c =  $this->plugin->get( 'tags' )->g( 'wil_c' );

		// test string ##
		// $this->plugin->log( $string );

		// the passed $string comes from a single Willow and might include one or multiple wilps ##
		$willow_count_open = substr_count( $string, trim( $wil_o ) ); // willow openers ##
		$willow_count_close = substr_count( $string, trim( $wil_c ) ); // willow closers ##

		// check ##
		$this->plugin->log( 'Count Open: '.$willow_count_open.' ~ Count Close: '.$willow_count_close ); 

		// no loops, return false;
		if( 
			0 === $willow_count_open
			|| 0 === $willow_count_close
		){

			$this->plugin->log( 'd:>No Willows in passed string, returning false.' );

			return false;

		}

		// yes ##
		return true;

	}

	/**
	 * Check if passed string is a willow 
	*/
	public function is( $string = null ){

		// sanity ##
		if(
			is_null( $string )
			|| ! is_string( $string )
		){

			$this->plugin->log( 'e:>No string passed to method' );

			return false;

		}

		// alternative method - get position of arg_o and position of LAST arg_c ( in case the string includes additional args )
		if(
			strpos( $string, trim( $this->plugin->get( 'tags' )->g( 'wil_o' )) ) !== false // start ##
			&& strrpos( $string, trim( $this->plugin->get( 'tags' )->g( 'wil_c' )) ) !== false // end ##
		){

			/*
			$loo_o = strpos( $string, trim( $this->plugin->get( 'tags')->g( 'loo_o' )) );
			$loo_c = strrpos( $string, trim( $this->plugin->get( 'tags')->g( 'loo_c' )) );

			$this->plugin->log( 'e:>Found opening loo_o @ "'.$loo_o.'" and closing loo_c @ "'.$loo_c.'"'  ); 

			// get string between opening and closing args ##
			$return_string = substr( 
				$string, 
				( $loo_o + strlen( trim( $this->plugin->get( 'tags')->g( 'loo_o' ) ) ) ), 
				( $loo_c - $loo_o - strlen( trim( $this->plugin->get( 'tags')->g( 'loo_c' ) ) ) ) ); 

			$this->plugin->log( 'e:>$string: "'.$return_string .'"' );

			return $return_string;
			*/

			return true;

		}

		// no ##
		return false;

	}

	/**@todo*/
	public function cleanup( $args = null, $process = 'secondary' ){

		// vars ##
		$_markup = $this->pluing->get( '_markup' );
		$_buffer_markup = $this->pluing->get( '_buffer_markup' );
		$open = trim( $this->plugin->get( 'tags')->g( 'wil_o' ) );
		$close = trim( $this->plugin->get( 'tags')->g( 'wil_c' ) );

		// strip all function blocks, we don't need them now ##
		// $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
		 	'willow/parse/willows/cleanup/regex', 
			"/(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|$open.*?$close/ms" // clean up with SKIP <code>tag</code> ##
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

			$this->plugin->log( 'e:>Error in stored $markup' );

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

				// $this->plugin->log( $matches );

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					$this->plugin->log( $count .' willow tags removed...' );

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
