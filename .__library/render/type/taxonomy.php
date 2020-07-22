<?php

namespace q\willow\render\type;

use q\core;
use q\core\helper as h;
// use q\ui;
use q\willow;
use q\get;
use q\willow\render;

class taxonomy extends render\type {

	/**
     * Category handler
     *  
     * 
     **/ 
    public static function format( \WP_Post $wp_post = null, String $type_field = null, String $field = null ): string {

		// start with default passed value ##
		$string = null;

		// get category ##
		$category = \get_the_category( $wp_post->ID );
		// h::log( $category );

		// get category ##
		if ( 
			! $category
			|| ! is_array( $category )
			|| ! isset( $category[0] )
		) {

			// h::log( 'No category or corrupt data returned' );

			// log ##
			h::log( self::$args['task'].'~>n:No category or corrupt data returned');

			return $string;

		}

		// h::log( 'Working: '.$field );

		switch( $type_field ) {

			case 'category_name' :

				$string = 
					isset( $category[0] ) ? 
					$category[0]->name : 
					$wp_post->post_type ; // category missing -- default to post type name ##

			break ;

			case 'category_permalink' :

				$string = isset( $category[0] ) ? \get_category_link( $category[0] ) : null ; // category missing ##

			break ;

		}

		// check ##
		if ( is_null( $string ) ) {

			h::log( 'String is empty.. so return null' );

			$string = null;

		}

		// check ##
		// h::log( '$string: '.$string );

        // kick back ##
        return $string;

    }


}
