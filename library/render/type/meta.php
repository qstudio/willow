<?php

namespace willow\render\type;

use willow\core;
use willow\core\helper as h;
// use q\get;
use willow\render;

class meta extends render\type {

	/**
     * WP Post handler
     *  
     * 
     **/ 
    public static function format( \WP_Post $wp_post = null, String $type_field = null, String $field = null ): string {

		// h::log( \get_post_meta( $wp_post->ID ) );

		// get all post meta in single query, this was already cached from WP_Query ##
		$post_meta = \get_post_meta( $wp_post->ID );

		// h::log( $post_meta );

		foreach( $post_meta as $key => $value ){

			// h::log( $value );

			if ( "_" == $key[0] ){

				// h::log( 'd:>Skipping Key, as pseudo private: '.$key );

				continue;

			}

			if( 
				is_string( $value )
			){

				// assign field and value ##
				render\fields::set( $field.'.meta.'.$key, $value );

			} else {

				foreach( $value as $sub_key => $sub_value ){

					if ( 
						! is_string( $sub_value )
						|| is_serialized( $sub_value )
					){

						// h::log( 'd:>Skipping, as value is not a string: '.$sub_key );
		
						continue;
		
					}

					// assign field and value ##
					render\fields::set( $field.'.meta.'.$key, $sub_value );

				}

			}

		}

		// h::log( self::$fields );

		// start with empty string ##
		// $string = '';

        // kick back ##
        return '';

    }



}
