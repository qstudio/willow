<?php

namespace willow\context;

use willow\core\helper as h;
use willow;

class action {

	private
		$plugin = null // this 
	;

	/**
	 * 
     */
    public function __construct( \willow\plugin $plugin ){

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

		// w__log( $args );

		if ( \has_action( $args['task'] ) ) {

			// @todo - filter to pass additional args to action / filter ##

			// w__log( 'e:>has_action: '.$args['task'] );

			// buffer action ##
			ob_start();
			
			\do_action( $args['task'] );
			
			$string = ob_get_clean();

			// prepare return array ##
			$array = [ $args['task'] => $string ];

			// w__log( $string );
			// w__log( $array );

			return $array;

		}

		// nothing coking ##
		return false;

	}

}
