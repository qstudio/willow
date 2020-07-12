<?php

namespace q\willow\render\type;

// use q\core;
use q\core\helper as h;
// use q\ui;
// use q\get;
use q\willow\render;

class author extends render\type {

	/**
     * Author handler
     *  
     * 
     **/ 
    public static function format( \WP_Post $wp_post = null, String $type_field = null, String $field = null ): string {

		// start with default passed value ##
		$string = null;

		// get author ##
		$author = $wp_post->post_author;
		$authordata = \get_userdata( $author );

		// validate ##
		if (
			! $authordata
		) {

			h::log( 'Error in returned author data' );

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

			h::log( 'String is empty.. so return null' );

			// $string = $wp_post->$field;

		}

        // kick back ##
        return $string;

    }



}
