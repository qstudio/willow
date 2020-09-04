<?php

namespace q\willow;

use q\willow;
use q\willow\core;
use q\willow\core\helper as h;

class partials extends willow\parse {

	// store ##
	protected static $partials = [];

	/**
	 * Scan for partials in markup and convert to variables and $fields
	 * 
	 * @since 4.1.0
	*/
	public static function prepare( $args = null, $process = 'internal' ){

		// global ##
		$config = core\config::get([ 'context' => 'partial', 'task' => 'config' ]);
		// h::log( $config );

		if ( 
			isset( $config['run'] )
			&& false === $config['run']
		){

			h::log( 'Partial config->run defined as false, so stopping here...' );

			return false;

		}
		
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

			h::log( self::$args['task'].'~>e:>Error in $string' );

			return false;

		}

		// h::log('d:>'.$string);

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( willow\tags::g( 'par_o' ) );
		$close = trim( willow\tags::g( 'par_c' ) );

		// h::log( 'open: '.$open. ' - close: '.$close );

		$regex = \apply_filters( 
			'q/willow/render/parse/partials/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
		);

		// h::log( 't:> allow for badly spaced tags around sections... whitespace flexible..' );
		if ( 
			preg_match_all( $regex, $string, $matches, PREG_OFFSET_CAPTURE ) 
		){

			// h::log( $matches[1] );

			// sanity ##
			if ( 
				! $matches
				|| ! isset( $matches[1] ) 
				|| ! $matches[1]
			){

				h::log( self::$args['task'].'~>e:>Error in returned matches array' );

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

					h::log( self::$args['task'].'~>e:>Error in returned matches - no position' );

					continue;

				}

				// h::log( 'd:>Searching for partials in  markup...' );

				// $position = $matches[0][$match][1]; // take from first array ##
				// h::log( 'd:>position: '.$position );
				// h::log( 'd:>position from 1: '.$matches[0][$match][1] ); 

				// get partial data ##
				$partial = core\method::string_between( $matches[0][$match][0], $open, $close );
				// $markup = method::string_between( $matches[0][$match][0], $close, $end );

				// return entire partial string, including tags for tag swap ##
				$partial_match = core\method::string_between( $matches[0][$match][0], $open, $close, true );
				// h::log( '$partial_match: '.$partial_match );

				// sanity ##
				if ( 
					! isset( $partial ) 
					// || ! strstr( $partial, '__' )
					// || ! isset( $markup ) 
				){

					h::log( self::$args['task'].'~>e:>Error in returned match function' );

					continue; 

				}

				// clean up ##
				$partial = trim($partial);
				$context = 'partial';
				$task = $partial;
				// list( $context, $task ) = explode( '__', $partial );

				// test what we have ##
				// h::log( 'd:>partial: "'.$partial.'"' );
				// h::log( self::$args );

				// perhaps better to hand this to a function, which can grab args ??
				$partial_data = core\config::get([ 'context' => $context, 'task' => $task ]);

				// no data, no go ##
				if(
					! $partial_data
					// || ! is_array( $partial_data )
				){

					h::log( self::$args['task'].'~>e:>Error in partial_data: "'.$partial.'"' );
					h::log( 'e:>Error loading config for partial: "'.$partial.'"' );

					continue;

				}

				// h::log( $partial_data );

				// data passed as a string, so format
				if(
					is_string( $partial_data )
				){

					h::log( self::$args['task'].'~>d:>Partial: "'.$partial.'" only sent markup, so converting to array format' );

					$partial_data = [
						'markup' => $partial_data
					];

				}

				// merge local partial data, with partial->config ##
				$partial_data = core\method::parse_args( $partial_data, $config );

				// h::log( 'd:>context: "'.$context.'"' );
				// h::log( 'd:>task: "'.$task.'"' );
				// h::log( $partial_data );

				// we should check local config for this partial, to see if they should run ##
				if( 
					isset( $partial_data['config']['run'] ) 
					&& false == $partial_data['config']['run']
				){

					// h::log( self::$args['task'].'~>n:>Partial "'.$partial.'" config->run defined as false, so stopping here...' );
					h::log( 'd:>Partial "'.$partial.'" config->run defined as false, so stopping here...' );

					continue;

				}

				// hash ##
				$hash = 'partial__'.$task.'__'.rand();
				// h::log( 'd:>partial hash: '.$hash );

				// add data to buffer map ##
				self::$buffer_map[] = [
					'hash'		=> $hash,
					'tag'		=> $partial_match,
					'output'	=> $partial_data['markup'],
					'parent'	=> false,
				];

				// finally -- add a variable "{{ $field }}" before this partial block in markup->template ##
				// $variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => $hash, 'close' => 'var_c' ]);
				// parse\markup::swap( $partial_match, $variable, 'partial', 'variable', $process ); // '{{ '.$field.' }}'

			}

		}

	}



	public static function cleanup( $args = null, $process = 'internal' ){

		$open = trim( willow\tags::g( 'par_o' ) );
		$close = trim( willow\tags::g( 'par_c' ) );

		// strip all section blocks, we don't need them now ##
		$regex = \apply_filters( 
			'q/willow/parse/partials/cleanup/regex', 
			"/(?s)<pre[^<]*>.*?<\/pre>(*SKIP)(*F)|$open.*?$close/ms" 
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

					h::log( $count .' partial tags removed...' );

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
