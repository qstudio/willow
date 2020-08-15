<?php

namespace q\willow\core;

use q\willow\core;
use q\willow\core\helper as h;

class filter extends \q_willow {

    /**
     * Filter items at set points to allow for manipulation
     * 
     * 
     */
    public static function apply( Array $args = null ){

        // sanity ##
        if ( 
            ! $args 
            || ! is_array( $args )
            || ! isset( $args['filter'] )
            || ! isset( $args['parameters'] )
            || ! is_array( $args['parameters'] )
        ) {

            h::log('Error in passed self::$args');

            return $args['return'];

		}
		
		// sanity ##
        if ( 
            ! isset( $args['return'] )
        ) {

            h::log('Error in passed self::$args - no $return specified');

            return 'Error';

        }

        if( \has_filter( $args['filter'] ) ) {

			if ( isset( $args['debug'] ) ) {

				h::log( '
					Filter: '.$args['filter'].' - 
					Parameters: '.implode( ',', array_keys( $args['parameters'] ) ).' - 
					Return: '.gettype( $args['return'] ) 
				);

			}

            // run filter ##
            $return = \apply_filters( $args['filter'], $args['parameters'] );

			if ( isset( $args['debug'] ) ) {

				// check return ##
				h::log( $return );

			}

        } else {

            // h::log( 'No matching filter found: '.$args['filter'] );
            $return = $args['return']; 

        }

        // return true ##
        return $return;

    }

}
