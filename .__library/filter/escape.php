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

		// filter markup ##
		\add_filter( 'q/willow/render/markup/markup', [ get_class(), 'markup' ], 10, 2 );

	}


	// @todo - escape ## per call, or globally ## ??
	public static function markup( $value, $key ) {

		// h::log( self::$filter );
		// h::log( self::$args );

		/*
		filters stored by context->task with ->global filters and ->variable filters, stored under original [f] flag reference
		*/

		// global first ##
		if( isset( self::$filter[self::$args['context']][self::$args['task']]['global']['e'] ) ){

			// h::log( 'e:>Global string escaping on: '.self::$args['context'].'->'.self::$args['task'].'->'.$key );

			$value = mb_convert_encoding( $value, 'UTF-8', 'UTF-8' );
			$value = htmlentities( $value, ENT_QUOTES, 'UTF-8' ); 

		}

		return $value;

	}



	// @todo - escape ## per call, or globally ## ??
	public static function variable( $value, $key ) {

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
