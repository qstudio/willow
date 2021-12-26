<?php

namespace willow\type;

use willow\core\helper as h;
use willow;

class media {

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
    public function format( \WP_Post $wp_post = null, String $type_field = null, String $field = null, $context = null, $type = null ):? string {

		// local var ##
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

		// start empty ##
        $string = null;

		// sanity ##
		if (
			is_null( $wp_post )
			|| ! $wp_post instanceof \WP_Post
			|| is_null ( $_args )
			|| ! isset( $type_field )
			|| ! isset( $field )
			|| ! isset( $context ) // can be WP_Post OR ... @todo
		){

			w__log( 'e:>Error in passed args' );

			return $string;

		}

		// check ##
		// w__log( 'Field: '.$field.' - Type: '.$type_field.' - Attachment ID: '.$wp_post->ID );

		// prepare args ##
		$args['post'] = $wp_post; // we have a post, so send it to control the loading ##
		$args['field'] = $field; // send on post_field name ##
		// w__log( '$field: '.$field );

		// get context ##
		switch ( $context ) {

			case 'WP_Post' :
				
				// we can get the attachment and pass this to media::src() ##
				$args['attachment_id'] = \get_post_thumbnail_id( $wp_post );
				// $att vachment = \get_post( $attachment_id );

				// w__log( $args );

				$array = \willow()->media->src( $args );
				// $array = willow\get\media::src( $args );

				// w__log( $array );

			break ;

		}

		// validate ##
		if ( 
			! $array
			|| ! is_array( $array ) 
		) {

			// log ##
			\w__log( $_args['task'].'~>n:>get\media::thumbnail returned bad data');

			return $string;

		}

		// ok.. so now we need to convert the returned array data, to a string --- 
		switch ( $type_field ) {

			case "src";

				// conditional -- add img meta values ( sizes ) and srcset ##
				if ( 
					isset( $_args['config']['srcset'] )
					&& true == $_args['config']['srcset'] 
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

		// w__log( $string );

		// check ##
		if ( 
			is_null( $string ) 
		) {

			\w__log( $_args['task'].'~>n:>String is empty.. so return null' );

		}

        // kick back ##
        return $string;

	}
	
}
