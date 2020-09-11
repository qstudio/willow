<?php

namespace willow\get;

// Q ##
use willow\core;
use willow\core\helper as h;
use willow\get;

class post extends \willow\get {

    /**
     * Method to clean up calling and checking for the global $post object
     * Allows $post to be passed
     *
     * @param       Mixed       $post       post ID or $post object
     *
     * @since       1.0.7
     * @return      Object      WP_Post object
     */
    public static function object( $args = null )
    {

        // h::log( $args );

        // let's try and get a $post from the passed $args ##
        if ( ! is_null ( $args ) && isset( $args ) ) {

            if ( is_array( $args ) && isset( $args["post"] ) ) {

				$post = $args["post"];
				// h::log( 'Post ID sent: '.$post );

            } else if ( is_object ( $args ) && isset ( $args->post ) ) {

                $post = $args->post;

            } else if ( is_integer( $args ) ) {

                $post = $args;

            }

        }

        // h::log( $post );

        // first let's see if anything was set ##
        if ( isset ( $post ) ) {

			// h::log( gettype( $post ) );

			// if ( ! is_object ( $post ) && is_int( $post ) ) {
            if ( is_string ( $post ) || is_int( $post ) ) {

                if ( $object = \get_post( $post ) ) {

                    // h::log( 'got post: '.$object->ID );

                    return (object) $object;

                }

            } else if ( is_object ( $post ) ) {

                return $post;

            }

		}
		
        // next, let's try the global scope ##
        global $post;

        // kick it back ##
        return $post;

    }


}
