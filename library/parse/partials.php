<?php

namespace Q\willow\parse;

use Q\willow;

class partials {

	private 
		$plugin = false,
		$args = false,
		$process = false,
		$partials = []
	;

	/**
	 * Scan for partials in markup and convert to variables and $fields
	 * 
	 * @since 4.1.0
	*/
	public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/**
	 * @todo..
	*/
	function match( $args = null, $process = 'secondary' ){

		// global ##
		$config = $this->plugin->get('config')->get([ 'context' => 'partial', 'task' => 'config' ]);
		// $this->plugin->log( $config );

		if ( 
			isset( $config['run'] )
			&& false === $config['run']
		){

			$this->plugin->log( 'Partial config->run defined as false, so stopping here...' );

			return false;

		}
		
		// sanity -- method requires requires ##
		if ( 
			(
				'secondary' == $process
				&& (
					null === ( $this->plugin->get('_markup') )
					|| ! is_array( $this->plugin->get('_markup') )
					|| ! isset( $this->plugin->get('_markup')['template'] )
				)
			)
			||
			(
				'primary' == $process
				&& (
					null === $this->plugin->get('_buffer_markup' )
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
				$string = $this->plugin->get('_markup')['template'];

			break ;

			case "primary" :

				// get markup ##
				$string = $this->plugin->get('_buffer_markup');

			break ;

		} 

		// sanity ##
		if (  
			! $string
			|| is_null( $string )
		){

			$this->plugin->log( $this->plugin->get('_args')['task'].'~>e:>Error in $string' );

			return false;

		}

		// $this->plugin->log('d:>'.$string);

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( $this->plugin->get('tags')->g( 'par_o' ) );
		$close = trim( $this->plugin->get('tags')->g( 'par_c' ) );

		// $this->plugin->log( 'open: '.$open. ' - close: '.$close );

		$regex = \apply_filters( 
			'willow/render/parse/partials/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
		);

		// $this->plugin->log( 't:> allow for badly spaced tags around sections... whitespace flexible..' );
		if ( 
			preg_match_all( $regex, $string, $matches, PREG_OFFSET_CAPTURE ) 
		){

			// $this->plugin->log( $matches[1] );

			// sanity ##
			if ( 
				! $matches
				|| ! isset( $matches[1] ) 
				|| ! $matches[1]
			){

				$this->plugin->log( $this->plugin->get('_args')['task'].'~>e:>Error in returned matches array' );

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

					$this->plugin->log( $this->plugin->get('_args')['task'].'~>e:>Error in returned matches - no position' );

					continue;

				}

				// $this->plugin->log( 'd:>Searching for partials in  markup...' );

				// $position = $matches[0][$match][1]; // take from first array ##
				// $this->plugin->log( 'd:>position: '.$position );
				// $this->plugin->log( 'd:>position from 1: '.$matches[0][$match][1] ); 

				// get partial data ##
				$partial = willow\core\method::string_between( $matches[0][$match][0], $open, $close );
				// $markup = method::string_between( $matches[0][$match][0], $close, $end );

				// return entire partial string, including tags for tag swap ##
				$partial_match = willow\core\method::string_between( $matches[0][$match][0], $open, $close, true );
				// $this->plugin->log( '$partial_match: '.$partial_match );

				// sanity ##
				if ( 
					! isset( $partial ) 
					// || ! strstr( $partial, '__' )
					// || ! isset( $markup ) 
				){

					$this->plugin->log( $this->plugin->get('_args')['task'].'~>e:>Error in returned match function' );

					continue; 

				}

				// clean up ##
				$partial = trim($partial);
				$context = 'partial';
				$task = $partial;
				// list( $context, $task ) = explode( '__', $partial );

				// test what we have ##
				// $this->plugin->log( 'd:>partial: "'.$partial.'"' );
				// $this->plugin->log( self::$args );

				// perhaps better to hand this to a method, which can grab args ??
				// $partial_data = core\config::get([ 'context' => $context, 'task' => $task ]);
				$partial_data = $this->plugin->get('config')->get([ 'context' => $context, 'task' => $task ]);

				// no data, no go ##
				if(
					! $partial_data
					// || ! is_array( $partial_data )
				){

					$this->plugin->log( $this->plugin->get('_args')['task'].'~>e:>Error in partial_data: "'.$partial.'"' );
					$this->plugin->log( 'e:>Error loading config for partial: "'.$partial.'"' );

					continue;

				}

				// data passed as a string, so format
				if(
					is_string( $partial_data )
				){

					$this->plugin->log( $this->plugin->get('_args')['task'].'~>d:>Partial: "'.$partial.'" only sent markup, so converting to array format' );

					$partial_data = [
						'markup' => $partial_data
					];

				}

				// merge local partial data, with partial->config ##
				$partial_data = willow\core\method::parse_args( $partial_data, $config );

				// $this->plugin->log( 'd:>context: "'.$context.'"' );
				// $this->plugin->log( 'd:>task: "'.$task.'"' );
				// $this->plugin->log( $partial_data );

				// we should check local config for this partial, to see if they should run ##
				if( 
					isset( $partial_data['config']['run'] ) 
					&& false == $partial_data['config']['run']
				){

					// $this->plugin->log( $this->plugin->get('_args')['task'].'~>n:>Partial "'.$partial.'" config->run defined as false, so stopping here...' );
					$this->plugin->log( 'd:>Partial "'.$partial.'" config->run defined as false, so stopping here...' );

					continue;

				}

				// hash way ##
				/*
				$hash = 'partial__'.$task.'__'.rand();
				// $this->plugin->log( 'd:>partial hash: '.$hash );

				// add data to buffer map ##
				self::$buffer_map[] = [
					'hash'		=> $hash,
					'tag'		=> $partial_match,
					'output'	=> $partial_data['markup'],
					'parent'	=> false,
				];
				*/

				// function returns which update the template also need to update the markup_template, for later find/replace ##
				$this->plugin->set(
					'_markup_template', 
					str_replace( $partial_match, $partial_data['markup'], $this->plugin->get('_markup_template') )
				);

				// update markup for willow parse ##
				$markup = new willow\parse\markup( $this->plugin );
				$markup->swap( $partial_match, $partial_data['markup'], 'partial', 'string', $process );

				// finally -- add a variable "{{ $field }}" before this partial block in markup->template ##
				// $variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => $hash, 'close' => 'var_c' ]);
				// parse\markup::swap( $partial_match, $variable, 'partial', 'variable', $process ); // '{{ '.$field.' }}'

			}

		}

	}



	public function cleanup( $args = null, $process = 'secondary' ){

		$open = trim( willow\tags::g( 'par_o' ) );
		$close = trim( willow\tags::g( 'par_c' ) );

		// strip all section blocks, we don't need them now ##
		$regex = \apply_filters( 
			'willow/parse/partials/cleanup/regex', 
			"/(?s)<code[^<]*>.*?<\/code>(*SKIP)(*F)|$open.*?$close/ms" 
		);

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

			$this->plugin->log( 'e:>Error in stored $markup' );

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
				
				// $this->plugin->log( $matches );
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

					$this->plugin->log( $count .' partial tags removed...' );

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
