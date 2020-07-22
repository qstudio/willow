<?php

namespace q\willow\context;

use q\core\helper as h;
// use q\ui;
use q\get;
use q\willow;
use q\willow\context;
use q\willow\render; 

class taxonomy extends willow\context {

	/**
     * Post Category
     *
     * @param       Array       $args
     * @since       1.3.0
     * @return      String
     */
    public static function terms( $args = null ) {

		// h::log( $args );
		// h::log( self::$markup );

		// get term - returns array with keys 'title', 'permalink', 'slug', 'active' ##
		// render\fields::define(
			// return an array of term items, in the array "terms" ##
			return get\taxonomy::terms( $args );
		// );

	}

	
	public static function category( Array $args = null ) {

		// get first post category ##
		// render\fields::define( 
		return get\taxonomy::category( $args );
		// );

	}


	public static function categories( Array $args = null ) {

		// get all post categories ##
		// render\fields::define( 
		return get\taxonomy::categories( $args );
		// );

	}


	public static function tag( Array $args = null ) {

		// get first post tag ##
		// render\fields::define( 
		return get\taxonomy::tag( $args );
		// );

	}

	public static function tags( Array $args = null ) {

		// get all post tags ##
		// render\fields::define( 
		return get\taxonomy::tags( $args );
		// );

	}
	
}
