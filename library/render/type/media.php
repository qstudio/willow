<?php

namespace q\willow\render\type;

use q\willow\core;
use q\core\helper as h;
// use q\ui;
use q\get;
use q\willow\render;

class media extends render\type {

    /**
     * Image type handler 
     * 
	 * @wp_post		Object 
     * @todo - placeholder fallback
     * @todo - Add picture handler ##
     **/ 
    public static function format( \WP_Post $wp_post = null, String $type_field = null, String $field = null, $context = null ): ?string {

		// start empty ##
        $string = null;

		// sanity ##
		if (
			is_null( $wp_post )
			|| ! $wp_post instanceof \WP_Post
			|| ! isset( self::$args )
			|| ! isset( $type_field )
			|| ! isset( $field )
			|| ! isset( $context ) // can be WP_Post OR ... @todo
		){

			h::log( 'e:>Error in passed args' );

			return $string;

		}

		// check ##
		// h::log( 'Field: '.$field.' - Type: '.$type_field.' - Attachment ID: '.$wp_post->ID );

		// prepare args ##
		$args['post'] = $wp_post; // we have a post, so send it to control the loading ##
		$args['field'] = $field; // send on post_field name ##
		// h::log( '$field: '.$field );

		// get context ##
		switch ( $context ) {

			case 'WP_Post' :
				
				// we can get the attachment and pass this to media::src() ##
				$args['attachment_id'] = \get_post_thumbnail_id( $wp_post );
				// $att vachment = \get_post( $attachment_id );

				$array = get\media::src( $args );

				// h::log( $array );

			break ;

		}

		// validate ##
		if ( 
			! $array
			|| ! is_array( $array ) 
		) {

			// log ##
			h::log( self::$args['task'].'~>n:>get\media::thumbnail returned bad data');

			return $string;

		}

		// ok.. so now we need to convert the returned array data, to a string --- 
		switch ( $type_field ) {

			case "src";

				// esc src array ##
				// $array = array_map( 'esc_attr', $array );

				// h::log( $array );

				// let's do this nicely -- remember we're starting inside src="{{}}" ##
				// $markup = \apply_filters( 'q/render/type/media/src', '" data-src="{{ src }}" srcset="{{ srcset }}" sizes="{{ sizes }}" alt="{{ alt }}' );
				// $markup = \apply_filters( 'q/render/type/media/src', '{{ src }}' );
				// $string = render\method::markup( $markup, $array );

				// $string = $array['src'];

				// conditional -- add img meta values ( sizes ) and srcset ##
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

					$src = \esc_attr($array['src']).'"'; 
					$srcset = ' srcset="'.\esc_attr($array['src_srcset']).'"'; 
					$data = ' data-src="'.\esc_attr($array['src']).'"'; // lazy way -- @todo, make this based on config ##
					$sizes = ' sizes="'.\esc_attr($array['src_sizes']).'"'; 
					$alt = ' alt="'.\esc_attr($array['src_alt']); 

					// compile string ##
					$string = $src.$srcset.$data.$sizes.$alt;

				} else {

					// just the src ##
					$string = \esc_attr($array['src']); 

				}

			break ;

		}

		// h::log( $string );

		// check ##
		if ( 
			is_null( $string ) 
		) {

			h::log( self::$args['task'].'~>n:>String is empty.. so return null' );

		}

        // kick back ##
        return $string;

	}
	
}
