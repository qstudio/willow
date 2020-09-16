<?php

namespace willow\filter;

use willow\core;
use willow\core\helper as h;
use willow;
use willow\filter;

// load it up ##
\willow\filter\apply::__run();

class apply extends willow\filter {

	public static function __run(){

		// filter tag ##
		\add_filter( 'willow/render/markup/tag', [ get_class(), 'tag' ], 10, 2 );

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
			isset( self::$filter[ self::$args['config']['hash'] ] )
			&& is_array( self::$filter[ self::$args['config']['hash'] ] )
		){

			// h::log( 'e:>Filters set for Willow tag: "{~ '.self::$args['context'].'~'.self::$args['task'].' ~}"' );
			// h::log( self::$filter[ self::$args['config']['hash'] ] );
			// h::log( $value );

			// get filters ##
			// $filters = filter\method::prepare([ 'filters' => self::$filter[ self::$args['config']['hash'] ] ]);

			// h::log( $filters );

			// store pre-filter value ##
			$pre_value = $value; 

			// bounce to filter::apply()
			$filter_value = filter\method::apply([ 
				'filters' 	=> self::$filter[ self::$args['config']['hash'] ], 
				'string' 	=> $value, 
				'use' 		=> 'tag', // for filters ##
				// 'key'		=> $key
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

}
