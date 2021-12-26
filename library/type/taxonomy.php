<?php

namespace willow\type;

use willow\core\helper as h;
use willow;

class taxonomy {

	private 
		$plugin = false
	;

	/**
     */
    public function __construct( willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/**
     * WP Post handler
     *  
     * 
     **/ 
    public function format( \WP_Post $wp_post = null, String $type_field = null, String $field = null, $context = null, $type = null ): string {

		$_args = \willow()->get( '_args' );

		// check if type allowed ##
		if ( ! array_key_exists( $type, \willow()->type->get->allowed() ) ) {

			// w__log( 'e:>Value Type not allowed: '.$type );

			// log ##
			w__log( $_args['task'].'~>e:Value Type not allowed: "'.$type.'"');

			// return $args[0]->$args[1]; // WHY ??#
			return false;

		}

		// $value needs to be a WP_Post object ##
		if ( ! $wp_post instanceof \WP_Post ) {

			// log ##
			w__log( $_args['task'].'~>e:Error in pased $args - not a WP_Post object');

			return false;

		}

		// start with default passed value ##
		$string = '';

		// get category ##
		$category = \get_the_category( $wp_post->ID );
		// w__log( $category );

		// get category ##
		if ( 
			! $category
			|| ! is_array( $category )
			|| ! isset( $category[0] )
		) {

			// w__log( 'No category or corrupt data returned' );

			// log ##
			w__log( $_args['task'].'~>n:No category or corrupt data returned');

			return $string;

		}

		// w__log( 'Working: '.$field );

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

			w__log( 'String is empty.. so return null' );

			$string = null;

		}

		// check ##
		// w__log( '$string: '.$string );

        // kick back ##
        return $string;

    }


}
