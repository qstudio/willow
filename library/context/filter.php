<?php

namespace Q\willow\context;

use Q\willow\core\helper as h;
use Q\willow;

class filter {

	private
		$plugin = null // this 
	;

	/**
	 * 
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/**
     * Run WP filter - buffer and return with matching field name
     *
     * @param       Array       $args
     * @since       1.3.0
	 * @uses		define
     * @return      Array
     */
    public function get( $args = null ) {

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
