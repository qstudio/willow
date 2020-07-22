<?php

namespace q\willow\filter;

use q\core;
use q\core\helper as h;
use q\willow;
use q\willow\filter;

// load it up ##
\q\willow\filter\strip::run();

class strip extends willow\filter {

	public static function run(){

		// filter variable ##
		\add_filter( 'q/willow/render/markup/variable', [ get_class(), 'variable' ], 10, 2 );

		// filter tag ##
		\add_filter( 'q/willow/render/markup/tag', [ get_class(), 'tag' ], 10, 2 );

	}


	

	// single variable
	public static function variable( $value, $key ) {

		// global first ##
		if( isset( self::$filter[self::$args['context']][self::$args['task']]['variables'][$key]['s'] ) ){

			// h::log( 'e:>Variable tag stripping on: '.self::$args['context'].'->'.self::$args['task'].'->'.$key );

			// h::log( 'd:>stripping tags from value: '.$value );

			$value = strip_tags( $value );
			// $value = htmlentities( $value, ENT_QUOTES, 'UTF-8' ); 

		}

		return $value;

	}



	
	// whole tag
	public static function tag( $value, $key ) {

		// h::log( self::$args );

		// global first ##
		if( isset( self::$filter[self::$args['context']][self::$args['task']]['global']['s'] ) ){

			// h::log( 'e:>Global tag stripping on: '.self::$args['context'].'->'.self::$args['task'].'->'.$key );

			// h::log( 'd:>stripping tags from value: '.$value );

			$value = strip_tags( $value );
			// $value = htmlentities( $value, ENT_QUOTES, 'UTF-8' ); 

		}

		return $value;

	}


}
