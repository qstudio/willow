<?php

namespace Q\willow\type;

use Q\willow\core\helper as h;
use Q\willow;

class author {

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

		// get author ##
		$author = $wp_post->post_author;
		$authordata = \get_userdata( $author );

		// validate ##
		if (
			! $authordata
		) {

			h::log( 'e:>Error in returned author data' );

			return $string;

		}

		// special fields first ?? ##
		switch( $type_field ) {

			// author permalink ##
			case 'author_permalink' :

				$string = \esc_url( \get_author_posts_url( $author ) );

			break ;

			// formatted author name ##
			case 'author_name' :

				$string = isset( $authordata->display_name ) ? $authordata->display_name : $authordata->user_login ;
				
			break ;

		}

		// check ##
		if ( is_null( $string ) ) {

			h::log( 'e:>String is empty.. so return null' );

			// $string = $wp_post->$field;

		}

        // kick back ##
        return $string;

    }

}
