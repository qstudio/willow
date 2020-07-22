<?php

namespace q\willow\filter;

use q\core;
use q\core\helper as h;
use q\willow;
use q\willow\filter;

// load it up ##
\q\willow\filter\escape::run();

class escape extends willow\filter {

	public static function run(){

		// filter variable ##
		\add_filter( 'q/willow/render/markup/variable', [ get_class(), 'variable' ], 10, 2 );

		// filter tag ##
		\add_filter( 'q/willow/render/markup/tag', [ get_class(), 'tag' ], 10, 2 );

	}


	// @todo - escape entire tag
	public static function tag( $value, $key ) {

		// h::log( self::$filter );
		// h::log( self::$args );

		/*
		filters stored by context->task with ->global filters and ->variable filters, stored under original [f] flag reference
		*/

		// h::log( 'e:>Global string escaping on: '.self::$args['context'].'->'.self::$args['task'].'->'.$key );

		// global first ##
		if( isset( self::$filter[self::$args['context']][self::$args['task']]['global']['e'] ) ){

			// h::log( 'e:>Global string escaping on: '.self::$args['context'].'->'.self::$args['task'].'->'.$key );

			$value = mb_convert_encoding( $value, 'UTF-8', 'UTF-8' );
			$value = htmlentities( $value, ENT_QUOTES, 'UTF-8' ); 

		}

		return $value;

	}



	// @todo - escape single variable
	public static function variable( $value, $key ) {

		// h::log( 't:>ALL values should be escaped - this can be avoided with [u] tag -- plus, we need to remove flags again for search/replace to work...??' );

		// h::log( self::$filter );
		// h::log( self::$args );

		/*
		filters stored by context->task with ->global filters and ->variable filters, stored under original [f] flag reference
		*/

		// global first ##
		if( isset( self::$filter[self::$args['context']][self::$args['task']]['variables'][$key]['e'] ) ){

			// h::log( 'd:>Variable escaping on: '.self::$args['context'].'->'.self::$args['task'].'->'.$key );

			// look for {{ variable }}
			// h::log( '$value: '.$value );

			$value = mb_convert_encoding( $value, 'UTF-8', 'UTF-8' );
			$value = htmlentities( $value, ENT_QUOTES, 'UTF-8' ); 

		}

		return $value;

	}

}
