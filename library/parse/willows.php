<?php

namespace willow\parse;

use willow;

class willows {

	private 
		$willow_context,
		$willow_task,
		$willow_matches, // array of matches ##
		$willow,
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
		\willow()->set( '_flags_willow', false );
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
		\willow()->set( '_scope_map', [] );

	}

	/**
	 * Construct
     */
    public function __construct(){

		// silence ##

	}

	/**
	 * Scan for {~ willows ~} in markup and add any required markup or call requested context method and capture output
	 * 
	 * @since 	1.0.0
	 * @return	void
	*/
    public function match( $args = null, $process = 'secondary' ){ 

		// local vars ##
		$_args = \willow()->get( '_args' );
		$_markup = \willow()->get( '_markup' );
		$_buffer_markup = \willow()->get( '_buffer_markup' );

		// get parse task ##
		$_parse_task = $_args['task'] ?? \willow()->get( '_parse_task' );

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

		// get markup ##
		// $string = $this->markup['template'];
		// w__log( $string );

		// sanity ##
		if (  
			! $string
			|| is_null( $string )
		){

			w__log( $_parse_task.'~>e:>Error in $markup' );

			return false;

		}

		// w__log( $args );

		// w__log('d:>'.$string);

		// get all willows, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( \willow()->tags->g( 'wil_o' ) );
		$close = trim( \willow()->tags->g( 'wil_c' ) );

		// w__log( 'open: '.$open. ' - close: '.$close. ' - end: '.$end );

		$regex_find = \apply_filters( 
			'willow/parse/willows/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces ##
		);

		// w__log( $args );
		// w__log( $this->parse_args );

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

				// take position ##
				// w__log( 'position: '.$matches[0][$match][1] );
				$position = $matches[0][$match][1];

				// take match ##
				$match = $matches[0][$match][0];

				// store matches ##
				$this->willow_matches = $match;

				// w__log( $match );

				// w__log( $args );

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

			w__log( 'e:>No function match passed to format method' );

			return false;

		}

		// w__log( $args );

		// get Willow tags ##
		$open = trim( \willow()->tags->g( 'wil_o' ) );
		$close = trim( \willow()->tags->g( 'wil_c' ) );

		// clear slate ##
		$this->reset();

		// return entire function string, including tags for tag swap ##
		$this->willow_match = willow\core\strings::between( $match, $open, $close, true );
		// w__log( $this->willow_match );

		// get Willow, without tags ##
		$this->willow = willow\core\strings::between( $match, $open, $close );
		// w__log( $this->willow );

		// look for Willow flags -- assigned to a filter for use late on, pre-rendering ##
		$this->willow = \willow()->parse->flags->get( $this->willow, 'willow' );
		// w__log( \willow()->get( '_flags_willow' ) );

		// clean up ##
		$this->willow = trim( $this->willow );

		// sanity ##
		if ( 
			! $this->willow
			|| ! isset( $this->willow ) 
		){

			w__log( 'e:>Error in returned match function' );

			return false; 

		}

		// check format -- all willows require a tilder delimiter '~' ##
		if(
			false === strpos( $this->willow, '~' ) 
		){

			w__log( 'e:>Error: All Willows must be in "context~task" format' );

			return false; 

		}

		// grab context and task for debugging ##
		$this->willow_context = explode( '~', $this->willow, 2 )[0];
		$this->willow_task = explode( '~', $this->willow, 2 )[1];

		// w__log( 'Willow Context: '.$this->willow_context.' // Task: '.$this->willow_task );
		// w__log( $args );

		// get position of first arg_o and position of last arg_c ( in case the string includes additional args )
		if(
			strpos( $this->willow, trim( \willow()->tags->g( 'arg_o' )) ) !== false
			&& strrpos( $this->willow, trim( \willow()->tags->g( 'arg_c' )) ) !== false
		){

			$arg_o = strpos( $this->willow, trim( \willow()->tags->g( 'arg_o' )) );
			$arg_c = strrpos( $this->willow, trim( \willow()->tags->g( 'arg_c' )) );

			// w__log( 'e:>Found opening arg_o @ "'.$arg_o.'" and closing arg_c @ "'.$arg_c.'" for willow: '.$this->willow  ); 

			// get string between opening and closing args ##
			$this->argument_string = substr( 
				$this->willow, 
				( $arg_o + strlen( trim( \willow()->tags->g( 'arg_o' ) ) ) ), 
				( $arg_c - $arg_o - strlen( trim( \willow()->tags->g( 'arg_c' ) ) ) ) 
			); 

			// update task ##
			$this->willow_task = trim( str_replace(
				[
					trim( \willow()->tags->g( 'arg_o' ) ),
					$this->argument_string, 
					trim( \willow()->tags->g( 'arg_c' ) )
				],
				'',
				$this->willow_task
			) );

		}

		// w__log( 'willow_task: '.$this->willow_task  );

		// argument_string looks ok, so go with it ##
		if ( 
			$this->argument_string 
		){	

			// check for loops in argument string - might be one or multiple ##
			if( $loops = \willow()->parse->loops->has( $this->argument_string ) ){

				// w__log( $this->willow_task.'~>n:>HAS a loop so taking part of config string as markup' );
				// w__log( 'd:>HAS a loop so taking part of config string as markup' );

				// we need the entire markup, without the flags ##
				$decode_flags = \willow()->parse->arguments->decode( $this->argument_string );
				// w__log( $decode_flags );

				// check if string contains any [ flags ] -- technically filters -- ##
				// NOTE -- changed scraped markup from tag to pass into array key "markup->template" directly ##
				if( 
					\willow()->parse->flags->has( $this->argument_string ) 
					&& $decode_flags
					&& is_array( $decode_flags )
					&& isset( $decode_flags['markup']['template'] )
				) {

					// w__log( 'template -> '.$decode_flags['markup']['template'] );
					// w__log( $this->willow_task.'~>n:>FLAG set so take just loop markup: '.$loop['markup'] );
					// w__log( 'd:>Flags set, so take just loop markup: '.$loop['markup'] );

					$this->arguments = willow\core\arrays::parse_args( 
						$this->arguments, 
						[ 
							// 'markup' 	=> $decode_flags['markup']['template'] 
							'markup' 	=> [ 'template' => $decode_flags['markup']['template'] ] // <-- new return to markup->template ##
						]
					);

				} else {

					// w__log( $this->willow_task.'~>n:>NO flags, so take whole string: '.$this->argument_string );
					// w__log( 'd:>No Flags, so take whole string: '.$this->argument_string );

					$this->arguments = willow\core\arrays::parse_args( 
						$this->arguments, 
						[ 
							// 'markup' 	=> $this->argument_string 
							'markup' 	=> [ 'template' => $this->argument_string ] // <-- new return to markup->template ##
						]
					);

				}

			} 

			// parse arguments ##
			$this->arguments = willow\core\arrays::parse_args( 
				$this->arguments, 
				\willow()->parse->arguments->decode( $this->argument_string )
			);

			// w__log( $this->arguments );

			// take the first part of the passed string, before the arg_o tag as the {~ Willow ~} ##
			$willow_explode = explode( trim( \willow()->tags->g( 'arg_o' )), $this->willow );
			$this->willow = trim( $willow_explode[0] );

			// if arguments are not in an array, take the whole string passed as the arguments ##
			if ( 
				! $this->arguments
				|| ! is_array( $this->arguments ) 
			) {

				w__log( $this->willow_task.'~>d:>No [ array ] arguments found in willow, but perhaps we still have filters in the {{ variables }}' );
				// w__log( $args['task'].'~>d:>'.$this->argument_string );

				// check for variable filters ##
				$this->argument_string = \willow()->parse->flags->get( $this->argument_string, 'variable' );	

				// clean up ## -- 
				$this->argument_string = trim( $this->argument_string ); // trim whitespace ##
				$this->argument_string = trim( $this->argument_string, '"' ); // trim leading and trailing double quote ##

				// assign string to markup - as this is the only argument we can find ##
				$this->arguments = [ 'markup' => $this->argument_string ];

			}
			
		}

		// function name might still contain opening and closing args brakets, which were empty - so remove them ##
		$this->willow = str_replace( [
				trim( \willow()->tags->g( 'arg_o' )), 
				trim( \willow()->tags->g( 'arg_c' )) 
			], '',
			$this->willow 
		);

		// format passed context~task to "$class__$method" ##
		$this->willow = str_replace( '~', '__', $this->willow ); // '::' ##

		// create hash ##
		$this->willow_hash = $this->willow.'.'.willow\core\strings::hash(); 
		// w__log( 'willow_hash: '.$this->willow_hash );
		
		// add escaped Willow namespace --- willow\context:: ##
		// $this->willow = '\\willow\\context::'.$this->willow;

		// break function into class::method parts ##
		// list( $this->class, $this->method ) = explode( '::', $this->willow ); 

		// add escaped Willow namespace --- willow\context\\XXX ##
		$this->class = '\\willow\\context\\'.$this->willow_context;

		// break function into class::method parts ##
		$this->method = $this->willow_task;

		// w__log( 'willow_method: '.$this->method );

		// check ##
		if ( 
			! $this->class 
			|| ! $this->method 
		){

			w__log( 'e:>Error in passed function name, stopping here' );

			return false;

		}

		// clean up class name ##
		$this->class = willow\core\sanitize::value( $this->class, 'php_class' );

		// clean up method name ##
		$this->method = willow\core\sanitize::value( $this->method, 'php_function' );

		// w__log( 'class->method: '.$this->class.'->'.$this->method );

		if ( 
			! class_exists( $this->class )
			// || ! method_exists( $this->class, $this->method ) // methods are found via __MAGIC__ lookup __call ##
			|| ! is_callable( $this->class, $this->method )
		){

			w__log( 'e:>Cannot find - class: '.$this->class.' - method: '.$this->method );

			return false;

		}	

		// make class__method an array ##
		$this->willow_array = [ $this->class, $this->method ];

		// context_class ##
		$willow_array = explode( '__', $this->method );

		// get all {{ variables }} in $argument_string and check for flags ##
		if ( 
			$argument_variables = \willow()->parse->markup->get( $this->argument_string, 'variable' )
		){

			// w__log( $argument_variables );

			// loop each variable ##
			foreach( $argument_variables as $arg_var_k => $arg_var_v ){

				// w__log( 'variable: '.$arg_var_v );

				// check for variable filters ( formally flags  )##
				\willow()->parse->variables->flags([
					'variable' 	=> $arg_var_v, 
					'context' 	=> $this->class, 
					'task'		=> $this->method,
					'tag'		=> $this->willow_match,
					'hash'		=> $this->willow_hash // pass willow hash ##
				]);

			}

		}

		// w__log( $this->willow_match );

		// add hash, process, tag + parent values to arguments array ##
		$this->arguments = willow\core\arrays::parse_args( 
			$this->arguments, 
			[ 
				'config' 		=> [ 
					'hash'		=> $this->willow_hash, // pass hash ##
					'process'	=> $process,
					'tag'		=> $this->willow_match,
					'parent'	=> $process == 'primary' ? false : \willow()->get('_args')['config']['tag'],
				] 
			]
		);

		// w__log( $this->arguments );

		// get willow flags ##
		$_flags_willow = \willow()->get( '_flags_willow' );
		// w__log( $_flags_willow );

		// Does the willow have flags / filters ##
		if( $_flags_willow ) {

			// store filters under willow hash - this avoids conflicts if Willows are re-used in the same template / view ##
			$_fllters = \willow()->get( '_filters' );
			$_filters[ $this->willow_hash ] = $_flags_willow;
			\willow()->set( '_filters', $_filters );

		}

		// buffer => output buffer, collect return data which would render if not  ##
		if( 
			$_flags_willow // flags set ##
			&& is_array( $_flags_willow ) // is an array 
			&& in_array( 'buffer', $_flags_willow ) // output buffer defined ##
		) {

			$this->arguments = willow\core\arrays::parse_args( 
				$this->arguments, 
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

			// w__log( 'Setting up debug for: '.$this->willow );

			w__log( $this->willow_task.'~>n:>Debugging set-up for "'.$this->willow_match.'"' );

			$this->arguments = willow\core\arrays::parse_args( 
				$this->arguments, 
				[ 
					'config' => [ 
						'debug' => true 
					] 
				]
			);
		}

		// collect current process state ##
		\willow()->render->args->collect();
		
		// w__log( 'Task: '.$this->method );

		// pass args, if set ##
		if( $this->arguments ){

			w__log( $this->willow_task.'~>n:>Passing args array to: '.$this->class.'->'.$this->method.'()' );
			// w__log( $this->arguments );
			
			$this->return = \willow()->context->{ $this->willow }( $this->arguments );

		} else { 

			w__log( $this->willow_task.'~>n:>NOT passing args array to: '.$this->class.'->'.$this->method.'()' );
			// $this->return = call_user_func_array( $this->willow_array ); 
			$this->return = \willow()->context->{ $this->willow };

		}	

		// check return ##
		// w__log( $this->return );

		if ( 
			! isset( $this->return ) 
			|| ! $this->return
			|| false === $this->return
			|| ! is_array( $this->return )
		) {

			$task = $this->willow_task ?? $args['task'] ;

			w__log( $task.'~>n:>Willow "'.$this->willow_match.'" did not return a value.' );
			// w__log( 'e:>Willow "'.$this->willow_match.'" did not return a value.' );

		}

		// restore previous process state ##
		\willow()->render->args->restore();

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

			w__log( 'e:>No string passed to method' );

			return false;

		}

		// get loop tags ##
		$wil_o = \willow()->tags->g( 'wil_o' );
		$wil_c = \willow()->tags->g( 'wil_c' );

		// test string ##
		// w__log( $string );

		// the passed $string comes from a single Willow and might include one or multiple wilps ##
		$willow_count_open = substr_count( $string, trim( $wil_o ) ); // willow openers ##
		$willow_count_close = substr_count( $string, trim( $wil_c ) ); // willow closers ##

		// check ##
		w__log( 'Count Open: '.$willow_count_open.' ~ Count Close: '.$willow_count_close ); 

		// no loops, return false;
		if( 
			0 === $willow_count_open
			|| 0 === $willow_count_close
		){

			w__log( 'd:>No Willows in passed string, returning false.' );

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

			w__log( 'e:>No string passed to method' );

			return false;

		}

		// alternative method - get position of arg_o and position of LAST arg_c ( in case the string includes additional args )
		if(
			strpos( $string, trim( \willow()->tags->g( 'wil_o' )) ) !== false // start ##
			&& strrpos( $string, trim( \willow()->tags->g( 'wil_c' )) ) !== false // end ##
		){

			/*
			$loo_o = strpos( $string, trim( \willow()->tags->g( 'loo_o' )) );
			$loo_c = strrpos( $string, trim( \willow()->tags->g( 'loo_c' )) );

			w__log( 'e:>Found opening loo_o @ "'.$loo_o.'" and closing loo_c @ "'.$loo_c.'"'  ); 

			// get string between opening and closing args ##
			$return_string = substr( 
				$string, 
				( $loo_o + strlen( trim( \willow()->tags->g( 'loo_o' ) ) ) ), 
				( $loo_c - $loo_o - strlen( trim( \willow()->tags->g( 'loo_c' ) ) ) ) ); 

			w__log( 'e:>$string: "'.$return_string .'"' );

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
		$_markup = \willow()->get( '_markup' );
		$_buffer_markup = \willow()->get( '_buffer_markup' );
		$open = trim( \willow()->tags->g( 'wil_o' ) );
		$close = trim( \willow()->tags->g( 'wil_c' ) );

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

					w__log( $count .' willow tags removed...' );

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
