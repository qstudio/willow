<?php

namespace Q\willow\context;

use Q\willow\core\helper as h;
use Q\willow;

class action {

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
     * Run WP action - buffer and return with matching field name
     *
     * @param       Array       $args
     * @since       1.3.0
	 * @uses		define
     * @return      Array
     */
    public function get( $args = null ) {

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
