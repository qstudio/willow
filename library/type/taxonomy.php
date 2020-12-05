<?php

namespace Q\willow\type;

use Q\willow\core\helper as h;
use Q\willow;

class taxonomy {

	private 
		$plugin = false,
		$type_method = false
	;

	/**
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

		// get types ##
		$this->type_method = new willow\type\method( $this->plugin );

	}

	/**
     * WP Post handler
     *  
     * 
     **/ 
    public function format( \WP_Post $wp_post = null, String $type_field = null, String $field = null, $context = null ): string {

		// check if type allowed ##
		if ( ! array_key_exists( __CLASS__, $this->type_method->get_allowed() ) ) {

			// h::log( 'e:>Value Type not allowed: '.__CLASS__ );

			// log ##
			h::log( $this->plugin->get( '_args' )['task'].'~>e:Value Type not allowed: "'.__CLASS__.'"');

			// return $args[0]->$args[1]; // WHY ??#
			return false;

		}

		// $value needs to be a WP_Post object ##
		if ( ! $wp_post instanceof \WP_Post ) {

			// log ##
			h::log( $this->plugin->get( '_args' )['task'].'~>e:Error in pased $args - not a WP_Post object');

			return false;

		}

		// start with default passed value ##
		$string = '';

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
