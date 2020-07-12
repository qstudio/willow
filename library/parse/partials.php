<?php

namespace q\willow;

use q\willow;
use q\willow\core;
use q\core\helper as h;
use q\willow\render;

class partials extends willow\parse {

	// store ##
	protected static $partials = [];

	/**
	 * Scan for partials in markup and convert to variables and $fields
	 * 
	 * @since 4.1.0
	*/
	public static function prepare( $args = null ){

		// h::log( $args['key'] );

		// global ##
		$config = core\config::get([ 'context' => 'partial', 'task' => 'config' ]);
		// h::log( $config );

		if ( 
			isset( $config['run'] )
			&& false === $config['run']
		){

			h::log( 'Partial config->run defined as false, so stopping here...' );

			// clean up tags, if not buffering ##
			// if ( is_null( self::$buffer ) ) self::cleanup();

			return false;

		}

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

			h::log( self::$args['task'].'~>e:>Error in $string' );

			return false;

		}

		// h::log('d:>'.$string);

		// get all sections, add markup to $markup->$field ##
		// note, we trim() white space off tags, as this is handled by the regex ##
		$open = trim( willow\tags::g( 'par_o' ) );
		$close = trim( willow\tags::g( 'par_c' ) );
		// $end = trim( tags::g( 'sec_e' ) );
		// $end_preg = str_replace( '/', '\/', ( trim( tags::g( 'sec_e' ) ) ) );
		// $end = '{{\/#}}';

		// h::log( 'open: '.$open. ' - close: '.$close );

		$regex = \apply_filters( 
			'q/render/parse/partial/regex/find', 
			"/$open\s+(.*?)\s+$close/s"  // note:: added "+" for multiple whitespaces.. not sure it's good yet...
			// "/{{#(.*?)\/#}}/s" 
		);

		// h::log( 't:> allow for badly spaced tags around sections... whitespace flexible..' );
		if ( 
			preg_match_all( $regex, $string, $matches, PREG_OFFSET_CAPTURE ) 
		){

			// clean up tags, if not buffering ##
			// if ( is_null( self::$buffer ) ) self::cleanup();

			// // strip all section blocks, we don't need them now ##
			// // $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
			// $regex_remove = \apply_filters( 
			// 	'q/render/markup/partial/regex/remove', 
			// 	"/$open.*?$close/ms" 
			// 	// "/{{#.*?\/#}}/ms"
			// );
			// self::$markup['template'] = preg_replace( $regex_remove, "", self::$markup['template'] ); 
		
			// preg_match_all( '/%[^%]*%/', $string, $matches, PREG_SET_ORDER );
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

				$position = $matches[0][$match][1]; // take from first array ##
				// h::log( 'd:>position: '.$position );
				// h::log( 'd:>position from 1: '.$matches[0][$match][1] ); 

				// get partial data ##
				$partial = core\method::string_between( $matches[0][$match][0], $open, $close );
				// $markup = method::string_between( $matches[0][$match][0], $close, $end );

				// return entire partial string, including tags for tag swap ##
				$partial_match = core\method::string_between( $matches[0][$match][0], $open, $close, true );
				// h::log( '$function_match: '.$function_match );

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
					h::log( 'e:>Error in partial_data: "'.$partial.'"' );

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
				$partial_data = \q\core\method::parse_args( $partial_data, $config );

				// h::log( 'd:>context: "'.$context.'"' );
				// h::log( 'd:>task: "'.$task.'"' );
				// h::log( $partial_data );

				// we should check local config for this partial, to see if they should run ##
				if( 
					isset( $partial_data['config']['run'] ) 
					&& false == $partial_data['config']['run']
				){

					h::log( self::$args['task'].'~>n:>Partial "'.$partial.'" config->run defined as false, so stopping here...' );
					h::log( 'd:>Partial "'.$partial.'" config->run defined as false, so stopping here...' );

					continue;

				}

				// hash ##
				$hash = 'partial__'.$task;
				// h::log( 'd:>partial hash: '.$hash );

				// // @todo -- currently only partials are handled... ##
				// switch( $context ) {

				// 	case 'partial' :

						// // so, we can add a new field value to $args array based on the field name - with the markup as value
						// render\fields::define([
						// 	// $function => self::{$function}()
						// 	$hash => $partial_data['markup']
						// ]);

					// break ;

					// default :

					// 	h::log( self::$args['task'].'~>n:>Currently, only partial partials are supported... :)' );

					// break ;

				// }

				// store ##
				self::$partials[$hash] = $partial_data['markup'];

				// finally -- add a variable "{{ $field }}" before this partial block in markup->template ##
				$variable = willow\tags::wrap([ 'open' => 'var_o', 'value' => $hash, 'close' => 'var_c' ]);
				// variable::set( $variable, $position, 'variable' ); // '{{ '.$field.' }}'
				willow\markup::swap( $partial_match, $variable, 'partial', 'variable' ); // '{{ '.$field.' }}'

			}

			// h::log( self::$partials );

			// so, we can add a new field value to $args array based on the field name - with the markup as value
			render\fields::define( self::$partials );

			// h::log( self::$fields );
			// h::log( self::$markup['template'] );

		}

	}



	public static function cleanup( $args = null ){

		$open = trim( willow\tags::g( 'par_o' ) );
		$close = trim( willow\tags::g( 'par_c' ) );

		// strip all section blocks, we don't need them now ##
		// $regex_remove = \apply_filters( 'q/render/markup/section/regex/remove', "/{{#.*?\/#}}/ms" );
		$regex = \apply_filters( 
			'q/willow/parse/partials/cleanup/regex', 
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

				// get count ##
				$count = strlen($matches[1]);

				if ( $count > 0 ) {

					h::log( $count .' partial tags removed...' );

				}

				// return nothing for cleanup ##
				return "";

			}, 
			self::$markup['template'] 
		);

	}


}
