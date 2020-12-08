<?php

namespace willow\render;

use willow;
use willow\core\helper as h;

class log {

	private 
		$plugin = false
	;

	/**
	 * Scan for partials in markup and convert to variables and $fields
	 * 
	 * @since 4.1.0
	*/
	public function __construct( \willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

    /**
     * Logging function
     * 
     */
    public function set( Array $args = null ){

		// w__log( $args );
		// w__log( 'e:>'.$args['task'] );
		// w__log( self::$args['config']['debug'] );

        if (
            isset( $args['config']['debug'] )
			&& 
				( 
					'1' === $args['config']['debug']
					||  true === $args['config']['debug']
				)
			// || 'false' == self::$args['config']['debug']
			// || ! self::$args['config']['debug']
        ) {

			// w__log( 'd:>Debugging is turned ON for : "'.$args['task'].'"' );

			// filter in group to debug ##
			\add_filter( 'willow/core/log/debug', function( $key ) use ( $args ){ 
				// w__log( $key );
				$return = is_array( $key ) ? array_merge( $key, [ $args['task'] ] ) : [ $key, $args['task'] ]; 
				// w__log( $return );
				return 
					$return;
				}
			);

			// return ##
			return true; 

		}

		// default ##
		// w__log( 'd:>Debugging is turned OFF for : "'.$args['task'].'"' );

		return false;

		// debug the group ##
		// return core\log::write( $args['task'] );

    }

}
