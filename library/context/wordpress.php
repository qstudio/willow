<?php

namespace willow\context;

use willow\core\helper as h;
// use q\get;
use willow;
use willow\context;
use willow\render; 

class wordpress extends willow\context {


	/**
     * Get site option
     *
     * @param       Array       $args
     * @since       1.3.0
     * @return      String
     */
    public static function get_option( $args = null ) {

		return \get_site_option( $args );

	}
	


	/**
     * wp_enqueue_script
     *
     * @param       Array       $args
     * @since       1.3.0
     * @return      String
     */
    public static function enqueue_script( $args = null ) {

		h::log( $args );

		// check if we have a valid script to enquque ##
		// \wp_enqueue_script( $args[''] );

		return [  ];

	}


}
