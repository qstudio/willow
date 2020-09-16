<?php

namespace willow\render;

use willow\core\helper as h;
use willow;

class log extends willow\render {


    /**
     * Logging function
     * 
     */
    public static function set( Array $args = null ){

		// h::log( 'e:>'.$args['task'] );
		// h::log( self::$args['config'] );

        if (
            ! isset( self::$args['config']['debug'] )
			|| false === self::$args['config']['debug']
			|| 'false' == self::$args['config']['debug']
			|| ! self::$args['config']['debug']
        ) {

            // h::log( 'd:>Debugging is turned OFF for : "'.$args['task'].'"' );

            return false;

        }   

		h::log( 'd:>Debugging is turned ON for : "'.$args['task'].'"' );

		// filter in group to debug ##
		\add_filter( 'willow/core/log/debug', function( $key ) use ( $args ){ 
			// h::log( $key );
			$return = is_array( $key ) ? array_merge( $key, [ $args['task'] ] ) : [ $key, $args['task'] ]; 
			// h::log( $return );
			return 
				$return;
			}
		);

		// return ##
		return true; 

		// debug the group ##
		// return core\log::write( $args['task'] );

    }

}
