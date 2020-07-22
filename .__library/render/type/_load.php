<?php

namespace q\willow\render;

// use q\core;
use q\willow\core\helper as h;
use q\willow;

// load it up ##
\q\willow\render\type::run();

class type extends willow\render {
	
	public static function run(){

		\q\core\load::libraries( self::load() );

	}


    /**
    * Load Libraries
    *
    * @since        2.0.0
    */
    public static function load()
    {

		return $array = [

			// media object ##
			'media' => h::get( 'render/type/media.php', 'return', 'path' ),

			// post fields ##
			'post' => h::get( 'render/type/post.php', 'return', 'path' ),

			// author fields ##
			'author' => h::get( 'render/type/author.php', 'return', 'path' ),

			// taxonomy ## ##
			'taxonomy' => h::get( 'render/type/taxonomy.php', 'return', 'path' ),

		];

	}
	
	/** 
	 * bounce to function getter ##
	 * function name can be any of the following patterns:
	 * 
	 * the_group
	 * the_%%%
	 * 
	 * field_FIELDNAME // @todo
	 * type_IMAGE || ARRAY || WP_Object etc // @todo
	 */
	public static function __callStatic( $function, $args = null ){	

		// check if args format is correct ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			// log ##
			h::log( self::$args['task'].'~>e:Error in passed $args');

			return false;

		}

		// $value needs to be a WP_Post object ##
		if ( ! $args[0] instanceof \WP_Post ) {

			// log ##
			h::log( self::$args['task'].'~>e:Error in pased $args - not a WP_Post object');

			return false;

		}

		// $value needs to be a WP_Post object ##
		if ( ! isset( $args[1] ) ) {

			// log ##
			h::log( self::$args['task'].'~>e:Error in pased $args - missing type_field');

			return false;

		}

		// $value needs to be a WP_Post object ##
		if ( ! isset( $args[2] ) ) {

			// log ##
			h::log( self::$args['task'].'~>e:Error in pased $args - missing $field');

			return false;

		}

		// check if type allowed ##
		if ( ! array_key_exists( $function, self::get_allowed() ) ) {

			h::log( 'Value Type not allowed: '.$function );

			// log ##
			h::log( self::$args['task'].'~>e:Value Type not allowed: "'.$function.'"');

			return $args[0]->$args[1];

		}

		// test namespace ##
		$namespace = '\\q\\willow\\render\\type\\'.$function;
		$method_function = 'format';
		// h::log( $namespace.'::'.$function );

		// the__ methods ##
		if (
			\method_exists( $namespace, $method_function ) // && exists ##
			&& \is_callable([ $namespace, $method_function ]) // && exists ##
		) {

			// h::log( 'Found function: "'.$namespace.'::'.$method_function.'()"' );

			// call it and capture response ##
			$string = $namespace::{$method_function}( $args[0], $args[1], $args[2], $args[3] );

			// filter post fields -- global ##
			$string = \apply_filters( 
				'q/willow/render/type/'.$args[1], $string 
			);

			// filter group/field -- field specific ##
			$string = \apply_filters( 
				'q/willow/render/type/'.self::$args['task'].'/'.$args[1], $string
			);

			// test ##
			// h::log( $string );

			// return ##
			return $string;

		}

		// log ##
		h::log( 'd:>No matching method found for: '.$function );

		// kick back nada - as this renders on the UI ##
		return false;

	}
	

    /**
     * Get allowed fomats with filter ##
     * 
     */
    public static function get_allowed()
    {

        return \apply_filters( 'q/willow/render/type/get', self::$type );

    }


}
