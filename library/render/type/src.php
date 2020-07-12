<?php

namespace q\willow\render\type;

use q\willow\core;
use q\core\helper as h;
// use q\ui;
use q\willow;
use q\get;
use q\willow\render;

class src extends render\type {

    /**
     * Image type handler 
     * 
	 * @wp_post		Object 
     * @todo - placeholder fallback
     * @todo - Add picture handler ##
     **/ 
    public static function format( \WP_Post $wp_post = null, String $type_field = null, String $field = null ): string {

		// check ##
		// h::log( 'Field: '.$field.' - Type: '.$type_field.' - Attachment ID: '.$wp_post->ID );

		// check and assign ##
		// h::log( self::$args );
        $handle = 
            isset( self::$args['src'][$field]['handle'] ) ?
            self::$args['src'][$field]['handle'] : // get handle defined in calling args ##
            \apply_filters( 'q/render/type/src/handle', 'medium' ); // filterable default ##

        // h::log( 'Image handle: '.$handle );

		// start empty ##
        $string = '';

        // h::log( 'Image ID: '.$wp_post );

        // get image ##
		$src = \wp_get_attachment_image_src( $wp_post->ID, $handle );
		// h::log( $src );

		// validate ##
		if ( 
			! $src
			|| ! is_array( $src ) 
		) {

			// h::log( $src );
			// h::log( 'wp_get_attachment_image_src returned bad data' );

			// log ##
			h::log( self::$args['task'].'~>n:>wp_get_attachment_image_src returned bad data');

			return $string;

		}

		// assign to string ##
        $string = $src[0];

		// conditional -- add img meta values and srcset ##
        if ( 
			// set locally..
			(
				isset( self::$args['config']['srcset'] )
            	&& true == self::$args['config']['srcset'] 
			)
			||
			// OR, set globally ##
			(
				isset( core\config::get([ 'context' => 'media', 'task' => 'config' ])['srcset'] )
				&& true == core\config::get([ 'context' => 'media', 'task' => 'config' ])['srcset']
			)
        ) {

			// h::log( 'Adding srcset to: '.$string );

            // $id = \get_post_thumbnail_id( $wp_post );
            $srcset = \wp_get_attachment_image_srcset( $wp_post->ID, $handle );
            $sizes = \wp_get_attachment_image_sizes( $wp_post->ID, $handle );
            $alt = 
                \get_post_meta( $wp_post->ID, '_wp_attachment_image_alt', true ) ?
                \get_post_meta( $wp_post->ID, '_wp_attachment_image_alt', true ) :
                get\post::excerpt_from_id( $wp_post->ID, 100 );

            // markup tag attributes ##
            $srcset = '" srcset="'.\esc_attr($srcset).'"'; 
            $sizes = ' sizes="'.\esc_attr($sizes).'"'; 
            $alt = ' alt="'.\esc_attr($alt).'"'; 

			$string = $src[0].$srcset.$sizes.$alt;
			
			// h::log( $string );

        }

		// check ##
		if ( is_null( $string ) ) {

			h::log( self::$args['task'].'~>n:>String is empty.. so return null' );

		}

        // kick back ##
        return $string;

	}
	


}
