<?php

namespace q\willow\context;

use q\core\helper as h;
use q\get;
use q\willow;
use q\willow\context;
use q\willow\render; 

class filter extends willow\context {

	/**
     * Run WP filter - buffer and return with matching field name
     *
     * @param       Array       $args
     * @since       1.3.0
	 * @uses		define
     * @return      Array
     */
    public static function get( $args = null ) {

		if ( \has_filter( $args['task'] ) ) {

			// @todo - filter to pass additional args to action / filter ##

			// buffer action ##
			ob_start();
			
			\apply_filters( $args['task'] ); // TODO ##

			$action = ob_get_clean();

			return [ $args['task'] => $action ];

		}

		// nothing coking ##
		return false;

	}

}
