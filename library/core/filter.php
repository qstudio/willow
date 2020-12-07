<?php

namespace Q\willow\core;

use Q\willow\core;

class filter {

	/**
     * Plugin Instance
     *
     * @var     Object      $plugin
     */
	protected 
		$plugin
	;

	/**
	 * CLass Constructer 
	*/
	function __construct( \Q\willow\plugin $plugin = null ){

		// Log::write( $plugin );

        // grab passed plugin object ## 
		$this->plugin = $plugin;
		
	}

    /**
     * Filter items at set points to allow for manipulation
     * 
     * 
     */
    public function apply( Array $args = null ){

        // sanity ##
        if ( 
            ! $args 
            || ! is_array( $args )
            || ! isset( $args['filter'] )
            || ! isset( $args['parameters'] )
            || ! is_array( $args['parameters'] )
        ) {

            w__log('Error in passed $args');

            return $args['return'];

		}
		
		// sanity ##
        if ( 
            ! isset( $args['return'] )
        ) {

            w__log('Error in passed $args - no $return specified');

            return 'Error';

        }

        if( \has_filter( $args['filter'] ) ) {

			if ( isset( $args['debug'] ) ) {

				w__log( '
					Filter: '.$args['filter'].' - 
					Parameters: '.implode( ',', array_keys( $args['parameters'] ) ).' - 
					Return: '.gettype( $args['return'] ) 
				);

			}

            // run filter ##
            $return = \apply_filters( $args['filter'], $args['parameters'] );

			if ( isset( $args['debug'] ) ) {

				// check return ##
				w__log( $return );

			}

        } else {

            // w__log( 'No matching filter found: '.$args['filter'] );
            $return = $args['return']; 

        }

        // return true ##
        return $return;

    }

}
