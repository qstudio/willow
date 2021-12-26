<?php

namespace willow\parse;

use willow;

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
	public function __construct( willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/**
	 * @todo..
	*/
	function match( $args = null, $process = 'secondary' ){

		// local vars ##
		$_args = \willow()->get( '_args' );
		$_markup = \willow()->get( '_markup' );
		$_buffer_markup = \willow()->get( '_buffer_markup' );

		// global ##
		$config = \willow()->config->get([ 'context' => 'partial', 'task' => 'config' ]);
		// w__log( $config );

		// get parse task ##
		$_parse_task = $_args['task'] ?? \willow()->get( '_parse_task' );

		if ( 
			isset( $config['run'] )
			&& false === $config['run']
		){

			w__log( 'Partial config->run defined as false, so stopping here...' );

			return false;

		}
		
		// sanity -- method requires requires ##
		if ( 
			(
				'secondary' == $process
				&& (
					null === ( $_markup )
					|| ! is_array( $_markup )
					|| ! isset( $_markup['template'] )
				)
			)
			||
			(
				'primary' == $process
				&& (
					null === $_buffer_markup
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

			w__log( $_parse_task.'~>e:>Error in $string' );

			return false;

		}

		// w__log('d:>'.$string);

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( \willow()->tags->g( 'par_o' ) );
		$close = trim( \willow()->tags->g( 'par_c' ) );

		// w__log( 'open: '.$open. ' - close: '.$close );

		$regex = \apply_filters( 
			'willow/render/parse/partials/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
		);

		// w__log( 't:> allow for badly spaced tags around sections... whitespace flexible..' );
		if ( 
			preg_match_all( $regex, $string, $matches, PREG_OFFSET_CAPTURE ) 
		){

			// w__log( $matches[1] );

			// sanity ##
			if ( 
				! $matches
				|| ! isset( $matches[1] ) 
				|| ! $matches[1]
			){

				w__log( $_args['task'].'~>e:>Error in returned matches array' );

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

					w__log( $_args['task'].'~>e:>Error in returned matches - no position' );

					continue;

				}

				// w__log( 'd:>Searching for partials in  markup...' );

				// $position = $matches[0][$match][1]; // take from first array ##
				// w__log( 'd:>position: '.$position );
				// w__log( 'd:>position from 1: '.$matches[0][$match][1] ); 

				// get partial data ##
				$partial = willow\core\strings::between( $matches[0][$match][0], $open, $close );
				// $markup = strings::between( $matches[0][$match][0], $close, $end );

				// return entire partial string, including tags for tag swap ##
				$partial_match = willow\core\strings::between( $matches[0][$match][0], $open, $close, true );
				// w__log( '$partial_match: '.$partial_match );

				// sanity ##
				if ( 
					! isset( $partial ) 
					// || ! strstr( $partial, '__' )
					// || ! isset( $markup ) 
				){

					w__log( $_args['task'].'~>e:>Error in returned match function' );

					continue; 

				}

				// clean up ##
				$partial = trim($partial);
				$context = 'partial';
				$task = $partial;

				// test what we have ##
				// w__log( 'd:>partial: "'.$partial.'"' );
				// w__log( $_args );

				// perhaps better to hand this to a method, which can grab args ??
				$partial_data = \willow()->config->get([ 'context' => $context, 'task' => $task ]);

				// no data, no go ##
				if(
					! $partial_data
					// || ! is_array( $partial_data )
				){

					w__log( $_args['task'].'~>e:>Error in partial_data: "'.$partial.'"' );
					w__log( 'e:>Error loading config for partial: "'.$partial.'"' );

					continue;

				}

				// data passed as a string, so format
				if(
					is_string( $partial_data )
				){

					w__log( $_args['task'].'~>d:>Partial: "'.$partial.'" only sent markup, so converting to array format' );

					$partial_data = [
						'markup' => $partial_data
					];

				}

				// merge local partial data, with partial->config ##
				$partial_data = willow\core\arrays::parse_args( $partial_data, $config );

				// w__log( 'd:>context: "'.$context.'"' );
				// w__log( 'd:>task: "'.$task.'"' );
				// w__log( $partial_data );

				// we should check local config for this partial, to see if they should run ##
				if( 
					isset( $partial_data['config']['run'] ) 
					&& false == $partial_data['config']['run']
				){

					// w__log( $_args['task'].'~>n:>Partial "'.$partial.'" config->run defined as false, so stopping here...' );
					w__log( 'd:>Partial "'.$partial.'" config->run defined as false, so stopping here...' );

					continue;

				}

				// function returns which update the template also need to update the markup_template, for later find/replace ##
				\willow()->set(
					'_markup_template', 
					str_replace( $partial_match, $partial_data['markup'], \willow()->get('_markup_template') )
				);

				// update markup for willow parse ##
				\willow()->parse->markup->swap( $partial_match, $partial_data['markup'], 'partial', 'string', $process );

			}

		}

	}

	/***/
	public function cleanup( $args = null, $process = 'secondary' ){

		// local vars ##
		$_args = \willow()->get( '_args' );
		$_markup = \willow()->get( '_markup' );
		$_buffer_markup = \willow()->get( '_buffer_markup' );

		$open = trim( \willow()->tags->g( 'par_o' ) );
		$close = trim( \willow()->tags->g( 'par_c' ) );

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
				
				// w__log( $matches );
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

					w__log( $count .' partial tags removed...' );

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
