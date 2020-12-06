<?php

namespace Q\willow\type;

use Q\willow\core\helper as h;
use Q\willow;

class meta {

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

			// w__log( 'e:>Value Type not allowed: '.__CLASS__ );

			// log ##
			w__log( $this->plugin->get( '_args' )['task'].'~>e:Value Type not allowed: "'.__CLASS__.'"');

			// return $args[0]->$args[1]; // WHY ??#
			return false;

		}

		// $value needs to be a WP_Post object ##
		if ( ! $wp_post instanceof \WP_Post ) {

			// log ##
			w__log( $this->plugin->get( '_args' )['task'].'~>e:Error in pased $args - not a WP_Post object');

			return false;

		}

		// build render_fields object ##
		$render_fields = new willow\render\fields( $this->plugin );

		// w__log( \get_post_meta( $wp_post->ID ) );

		// get all post meta in single query, this was already cached from WP_Query ##
		$post_meta = \get_post_meta( $wp_post->ID );

		// w__log( $post_meta );

		foreach( $post_meta as $key => $value ){

			// w__log( $value );

			if ( "_" == $key[0] ){

				// w__log( 'd:>Skipping Key, as pseudo private: '.$key );

				continue;

			}

			if( 
				is_string( $value )
			){

				// assign field and value ##
				$render_fields->set( $field.'.meta.'.$key, $value );

			} else {

				foreach( $value as $sub_key => $sub_value ){

					if ( 
						! is_string( $sub_value )
						|| is_serialized( $sub_value )
					){

						// w__log( 'd:>Skipping, as value is not a string: '.$sub_key );
		
						continue;
		
					}

					// assign field and value ##
					$render_fields->set( $field.'.meta.'.$key, $sub_value );

				}

			}

		}

		// w__log( self::$fields );

        // kick back --> nothing, as fields added to object props ##
        return '';

    }

}
