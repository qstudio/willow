<?php

namespace q\willow\filter;

use q\willow\core;
use q\willow\core\helper as h;
use q\willow;
use q\willow\filter;

// load it up ##
\q\willow\filter\apply::__run();

class apply extends willow\filter {

	public static function __run(){

		// filter variable ##
		// \add_filter( 'q/willow/render/markup/variable', [ get_class(), 'variable' ], 10, 2 );

		// filter tag ##
		\add_filter( 'q/willow/render/markup/tag', [ get_class(), 'tag' ], 10, 2 );

	}


	/**
	 * Apply filters to entire {~ Willow ~} tag
	 * 
	 * @since 	1.2.0
	 * @return	String
	*/
	public static function tag( $value, $key ) {

		// h::log( self::$filter );

		// check for tag filter ##
		if( 
			isset( self::$filter[ self::$args['config']['hash'] ]['tag'] )
			&& is_array( self::$filter[ self::$args['config']['hash'] ]['tag'] )
		){

			// h::log( 'e:>Filters set for Willow tag: "{~ '.self::$args['context'].'~'.self::$args['task'].' ~}"' );
			// h::log( self::$filter[ self::$args['config']['hash'] ] );
			// h::log( $value );

			// get filters ##
			// $filters = filter\method::prepare([ 'filters' => self::$filter[ self::$args['config']['hash'] ]['tag'] ]);

			// h::log( $filters );

			// store pre-filter value ##
			$pre_value = $value; 

			// bounce to filter::apply()
			$filter_value = filter\method::apply([ 
				'filters' 	=> self::$filter[ self::$args['config']['hash'] ]['tag'], 
				'string' 	=> $value, 
				'use' 		=> 'tag', // for filters ##
				'key'		=> $key
			]);

			// compare pre and post filter values ##
			if( $filter_value != $pre_value ){

				// h::log( 'd:>Filtered value is different: '.$filter_value );

				// run unique str_replace on whole variable ##
				// $string = str_replace( $var_value, $filter_value, $string );

				return $filter_value;

			}

			return $value;

		}

		// nothing cooking -- return raw value ##
		return $value;

	}



	/**
	 * Apply filters to single {{ variable }}
	 * 
	 * @since 	1.2.0
	 * @return	String
	*/
	public static function variable( $value, $filter_key ) {

		// h::log( self::$filter );
		// h::log( self::$args );
		// h::log( 'hash: '.self::$args['config']['hash'] );
		// h::log( 'Key: '.$key );
		// h::log( 'Filter Key: '.$filter_key );

		// check for variable tag ##
		if( 
			isset( self::$filter[ self::$args['config']['hash'] ]['variable'][ $filter_key ] )
			&& is_array( self::$filter[ self::$args['config']['hash'] ]['variable'][ $filter_key ] )
			// isset( self::$filter[self::$args['context']][self::$args['task']]['variables'][$filter_key] ) 
			// && is_array( self::$filter[self::$args['context']][self::$args['task']]['variables'][$filter_key] ) 
		){

			// h::log( 'd:>Variable filters set for: "{~ '.self::$args['context'].'~'.self::$args['task'].' ~} -> {{ '.$filter_key.' }}"' );
			// h::log( 'Key: '.$key );
			// h::log( 'Filter Key: '.$filter_key );
			// h::log( '$value: '.$value );

			// bounce to filter::apply()
			$value = filter\method::apply([ 
				'filters' 		=> self::$filter[ self::$args['config']['hash'] ]['variable'][ $filter_key ], 
				'string' 		=> $value, 
				'use' 			=> 'variable', // for filters ##
				'filter_key'	=> $filter_key,
				// 'key'			=> $key
			]);

			// h::log( '$value: '.$value );

			return $value;

		}

		// nothing cooking -- return raw value ##
		return $value;

	}

}
