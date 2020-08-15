<?php

namespace q\willow\filter;

use q\willow\core\helper as h;
use q\willow;
use q\willow\filter;

// load it up ##
\q\willow\filter\escape::run();

class nl2br extends willow\filter {

	public static function run(){

		// filter variable ##
		\add_filter( 'q/willow/render/markup/variable', [ get_class(), 'variable' ], 10, 2 );

		// filter tag ##
		\add_filter( 'q/willow/render/markup/tag', [ get_class(), 'tag' ], 10, 2 );

	}


	// entire tag
	public static function tag( $value, $key ) {

		// h::log( self::$filter );
		// h::log( self::$args );

		/*
		filters stored by context->task with ->global filters and ->variable filters, stored under original [f] flag reference
		*/

		// h::log( 'e:>Global string escaping on: '.self::$args['context'].'->'.self::$args['task'].'->'.$key );

		// global first ##
		if( isset( self::$filter[self::$args['context']][self::$args['task']]['global']['n'] ) ){

			// h::log( 'e:>Global string escaping on: '.self::$args['context'].'->'.self::$args['task'].'->'.$key );

			$value = nl2br( $value );

		}

		return $value;

	}



	// single variable
	public static function variable( $value, $key ) {

		// h::log( self::$filter );
		// h::log( self::$args );
		// h::log( 'Key: '.$key );

		// global first ##
		if( isset( self::$filter[self::$args['context']][self::$args['task']]['variables'][$key]['n'] ) ){

			// h::log( 'd:>Variable escaping on: '.self::$args['context'].'->'.self::$args['task'].'->'.$key );

			// look for {{ variable }}
			// h::log( '$value: '.$value );

			$value = nl2br( $value );
			// $value = wpautop( $value );

			// h::log( '$value: '.$value );

		}

		return $value;

	}

}
