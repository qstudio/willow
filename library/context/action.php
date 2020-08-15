<?php

namespace q\willow\context;

use q\willow\core\helper as h;
// use q\get;
use q\willow;
use q\willow\context;
use q\willow\render; 

class action extends willow\context {

	/**
     * Run WP action - buffer and return with matching field name
     *
     * @param       Array       $args
     * @since       1.3.0
	 * @uses		define
     * @return      Array
     */
    public static function get( $args = null ) {

		// h::log( $args );

		if ( \has_action( $args['task'] ) ) {

			// @todo - filter to pass additional args to action / filter ##

			// h::log( 'e:>has_action: '.$args['task'] );

			// buffer action ##
			ob_start();
			
			\do_action( $args['task'] );
			
			$string = ob_get_clean();

			// h::log( $string );

			return [ $args['task'] => $string ];

		}

		// nothing coking ##
		return false;

	}

}
